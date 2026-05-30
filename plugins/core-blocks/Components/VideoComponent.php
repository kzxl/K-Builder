<?php

declare(strict_types=1);

namespace KBuilder\Plugins\CoreBlocks\Components;

use KBuilder\Core\Component\AbstractComponent;

class VideoComponent extends AbstractComponent
{
    public function getType(): string { return 'core_video'; }
    public function getLabel(): string { return 'Video'; }
    public function getIcon(): string { return 'Video'; }
    public function getGroup(): string { return 'Media'; }
    public function getTemplate(): string { return '@core-blocks/video.twig'; }

    public function getDefaults(): array
    {
        return [
            'provider'   => 'youtube',
            'video_id'   => 'dQw4w9WgXcQ',
            'url'        => '',
            'title'      => '',
            'aspect'     => '16-9',
            'max_width'  => '800px',
        ];
    }

    public function getSchema(): array
    {
        return [
            'type' => 'object',
            'version' => '1.0',
            'properties' => [
                'provider' => [
                    'type' => 'string',
                    'title' => 'Nguồn video',
                    'enum' => ['youtube', 'vimeo', 'file'],
                    'default' => 'youtube',
                ],
                'video_id' => [
                    'type' => 'string',
                    'title' => 'ID video (YouTube/Vimeo)',
                    'default' => 'dQw4w9WgXcQ',
                ],
                'url' => [
                    'type' => 'string',
                    'format' => 'image',
                    'title' => 'URL file video (khi chọn nguồn "file")',
                    'default' => '',
                ],
                'title' => [
                    'type' => 'string',
                    'title' => 'Tiêu đề (tùy chọn)',
                    'default' => '',
                ],
                'aspect' => [
                    'type' => 'string',
                    'title' => 'Tỉ lệ khung hình',
                    'enum' => ['16-9', '4-3', '1-1'],
                    'default' => '16-9',
                ],
                'max_width' => [
                    'type' => 'string',
                    'title' => 'Chiều rộng tối đa',
                    'default' => '800px',
                ],
            ],
        ];
    }
}
