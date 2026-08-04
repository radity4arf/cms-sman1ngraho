<?php

/**
 * CreateUser Page
 *
 * Halaman pembuatan pengguna baru — menangani sinkronisasi permission
 * (via CheckboxList) setelah record berhasil dibuat.
 *
 * @author   DSE (Delia Tse)
 * @created  2026-07-28
 * @updated  2026-08-04
 */

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
