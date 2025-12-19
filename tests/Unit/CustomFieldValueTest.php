<?php

use Carbon\Carbon;
use Xoshbin\CustomFields\Models\CustomFieldDefinition;
use Xoshbin\CustomFields\Models\CustomFieldValue;
use Xoshbin\CustomFields\Tests\Models\Partner;

describe('CustomFieldValue Model', function () {
    it('can store and retrieve text values correctly', function () {
        $definition = CustomFieldDefinition::factory()->withSimpleFields()->create();
        $partner = new Partner(['name' => 'Test Partner']);
        $partner->save();

        $value = CustomFieldValue::factory()
            ->forDefinition($definition)
            ->forModel(Partner::class, $partner->id)
            ->textValue('Test Industry')
            ->create(['field_key' => 'simple_text']);

        expect($value->getValue())->toBe('Test Industry')
            ->and($value->getDisplayValue())->toBe('Test Industry');
    });

    it('can store and retrieve number values with proper casting', function () {
        $definition = CustomFieldDefinition::factory()->create();
        $partner = new Partner(['name' => 'Test Partner']);
        $partner->save();

        $value = CustomFieldValue::factory()
            ->forDefinition($definition)
            ->forModel(Partner::class, $partner->id)
            ->numberValue(1000000.50)
            ->create(['field_key' => 'revenue']);

        expect($value->getValue())->toBe(1000000.50)
            ->and($value->getValue())->toBeFloat();
    });

    it('can store and retrieve boolean values correctly', function () {
        $definition = CustomFieldDefinition::factory()->create();
        $partner = new Partner(['name' => 'Test Partner']);
        $partner->save();

        $value = CustomFieldValue::factory()
            ->forDefinition($definition)
            ->forModel(Partner::class, $partner->id)
            ->booleanValue(true)
            ->create(['field_key' => 'is_preferred']);

        expect($value->getValue())->toBeTrue();

        // Check display value (might be 'Yes' or '1' depending on translation availability)
        $displayValue = $value->getDisplayValue();
        expect($displayValue)->toBeIn(['Yes', '1']);

        $falseValue = CustomFieldValue::factory()
            ->forDefinition($definition)
            ->forModel(Partner::class, $partner->id)
            ->booleanValue(false)
            ->create(['field_key' => 'is_active']);

        expect($falseValue->getValue())->toBeFalse();

        // Check display value (might be 'No' or '' depending on translation availability)
        $falseDisplayValue = $falseValue->getDisplayValue();
        expect($falseDisplayValue)->toBeIn(['No', '']);
    });

    it('can store and retrieve date values as Carbon instances', function () {
        $definition = CustomFieldDefinition::factory()->create([
            'field_definitions' => [
                [
                    'key' => 'established_date',
                    'label' => 'Established Date',
                    'type' => 'date',
                    'required' => false,
                    'show_in_table' => false,
                ],
            ],
        ]);
        $partner = new Partner(['name' => 'Test Partner']);
        $partner->save();

        $dateString = '2024-01-15';
        $value = CustomFieldValue::factory()
            ->forDefinition($definition)
            ->forModel(Partner::class, $partner->id)
            ->dateValue($dateString)
            ->create(['field_key' => 'established_date']);

        $retrievedValue = $value->getValue();
        expect($retrievedValue)->toBeInstanceOf(Carbon::class)
            ->and($retrievedValue->format('Y-m-d'))->toBe($dateString);
    });

    it('enforces unique constraint on field values per model instance', function () {
        $definition = CustomFieldDefinition::factory()->create();
        $partner = new Partner(['name' => 'Test Partner']);
        $partner->save();

        CustomFieldValue::factory()
            ->forDefinition($definition)
            ->forModel(Partner::class, $partner->id)
            ->create(['field_key' => 'industry']);

        expect(
            fn () => CustomFieldValue::factory()
                ->forDefinition($definition)
                ->forModel(Partner::class, $partner->id)
                ->create(['field_key' => 'industry'])
        )->toThrow(\Illuminate\Database\QueryException::class);
    });

    it('has correct relationships with definition and customizable model', function () {
        $definition = CustomFieldDefinition::factory()->create();
        $partner = new Partner(['name' => 'Test Partner']);
        $partner->save();

        $value = CustomFieldValue::factory()
            ->forDefinition($definition)
            ->forModel(Partner::class, $partner->id)
            ->create();

        expect($value->customFieldDefinition->id)->toBe($definition->id)
            ->and($value->definition->id)->toBe($definition->id)
            ->and($value->customizable)->toBeInstanceOf(Partner::class)
            ->and($value->customizable->id)->toBe($partner->id);
    });
});
