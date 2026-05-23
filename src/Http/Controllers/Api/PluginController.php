<?php

declare(strict_types=1);

namespace KBuilder\Http\Controllers\Api;

use KBuilder\Core\Plugin\PluginRegistry;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Illuminate\Database\Capsule\Manager as DB;
use Psr\Http\Message\UploadedFileInterface;
use ZipArchive;

class PluginController
{
    public function __construct(private readonly PluginRegistry $registry) {}

    public function index(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $allPlugins = $this->registry->all();
        $dbPlugins = DB::table('plugins')->get()->keyBy('slug')->toArray();

        foreach ($allPlugins as $plugin) {
            $slug = $plugin->getId();
            if (!isset($dbPlugins[$slug])) {
                DB::table('plugins')->insert([
                    'slug' => $slug,
                    'name' => $plugin->getName(),
                    'version' => $plugin->getVersion(),
                    'is_active' => true,
                    'is_system' => str_starts_with($slug, 'core-'),
                    'installed_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);
                // Refresh $dbPlugins
                $dbPlugins[$slug] = (object)[
                    'slug' => $slug, 
                    'is_active' => true, 
                    'is_system' => str_starts_with($slug, 'core-')
                ];
            }
        }

        $list = array_map(function ($plugin) use ($dbPlugins) {
            $slug = $plugin->getId();
            $db   = $dbPlugins[$slug] ?? null;
            return [
                'id'          => $slug,
                'name'        => $plugin->getName(),
                'version'     => $plugin->getVersion(),
                'description' => $plugin->getDescription(),
                'type'        => method_exists($plugin, 'getType') ? $plugin->getType() : 'extension',
                'is_active'   => $db ? (bool) $db->is_active : true,
                'is_system'   => $db ? (bool) $db->is_system : false,
            ];
        }, $this->registry->all());

        $q = trim(strtolower((string)($request->getQueryParams()['q'] ?? '')));
        if ($q !== '') {
            $list = array_filter($list, function($item) use ($q) {
                return str_contains(strtolower($item['name']), $q) || str_contains(strtolower($item['description']), $q);
            });
        }

        // Phân trang trên Array
        $page = (int)($request->getQueryParams()['page'] ?? 1);
        $limit = (int)($request->getQueryParams()['limit'] ?? 10);
        $total = count($list);
        
        $offset = ($page - 1) * $limit;
        $paginatedList = array_slice($list, $offset, $limit);

        return $this->json($response, [
            'success' => true,
            'data' => array_values($paginatedList),
            'pagination' => [
                'page' => $page,
                'limit' => $limit,
                'total' => $total,
                'totalPages' => ceil($total / $limit)
            ]
        ]);
    }

    public function toggle(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $slug = $args['slug'];
        $plugin = $this->registry->get($slug);

        if (!$plugin) {
            return $this->json($response, ['success' => false, 'error' => 'Plugin not found'], 404);
        }

        $dbPlugin = DB::table('plugins')->where('slug', $slug)->first();
        if (!$dbPlugin) {
            return $this->json($response, ['success' => false, 'error' => 'Plugin not installed in DB'], 404);
        }

        if ((bool)$dbPlugin->is_system) {
            return $this->json($response, ['success' => false, 'error' => 'System plugins cannot be disabled'], 403);
        }

        $newState = !(bool)$dbPlugin->is_active;

        // RÀNG BUỘC PHỤ THUỘC (DEPENDENCY GUARDS)
        if ($newState === true) {
            // Khi kích hoạt (Enable): Kiểm tra xem các plugin nó phụ thuộc đã bật chưa
            $deps = method_exists($plugin, 'getDependencies') ? $plugin->getDependencies() : [];
            foreach ($deps as $depSlug) {
                $depPluginDb = DB::table('plugins')->where('slug', $depSlug)->first();
                if (!$depPluginDb || !(bool)$depPluginDb->is_active) {
                    $depObj = $this->registry->get($depSlug);
                    $depName = $depObj ? $depObj->getName() : $depSlug;
                    return $this->json($response, [
                        'success' => false,
                        'error' => "Không thể kích hoạt plugin này vì nó phụ thuộc vào plugin '$depName' hiện đang bị tắt."
                    ], 400);
                }
            }
        } else {
            // Khi vô hiệu hóa (Disable): Kiểm tra xem có plugin active nào phụ thuộc vào plugin này không
            $allPlugins = $this->registry->all();
            $activePluginSlugs = DB::table('plugins')->where('is_active', true)->pluck('slug')->toArray();
            
            foreach ($allPlugins as $otherPlugin) {
                $otherSlug = $otherPlugin->getId();
                if ($otherSlug === $slug || !in_array($otherSlug, $activePluginSlugs, true)) {
                    continue;
                }
                
                $otherDeps = method_exists($otherPlugin, 'getDependencies') ? $otherPlugin->getDependencies() : [];
                if (in_array($slug, $otherDeps, true)) {
                    return $this->json($response, [
                        'success' => false,
                        'error' => "Không thể vô hiệu hóa plugin này vì plugin '{$otherPlugin->getName()}' đang hoạt động phụ thuộc vào nó."
                    ], 400);
                }
            }
        }

        DB::table('plugins')->where('slug', $slug)->update([
            'is_active'  => $newState,
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        return $this->json($response, ['success' => true, 'message' => 'Plugin updated']);
    }

    public function delete(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $slug = $args['slug'];
        
        $dbPlugin = DB::table('plugins')->where('slug', $slug)->first();
        if ($dbPlugin && (bool)$dbPlugin->is_system) {
            return $this->json($response, ['success' => false, 'error' => 'Không thể xóa System plugin'], 403);
        }

        // DỌN DẸP TÀI NGUYÊN (UNINSTALL LIFE-CYCLE)
        $plugin = $this->registry->get($slug);
        if ($plugin) {
            try {
                $plugin->uninstall();
            } catch (\Throwable $uninstErr) {
                // Tiếp tục gỡ bỏ file dù uninstall gặp sự cố để tránh kẹt hệ thống
            }
        }

        $pluginDir = KB_ROOT . '/plugins/' . $slug;
        if (is_dir($pluginDir)) {
            $this->deleteDir($pluginDir);
        }

        DB::table('plugins')->where('slug', $slug)->delete();

        return $this->json($response, ['success' => true, 'message' => 'Đã gỡ bỏ plugin']);
    }

    public function install(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $uploadedFiles = $request->getUploadedFiles();
        if (empty($uploadedFiles['plugin_zip'])) {
            return $this->json($response, ['success' => false, 'error' => 'No zip file provided'], 400);
        }

        /** @var UploadedFileInterface $uploadedFile */
        $uploadedFile = $uploadedFiles['plugin_zip'];
        if ($uploadedFile->getError() !== UPLOAD_ERR_OK) {
            return $this->json($response, ['success' => false, 'error' => 'Upload failed'], 400);
        }

        $extension = pathinfo($uploadedFile->getClientFilename(), PATHINFO_EXTENSION);
        if (strtolower($extension) !== 'zip') {
            return $this->json($response, ['success' => false, 'error' => 'Only .zip files are allowed'], 400);
        }

        $tempPath = KB_ROOT . '/storage/cache/' . uniqid('plugin_', true) . '.zip';
        $uploadedFile->moveTo($tempPath);

        $zip = new ZipArchive();
        if ($zip->open($tempPath) === true) {
            // Xác định slug của plugin từ tên thư mục gốc trong ZIP
            $pluginSlug = null;
            for ($i = 0; $i < $zip->numFiles; $i++) {
                $stat = $zip->statIndex($i);
                $path = $stat['name'];
                
                $parts = explode('/', trim($path, '/'));
                if (count($parts) >= 1) {
                    if ($pluginSlug === null) {
                        $pluginSlug = $parts[0];
                    } elseif ($pluginSlug !== $parts[0]) {
                        $zip->close();
                        unlink($tempPath);
                        return $this->json($response, ['success' => false, 'error' => 'Invalid plugin ZIP structure. It must contain exactly one root directory.'], 400);
                    }
                }
            }

            if (!$pluginSlug) {
                $zip->close();
                unlink($tempPath);
                return $this->json($response, ['success' => false, 'error' => 'Empty plugin ZIP'], 400);
            }

            // Kiểm tra xem plugin đã tồn tại chưa
            $pluginDest = KB_ROOT . '/plugins/' . $pluginSlug;
            if (is_dir($pluginDest)) {
                $zip->close();
                unlink($tempPath);
                return $this->json($response, ['success' => false, 'error' => "Plugin '$pluginSlug' already exists"], 400);
            }

            // Giải nén vào thư mục plugins
            $zip->extractTo(KB_ROOT . '/plugins/');
            $zip->close();
            unlink($tempPath);

            // Xác minh file Plugin.php có tồn tại không
            if (!file_exists($pluginDest . '/Plugin.php')) {
                $this->deleteDir($pluginDest);
                return $this->json($response, ['success' => false, 'error' => 'Missing Plugin.php in the plugin root directory'], 400);
            }

            return $this->json($response, ['success' => true, 'message' => 'Plugin installed successfully']);
        }

        @unlink($tempPath);
        return $this->json($response, ['success' => false, 'error' => 'Failed to open the ZIP file'], 400);
    }

    private function deleteDir(string $dirPath): void
    {
        if (!is_dir($dirPath)) {
            throw new \InvalidArgumentException("$dirPath must be a directory");
        }
        if (substr($dirPath, strlen($dirPath) - 1, 1) != '/') {
            $dirPath .= '/';
        }
        $files = glob($dirPath . '*', GLOB_MARK);
        foreach ($files as $file) {
            if (is_dir($file)) {
                $this->deleteDir($file);
            } else {
                unlink($file);
            }
        }
        rmdir($dirPath);
    }

    private function json(ResponseInterface $response, array $data, int $status = 200): ResponseInterface
    {
        $response->getBody()->write(json_encode($data, JSON_UNESCAPED_UNICODE));
        return $response->withHeader('Content-Type', 'application/json')->withStatus($status);
    }
}
