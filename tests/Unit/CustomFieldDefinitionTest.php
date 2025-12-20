<?php

use Xoshbin\CustomFields\Enums\CustomFieldType;
use Xoshbin\CustomFields\Models\CustomFieldDefinition;
use Xoshbin\CustomFields\Models\CustomFieldValue;
use Xoshbin\CustomFields\Tests\Models\Partner;

describe('CustomFieldDefinition Model', function () {
    it('can create a custom field definition with valid data', function () {
        $definition = CustomFieldDefinition::factory()->create([
            'model_type' => Partner::class,
            'name' => 'Partner Custom Fields',
            'description' => 'Custom fields for partner management',
            'is_active' => true,
        ]);

        expect($definition)->toBeInstanceOf(CustomFieldDefinition::class)
            ->and($definition->model_type)->toBe(Partner::class)
            ->and($definition->name)->toBe('Partner Custom Fields')
            ->and($definition->is_active)->toBeTrue();
    });

    it('validates field definitions structure correctly', function () {
        $definition = CustomFieldDefinition::factory()->create([
            'field_definitions' => [
                [
                    'key' => 'industry',
                    'label' => 'Industry',
                    'type' => CustomFieldType::Text->value,
                    'required' => false,
                    'show_in_table' => false,
                ],
            ],
        ]);

        $errors = $definition->validateFieldDefinitions();
        expect($errors)->toBeEmpty();
    });

    it('detects invalid field definitions', function () {
        $definition = new CustomFieldDefinition([
            'field_definitions' => [
                [
                    'key' => 'invalid_field',
                    'label' => 'Invalid Field',
                    'type' => '', // Empty type field
                ],
            ],
        ]);

        $errors = $definition->validateFieldDefinitions();
        expect($errors)->not->toBeEmpty();
    });

    it('enforces unique model_type constraint', function () {
        CustomFieldDefinition::factory()->create([
            'model_type' => Partner::class,
        ]);

        expect(fn () => CustomFieldDefinition::factory()->create([
            'model_type' => Partner::class,
        ]))->toThrow(\Illuminate\Database\QueryException::class);
    });

    it('has relationship with custom field values', function () {
        $definition = CustomFieldDefinition::factory()->create();
        $value = CustomFieldValue::factory()->forDefinition($definition)->create();

        expect($definition->customFieldValues)->toHaveCount(1)
            ->and($definition->customFieldValues->first()->id)->toBe($value->id);
    });

    it('returns field definitions as collection', function () {
        $definition = CustomFieldDefinition::factory()->withComplexFields()->create();
        $collection = $definition->getFieldDefinitionsCollection();

        expect($collection)->toBeInstanceOf(\Illuminate\Support\Collection::class)
            ->and($collection)->toHaveCount(6)
            ->and($collection->first()['key'])->toBe('industry');
    });
});
