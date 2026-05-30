<?php

declare(strict_types=1);

namespace KBuilder\Tests\Unit;

use KBuilder\Plugins\CoreBlocks\Components\VideoComponent;
use KBuilder\Plugins\CoreBlocks\Components\PricingComponent;
use KBuilder\Plugins\CoreBlocks\Components\GalleryComponent;
use KBuilder\Plugins\KbFormBuilder\Components\FormBuilderComponent;
use PHPUnit\Framework\TestCase;

class ComponentSchemaTest extends TestCase
{
    public static function componentProvider(): array
    {
        return [
            'video'   => [new VideoComponent(), 'core_video'],
            'pricing' => [new PricingComponent(), 'core_pricing'],
            'gallery' => [new GalleryComponent(), 'core_gallery'],
            'form'    => [new FormBuilderComponent(), 'kb_form'],
        ];
    }

    /**
     * @dataProvider componentProvider
     */
    public function testTypeMatches(object $component, string $expectedType): void
    {
        $this->assertSame($expectedType, $component->getType());
    }

    /**
     * @dataProvider componentProvider
     */
    public function testHasLabelAndTemplate(object $component): void
    {
        $this->assertNotEmpty($component->getLabel());
        $this->assertNotEmpty($component->getTemplate());
        $this->assertStringEndsWith('.twig', $component->getTemplate());
    }

    /**
     * @dataProvider componentProvider
     */
    public function testSchemaHasProperties(object $component): void
    {
        $schema = $component->getSchema();
        $this->assertArrayHasKey('properties', $schema);
        $this->assertNotEmpty($schema['properties']);
    }

    /**
     * @dataProvider componentProvider
     */
    public function testResolvePropsMergesDefaults(object $component): void
    {
        $defaults = $component->getDefaults();
        $resolved = $component->resolveProps([]);

        foreach ($defaults as $key => $value) {
            $this->assertArrayHasKey($key, $resolved);
            $this->assertSame($value, $resolved[$key]);
        }
    }

    public function testResolvePropsOverridesDefaults(): void
    {
        $component = new VideoComponent();
        $resolved = $component->resolveProps(['provider' => 'vimeo']);
        $this->assertSame('vimeo', $resolved['provider']);
    }

    public function testFormBuilderDefaultFields(): void
    {
        $component = new FormBuilderComponent();
        $defaults = $component->getDefaults();
        $this->assertArrayHasKey('fields', $defaults);
        $this->assertNotEmpty($defaults['fields']);
        $this->assertSame('name', $defaults['fields'][0]['name']);
    }
}
