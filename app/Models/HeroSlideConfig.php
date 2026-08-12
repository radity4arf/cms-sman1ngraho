<?php

/**
 * HeroSlideConfig — Single Source of Truth untuk default hero slide
 *
 * Tabel ini SELALU berisi tepat 1 row. Tidak ada kemungkinan 0 atau >1 default —
 * dijamin secara struktural oleh schema (1 row table, FK ON DELETE SET NULL).
 *
 * Menggantikan kolom boolean is_default di hero_slides yang terbukti rawan
 * (3 ronde CRITICAL CGX untuk masalah yang sama: bisa 0 default, bisa >1 default,
 * race condition, token/flag bypass-able).
 *
 * @author   DSE (Delia Tse)
 * @created  2026-08-12 — Restrukturisasi arsitektur: single source of truth
 */

// [THECHNOLOGY-CRE] : HeroSlideConfig model — single source of truth default slide

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HeroSlideConfig extends Model
{
    protected $table = 'hero_slide_config';

    protected $fillable = [
        'default_hero_slide_id',
    ];

    /**
     * Relasi ke slide yang sedang menjadi default.
     * ON DELETE SET NULL → kalau slide dihapus, config jadi NULL.
     */
    public function defaultSlide(): BelongsTo
    {
        return $this->belongsTo(HeroSlide::class, 'default_hero_slide_id');
    }

    /**
     * Ambil singleton config row. Selalu firstOrCreate — jamin tepat 1 row.
     */
    public static function current(): self
    {
        return static::firstOrCreate([], [
            'default_hero_slide_id' => null,
        ]);
    }

    /**
     * Shortcut: ambil ID slide default saat ini (null kalau tidak ada).
     */
    public static function defaultSlideId(): ?int
    {
        return static::current()->default_hero_slide_id;
    }

    /**
     * Shortcut: ambil model slide default saat ini (null kalau tidak ada).
     */
    public static function currentDefaultSlide(): ?HeroSlide
    {
        $id = static::defaultSlideId();
        return $id ? HeroSlide::find($id) : null;
    }
}
