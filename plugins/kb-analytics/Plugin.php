<?php

declare(strict_types=1);

namespace KBuilder\Plugins\KbAnalytics;

use KBuilder\Core\Plugin\AbstractPlugin;
use KBuilder\Core\Component\ComponentRegistry;
use Slim\App;
use KBuilder\Core\Hook\HookSystem;
use KBuilder\Core\Admin\AdminMenuRegistry;

class Plugin extends AbstractPlugin
{
    public function getId(): string
    {
        return 'kb-analytics';
    }

    public function getName(): string
    {
        return 'Analytics & Tracking';
    }

    public function getVersion(): string
    {
        return '1.0.0';
    }

    public function getDescription(): string
    {
        return 'Thu thập dữ liệu truy cập và theo dõi hành vi người dùng trên website.';
    }

    public function registerRoutes(App $app): void
    {
        require_once __DIR__ . '/Api/AnalyticsController.php';
        $app->post('/api/analytics/track', [\KBuilder\Plugins\KbAnalytics\Api\AnalyticsController::class, 'track']);
    }

    public function registerAdminMenus(AdminMenuRegistry $registry): void
    {
        $registry->add(
            id: 'analytics',
            label: 'Thống kê',
            icon: 'BarChart2',
            route: '/plugins/kb-analytics',
            pluginId: $this->getId(),
            order: 20
        );
    }

    public function boot(HookSystem $hooks): void
    {
        // Chèn đoạn script tracking vào cuối trang
        $hooks->addFilter('kb_after_render_page', function ($html) {
            $script = <<<'HTML'
            <script>
            (function() {
                var sessionId = localStorage.getItem('kb_session_id');
                if (!sessionId) {
                    sessionId = crypto.randomUUID();
                    localStorage.setItem('kb_session_id', sessionId);
                }
                
                function trackEvent(eventType, meta = {}) {
                    fetch('/kbuilder/public/api/analytics/track', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({
                            event_type: eventType,
                            target_url: window.location.href,
                            referrer: document.referrer,
                            session_id: sessionId,
                            meta: meta
                        })
                    }).catch(err => console.error('Tracking Error:', err));
                }
                
                // Track pageview
                trackEvent('pageview');
            })();
            </script>
HTML;
            return str_replace('</body>', $script . "\n</body>", $html);
        });
    }
}
