<?php

/**
 * HeroSlideConfig — Single Source of Truth untuk default hero slide
 *
 * Tabel ini SELALU berisi tepat 1 row (id=1).
 * Dijamin oleh:
 *   - MySQL:   CHECK(id = 1) constraint
 *   - SQLite:  BEFORE INSERT trigger (block jika sudah ada row)
 *   - Model:   firstOrCreate(['id' => 1]) — idempoten
 *
 * FK: ON DELETE RESTRICT (MySQL) / trigger (SQLite)
 *   → slide default TIDAK BISA dihapus di level database, jalur manapun.
 *
 * @author   DSE (Delia Tse)
 * @created  2026-08-12 — Restrukturisasi arsitektur
 * @updated  2026-08-12 — CGX round 4: DB-level singleton + FK restrict
 */

// [THECHNOLOGY-CRE] : HeroSlideConfig model — single source of truth
// [THECHNOLOGY-MOD] : DB-level enforcement: CHECK/trigger singleton + FK restrict

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HeroSlideConfig extends Model
{
    protected $table = 'hero_slide_config';

    /**
     * Non-incrementing — id selalu 1 (singleton).
     */
    public $incrementing = false;

    protected $fillable = [
        'default_hero_slide_id',
    ];

    // ──────────────────────────────────────────────────
    // Relations
    // ──────────────────────────────────────────────────

    public function defaultSlide(): BelongsTo
    {
        return $this->belongsTo(HeroSlide::class, 'default_hero_slide_id');
    }

    // ──────────────────────────────────────────────────
    // Singleton Access
    // ──────────────────────────────────────────────────

    /**
     * Ambil singleton config row (id=1).
     * firstOrCreate(['id' => 1]) — idempoten, selalu tepat 1 row.
     */
    public static function current(): self
    {
        return static::firstOrCreate(
            ['id' => 1],
            ['default_hero_slide_id' => null]
        );
    }

    /**
     * Shortcut: ambil ID slide default saat ini (null kalau belum ada).
     */
    public static function defaultSlideId(): ?int
    {
        return static::current()->default_hero_slide_id;
    }

    /**
     * Shortcut: ambil model slide default saat ini (null kalau belum ada).
     */
    public static function currentDefaultSlide(): ?HeroSlide
    {
        $id = static::defaultSlideId();
        return $id ? HeroSlide::find($id) : null;
    }

    // ──────────────────────────────────────────────────
    // Guard: prevent null after first init
    // ──────────────────────────────────────────────────

    protected static function booted(): void
    {
        static::updating(function (self $config) {
            // Setelah default_hero_slide_id pertama kali diisi, TIDAK BOLEH
            // dikosongkan kembali kecuali melalui promoteAsDefault() yang
            // mengganti dengan slide lain (tetap non-null).
            if (
                $config->isDirty('default_hero_slide_id')
                && $config->getOriginal('default_hero_slide_id') !== null
                && $config->default_hero_slide_id === null
            ) {
                throw new \RuntimeException(
                    'Tidak dapat mengosongkan default slide. Promosikan slide lain terlebih dahulu.'
                );
            }
        });
    }
}
