<?php

declare(strict_types=1);

namespace KBuilder\Plugins\KbFormBuilder\Controllers;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Illuminate\Database\Capsule\Manager as DB;

/**
 * Nhận submission từ form tùy biến và lưu vào bảng kb_form_entries.
 * Toàn bộ field động được lưu dạng JSON ở cột `data`.
 */
class FormSubmitController
{
    public function submit(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $body = $request->getParsedBody();
        if (!$body) {
            $body = json_decode((string) $request->getBody(), true) ?? [];
        }

        $formKey = trim($body['form_key'] ?? 'default');
        $fields  = $body['fields'] ?? [];

        if (!is_array($fields) || empty($fields)) {
            return $this->json($response, ['success' => false, 'error' => 'Không có dữ liệu để gửi.'], 422);
        }

        // Validate email nếu có
        if (!empty($fields['email']) && !filter_var($fields['email'], FILTER_VALIDATE_EMAIL)) {
            return $this->json($response, ['success' => false, 'error' => 'Email không hợp lệ.'], 422);
        }

        // Lọc dữ liệu (cắt độ dài để tránh lạm dụng)
        $clean = [];
        foreach ($fields as $k => $val) {
            $clean[substr((string) $k, 0, 100)] = is_scalar($val) ? mb_substr((string) $val, 0, 5000) : null;
        }

        try {
            DB::table('form_entries')->insert([
                'site_id'    => 1,
                'form_key'   => substr($formKey, 0, 100),
                'name'       => isset($clean['name']) ? (string) $clean['name'] : null,
                'email'      => isset($clean['email']) ? (string) $clean['email'] : null,
                'data'       => json_encode($clean, JSON_UNESCAPED_UNICODE),
                'status'     => 'new',
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
                'created_at' => date('Y-m-d H:i:s'),
            ]);
        } catch (\Throwable $e) {
            return $this->json($response, ['success' => false, 'error' => 'Lỗi hệ thống khi lưu dữ liệu.'], 500);
        }

        return $this->json($response, ['success' => true, 'message' => 'Gửi thành công. Cảm ơn bạn!']);
    }

    private function json(ResponseInterface $response, array $data, int $status = 200): ResponseInterface
    {
        $response->getBody()->write(json_encode($data, JSON_UNESCAPED_UNICODE));
        return $response->withHeader('Content-Type', 'application/json')->withStatus($status);
    }
}
