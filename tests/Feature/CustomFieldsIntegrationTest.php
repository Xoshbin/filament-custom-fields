<?php

use Xoshbin\CustomFields\Enums\CustomFieldType;
use Xoshbin\CustomFields\Models\CustomFieldDefinition;
use Xoshbin\CustomFields\Models\CustomFieldValue;
use Xoshbin\CustomFields\Tests\Models\Partner;

describe('Custom Fields Integration', function () {
    it('can create a complete custom fields workflow', function () {
        // Step 1: Create a custom field definition
        $definition = CustomFieldDefinition::factory()
            ->forModel(Partner::class)
            ->withComplexFields()
            ->create();

        // Step 2: Create a model instance
        $partner = new Partner(['name' => 'Acme Corp', 'email' => 'contact@acme.com']);
        $partner->save();

        // Step 3: Set custom field values
        $customValues = [
            'industry' => 'Technology',
            'priority' => 'high',
            'established_date' => '2020-01-15',
            'is_preferred' => true,
            'annual_revenue' => 5000000,
            'notes' => 'Important client with high potential for growth.',
        ];

        $partner->setCustomFieldValues($customValues);

        // Step 4: Verify values are stored correctly
        $retrievedValues = $partner->getCustomFieldValues();
        expect($retrievedValues['industry'])->toBe('Technology')
            ->and($retrievedValues['priority'])->toBe('high')
            ->and($retrievedValues['is_preferred'])->toBeTrue()
            ->and($retrievedValues['annual_revenue'])->toBe(5000000.0);

        // Step 5: Verify database records
        expect(CustomFieldValue::count())->toBe(6);
    });

    it('handles polymorphic relationships correctly across different models', function () {
        // Create definitions for different model types
        $partnerDefinition = CustomFieldDefinition::factory()
            ->forModel(Partner::class)
            ->withSimpleFields()
            ->create();

        $userDefinition = CustomFieldDefinition::factory()
            ->forModel('App\\Models\\User')
            ->withSimpleFields()
            ->create();

        // Create model instances
        $partner = new Partner(['name' => 'Test Partner']);
        $partner->save();

        // Set values for partner
        $partner->setCustomFieldValue('simple_text', 'Partner Value');

        // Verify correct association
        $partnerValue = CustomFieldValue::where('customizable_type', Partner::class)
            ->where('customizable_id', $partner->id)
            ->first();

        expect($partnerValue)->not->toBeNull()
            ->and($partnerValue->getValue())->toBe('Partner Value')
            ->and($partnerValue->customizable->id)->toBe($partner->id);
    });

    it('supports eager loading for performance optimization', function () {
        $definition = CustomFieldDefinition::factory()
            ->forModel(Partner::class)
            ->withComplexFields()
            ->create();

        // Create multiple partners with custom fields
        $partners = collect();
        for ($i = 1; $i <= 3; $i++) {
            $partner = new Partner(['name' => "Partner {$i}"]);
            $partner->save();
            $partner->setCustomFieldValue('industry', "Industry {$i}");
            $partners->push($partner);
        }

        // Test eager loading
        $loadedPartners = Partner::with('customFieldValues')->get();

        expect($loadedPartners)->toHaveCount(3);

        // Verify no additional queries are made when accessing custom field values
        foreach ($loadedPartners as $partner) {
            $values = $partner->getCustomFieldValues();
            expect($values)->toHaveKey('industry');
        }
    });

    it('handles cascade deletion correctly', function () {
        $definition = CustomFieldDefinition::factory()
            ->forModel(Partner::class)
            ->withSimpleFields()
            ->create();

        $partner = new Partner(['name' => 'Test Partner']);
        $partner->save();
        $partner->setCustomFieldValue('simple_text', 'Test Value');

        expect(CustomFieldValue::count())->toBe(1);

        // Delete the definition - should cascade to values
        $definition->delete();
        expect(CustomFieldValue::count())->toBe(0);
    });

    it('validates field values against field definitions', function () {
        $definition = CustomFieldDefinition::factory()
            ->forModel(Partner::class)
            ->create([
                'field_definitions' => [
                    [
                        'key' => 'priority',
                        'label' => 'Priority',
                        'type' => CustomFieldType::Select->value,
                        'required' => true,
                        'show_in_table' => false,
                        'options' => [
                            ['value' => 'high', 'label' => 'High'],
                            ['value' => 'medium', 'label' => 'Medium'],
                            ['value' => 'low', 'label' => 'Low'],
                        ],
                    ],
                ],
            ]);

        $partner = new Partner(['name' => 'Test Partner']);
        $partner->save();

        // Valid value should work
        $partner->setCustomFieldValue('priority', 'high');
        expect($partner->getCustomFieldValue('priority'))->toBe('high');

        // Invalid value should throw InvalidArgumentException
        expect(fn () => $partner->setCustomFieldValue('priority', 'invalid'))
            ->toThrow(\InvalidArgumentException::class);
    });

    it('handles complex field type scenarios correctly', function () {
        $definition = CustomFieldDefinition::factory()
            ->forModel(Partner::class)
            ->create([
                'field_definitions' => [
                    [
                        'key' => 'revenue',
                        'label' => 'Annual Revenue',
                        'type' => CustomFieldType::Number->value,
                        'required' => false,
                        'show_in_table' => false,
                        'validation_rules' => ['min:0', 'max:1000000000'],
                    ],
                    [
                        'key' => 'contract_date',
                        'label' => 'Contract Date',
                        'type' => CustomFieldType::Date->value,
                        'required' => false,
                        'show_in_table' => false,
                    ],
                ],
            ]);

        $partner = new Partner(['name' => 'Test Partner']);
        $partner->save();

        // Test number validation
        $partner->setCustomFieldValue('revenue', 500000);
        expect($partner->getCustomFieldValue('revenue'))->toBe(500000.0);

        // Test date handling
        $partner->setCustomFieldValue('contract_date', '2024-06-15');
        $dateValue = $partner->getCustomFieldValue('contract_date');
        expect($dateValue)->toBeInstanceOf(\Carbon\Carbon::class)
            ->and($dateValue->format('Y-m-d'))->toBe('2024-06-15');
    });
});
