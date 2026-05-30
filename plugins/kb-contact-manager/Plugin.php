<?php

declare(strict_types=1);

namespace KBuilder\Plugins\KbContactManager;

use KBuilder\Core\Plugin\AbstractPlugin;
use KBuilder\Core\Hook\HookSystem;
use KBuilder\Core\Admin\AdminMenuRegistry;
use Illuminate\Database\Capsule\Manager as DB;
use Illuminate\Database\Schema\Blueprint;
use KBuilder\Http\Middleware\JwtMiddleware;
use Slim\App;

class Plugin extends AbstractPlugin
{
    public function getId(): string
    {
        return 'kb-contact-manager';
    }

    public function getName(): string
    {
        return 'Contact Manager (Quản lý liên hệ)';
    }

    public function getVersion(): string
    {
        return '1.0.0';
    }

    public function getDescription(): string
    {
        return 'Thu thập và quản lý các lượt gửi Form liên hệ (CRM).';
    }

    public function boot(HookSystem $hooks): void
    {
    }

    public function install(): void
    {
        // Bảng thật: kb_form_submissions (Schema tự thêm prefix 'kb_').
        // Bổ sung các cột phục vụ CRM nếu chưa có.
        if (DB::schema()->hasTable('form_submissions')) {
            DB::schema()->table('form_submissions', function (Blueprint $table) {
                if (!DB::schema()->hasColumn('form_submissions', 'status')) {
                    $table->string('status')->default('new');
                }
                if (!DB::schema()->hasColumn('form_submissions', 'notes')) {
                    $table->text('notes')->nullable();
                }
                if (!DB::schema()->hasColumn('form_submissions', 'priority')) {
                    $table->string('priority')->default('medium');
                }
            });
        }
    }

    public function uninstall(): void
    {
        if (DB::schema()->hasTable('form_submissions')) {
            DB::schema()->table('form_submissions', function (Blueprint $table) {
                if (DB::schema()->hasColumn('form_submissions', 'status')) {
                    $table->dropColumn('status');
                }
                if (DB::schema()->hasColumn('form_submissions', 'notes')) {
                    $table->dropColumn('notes');
                }
                if (DB::schema()->hasColumn('form_submissions', 'priority')) {
                    $table->dropColumn('priority');
                }
            });
        }
    }

    public function registerRoutes(App $app): void
    {
        require_once __DIR__ . '/Controllers/AdminContactController.php';

        $container = $app->getContainer();
        $jwt = $container->get(JwtMiddleware::class);

        $app->group('/api/admin/contacts', function ($g) {
            $g->get('', [\KBuilder\Plugins\KbContactManager\Controllers\AdminContactController::class, 'list']);
            $g->get('/stats/summary', [\KBuilder\Plugins\KbContactManager\Controllers\AdminContactController::class, 'stats']);
            $g->get('/{id}', [\KBuilder\Plugins\KbContactManager\Controllers\AdminContactController::class, 'get']);
            $g->put('/{id}', [\KBuilder\Plugins\KbContactManager\Controllers\AdminContactController::class, 'update']);
            $g->delete('/{id}', [\KBuilder\Plugins\KbContactManager\Controllers\AdminContactController::class, 'delete']);
        })->add($jwt);
    }

    public function registerAdminMenus(AdminMenuRegistry $registry): void
    {
        $registry->add(
            id: 'contacts',
            label: 'Khách liên hệ',
            icon: 'Mail',
            route: '/contacts',
            pluginId: $this->getId(),
            order: 25
        );
    }

    public function getDependencies(): array
    {
        return ['kb-contact-form'];
    }
}
