<?php

namespace Xoshbin\CustomFields;

use Filament\Contracts\Plugin;
use Filament\Panel;
use Xoshbin\CustomFields\Filament\Resources\CustomFieldDefinitionResource;

class CustomFieldsPlugin implements Plugin
{
    protected ?string $cluster = null;

    protected ?int $navigationSort = null;

    /** @var array<string, string> */
    protected array $modelTypes = [];

    public function getId(): string
    {
        return 'custom-fields';
    }

    public function cluster(?string $cluster): static
    {
        $this->cluster = $cluster;

        return $this;
    }

    public function getCluster(): ?string
    {
        return $this->cluster;
    }

    public function navigationSort(?int $sort): static
    {
        $this->navigationSort = $sort;

        return $this;
    }

    public function getNavigationSort(): ?int
    {
        return $this->navigationSort;
    }

    /**
     * Configure the model types that can have custom fields.
     *
     * @param  array<string, string>  $modelTypes  An array mapping model class names to their labels
     */
    public function modelTypes(array $modelTypes): static
    {
        $this->modelTypes = $modelTypes;

        return $this;
    }

    /**
     * Get the configured model types.
     *
     * @return array<string, string>
     */
    public function getModelTypes(): array
    {
        return $this->modelTypes;
    }

    public function register(Panel $panel): void
    {
        $panel
            ->resources([
                CustomFieldDefinitionResource::class,
            ]);
    }

    public function boot(Panel $panel): void
    {
        // Register the resource to the cluster after plugin configuration is complete
        // This is necessary because getCluster() is called during register(), but the
        // plugin configuration isn't available yet at that point
        if ($this->cluster !== null) {
            $reflection = new \ReflectionProperty($panel, 'clusteredComponents');
            $clusteredComponents = $reflection->getValue($panel);
            $clusteredComponents[$this->cluster][] = CustomFieldDefinitionResource::class;
            $reflection->setValue($panel, $clusteredComponents);
        }
    }

    public static function make(): static
    {
        return app(static::class);
    }

    public static function get(): static
    {
        /** @var static $plugin */
        $plugin = filament(app(static::class)->getId());

        return $plugin;
    }
}
