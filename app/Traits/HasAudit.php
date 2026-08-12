<?php

/**
 * HasAudit Trait
 *
 * Trait untuk otomatis mengisi created_by / updated_by pada model event
 * creating dan updating. FK ke users.id dengan ON DELETE SET NULL.
 * Dipakai oleh semua model konten Fase 3.
 *
 * @author   DSE (Delia Tse)
 * @created  2026-08-08
 */

// [THECHNOLOGY-CRE] : HasAudit trait — isi created_by/updated_by via model event

namespace App\Traits;

use Illuminate\Support\Facades\Auth;

trait HasAudit
{
    public static function bootHasAudit(): void
    {
        // Saat record pertama kali dibuat
        static::creating(function ($model) {
            if (Auth::check() && is_null($model->created_by)) {
                $model->created_by = Auth::id();
            }
            if (Auth::check() && is_null($model->updated_by)) {
                $model->updated_by = Auth::id();
            }
        });

        // Saat record diupdate
        static::updating(function ($model) {
            if (Auth::check()) {
                $model->updated_by = Auth::id();
            }
        });
    }

    /**
     * Relasi ke user yang membuat record.
     */
    public function creator()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    /**
     * Relasi ke user yang terakhir mengupdate record.
     */
    public function updater()
    {
        return $this->belongsTo(\App\Models\User::class, 'updated_by');
    }
}
