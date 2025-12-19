<?php

namespace Xoshbin\CustomFields\Filament\Resources\CustomFieldDefinitionResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Xoshbin\CustomFields\Filament\Resources\CustomFieldDefinitionResource;

class ListCustomFieldDefinitions extends ListRecords
{
    protected static string $resource = CustomFieldDefinitionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label(__('custom-fields::custom_fields.actions.create')),
        ];
    }

    public function getTitle(): string
    {
        return __('custom-fields::custom_fields.plural_label');
    }
}
