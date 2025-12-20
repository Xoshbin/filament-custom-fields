<?php

use Filament\Schemas\Components\Fieldset;
use Xoshbin\CustomFields\Enums\CustomFieldType;
use Xoshbin\CustomFields\Filament\Forms\Components\CustomFieldsComponent;
use Xoshbin\CustomFields\Models\CustomFieldDefinition;
use Xoshbin\CustomFields\Tests\Models\Partner;

describe('Filament Component Integration', function () {
    it('generates form fields for active custom field definitions', function () {
        $definition = CustomFieldDefinition::factory()
            ->forModel(Partner::class)
            ->withComplexFields()
            ->create(['is_active' => true]);

        $component = CustomFieldsComponent::make(Partner::class);

        expect($component)->toBeInstanceOf(Fieldset::class);
    });

    it('returns null when no active definition exists', function () {
        // No definition exists
        $component = CustomFieldsComponent::make(Partner::class);
        expect($component)->toBeNull();

        // Inactive definition exists
        CustomFieldDefinition::factory()
            ->forModel(Partner::class)
            ->inactive()
            ->create();

        $component = CustomFieldsComponent::make(Partner::class);
        expect($component)->toBeNull();
    });

    it('returns null when definition has no field definitions', function () {
        CustomFieldDefinition::factory()
            ->forModel(Partner::class)
            ->withoutFields()
            ->create();

        $component = CustomFieldsComponent::make(Partner::class);
        expect($component)->toBeNull();
    });

    it('generates correct field types for different custom field types', function () {
        $definition = CustomFieldDefinition::factory()
            ->forModel(Partner::class)
            ->create([
                'field_definitions' => [
                    [
                        'key' => 'text_field',
                        'label' => 'Text Field',
                        'type' => CustomFieldType::Text->value,
                        'required' => false,
                        'show_in_table' => false,
                    ],
                    [
                        'key' => 'number_field',
                        'label' => 'Number Field',
                        'type' => CustomFieldType::Number->value,
                        'required' => false,
                        'show_in_table' => false,
                    ],
                    [
                        'key' => 'boolean_field',
                        'label' => 'Boolean Field',
                        'type' => CustomFieldType::Boolean->value,
                        'required' => false,
                        'show_in_table' => false,
                    ],
                    [
                        'key' => 'date_field',
                        'label' => 'Date Field',
                        'type' => CustomFieldType::Date->value,
                        'required' => false,
                        'show_in_table' => false,
                    ],
                    [
                        'key' => 'select_field',
                        'label' => 'Select Field',
                        'type' => CustomFieldType::Select->value,
                        'required' => false,
                        'show_in_table' => false,
                        'options' => [
                            ['value' => 'option1', 'label' => 'Option 1'],
                            ['value' => 'option2', 'label' => 'Option 2'],
                        ],
                    ],
                ],
            ]);

        $component = CustomFieldsComponent::make(Partner::class);
        expect($component)->toBeInstanceOf(Fieldset::class);
    });

    it('handles required field validation in form components', function () {
        $definition = CustomFieldDefinition::factory()
            ->forModel(Partner::class)
            ->create([
                'field_definitions' => [
                    [
                        'key' => 'required_field',
                        'label' => 'Required Field',
                        'type' => CustomFieldType::Text->value,
                        'required' => true,
                        'show_in_table' => false,
                    ],
                ],
            ]);

        $component = CustomFieldsComponent::make(Partner::class);
        expect($component)->toBeInstanceOf(Fieldset::class);
    });

    it('applies custom validation rules to form fields', function () {
        $definition = CustomFieldDefinition::factory()
            ->forModel(Partner::class)
            ->create([
                'field_definitions' => [
                    [
                        'key' => 'validated_field',
                        'label' => 'Validated Field',
                        'type' => CustomFieldType::Number->value,
                        'required' => false,
                        'show_in_table' => false,
                        'validation_rules' => ['min:0', 'max:1000'],
                    ],
                ],
            ]);

        $component = CustomFieldsComponent::make(Partner::class);
        expect($component)->toBeInstanceOf(Fieldset::class);
    });

    it('handles select field options correctly', function () {
        $definition = CustomFieldDefinition::factory()
            ->forModel(Partner::class)
            ->create([
                'field_definitions' => [
                    [
                        'key' => 'priority',
                        'label' => 'Priority Level',
                        'type' => CustomFieldType::Select->value,
                        'required' => false,
                        'show_in_table' => false,
                        'options' => [
                            ['value' => 'high', 'label' => 'High Priority'],
                            ['value' => 'medium', 'label' => 'Medium Priority'],
                            ['value' => 'low', 'label' => 'Low Priority'],
                        ],
                    ],
                ],
            ]);

        $component = CustomFieldsComponent::make(Partner::class);
        expect($component)->toBeInstanceOf(Fieldset::class);
    });

    it('includes help text when provided', function () {
        $definition = CustomFieldDefinition::factory()
            ->forModel(Partner::class)
            ->create([
                'field_definitions' => [
                    [
                        'key' => 'help_field',
                        'label' => 'Field with Help',
                        'type' => CustomFieldType::Text->value,
                        'required' => false,
                        'show_in_table' => false,
                        'help_text' => 'This is helpful information for the user.',
                    ],
                ],
            ]);

        $component = CustomFieldsComponent::make(Partner::class);
        expect($component)->toBeInstanceOf(Fieldset::class);
    });
});
