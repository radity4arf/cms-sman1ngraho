<?php

/**
 * ListAnnouncements — Halaman daftar Pengumuman
 *
 * @author   DSE (Delia Tse)
 * @created  2026-08-08
 * @updated  2026-08-10 — tambah file header
 */

// [THECHNOLOGY-CRE] : ListAnnouncements page
// [THECHNOLOGY-MOD] : Tambah file header & format ulang

namespace App\Filament\Resources\Announcements\Pages;

use App\Filament\Resources\Announcements\AnnouncementResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListAnnouncements extends ListRecords
{
    protected static string $resource = AnnouncementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
