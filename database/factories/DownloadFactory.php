<?php

/**
 * DownloadFactory — Factory untuk model Download (RT-10)
 *
 * @author   DSE (Delia Tse)
 * @created  2026-08-10
 */

// [THECHNOLOGY-CRE] : DownloadFactory

namespace Database\Factories;

use App\Enums\ContentStatus;
use App\Models\Download;
use App\Models\DownloadCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Download>
 */
class DownloadFactory extends Factory
{
    protected $model = Download::class;

    public function definition(): array
    {
        return [
            'title'                => fake()->words(3, true),
            'download_category_id' => DownloadCategory::factory(),
            'status'               => ContentStatus::Published->value,
            'is_active'            => true,
            'published_at'         => now(),
        ];
    }

    public function draft(): static
    {
        return $this->state(['status' => ContentStatus::Draft->value, 'published_at' => null]);
    }

    public function forCategory(DownloadCategory $category): static
    {
        return $this->state(['download_category_id' => $category->id]);
    }
}
