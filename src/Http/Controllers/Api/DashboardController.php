<?php

declare(strict_types=1);

namespace KBuilder\Http\Controllers\Api;

use Illuminate\Database\Capsule\Manager as DB;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class DashboardController
{
    public function stats(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        try {
            // Get stats
            $pagesTotal = DB::table('pages')->whereNull('deleted_at')->count();
            $pagesPublished = DB::table('pages')->where('status', 'published')->whereNull('deleted_at')->count();
            $pagesDraft = DB::table('pages')->where('status', 'draft')->whereNull('deleted_at')->count();
            
            $mediaTotal = DB::table('media')->count();
            
            $pluginsActive = DB::table('plugins')->where('is_active', 1)->count();

            // Recent pages
            $recentPages = DB::table('pages')
                ->whereNull('deleted_at')
                ->orderBy('updated_at', 'desc')
                ->limit(5)
                ->get(['id', 'title', 'slug', 'updated_at', 'status']);

            $data = [
                'success' => true,
                'data' => [
                    'counts' => [
                        'pages_total' => $pagesTotal,
                        'pages_published' => $pagesPublished,
                        'pages_draft' => $pagesDraft,
                        'media_total' => $mediaTotal,
                        'plugins_active' => $pluginsActive,
                    ],
                    'recent_pages' => $recentPages
                ]
            ];

            return $this->json($response, $data);
        } catch (\Throwable $e) {
            return $this->json($response, ['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    private function json(ResponseInterface $response, array $data, int $status = 200): ResponseInterface
    {
        $response->getBody()->write(json_encode($data, JSON_UNESCAPED_UNICODE));
        return $response->withHeader('Content-Type', 'application/json')->withStatus($status);
    }
}
