<?php

/**
 * AlbumFactory — Factory untuk model Album (RT-06)
 *
 * @author   DSE (Delia Tse)
 * @created  2026-08-10
 */

// [THECHNOLOGY-CRE] : AlbumFactory

namespace Database\Factories;

use App\Enums\ContentStatus;
use App\Models\Album;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Album>
 */
class AlbumFactory extends Factory
{
    protected $model = Album::class;

    public function definition(): array
    {
        $name = fake()->unique()->words(3, true);

        return [
            'name'        => $name,
            'slug'        => \Illuminate\Support\Str::slug($name),
            'description' => fake()->optional()->sentence(),
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
}
