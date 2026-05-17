<?php

declare(strict_types=1);

namespace KBuilder\Http\Controllers\Api;

use Illuminate\Database\Capsule\Manager as DB;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Ramsey\Uuid\Uuid;

class MediaController
{
    private function json(ResponseInterface $response, array $data, int $status = 200): ResponseInterface
    {
        $response->getBody()->write(json_encode($data, JSON_UNESCAPED_UNICODE));
        return $response->withHeader('Content-Type', 'application/json')->withStatus($status);
    }

    private function getSiteId(ServerRequestInterface $request): int
    {
        return (int) ($request->getAttribute('auth_site_id') ?? 1);
    }

    public function index(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $siteId = $this->getSiteId($request);
        $media = DB::table('media')
            ->where('site_id', $siteId)
            ->whereNull('deleted_at')
            ->orderBy('created_at', 'desc')
            ->get();

        return $this->json($response, ['success' => true, 'data' => $media]);
    }

    public function upload(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $uploadedFiles = $request->getUploadedFiles();
        if (empty($uploadedFiles['file'])) {
            return $this->json($response, ['success' => false, 'error' => 'Vui lòng chọn file để upload'], 400);
        }

        /** @var \Slim\Psr7\UploadedFile $file */
        $file = $uploadedFiles['file'];
        if ($file->getError() !== UPLOAD_ERR_OK) {
            return $this->json($response, ['success' => false, 'error' => 'Lỗi upload code: ' . $file->getError()], 400);
        }

        $siteId = $this->getSiteId($request);
        $userId = (int) $request->getAttribute('auth_user_id');

        $originalName = $file->getClientFilename();
        $ext = pathinfo($originalName, PATHINFO_EXTENSION);
        $uuid = Uuid::uuid4()->toString();
        $filename = $uuid . '.' . $ext;
        
        $yearMonth = date('Y/m');
        $uploadDir = KB_ROOT . '/public/uploads/' . $yearMonth;
        
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $filePath = $uploadDir . '/' . $filename;
        $file->moveTo($filePath);

        $url = '/uploads/' . $yearMonth . '/' . $filename;
        $size = filesize($filePath);
        $mime = mime_content_type($filePath);

        $width = null;
        $height = null;
        if (str_starts_with($mime, 'image/')) {
            $imgSize = @getimagesize($filePath);
            if ($imgSize) {
                $width = $imgSize[0];
                $height = $imgSize[1];
            }
        }

        $id = DB::table('media')->insertGetId([
            'uuid' => $uuid,
            'site_id' => $siteId,
            'disk' => 'local',
            'path' => 'uploads/' . $yearMonth . '/' . $filename,
            'url' => $url,
            'original_name' => $originalName,
            'filename' => $filename,
            'mime_type' => $mime,
            'size' => $size,
            'width' => $width,
            'height' => $height,
            'uploaded_by' => $userId,
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        $media = DB::table('media')->find($id);

        return $this->json($response, ['success' => true, 'data' => $media]);
    }

    public function destroy(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $id = (int) $args['id'];
        $siteId = $this->getSiteId($request);

        // Xóa logic (Soft delete) hoặc có thể thêm code unlink file nếu cần
        DB::table('media')
            ->where('id', $id)
            ->where('site_id', $siteId)
            ->update(['deleted_at' => date('Y-m-d H:i:s')]);

        return $this->json($response, ['success' => true]);
    }
}
