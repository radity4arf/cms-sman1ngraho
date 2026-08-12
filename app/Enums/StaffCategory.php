<?php

/**
 * StaffCategory Enum
 *
 * Enum kategori staff — Guru vs Tenaga Kependidikan.
 *
 * @author   DSE (Delia Tse)
 * @created  2026-08-08
 */

// [THECHNOLOGY-CRE] : StaffCategory enum — Guru & Tenaga Kependidikan

namespace App\Enums;

enum StaffCategory: string
{
    case Guru                = 'guru';
    case TenagaKependidikan  = 'tenaga_kependidikan';

    public function label(): string
    {
        return match ($this) {
            self::Guru               => 'Guru',
            self::TenagaKependidikan => 'Tenaga Kependidikan',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Guru               => 'primary',
            self::TenagaKependidikan => 'info',
        };
    }
}
