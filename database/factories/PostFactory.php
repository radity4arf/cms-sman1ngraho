<?php

/**
 * PostFactory — Factory untuk model Post (RT-01 Berita)
 *
 * @author   DSE (Delia Tse)
 * @created  2026-08-11
 */

// [THECHNOLOGY-CRE] : PostFactory

namespace Database\Factories;

use App\Enums\ContentStatus;
use App\Models\Post;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Post>
 */
class PostFactory extends Factory
{
    protected $model = Post::class;

    public function definition(): array
    {
        return [
            'title'        => fake()->sentence(3),
            'slug'         => null, // auto-generated via model booted()
            'excerpt'      => fake()->optional()->sentence(),
            'body'         => fake()->paragraphs(3, true),
            'status'       => ContentStatus::Published->value,
            'is_active'    => true,
            'published_at' => now(),
        ];
    }

    public function draft(): static
    {
        return $this->state(['status' => ContentStatus::Draft->value, 'published_at' => null]);
    }
}
