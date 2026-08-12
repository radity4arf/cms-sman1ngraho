<?php

/**
 * ExtracurricularCategory Enum
 *
 * Enum kategori ekstrakurikuler — Olahraga, Seni, Akademik, Keagamaan.
 *
 * @author   DSE (Delia Tse)
 * @created  2026-08-08
 */

// [THECHNOLOGY-CRE] : ExtracurricularCategory enum

namespace App\Enums;

enum ExtracurricularCategory: string
{
    case Olahraga   = 'olahraga';
    case Seni       = 'seni';
    case Akademik   = 'akademik';
    case Keagamaan  = 'keagamaan';

    public function label(): string
    {
        return match ($this) {
            self::Olahraga  => 'Olahraga',
            self::Seni      => 'Seni',
            self::Akademik  => 'Akademik',
            self::Keagamaan => 'Keagamaan',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Olahraga  => 'success',
            self::Seni      => 'warning',
            self::Akademik  => 'primary',
            self::Keagamaan => 'danger',
        };
    }
}
