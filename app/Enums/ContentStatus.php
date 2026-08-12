<?php

/**
 * ContentStatus Enum
 *
 * Enum untuk status konten — Draft → Publish workflow.
 * Backed enum (string) untuk digunakan di cast model dan Filament Select.
 *
 * @author   DSE (Delia Tse)
 * @created  2026-08-08
 */

// [THECHNOLOGY-CRE] : ContentStatus enum — workflow Draft→Publish untuk semua konten

namespace App\Enums;

enum ContentStatus: string
{
    case Draft     = 'draft';
    case Published = 'published';

    /**
     * Label bahasa Indonesia untuk tampilan Filament.
     */
    public function label(): string
    {
        return match ($this) {
            self::Draft     => 'Draft',
            self::Published => 'Publish',
        };
    }

    /**
     * Warna badge Filament.
     */
    public function color(): string
    {
        return match ($this) {
            self::Draft     => 'warning',
            self::Published => 'success',
        };
    }

    /**
     * Icon Filament.
     */
    public function icon(): string
    {
        return match ($this) {
            self::Draft     => 'heroicon-o-pencil',
            self::Published => 'heroicon-o-check-circle',
        };
    }
}
