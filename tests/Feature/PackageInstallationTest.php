<?php

use Illuminate\Support\Facades\Schema;
use Xoshbin\CustomFields\CustomFieldsPlugin;
use Xoshbin\CustomFields\CustomFieldsServiceProvider;
use Xoshbin\CustomFields\Facades\CustomFields;

describe('Package Installation and Setup', function () {
    it('registers the service provider correctly', function () {
        $providers = app()->getLoadedProviders();
        expect($providers)->toHaveKey(CustomFieldsServiceProvider::class);
    });

    it('creates the required database tables', function () {
        expect(Schema::hasTable('custom_field_definitions'))->toBeTrue()
            ->and(Schema::hasTable('custom_field_values'))->toBeTrue();
    });

    it('has correct table structure for custom_field_definitions', function () {
        expect(Schema::hasColumns('custom_field_definitions', [
            'id', 'model_type', 'field_definitions', 'name', 'description', 'is_active', 'created_at', 'updated_at',
        ]))->toBeTrue();
    });

    it('has correct table structure for custom_field_values', function () {
        expect(Schema::hasColumns('custom_field_values', [
            'id', 'custom_field_definition_id', 'customizable_type', 'customizable_id',
            'field_key', 'field_value', 'created_at', 'updated_at',
        ]))->toBeTrue();
    });

    it('registers the facade correctly', function () {
        expect(class_exists(CustomFields::class))->toBeTrue();
    });

    it('can instantiate the Filament plugin', function () {
        $plugin = CustomFieldsPlugin::make();
        expect($plugin)->toBeInstanceOf(CustomFieldsPlugin::class)
            ->and($plugin->getId())->toBe('custom-fields');
    });

    it('has correct package configuration', function () {
        // Test that the service provider class exists
        expect(class_exists(CustomFieldsServiceProvider::class))->toBeTrue();
    });

    it('loads required dependencies correctly', function () {
        // Test that Filament and other required packages are available
        expect(class_exists(\Filament\Forms\Components\TextInput::class))->toBeTrue()
            ->and(class_exists(\Spatie\LaravelPackageTools\Package::class))->toBeTrue();
    });
});
