<?php

declare(strict_types=1);

namespace KBuilder\Core\Plugin;

use KBuilder\Core\Hook\HookSystem;
use KBuilder\Core\Component\ComponentRegistry;
use KBuilder\Core\Admin\AdminMenuRegistry;
use Slim\App;

/**
 * PluginInterface — Mọi plugin phải implement interface này.
 *
 * Lifecycle:
 *   1. PluginLoader discover plugin
 *   2. Gọi boot() — plugin đăng ký hooks
 *   3. Gọi registerComponents() — plugin thêm component types
 *   4. Gọi registerRoutes() — plugin thêm API endpoints
 *   5. Gọi registerAdminMenus() — plugin thêm menu vào admin SPA (qua API)
 */
interface PluginInterface
{
    /**
     * Unique slug của plugin. VD: "core-hero", "blog", "products"
     */
    public function getId(): string;

    /**
     * Tên hiển thị. VD: "Hero Sections"
     */
    public function getName(): string;

    /**
     * Version theo semver. VD: "1.0.0"
     */
    public function getVersion(): string;

    /**
     * Mô tả ngắn về plugin.
     */
    public function getDescription(): string;

    /**
     * Lấy loại của plugin (system, content, extension, feature, etc.)
     */
    public function getType(): string;

    /**
     * Plugin boot — đăng ký actions/filters vào HookSystem.
     * Chạy TRƯỚC khi route được đăng ký.
     */
    public function boot(HookSystem $hooks): void;

    /**
     * Đăng ký component types vào ComponentRegistry.
     * VD: HeroComponent, HeroSplitComponent...
     */
    public function registerComponents(ComponentRegistry $registry): void;

    /**
     * Đăng ký Slim routes (API endpoints) của plugin.
     * VD: GET /api/products, POST /api/products
     */
    public function registerRoutes(App $app): void;

    /**
     * Đăng ký menu items cho Admin SPA.
     * Admin React sẽ fetch danh sách menu từ API.
     */
    public function registerAdminMenus(AdminMenuRegistry $registry): void;

    /**
     * Trả về danh sách file migration của plugin.
     * KBuilder sẽ chạy khi install/update plugin.
     *
     * @return string[] absolute paths to migration files
     */
    public function getMigrations(): array;
}
