# Custom Fields System for Laravel Filament

[![Latest Version on Packagist](https://img.shields.io/packagist/v/xoshbin/custom-fields.svg?style=flat-square)](https://packagist.org/packages/xoshbin/custom-fields)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/xoshbin/custom-fields/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/xoshbin/custom-fields/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/xoshbin/custom-fields/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/xoshbin/custom-fields/actions?query=workflow%3A"Fix+PHP+code+styling"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/xoshbin/custom-fields.svg?style=flat-square)](https://packagist.org/packages/xoshbin/custom-fields)

## Overview

The Custom Fields System for Laravel Filament provides a flexible way to add dynamic, user-defined fields to any model in your application. This system supports multiple field types, translations, validation, and seamless integration with Filament resources.

## Key Features

- **Single Record Per Model**: One `CustomFieldDefinition` record contains multiple field definitions using Filament repeater components
- **Multiple Field Types**: Text, Textarea, Number, Boolean, Date, and Select fields
- **Validation Support**: Built-in validation with custom rules
- **Filament Integration**: Seamless integration with existing Filament resources
- **Type-Safe Values**: Automatic type casting based on field types
- **Performance Optimized**: Proper indexing and eager loading support
- **Table Column Integration**: Display custom fields as columns in Filament tables

## Architecture

### Database Schema

#### CustomFieldDefinition Table
```sql
- id (primary key)
- model_type (string - fully qualified class name)
- name (JSON - translatable field names)
- description (JSON - translatable descriptions)
- field_definitions (JSON - array of field configurations)
- is_active (boolean)
- timestamps
- unique constraint on (model_type)
```

#### CustomFieldValue Table
```sql
- id (primary key)
- custom_field_definition_id (foreign key)
- customizable_type (string - polymorphic)
- customizable_id (integer - polymorphic)
- field_key (string)
- field_value (JSON - contains value and metadata)
- timestamps
- unique constraint on (custom_field_definition_id, customizable_type, customizable_id, field_key)
```

### Core Components

#### 1. Models

**CustomFieldDefinition Model** (`Xoshbin\CustomFields\Models\CustomFieldDefinition`)
- Manages field definitions for a specific model type
- Provides methods for managing field definitions

**CustomFieldValue Model** (`Xoshbin\CustomFields\Models\CustomFieldValue`)
- Stores actual field values with polymorphic relationships
- Handles type-safe value casting

#### 2. Traits

**HasCustomFields Trait** (`Xoshbin\CustomFields\Traits\HasCustomFields`)
- Provides custom fields functionality to models
- Methods: `getCustomFieldValues()`, `setCustomFieldValues()`, `getCustomFieldValue()`, `setCustomFieldValue()`
- Handles validation and relationship management

#### 3. Enums

**CustomFieldType Enum** (`Xoshbin\CustomFields\Enums\CustomFieldType`)
- Defines available field types: Text, Textarea, Number, Boolean, Date, Select
- Implements `HasColor`, `HasIcon`, `HasLabel` interfaces
- Provides validation rules and type casting methods

#### 4. Filament Components

**CustomFieldsComponent** (`Xoshbin\CustomFields\Filament\Forms\Components\CustomFieldsComponent`)
- Generates dynamic form fields based on custom field definitions
- Handles form data mutations for create and edit operations
- Supports all field types with proper validation

## Installation

You can install the package via composer:

```bash
composer require xoshbin/filament-custom-fields
```

You can publish and run the migrations with:

```bash
php artisan vendor:publish --tag="custom-fields-migrations"
php artisan migrate
```

You can publish the translations with:

```bash
php artisan vendor:publish --tag="custom-fields-translations"
```

## Plugin Configuration

Register the plugin in your Filament Panel Provider and configure the model types that can have custom fields:

```php
use Xoshbin\CustomFields\CustomFieldsPlugin;

public function panel(Panel $panel): Panel
{
    return $panel
        // ... other configurations
        ->plugins([
            CustomFieldsPlugin::make()
                ->modelTypes([
                    \App\Models\User::class => 'User',
                    \App\Models\Product::class => 'Product',
                    \App\Models\Post::class => 'Blog Post',
                ])
                ->cluster(SettingsCluster::class) // Optional: Add to a cluster
                ->navigationSort(10), // Optional: Set navigation order
        ]);
}
```

### Configuration Options

| Method | Description |
|--------|-------------|
| `modelTypes(array $types)` | **Required.** Defines which models can have custom fields. Keys are the fully qualified class names, values are the display labels. |
| `cluster(?string $cluster)` | Optional. Assigns the Custom Fields resource to a Filament Cluster. |
| `navigationSort(?int $sort)` | Optional. Sets the navigation sort order (default: 10). |

## Usage Guide

### 1. Setting Up Custom Fields for a Model

Add the `HasCustomFields` trait to your model:

```php
use Xoshbin\CustomFields\Traits\HasCustomFields;

class Partner extends Model
{
    use HasCustomFields;

    // ... rest of your model
}
```

### 2. Creating Custom Field Definitions

Navigate to the Custom Field Definitions resource in your Filament admin panel:

1. Select the model type (e.g., Partner)
2. Add field definitions using the repeater component:
   - **Key**: Unique identifier for the field (e.g., 'industry')
   - **Label**: Translatable display name
   - **Type**: Select from available field types
   - **Required**: Whether the field is mandatory
   - **Show in Table**: Whether to display as table column in Filament resources
   - **Help Text**: Optional help text for users
   - **Validation Rules**: Additional validation rules
   - **Options**: For select fields, define available options

### 3. Integrating with Existing Resources

Add the custom fields component to your Filament resource form:

```php
use Xoshbin\CustomFields\Filament\Forms\Components\CustomFieldsComponent;

public static function form(Form $form): Form
{
    return $form->schema([
        // ... your existing form components

        CustomFieldsComponent::make(YourModel::class),
    ]);
}
```

Update your create and edit pages to handle custom field data:

**CreatePage:**
```php
protected function handleRecordCreation(array $data): Model
{
    $customFieldsData = $data['custom_fields'] ?? [];
    unset($data['custom_fields']);

    $record = static::getModel()::create($data);

    if (!empty($customFieldsData)) {
        $record->setCustomFieldValues($customFieldsData);
    }

    return $record;
}
```

**EditPage:**
```php
protected function mutateFormDataBeforeFill(array $data): array
{
    $data['custom_fields'] = $this->record->getCustomFieldValues();
    return $data;
}

protected function mutateFormDataBeforeSave(array $data): array
{
    $customFieldsData = $data['custom_fields'] ?? [];
    unset($data['custom_fields']);

    if (!empty($customFieldsData)) {
        $this->record->setCustomFieldValues($customFieldsData);
    }

    return $data;
}
```

### 4. Working with Custom Field Values

**Setting Values:**
```php
// Set multiple values
$partner->setCustomFieldValues([
    'industry' => 'Technology',
    'priority' => 'high',
    'is_preferred' => true,
]);

// Set single value
$partner->setCustomFieldValue('industry', 'Finance');

// Set translatable value
$partner->setCustomFieldValue('description', [
    'en' => 'English description',
    'ar' => 'وصف باللغة العربية',
]);
```

**Getting Values:**
```php
// Get all values
$values = $partner->getCustomFieldValues();

// Get single value
$industry = $partner->getCustomFieldValue('industry');

// Get translatable value for specific locale
$description = $partner->getCustomFieldValue('description', 'ar');
```

## Field Types

### Text
- Single-line text input
- Supports translatable values
- Default validation: string, max:255

### Textarea
- Multi-line text input
- Supports translatable values
- Default validation: string

### Number
- Numeric input
- Supports integers and decimals
- Default validation: numeric

### Boolean
- Checkbox input
- Returns true/false values
- Default validation: boolean

### Date
- Date picker input
- Returns Carbon instances
- Default validation: date

### Select
- Dropdown selection
- Requires predefined options

- Validates against available options

## Validation

### Built-in Validation
- Required field validation
- Type-specific validation (numeric, date, boolean)
- Select field option validation
- Field existence validation

### Custom Validation Rules
Add custom validation rules in the field definition:
```php
'validation_rules' => ['min:0', 'max:1000000']
```

## Performance Considerations

### Database Optimization
- Proper indexing on foreign keys and polymorphic relationships
- Unique constraints to prevent duplicate values
- JSON field indexing for frequently queried fields

### Eager Loading
```php
// Load custom field values with the model
$partners = Partner::with('customFieldValues')->get();

// Access values efficiently
foreach ($partners as $partner) {
    $values = $partner->getCustomFieldValues(); // No additional queries
}
```

## Testing

The system includes comprehensive test coverage:

- **Unit Tests**: Model relationships, validation, type casting
- **Feature Tests**: CRUD operations, trait functionality
- **Filament Tests**: Form integration, validation, UI components

Run tests:
```bash
composer test
```

## Security Considerations

- **Input Validation**: All field values are validated before storage
- **Type Safety**: Values are cast to appropriate types
- **Access Control**: Respects Filament's built-in authorization
- **SQL Injection Prevention**: Uses Eloquent ORM and parameterized queries

## Troubleshooting

### Common Issues

1. **Custom fields not showing**: Ensure the model uses `HasCustomFields` trait and has an active definition
2. **Validation errors**: Check field definitions and ensure required fields are provided
3. **Translation issues**: Verify Spatie Translatable is properly configured
4. **Performance issues**: Use eager loading for bulk operations

### Debug Mode
Enable debug logging to troubleshoot issues:
```php
// In your model or component
Log::info('Custom field values:', $model->getCustomFieldValues());
```

## Future Enhancements

Potential improvements for future versions:
- File upload field type
- Rich text editor field type
- Conditional field visibility
- Field grouping and sections
- Import/export functionality
- Field usage analytics

## Support

For issues or questions:
1. Check the test files for usage examples
2. Review the source code documentation
3. Create an issue in the project repository

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](.github/CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Khoshbin](https://github.com/Xoshbin)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
