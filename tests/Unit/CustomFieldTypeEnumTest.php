<?php

use Carbon\Carbon;
use Xoshbin\CustomFields\Enums\CustomFieldType;

describe('CustomFieldType Enum', function () {
    it('provides correct labels for all field types', function () {
        expect(CustomFieldType::Text->getLabel())->toBeString()
            ->and(CustomFieldType::Textarea->getLabel())->toBeString()
            ->and(CustomFieldType::Number->getLabel())->toBeString()
            ->and(CustomFieldType::Boolean->getLabel())->toBeString()
            ->and(CustomFieldType::Date->getLabel())->toBeString()
            ->and(CustomFieldType::Select->getLabel())->toBeString();
    });

    it('provides correct colors for all field types', function () {
        expect(CustomFieldType::Text->getColor())->toBeString()
            ->and(CustomFieldType::Textarea->getColor())->toBeString()
            ->and(CustomFieldType::Number->getColor())->toBeString()
            ->and(CustomFieldType::Boolean->getColor())->toBeString()
            ->and(CustomFieldType::Date->getColor())->toBeString()
            ->and(CustomFieldType::Select->getColor())->toBeString();
    });

    it('provides correct icons for all field types', function () {
        expect(CustomFieldType::Text->getIcon())->toBeString()
            ->and(CustomFieldType::Textarea->getIcon())->toBeString()
            ->and(CustomFieldType::Number->getIcon())->toBeString()
            ->and(CustomFieldType::Boolean->getIcon())->toBeString()
            ->and(CustomFieldType::Date->getIcon())->toBeString()
            ->and(CustomFieldType::Select->getIcon())->toBeString();
    });

    it('provides correct validation rules for each field type', function () {
        expect(CustomFieldType::Text->getValidationRules())->toContain('string', 'max:255')
            ->and(CustomFieldType::Textarea->getValidationRules())->toContain('string')
            ->and(CustomFieldType::Number->getValidationRules())->toContain('numeric')
            ->and(CustomFieldType::Boolean->getValidationRules())->toContain('boolean')
            ->and(CustomFieldType::Date->getValidationRules())->toContain('date')
            ->and(CustomFieldType::Select->getValidationRules())->toContain('string');
    });

    it('casts text values correctly', function () {
        $value = CustomFieldType::Text->castValue(123);
        expect($value)->toBe('123')
            ->and($value)->toBeString();
    });

    it('casts number values correctly', function () {
        $intValue = CustomFieldType::Number->castValue('123');
        expect($intValue)->toBe(123.0)
            ->and($intValue)->toBeFloat();

        $floatValue = CustomFieldType::Number->castValue('123.45');
        expect($floatValue)->toBe(123.45)
            ->and($floatValue)->toBeFloat();

        $invalidValue = CustomFieldType::Number->castValue('invalid');
        expect($invalidValue)->toBe(0);
    });

    it('casts boolean values correctly', function () {
        expect(CustomFieldType::Boolean->castValue(1))->toBeTrue()
            ->and(CustomFieldType::Boolean->castValue('true'))->toBeTrue()
            ->and(CustomFieldType::Boolean->castValue(0))->toBeFalse()
            ->and(CustomFieldType::Boolean->castValue(''))->toBeFalse();
    });

    it('casts date values correctly', function () {
        $dateString = '2024-01-15';
        $castedValue = CustomFieldType::Date->castValue($dateString);

        expect($castedValue)->toBeInstanceOf(Carbon::class)
            ->and($castedValue->format('Y-m-d'))->toBe($dateString);

        $nullValue = CustomFieldType::Date->castValue(null);
        expect($nullValue)->toBeNull();
    });

    it('casts select values correctly', function () {
        $value = CustomFieldType::Select->castValue(123);
        expect($value)->toBe('123')
            ->and($value)->toBeString();
    });

    it('casts textarea values correctly', function () {
        $value = CustomFieldType::Textarea->castValue(123);
        expect($value)->toBe('123')
            ->and($value)->toBeString();
    });
});
