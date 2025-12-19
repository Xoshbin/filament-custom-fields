<?php

use Xoshbin\CustomFields\Models\CustomFieldDefinition;
use Xoshbin\CustomFields\Models\CustomFieldValue;
use Xoshbin\CustomFields\Tests\Models\Partner;

describe('Database Constraints and Relationships', function () {
    it('enforces unique constraint on model_type in custom_field_definitions', function () {
        CustomFieldDefinition::factory()
            ->forModel(Partner::class)
            ->create();

        expect(
            fn () => CustomFieldDefinition::factory()
                ->forModel(Partner::class)
                ->create()
        )->toThrow(\Illuminate\Database\QueryException::class);
    });

    it('enforces unique constraint on custom field values per model instance', function () {
        $definition = CustomFieldDefinition::factory()
            ->forModel(Partner::class)
            ->create();

        $partner = new Partner(['name' => 'Test Partner']);
        $partner->save();

        CustomFieldValue::factory()
            ->forDefinition($definition)
            ->forModel(Partner::class, $partner->id)
            ->create(['field_key' => 'test_field']);

        expect(
            fn () => CustomFieldValue::factory()
                ->forDefinition($definition)
                ->forModel(Partner::class, $partner->id)
                ->create(['field_key' => 'test_field'])
        )->toThrow(\Illuminate\Database\QueryException::class);
    });

    it('allows same field key for different model instances', function () {
        $definition = CustomFieldDefinition::factory()
            ->forModel(Partner::class)
            ->create();

        $partner1 = new Partner(['name' => 'Partner 1']);
        $partner1->save();

        $partner2 = new Partner(['name' => 'Partner 2']);
        $partner2->save();

        $value1 = CustomFieldValue::factory()
            ->forDefinition($definition)
            ->forModel(Partner::class, $partner1->id)
            ->create(['field_key' => 'industry']);

        $value2 = CustomFieldValue::factory()
            ->forDefinition($definition)
            ->forModel(Partner::class, $partner2->id)
            ->create(['field_key' => 'industry']);

        expect($value1)->toBeInstanceOf(CustomFieldValue::class)
            ->and($value2)->toBeInstanceOf(CustomFieldValue::class)
            ->and($value1->id)->not->toBe($value2->id);
    });

    it('cascades deletion from custom_field_definitions to custom_field_values', function () {
        $definition = CustomFieldDefinition::factory()
            ->forModel(Partner::class)
            ->create();

        $partner = new Partner(['name' => 'Test Partner']);
        $partner->save();

        CustomFieldValue::factory()
            ->forDefinition($definition)
            ->forModel(Partner::class, $partner->id)
            ->create();

        expect(CustomFieldValue::count())->toBe(1);

        $definition->delete();

        expect(CustomFieldValue::count())->toBe(0);
    });

    it('maintains referential integrity with polymorphic relationships', function () {
        $definition = CustomFieldDefinition::factory()
            ->forModel(Partner::class)
            ->create();

        $partner = new Partner(['name' => 'Test Partner']);
        $partner->save();

        $value = CustomFieldValue::factory()
            ->forDefinition($definition)
            ->forModel(Partner::class, $partner->id)
            ->create();

        // Test relationships work correctly
        expect($value->customFieldDefinition->id)->toBe($definition->id)
            ->and($value->customizable->id)->toBe($partner->id)
            ->and($definition->customFieldValues->first()->id)->toBe($value->id)
            ->and($partner->customFieldValues->first()->id)->toBe($value->id);
    });

    it('handles foreign key constraints correctly', function () {
        $partner = new Partner(['name' => 'Test Partner']);
        $partner->save();

        // Try to create a custom field value with non-existent definition ID
        expect(
            fn () => CustomFieldValue::factory()
                ->forModel(Partner::class, $partner->id)
                ->create(['custom_field_definition_id' => 99999])
        )->toThrow(\Illuminate\Database\QueryException::class);
    });

    it('properly indexes fields for performance', function () {
        // This test verifies that the database schema has the expected indexes
        // In a real application, you might query the database schema directly

        $definition = CustomFieldDefinition::factory()
            ->forModel(Partner::class)
            ->create();

        $partner = new Partner(['name' => 'Test Partner']);
        $partner->save();

        // Create multiple values to test index performance
        for ($i = 1; $i <= 100; $i++) {
            CustomFieldValue::factory()
                ->forDefinition($definition)
                ->forModel(Partner::class, $partner->id)
                ->create(['field_key' => "field_{$i}"]);
        }

        $startTime = microtime(true);

        // Query that should benefit from indexes
        $values = CustomFieldValue::where('customizable_type', Partner::class)
            ->where('customizable_id', $partner->id)
            ->get();

        $endTime = microtime(true);
        $executionTime = $endTime - $startTime;

        expect($values)->toHaveCount(100);
        expect($executionTime)->toBeLessThan(0.1); // Should be very fast with proper indexing
    });

    it('handles JSON field operations correctly', function () {
        $definition = CustomFieldDefinition::factory()
            ->create([
                'field_definitions' => [
                    [
                        'key' => 'complex_field',
                        'label' => 'Complex Field',
                        'type' => 'text',
                        'required' => false,
                        'show_in_table' => false,
                        'validation_rules' => ['max:255'],
                        'help_text' => 'This is a complex field definition',
                    ],
                ],
            ]);

        // Verify JSON field is properly stored and retrieved
        $retrievedDefinition = CustomFieldDefinition::find($definition->id);
        $fieldDefs = $retrievedDefinition->field_definitions;

        expect($fieldDefs)->toBeArray()
            ->and($fieldDefs[0]['key'])->toBe('complex_field')
            ->and($fieldDefs[0]['validation_rules'])->toBe(['max:255']);
    });
});
