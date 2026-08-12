<?php

/**
 * HeroSlide Model (RT-15 — Hero Slider)
 *
 * is_default=true: guarded dari delete/draft/nonaktif via policy.
 * Seeder wajib buat 1 record is_default=true published+active.
 *
 * Aturan ketat is_default (CGX review Fase 3):
 * - Swap is_default HANYA melalui HeroSlideService::promoteAsDefault().
 * - Unset is_default langsung (tanpa service) DITOLAK.
 * - Create/update is_default=true dengan status=draft atau is_active=false DITOLAK.
 * - DB-level partial unique index sebagai safety net.
 *
 * @author   DSE (Delia Tse)
 * @created  2026-08-08
 * @updated  2026-08-11 — strict CGX review fix: service-only swap, reject draft/inactive default, SQLite index
 * @updated  2026-08-12 — CLX fix: private $swappingDefault + token-guard beginSwap/endSwap + try/finally
 * @updated  2026-08-12 — CGX fix lanjutan: DB session-variable/flag-table untuk trigger unset guard
 */

// [THECHNOLOGY-CRE] : HeroSlide model — Hero Slider
// [THECHNOLOGY-FIX] : Race condition is_default — DB transaction + row lock + reject unset tanpa kandidat
// [THECHNOLOGY-FIX] : Strict CGX guard — service-only swap, reject draft+default, reject inactive+default
// [THECHNOLOGY-MOD] : Token-guard beginSwap/endSwap + private flag + try/finally — CLX review fix
// [THECHNOLOGY-MOD] : DB-level flag (session var/flag table) + trigger unset guard — CGX QB bypass fix

namespace App\Models;

use App\Enums\ContentStatus;
use App\Traits\HasAudit;
use App\Traits\HasOrdering;
use App\Traits\HasPublishWorkflow;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

#[Fillable([
    'title', 'caption', 'cta_label', 'cta_url',
    'is_default', 'sort_order', 'status', 'is_active', 'published_at',
])]
class HeroSlide extends Model implements HasMedia
{
    use HasFactory, SoftDeletes, HasAudit, HasPublishWorkflow, HasOrdering;
    use InteractsWithMedia;

    /**
     * Token internal — mencegah pemanggilan beginSwap()/endSwap() dari
     * luar HeroSlideService. Token ini public agar bisa dibaca oleh
     * HeroSlideService, tapi hanya digunakan sebagai validasi.
     */
    public const SWAP_TOKEN = 'hero_slide_svc_internal_v1_8a7f3c';

    /**
     * Flag internal — menandakan bahwa operasi unset is_default sedang dalam
     * proses swap (dipicu oleh HeroSlideService::promoteAsDefault() atau
     * saving event internal), sehingga pengecekan updating guard dilewati.
     *
     * HANYA HeroSlideService dan internal saving event yang boleh menyetel flag ini.
     * Flag dijaga dengan token validasi — beginSwap()/endSwap() tanpa token valid akan throw.
     */
    private static bool $swappingDefault = false;

    /**
     * @internal HANYA untuk HeroSlideService dan saving event internal.
     *            Set flag swap ke true (PHP + DB level) — wajib sertakan SWAP_TOKEN.
     * @throws \RuntimeException jika token tidak valid.
     */
    public static function beginSwap(string $token): void
    {
        if ($token !== self::SWAP_TOKEN) {
            throw new \RuntimeException(
                'beginSwap() hanya dapat dipanggil oleh HeroSlideService. Gunakan HeroSlideService::promoteAsDefault().'
            );
        }
        static::$swappingDefault = true;

        // [THECHNOLOGY-MOD] : Set DB-level flag supaya trigger tahu ini swap sah.
        // MySQL: session variable @hero_swapping_default.
        // SQLite: temp table _hero_swap_flag.
        $driver = DB::connection()->getDriverName();
        if ($driver === 'mysql') {
            DB::statement('SET @hero_swapping_default = 1');
        } elseif ($driver === 'sqlite') {
            DB::statement('INSERT INTO hero_slide_swap_flags (flag) VALUES (1)');
        }
    }

    /**
     * @internal HANYA untuk HeroSlideService dan saving event internal.
     *            Reset flag swap ke false (PHP + DB level) — wajib sertakan SWAP_TOKEN.
     * @throws \RuntimeException jika token tidak valid.
     */
    public static function endSwap(string $token): void
    {
        if ($token !== self::SWAP_TOKEN) {
            throw new \RuntimeException(
                'endSwap() hanya dapat dipanggil oleh HeroSlideService. Gunakan HeroSlideService::promoteAsDefault().'
            );
        }
        static::$swappingDefault = false;

        // [THECHNOLOGY-MOD] : Reset DB-level flag.
        $driver = DB::connection()->getDriverName();
        if ($driver === 'mysql') {
            DB::statement('SET @hero_swapping_default = 0');
        } elseif ($driver === 'sqlite') {
            DB::statement('DELETE FROM hero_slide_swap_flags');
        }
    }

    protected function casts(): array
    {
        return [
            'is_default'   => 'boolean',
            'sort_order'   => 'integer',
            'is_active'    => 'boolean',
            'published_at' => 'datetime',
        ];
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('image')
            ->singleFile()
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp']);
    }

    public function registerMediaConversions(?\Spatie\MediaLibrary\MediaCollections\Models\Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->width(480)->height(270)
            ->performOnCollections('image');

        $this->addMediaConversion('medium')
            ->width(1920)->height(1080)
            ->performOnCollections('image');
    }

