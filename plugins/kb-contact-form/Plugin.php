<?php

declare(strict_types=1);

namespace KBuilder\Plugins\KbContactForm;

use KBuilder\Core\Plugin\AbstractPlugin;
use KBuilder\Core\Hook\HookSystem;
use KBuilder\Core\Component\ComponentRegistry;
use KBuilder\Core\Admin\AdminMenuRegistry;
use Slim\App;
use Illuminate\Database\Capsule\Manager as DB;
use Illuminate\Database\Schema\Blueprint;

class Plugin extends AbstractPlugin
{
    public function getId(): string
    {
        return 'kb-contact-form';
    }

    public function getName(): string
    {
        return 'Contact Form (Biểu mẫu liên hệ)';
    }

    public function getVersion(): string
    {
        return '1.0.0';
    }

    public function getDescription(): string
    {
        return 'Plugin chuẩn cho phép người dùng chèn Biểu mẫu liên hệ vào trang và lưu thông tin vào CSDL.';
    }

    public function boot(HookSystem $hooks): void
    {
    }

    public function install(): void
    {
        if (!DB::schema()->hasTable('kb_form_submissions')) {
            DB::schema()->create('kb_form_submissions', function (Blueprint $table) {
                $table->id();
                $table->string('name')->nullable();
                $table->string('email')->nullable();
                $table->string('phone')->nullable();
                $table->text('message')->nullable();
                $table->string('ip_address')->nullable();
                $table->timestamps();
            });
        }
    }

    public function uninstall(): void
    {
        DB::schema()->dropIfExists('kb_form_submissions');
    }

    public function registerComponents(ComponentRegistry $registry): void
    {
        require_once __DIR__ . '/Components/ContactFormComponent.php';
        $registry->register(new \KBuilder\Plugins\KbContactForm\Components\ContactFormComponent());
    }

    public function registerRoutes(App $app): void
    {
        require_once __DIR__ . '/Controllers/ContactController.php';
        
        $app->post('/api/contact-form/submit', [\KBuilder\Plugins\KbContactForm\Controllers\ContactController::class, 'submit']);
    }
}
