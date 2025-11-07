<?php

namespace Database\Factories;

use App\Models\Article;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Article>
 */
class ArticleFactory extends Factory
{
    protected $model = Article::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'source' => $this->faker->randomElement(['guardian', 'nyt', 'newsapi']),
            'external_id' => $this->faker->uuid(),
            'url' => $this->faker->unique()->url(),
            'title' => $this->faker->sentence(6),
            'summary' => $this->faker->optional()->paragraph(),
            'authors' => ['Jane Doe', 'John Smith'], // stored as JSON (cast in model)
            'category' => $this->faker->randomElement(['World', 'Technology', 'Sports']),
            'published_at' => now()->subDays(rand(0, 10)),
            'raw' => ['k' => 'v'],
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    public function source(string $name): self
    {
        return $this->state(fn () => ['source' => $name]);
    }

    public function category(string $name): self
    {
        return $this->state(fn () => ['category' => $name]);
    }

    public function published(string $date): self
    {
        return $this->state(fn () => ['published_at' => $date]);
    }
}
