<?php

use Xoshbin\CustomFields\CustomFieldsPlugin;

it('can configure model types via plugin', function () {
    $plugin = CustomFieldsPlugin::make()
        ->modelTypes([
            'App\\Models\\User' => 'User',
            'App\\Models\\Post' => 'Post',
        ]);

    expect($plugin->getModelTypes())
        ->toHaveKey('App\\Models\\User')
        ->toHaveKey('App\\Models\\Post')
        ->and($plugin->getModelTypes()['App\\Models\\User'])->toBe('User')
        ->and($plugin->getModelTypes()['App\\Models\\Post'])->toBe('Post');
});

it('returns empty array when no model types configured', function () {
    $plugin = CustomFieldsPlugin::make();

    expect($plugin->getModelTypes())->toBeEmpty();
});

it('can chain model types with other plugin configurations', function () {
    $plugin = CustomFieldsPlugin::make()
        ->modelTypes([
            'App\\Models\\User' => 'User',
        ])
        ->navigationSort(5);

    expect($plugin->getModelTypes())
        ->toHaveKey('App\\Models\\User')
        ->and($plugin->getNavigationSort())->toBe(5);
});

it('can configure cluster and model types together', function () {
    $plugin = CustomFieldsPlugin::make()
        ->cluster('App\\Filament\\Clusters\\Settings')
        ->modelTypes([
            'App\\Models\\Product' => 'Product',
        ]);

    expect($plugin->getCluster())->toBe('App\\Filament\\Clusters\\Settings')
        ->and($plugin->getModelTypes())->toHaveKey('App\\Models\\Product');
});
