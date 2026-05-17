<?php

declare(strict_types=1);

namespace KBuilder\Http\Controllers\Api;

use KBuilder\Core\Plugin\PluginRegistry;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Illuminate\Database\Capsule\Manager as DB;

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

        $pluginDir = KB_ROOT . '/plugins/' . $slug;
        if (is_dir($pluginDir)) {
            $this->deleteDir($pluginDir);
        }

        DB::table('plugins')->where('slug', $slug)->delete();

        return $this->json($response, ['success' => true, 'message' => 'Đã gỡ bỏ plugin']);
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
