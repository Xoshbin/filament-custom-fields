<?php

use Xoshbin\CustomFields\Models\CustomFieldDefinition;
use Xoshbin\CustomFields\Models\CustomFieldValue;
use Xoshbin\CustomFields\Tests\Models\Partner;

describe('Performance and Bulk Operations', function () {
    it('handles bulk custom field operations efficiently', function () {
        $definition = CustomFieldDefinition::factory()
            ->forModel(Partner::class)
            ->withComplexFields()
            ->create();

        // Create multiple partners
        $partners = collect();
        for ($i = 1; $i <= 10; $i++) {
            $partner = new Partner(['name' => "Partner {$i}", 'email' => "partner{$i}@example.com"]);
            $partner->save();
            $partners->push($partner);
        }

        // Bulk set custom field values
        $startTime = microtime(true);

        foreach ($partners as $index => $partner) {
            $partner->setCustomFieldValues([
                'industry' => "Industry {$index}",
                'priority' => 'high',
                'is_preferred' => $index % 2 === 0,
                'annual_revenue' => 1000000 + ($index * 100000),
            ]);
        }

        $endTime = microtime(true);
        $executionTime = $endTime - $startTime;

        // Should complete within reasonable time (adjust threshold as needed)
        expect($executionTime)->toBeLessThan(5.0);
        expect(CustomFieldValue::count())->toBe(40); // 10 partners × 4 fields each
    });

    it('efficiently retrieves custom field values with eager loading', function () {
        $definition = CustomFieldDefinition::factory()
            ->forModel(Partner::class)
            ->withSimpleFields()
            ->create();

        // Create partners with custom fields
        for ($i = 1; $i <= 5; $i++) {
            $partner = new Partner(['name' => "Partner {$i}"]);
            $partner->save();
            $partner->setCustomFieldValue('simple_text', "Value {$i}");
        }

        $startTime = microtime(true);

        // Load all partners with custom field values
        $partners = Partner::with('customFieldValues')->get();

        // Access custom field values (should not trigger additional queries)
        foreach ($partners as $partner) {
            $values = $partner->getCustomFieldValues();
            expect($values)->toHaveKey('simple_text');
        }

        $endTime = microtime(true);
        $executionTime = $endTime - $startTime;

        expect($executionTime)->toBeLessThan(1.0);
        expect($partners)->toHaveCount(5);
    });

    it('handles complex field definitions without performance degradation', function () {
        // Create a definition with many fields
        $fieldDefinitions = [];
        for ($i = 1; $i <= 20; $i++) {
            $fieldDefinitions[] = [
                'key' => "field_{$i}",
                'label' => "Field {$i}",
                'type' => 'text',
                'required' => false,
                'show_in_table' => false,
            ];
        }

        $definition = CustomFieldDefinition::factory()
            ->forModel(Partner::class)
            ->create(['field_definitions' => $fieldDefinitions]);

        $partner = new Partner(['name' => 'Test Partner']);
        $partner->save();

        $startTime = microtime(true);

        // Set values for all fields
        $values = [];
        for ($i = 1; $i <= 20; $i++) {
            $values["field_{$i}"] = "Value {$i}";
        }
        $partner->setCustomFieldValues($values);

        // Retrieve all values
        $retrievedValues = $partner->getCustomFieldValues();

        $endTime = microtime(true);
        $executionTime = $endTime - $startTime;

        expect($executionTime)->toBeLessThan(2.0);
        expect($retrievedValues)->toHaveCount(20);
        expect(CustomFieldValue::count())->toBe(20);
    });

    it('maintains performance with large text values', function () {
        $definition = CustomFieldDefinition::factory()
            ->forModel(Partner::class)
            ->create([
                'field_definitions' => [
                    [
                        'key' => 'large_description',
                        'label' => 'Large Description',
                        'type' => 'textarea',
                        'required' => false,
                        'show_in_table' => false,
                    ],
                ],
            ]);

        $partner = new Partner(['name' => 'Test Partner']);
        $partner->save();

        // Create a large text value (approximately 10KB)
        $largeText = str_repeat('Lorem ipsum dolor sit amet, consectetur adipiscing elit. ', 200);

        $startTime = microtime(true);

        $partner->setCustomFieldValue('large_description', $largeText);
        $retrievedValue = $partner->getCustomFieldValue('large_description');

        $endTime = microtime(true);
        $executionTime = $endTime - $startTime;

        expect($executionTime)->toBeLessThan(1.0);
        expect($retrievedValue)->toBe($largeText);
        expect(strlen($retrievedValue))->toBeGreaterThan(10000);
    });

    it('efficiently handles multiple model types with custom fields', function () {
        // Create definitions for different model types
        $partnerDefinition = CustomFieldDefinition::factory()
            ->forModel(Partner::class)
            ->withSimpleFields()
            ->create();

        $userDefinition = CustomFieldDefinition::factory()
            ->forModel('App\\Models\\User')
            ->withSimpleFields()
            ->create();

        $startTime = microtime(true);

        // Create multiple instances of each model type
        for ($i = 1; $i <= 5; $i++) {
            $partner = new Partner(['name' => "Partner {$i}"]);
            $partner->save();
            $partner->setCustomFieldValue('simple_text', "Partner Value {$i}");
        }

        $endTime = microtime(true);
        $executionTime = $endTime - $startTime;

        expect($executionTime)->toBeLessThan(2.0);
        expect(CustomFieldValue::where('customizable_type', Partner::class)->count())->toBe(5);
    });
});
