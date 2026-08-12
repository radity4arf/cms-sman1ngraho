<?php

/**
 * ListUsers — Halaman daftar Pengguna
 *
 * @author   DSE (Delia Tse)
 * @created  2026-07-28
 * @updated  2026-08-04 — implementasi permission granular
 * @updated  2026-08-10 — tambah file header
 */

// [THECHNOLOGY-CRE-DSE] : ListUsers page
// [THECHNOLOGY-MOD] : Tambah file header

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
