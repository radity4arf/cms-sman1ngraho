<?php

/**
 * DownloadCategoryFactory — Factory untuk model DownloadCategory (RT-10)
 *
 * @author   DSE (Delia Tse)
 * @created  2026-08-10
 */

// [THECHNOLOGY-CRE] : DownloadCategoryFactory

namespace Database\Factories;

use App\Models\DownloadCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DownloadCategory>
 */
class DownloadCategoryFactory extends Factory
{
    protected $model = DownloadCategory::class;

    public function definition(): array
    {
        $name = fake()->unique()->words(2, true);

        return [
            'name'       => $name,
            'slug'       => \Illuminate\Support\Str::slug($name),
            'sort_order' => fake()->numberBetween(0, 100),
            'is_active'  => true,
        ];
    }
}
