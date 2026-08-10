<?php

/**
 * HeroSlide Model (RT-15 — Hero Slider)
 *
 * is_default=true: guarded dari delete/draft/nonaktif via policy.
 * Seeder wajib buat 1 record is_default=true published+active.
 *
 * @author   DSE (Delia Tse)
 * @created  2026-08-08
 * @updated  2026-08-10 — race condition fix: transaction + lockForUpdate + reject unset
 */

// [THECHNOLOGY-CRE] : HeroSlide model — Hero Slider
// [THECHNOLOGY-FIX] : Race condition is_default — DB transaction + row lock + reject unset tanpa kandidat

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
     * Flag internal — menandakan bahwa operasi unset is_default sedang dalam
     * proses swap (dipicu oleh create/update slide default baru), sehingga
     * pengecekan "harus ada kandidat pengganti" dilewati.
     */
    protected static bool $swappingDefault = false;

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
        // [THECHNOLOGY-FIX] : Race condition — atomic swap is_default
        // Lock semua row is_default=true sebelum unset/create,
        // memaksa serialisasi concurrent request.
        // ──────────────────────────────────────────────
        static::saving(function (self $slide) {
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
                    static::$swappingDefault = true;

                    foreach ($existingDefaults as $default) {
                        $default->is_default = false;
                        $default->save();
                    }

                    static::$swappingDefault = false;
                }
            });
        });

        // ──────────────────────────────────────────────
        // Guard: cegah update status ke draft/nonaktif + tolak unset is_default tanpa kandidat
        // ──────────────────────────────────────────────
        static::updating(function (self $slide) {
            // Guard existing: record default tidak bisa di-draft/dinonaktifkan
            if ($slide->is_default) {
                if ($slide->isDirty('status') && $slide->status === ContentStatus::Draft) {
                    throw new \RuntimeException('Slide default tidak dapat diubah menjadi draft.');
                }
                if ($slide->isDirty('is_active') && $slide->is_active === false) {
                    throw new \RuntimeException('Slide default tidak dapat dinonaktifkan.');
                }
            }

            // [THECHNOLOGY-FIX] : Tolak update yang meng-unset is_default dari true ke false
            // tanpa ada kandidat pengganti (published + aktif). 
            // Dilewati jika ini bagian dari swap internal (static::$swappingDefault = true).
            if (
                ! static::$swappingDefault
                && $slide->isDirty('is_default')
                && $slide->getOriginal('is_default') === true
                && $slide->is_default === false
            ) {
                $replacementExists = static::where('id', '!=', $slide->id)
                    ->where('status', ContentStatus::Published->value)
                    ->where('is_active', true)
                    ->exists();

                if (! $replacementExists) {
                    throw new \RuntimeException(
                        'Tidak dapat menghapus status default — tidak ada slide lain yang published dan aktif sebagai pengganti.'
                    );
                }
            }
        });
    }
}
