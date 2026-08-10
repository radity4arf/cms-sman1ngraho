<?php

/**
 * HeroSlideFactory — Factory untuk model HeroSlide (RT-15)
 *
 * @author   DSE (Delia Tse)
 * @created  2026-08-10
 */

// [THECHNOLOGY-CRE] : HeroSlideFactory

namespace Database\Factories;

use App\Enums\ContentStatus;
use App\Models\HeroSlide;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<HeroSlide>
 */
class HeroSlideFactory extends Factory
{
    protected $model = HeroSlide::class;

    public function definition(): array
    {
        return [
            'title'      => fake()->sentence(3),
            'caption'    => fake()->optional()->sentence(),
            'cta_label'  => fake()->optional()->word(),
            'cta_url'    => fake()->optional()->url(),
            'is_default' => false,
            'sort_order' => fake()->numberBetween(0, 100),
            'status'     => ContentStatus::Published->value,
            'is_active'  => true,
            'published_at' => now(),
        ];
    }

    public function draft(): static
    {
        return $this->state(['status' => ContentStatus::Draft->value, 'published_at' => null]);
    }

    public function inactive(): static
    {
        return $this->state(['is_active' => false]);
    }

    public function default(): static
    {
        return $this->state(['is_default' => true]);
    }
}