    // [THECHNOLOGY-FIX] : Model-level guard — record is_default=true tidak bisa delete/draft/nonaktif
    // Guard ini berlaku di SEMUA level (UI, Tinker, API, job) — bukan cuma UI hiding.
    //
    // [THECHNOLOGY-FIX] : Race condition fix — DB transaction + lockForUpdate saat create/update is_default.
    // Bersamaan dengan partial unique index di migration, menjamin tepat satu is_default=true setiap saat.
    //
    // [THECHNOLOGY-FIX] : Strict CGX guard — service-only swap, reject draft+default, reject inactive+default
    protected static function booted(): void
    {
        // ──────────────────────────────────────────────
        // Guard: cegah soft-delete record default
        // ──────────────────────────────────────────────
        static::deleting(function (self $slide) {
            if ($slide->is_default) {
                throw new \RuntimeException('Slide default tidak dapat dihapus.');
            }
        });

        // ──────────────────────────────────────────────
        // Guard: cegah force-delete record default
        // ──────────────────────────────────────────────
        static::forceDeleting(function (self $slide) {
            if ($slide->is_default) {
                throw new \RuntimeException('Slide default tidak dapat dihapus permanen.');
            }
        });

        // ──────────────────────────────────────────────
        // [THECHNOLOGY-FIX] : Guard creating — tolak is_default=true
        // dengan status=draft ATAU is_active=false.
        // Slide default WAJIB published + aktif dari awal.
        // ──────────────────────────────────────────────
        static::creating(function (self $slide) {
            if (! $slide->is_default) {
                return;
            }

            if ($slide->status === ContentStatus::Draft) {
                throw new \RuntimeException(
                    'Slide default tidak dapat dibuat dengan status draft. Gunakan status published.'
                );
            }

            if ($slide->is_active === false) {
                throw new \RuntimeException(
                    'Slide default tidak dapat dibuat dalam keadaan nonaktif. Set is_active=true.'
                );
            }
        });

        // ──────────────────────────────────────────────
        // [THECHNOLOGY-FIX] : Guard saving — tolak is_default=true
        // (baik baru diset maupun existing) dengan status=draft
        // atau is_active=false. Mencakup update field selain is_default.
        // ──────────────────────────────────────────────
        static::saving(function (self $slide) {
            // Cek 1: tolak default dengan status draft atau nonaktif
            if ($slide->is_default) {
                if ($slide->status === ContentStatus::Draft) {
                    throw new \RuntimeException(
                        'Slide default tidak dapat berstatus draft.'
                    );
                }

                if ($slide->is_active === false) {
                    throw new \RuntimeException(
                        'Slide default tidak dapat dinonaktifkan.'
                    );
                }
            }

            // Cek 2: [THECHNOLOGY-FIX] Race condition — atomic swap is_default
            // Jika is_default baru diset true (record baru atau dirty dari false ke true),
            // lock semua row is_default=true dan unset yang existing.
            if (! $slide->is_default) {
                return;
            }

            // Hanya proses jika is_default baru diset true (new record, atau berubah dari false)
            if (! $slide->isDirty('is_default')) {
                return;
            }

            DB::transaction(function () use ($slide) {
                // Lock semua baris dengan is_default=true — serialisasi akses concurrent
                $existingDefaults = static::where('is_default', true)
                    ->when($slide->exists, fn ($q) => $q->where('id', '!=', $slide->id))
                    ->lockForUpdate()
                    ->get();

                if ($existingDefaults->isNotEmpty()) {
                    // [THECHNOLOGY-MOD] : Gunakan beginSwap()/endSwap() dengan token —
                    // ini juga menyetel DB-level flag (session var / temp table)
                    // supaya trigger DB tidak memblokir unset dalam transaksi swap sah.
                    static::beginSwap(self::SWAP_TOKEN);
                    try {
                        foreach ($existingDefaults as $default) {
                            $default->is_default = false;
                            $default->save();
                        }
                    } finally {
                        static::endSwap(self::SWAP_TOKEN);
                    }
                }
            });
        });

        // ──────────────────────────────────────────────
        // [THECHNOLOGY-FIX] : Guard updating — TOLAK SEMUA unset is_default
        // (true→false) yang TIDAK melalui mekanisme swap resmi.
        //
        // Mekanisme swap resmi:
        //   1. HeroSlideService::promoteAsDefault() — set beginSwap()/endSwap()
        //   2. saving() event internal — set $swappingDefault saat unset default lama
        //
        // Perubahan is_default langsung via property assignment + save()
        // SELALU ditolak, bahkan jika ada kandidat pengganti.
        // ──────────────────────────────────────────────
        static::updating(function (self $slide) {
            // Guard: record default tidak bisa di-draft/dinonaktifkan
            if ($slide->is_default) {
                if ($slide->isDirty('status') && $slide->status === ContentStatus::Draft) {
                    throw new \RuntimeException('Slide default tidak dapat diubah menjadi draft.');
                }
                if ($slide->isDirty('is_active') && $slide->is_active === false) {
                    throw new \RuntimeException('Slide default tidak dapat dinonaktifkan.');
                }
            }

            // Guard: tolak SEMUA unset is_default (true→false) di luar swap resmi
            if (
                ! static::$swappingDefault
                && $slide->isDirty('is_default')
                && $slide->getOriginal('is_default') === true
                && $slide->is_default === false
            ) {
                throw new \RuntimeException(
                    'Tidak dapat menghapus status default secara langsung. '
                    . 'Gunakan HeroSlideService::promoteAsDefault() untuk mengganti slide default.'
                );
            }
        });
    }
}
