<?php

// [THECHNOLOGY-CRE-DSE] : EditUser page — handle sync permissions setelah user diupdate + populate data awal

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    // [THECHNOLOGY-CRE-DSE] : populate CheckboxList 'permissions' dengan data existing user saat edit
    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['permissions'] = $this->record->permissions()->pluck('name')->toArray();

        return $data;
    }

    // [THECHNOLOGY-CRE-DSE] : setelah record diupdate, sync ulang permissions (assign & cabut)
    protected function afterSave(): void
    {
        $permissions = $this->data['permissions'] ?? [];
        $this->record->syncPermissions($permissions);
    }
}
