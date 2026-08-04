<?php

// [THECHNOLOGY-CRE-DSE] : CreateUser page — handle sync permissions setelah user dibuat

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use Filament\Resources\Pages\CreateRecord;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    // [THECHNOLOGY-CRE-DSE] : setelah record dibuat, sync permissions dari input CheckboxList
    protected function afterCreate(): void
    {
        $permissions = $this->data['permissions'] ?? [];
        $this->record->syncPermissions($permissions);
    }
}
