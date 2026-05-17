import { Book, Code, Package, Zap } from 'lucide-react';

export default function PluginDocs() {
  return (
    <div className="animate-fade-in" style={{ maxWidth: '900px', margin: '0 auto' }}>
      <div className="kb-page-header">
        <div>
          <h1 className="kb-page-title">Tài liệu Phát triển Plugin</h1>
          <p className="kb-page-subtitle">Hướng dẫn tạo và mở rộng KBuilder thông qua hệ thống Plugin</p>
        </div>
      </div>

      <div style={{ display: 'grid', gap: '2rem' }}>
        
        {/* Section 1: Kiến trúc */}
        <div style={{ background: 'white', padding: '2rem', borderRadius: 'var(--kb-radius)', boxShadow: '0 1px 3px rgba(0,0,0,0.1)' }}>
          <div style={{ display: 'flex', alignItems: 'center', gap: '0.75rem', marginBottom: '1rem' }}>
            <Package size={24} style={{ color: 'hsl(var(--color-primary))' }} />
            <h2 style={{ fontSize: '1.25rem', fontWeight: 600 }}>1. Cấu trúc thư mục Plugin</h2>
          </div>
          <p style={{ color: 'hsl(var(--color-text-muted))', marginBottom: '1rem', lineHeight: 1.6 }}>
            Mỗi plugin là một thư mục nằm trong <code>kbuilder/plugins/</code>. Tên thư mục (ví dụ: <code>kb-my-plugin</code>) sẽ là ID của plugin.
            Bên trong thư mục, cần có file <code>Plugin.php</code> để hệ thống nhận diện.
          </p>
          <pre style={{ background: '#1e293b', color: '#f8fafc', padding: '1.25rem', borderRadius: '8px', fontSize: '0.875rem', overflowX: 'auto' }}>
            {`plugins/
  kb-my-plugin/
    Plugin.php           # File cấu hình bắt buộc
    Components/          # Chứa các Class Component (Kéo thả)
    templates/           # Chứa các file Twig template
    src/                 # Thư mục chứa code PHP khác (nếu có)`}
          </pre>
        </div>

        {/* Section 2: Khai báo Plugin */}
        <div style={{ background: 'white', padding: '2rem', borderRadius: 'var(--kb-radius)', boxShadow: '0 1px 3px rgba(0,0,0,0.1)' }}>
          <div style={{ display: 'flex', alignItems: 'center', gap: '0.75rem', marginBottom: '1rem' }}>
            <Code size={24} style={{ color: 'hsl(var(--color-primary))' }} />
            <h2 style={{ fontSize: '1.25rem', fontWeight: 600 }}>2. Khai báo Plugin.php</h2>
          </div>
          <p style={{ color: 'hsl(var(--color-text-muted))', marginBottom: '1rem', lineHeight: 1.6 }}>
            File <code>Plugin.php</code> phải extends <code>AbstractPlugin</code> và thực thi các hàm cơ bản:
          </p>
          <pre style={{ background: '#1e293b', color: '#f8fafc', padding: '1.25rem', borderRadius: '8px', fontSize: '0.875rem', overflowX: 'auto' }}>
            {`<?php
namespace KBuilder\\Plugins\\KbMyPlugin;

use KBuilder\\Core\\Plugin\\AbstractPlugin;
use KBuilder\\Core\\Component\\ComponentRegistry;

class Plugin extends AbstractPlugin {
    public function getId(): string { return 'kb-my-plugin'; }
    public function getName(): string { return 'My Custom Plugin'; }
    public function getVersion(): string { return '1.0.0'; }
    
    // Đăng ký component kéo thả vào Builder
    public function registerComponents(ComponentRegistry $registry): void {
        require_once __DIR__ . '/Components/MyComponent.php';
        $registry->register(new \\KBuilder\\Plugins\\KbMyPlugin\\Components\\MyComponent());
    }
}`}
          </pre>
        </div>

        {/* Section 3: Component Kéo Thả */}
        <div style={{ background: 'white', padding: '2rem', borderRadius: 'var(--kb-radius)', boxShadow: '0 1px 3px rgba(0,0,0,0.1)' }}>
          <div style={{ display: 'flex', alignItems: 'center', gap: '0.75rem', marginBottom: '1rem' }}>
            <Book size={24} style={{ color: 'hsl(var(--color-primary))' }} />
            <h2 style={{ fontSize: '1.25rem', fontWeight: 600 }}>3. Viết Builder Component</h2>
          </div>
          <p style={{ color: 'hsl(var(--color-text-muted))', marginBottom: '1rem', lineHeight: 1.6 }}>
            Một Component kéo thả phải extends <code>AbstractComponent</code>. Bạn cần khai báo <code>Schema</code> (cấu trúc dữ liệu JSON Schema) để Admin tự động render form nhập liệu, và hàm <code>render()</code> để xuất HTML ra ngoài trang chủ.
          </p>
          <pre style={{ background: '#1e293b', color: '#f8fafc', padding: '1.25rem', borderRadius: '8px', fontSize: '0.875rem', overflowX: 'auto' }}>
            {`<?php
namespace KBuilder\\Plugins\\KbMyPlugin\\Components;
use KBuilder\\Core\\Component\\AbstractComponent;

class MyComponent extends AbstractComponent {
    public function getType(): string { return 'kb-my-component'; }
    public function getLabel(): string { return 'Tên Component'; }
    public function getIcon(): string { return 'Star'; } // Lucide icon name

    public function getSchema(): array {
        return [
            'type' => 'object',
            'properties' => [
                'title' => [
                    'type' => 'string',
                    'title' => 'Tiêu đề',
                    'default' => 'Hello World'
                ]
            ]
        ];
    }

    public function render(array $data): string {
        $twig = $this->getTwig();
        // Render qua file Twig
        return $twig->render(
            file_get_contents(dirname(__DIR__) . '/templates/view.twig'), 
            $data
        );
    }
}`}
          </pre>
        </div>

        {/* Section 4: Hook & Filter */}
        <div style={{ background: 'white', padding: '2rem', borderRadius: 'var(--kb-radius)', boxShadow: '0 1px 3px rgba(0,0,0,0.1)' }}>
          <div style={{ display: 'flex', alignItems: 'center', gap: '0.75rem', marginBottom: '1rem' }}>
            <Zap size={24} style={{ color: 'hsl(var(--color-primary))' }} />
            <h2 style={{ fontSize: '1.25rem', fontWeight: 600 }}>4. Hệ thống Hook & Filter (Sắp ra mắt)</h2>
          </div>
          <p style={{ color: 'hsl(var(--color-text-muted))', marginBottom: '1rem', lineHeight: 1.6 }}>
            KBuilder hỗ trợ Hook (Action/Filter) tương tự WordPress để can thiệp vào vòng đời ứng dụng.
          </p>
          <ul style={{ listStyleType: 'disc', paddingLeft: '1.5rem', color: 'hsl(var(--color-text-muted))', display: 'flex', flexDirection: 'column', gap: '0.5rem' }}>
            <li><code>kb_before_render_page</code>: Chạy trước khi render toàn bộ trang.</li>
            <li><code>kb_after_render_page</code>: Can thiệp HTML đầu ra cuối cùng.</li>
            <li><code>kb_register_routes</code>: Đăng ký thêm custom API/Web routes.</li>
            <li><code>kb_admin_menu</code>: Thêm menu riêng cho Plugin vào Admin Dashboard.</li>
          </ul>
        </div>

      </div>
    </div>
  );
}
