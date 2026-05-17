<?php

declare(strict_types=1);

namespace KBuilder\Core\Twig;

use KBuilder\Core\Component\ComponentRegistry;
use KBuilder\Core\Hook\HookSystem;
use Twig\Environment;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use Illuminate\Database\Capsule\Manager as DB;

class ComponentExtension extends AbstractExtension
{
    private Environment $twig;

    public function __construct(
        private readonly ComponentRegistry $registry,
        private readonly HookSystem $hooks
    ) {}

    // We need setter for Twig Environment since extension is registered into it
    public function setTwig(Environment $twig): void
    {
        $this->twig = $twig;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('render_component', [$this, 'renderComponent'], ['is_safe' => ['html']]),
            new TwigFunction('render_children', [$this, 'renderChildren'], ['is_safe' => ['html']]),
        ];
    }

    /**
     * Render một Component riêng lẻ, xử lý Dynamic Data và Props resolution
     */
    public function renderComponent(array $section): string
    {
        if (isset($section['is_active']) && !$section['is_active']) {
            return '';
        }

        $type = $section['type'] ?? '';
        $props = $section['props'] ?? [];

        if (empty($type) || !$this->registry->has($type)) {
            return "<!-- Missing or unregistered component: {$type} -->";
        }

        $component = $this->registry->get($type);
        
        // Xử lý Dữ liệu Động (Dynamic Content)
        if (isset($props['data_source']) && is_array($props['data_source'])) {
            $ds = $props['data_source'];
            if (($ds['type'] ?? '') === 'posts') {
                $limit = (int)($ds['limit'] ?? 6);
                $posts = DB::table('posts') // Illuminate handles prefix
                    ->where('type', 'post')
                    ->where('status', 'published')
                    ->orderBy('created_at', 'desc')
                    ->limit($limit)
                    ->get()
                    ->toArray();
                
                $props['items'] = json_decode(json_encode($posts), true);
            }
        }

        // Lấy props mặc định từ Component class
        $resolvedProps = $component->resolveProps($props);

        // Truyền thêm children vào props để component có thể gọi {{ render_children(props.children) }}
        if (isset($section['children'])) {
            $resolvedProps['children'] = $section['children'];
        }

        try {
            $html = $this->twig->render($component->getTemplate(), array_merge($resolvedProps, [
                'component_type' => $type,
            ]));

            // Cho phép các plugin thay đổi nội dung HTML (Hook)
            return $this->hooks->applyFilters('kbuilder/render_section', $html, $type, $resolvedProps);
        } catch (\Exception $e) {
            return "<!-- Error rendering {$type}: " . $e->getMessage() . " -->";
        }
    }

    /**
     * Render mảng children (dùng cho Nested Layouts)
     */
    public function renderChildren(?array $children): string
    {
        if (empty($children)) {
            return '';
        }

        $html = '';
        foreach ($children as $child) {
            $html .= $this->renderComponent($child);
        }

        return $html;
    }
}
