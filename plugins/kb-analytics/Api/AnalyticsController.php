<?php

declare(strict_types=1);

namespace KBuilder\Plugins\KbAnalytics\Api;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Illuminate\Database\Capsule\Manager as DB;

class AnalyticsController
{
    public function track(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $data = $request->getParsedBody() ?? [];
        $eventType = $data['event_type'] ?? 'pageview';
        $targetUrl = $data['target_url'] ?? $_SERVER['HTTP_REFERER'] ?? null;
        
        $ip = $_SERVER['REMOTE_ADDR'] ?? null;
        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0];
        }

        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? null;
        $referrer = $data['referrer'] ?? null;

        // Bỏ qua bot
        if ($userAgent && preg_match('/bot|crawl|slurp|spider|mediapartners/i', $userAgent)) {
            $response->getBody()->write(json_encode(['success' => true, 'bot' => true]));
            return $response->withHeader('Content-Type', 'application/json');
        }

        // Validate cơ bản
        if (empty($targetUrl)) {
            $response->getBody()->write(json_encode(['success' => false, 'error' => 'Missing target_url']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        // Lấy site_id mặc định là 1
        $siteId = 1;

        try {
            DB::table('analytics_events')->insert([
                'site_id' => $siteId,
                'event_type' => $eventType,
                'target_url' => $targetUrl,
                'target_id' => $data['target_id'] ?? null,
                'session_id' => $data['session_id'] ?? null,
                'ip' => $ip,
                'referrer' => $referrer,
                'user_agent' => $userAgent,
                'meta' => isset($data['meta']) ? json_encode($data['meta']) : null,
                'created_at' => date('Y-m-d H:i:s')
            ]);
            
            $response->getBody()->write(json_encode(['success' => true]));
        } catch (\Exception $e) {
            $response->getBody()->write(json_encode(['success' => false, 'error' => 'Database error']));
            return $response->withStatus(500);
        }

        return $response->withHeader('Content-Type', 'application/json');
    }
}
