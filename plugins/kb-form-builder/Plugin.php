<?php

declare(strict_types=1);

namespace KBuilder\Plugins\KbFormBuilder;

use KBuilder\Core\Plugin\AbstractPlugin;
use KBuilder\Core\Hook\HookSystem;
use KBuilder\Core\Component\ComponentRegistry;
use KBuilder\Core\Admin\AdminMenuRegistry;
use KBuilder\Http\Middleware\JwtMiddleware;
use Slim\App;
use Illuminate\Database\Capsule\Manager as DB;
use Illuminate\Database\Schema\Blueprint;

class Plugin extends AbstractPlugin
{
    public function getId(): string { return 'kb-form-builder'; }
    public function getName(): string { return 'Form Builder (Trình tạo biểu mẫu)'; }
    public function getVersion(): string { return '1.0.0'; }
    public function getDescription(): string { return 'Tạo biểu mẫu tùy biến bằng kéo thả, lưu submission linh hoạt theo dạng JSON.'; }

    public function install(): void
    {
        // Bảng thật: kb_form_entries (Schema tự thêm prefix 'kb_')
        if (!DB::schema()->hasTable('form_entries')) {
            DB::schema()->create('form_entries', function (Blueprint $table) {
                $table->id();
                $table->unsignedInteger('site_id')->default(1);
                $table->string('form_key', 100)->default('default');
                $table->string('name')->nullable();
                $table->string('email')->nullable();
                $table->json('data')->nullable();
                $table->string('status', 30)->default('new');
                $table->string('ip_address', 45)->nullable();
                $table->dateTime('created_at')->nullable();
                $table->index(['form_key', 'created_at'], 'idx_form_entries_key');
            });
        }
    }

    public function uninstall(): void
    {
        DB::schema()->dropIfExists('form_entries');
    }

    public function registerComponents(ComponentRegistry $registry): void
    {
        require_once __DIR__ . '/Components/FormBuilderComponent.php';
        $registry->register(new \KBuilder\Plugins\KbFormBuilder\Components\FormBuilderComponent());
    }

    public function registerRoutes(App $app): void
    {
        require_once __DIR__ . '/Controllers/FormSubmitController.php';
        require_once __DIR__ . '/Controllers/AdminFormController.php';

        // Public: nhận submission (không cần JWT)
        $app->post('/api/forms/submit', [\KBuilder\Plugins\KbFormBuilder\Controllers\FormSubmitController::class, 'submit']);

        // Admin: xem/xóa submission (JWT-protected)
        $container = $app->getContainer();
        if ($container === null) {
            return;
        }
        $jwt = $container->get(JwtMiddleware::class);

        $app->group('/api/admin/form-entries', function ($g) {
            $g->get('', [\KBuilder\Plugins\KbFormBuilder\Controllers\AdminFormController::class, 'index']);
            $g->delete('/{id}', [\KBuilder\Plugins\KbFormBuilder\Controllers\AdminFormController::class, 'destroy']);
        })->add($jwt);
    }

    public function registerAdminMenus(AdminMenuRegistry $registry): void
    {
        $registry->add(
            id: 'form-entries',
            label: 'Biểu mẫu',
            icon: 'ClipboardList',
            route: '/plugins/kb-form-builder',
            pluginId: $this->getId(),
            order: 26
        );
    }

    public function boot(HookSystem $hooks): void
    {
    }
}
