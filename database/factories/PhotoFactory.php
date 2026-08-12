<?php

/**
 * PhotoFactory — Factory untuk model Photo (RT-06 — Galeri: Foto)
 *
 * @author   DSE (Delia Tse)
 * @created  2026-08-10
 */

// [THECHNOLOGY-CRE] : PhotoFactory

namespace Database\Factories;

use App\Enums\ContentStatus;
use App\Models\Album;
use App\Models\Photo;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Photo>
 */
class PhotoFactory extends Factory
{
    protected $model = Photo::class;

    public function definition(): array
    {
        return [
            'album_id'    => Album::factory(),
            'caption'     => fake()->optional()->sentence(),
            'alt_text'    => fake()->optional()->words(3, true),
            'sort_order'  => fake()->numberBetween(0, 100),
            'status'      => ContentStatus::Published->value,
            'is_active'   => true,
            'published_at' => now(),
        ];
    }

    public function draft(): static
    {
        return $this->state(['status' => ContentStatus::Draft->value, 'published_at' => null]);
    }

    public function forAlbum(Album $album): static
    {
        return $this->state(['album_id' => $album->id]);
    }
}
