<?php

namespace Xoshbin\CustomFields\Filament\Resources;

use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Xoshbin\CustomFields\CustomFieldsPlugin;
use Xoshbin\CustomFields\Filament\Resources\CustomFieldDefinitionResource\Pages\CreateCustomFieldDefinition;
use Xoshbin\CustomFields\Filament\Resources\CustomFieldDefinitionResource\Pages\EditCustomFieldDefinition;
use Xoshbin\CustomFields\Filament\Resources\CustomFieldDefinitionResource\Pages\ListCustomFieldDefinitions;
use Xoshbin\CustomFields\Filament\Resources\CustomFieldDefinitionResource\Schemas\CustomFieldDefinitionForm;
use Xoshbin\CustomFields\Filament\Resources\CustomFieldDefinitionResource\Tables\CustomFieldDefinitionsTable;
use Xoshbin\CustomFields\Models\CustomFieldDefinition;

class CustomFieldDefinitionResource extends Resource
{
    protected static ?string $model = CustomFieldDefinition::class;

    protected static bool $isScopedToTenant = false;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-adjustments-horizontal';

    protected static ?string $recordTitleAttribute = 'name';

    public static function getCluster(): ?string
    {
        try {
            return CustomFieldsPlugin::get()->getCluster();
        } catch (\Throwable $e) {
            return null;
        }
    }

    public static function getNavigationSort(): ?int
    {
        try {
            return CustomFieldsPlugin::get()->getNavigationSort() ?? 10;
        } catch (\Throwable $e) {
            return 10;
        }
    }

    /**
     * @return array<string>
     */
    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'description', 'model_type'];
    }

    public static function getNavigationLabel(): string
    {
        return __('custom-fields::custom_fields.navigation_label');
    }

    public static function getModelLabel(): string
    {
        return __('custom-fields::custom_fields.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('custom-fields::custom_fields.plural_label');
    }

    public static function form(Schema $schema): Schema
    {
        return CustomFieldDefinitionForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CustomFieldDefinitionsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCustomFieldDefinitions::route('/'),
            'create' => CreateCustomFieldDefinition::route('/create'),
            'edit' => EditCustomFieldDefinition::route('/{record}/edit'),
        ];
    }

    /**
     * Get available model types for custom fields.
     * Returns the model types configured via the plugin.
     *
     * @return array<string, string>
     */
    public static function getAvailableModelTypes(): array
    {
        try {
            return CustomFieldsPlugin::get()->getModelTypes();
        } catch (\Throwable $e) {
            return [];
        }
    }
}
