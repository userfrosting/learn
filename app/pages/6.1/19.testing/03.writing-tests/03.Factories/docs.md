---
title: Factories
description: Generate realistic test data efficiently using Laravel's Eloquent factory system in UserFrosting.
---

Model factories allow you to generate realistic test data quickly and consistently. UserFrosting uses Laravel's Eloquent factory system with a custom base class.

## Creating a Factory

Model factories must extend `UserFrosting\Sprinkle\Core\Database\Factories\Factory`. The factory should be named after your model with a "Factory" suffix (e.g., `UserFactory` for the `User` model).

**Example Factory:**

```php
<?php

namespace App\MySite\Database\Factories;

use App\MySite\Database\Models\Article;
use UserFrosting\Sprinkle\Core\Database\Factories\Factory;

class ArticleFactory extends Factory
{
    protected $model = Article::class;

    public function definition(): array
    {
        return [
            'title'      => $this->faker->sentence(),
            'content'    => $this->faker->paragraphs(3, true),
            'published'  => true,
            'created_at' => $this->faker->dateTimeBetween('-1 year', 'now'),
        ];
    }
}
```

## Integrating with Your Model

Your model needs two things to work with factories:

1. Use the `HasFactory` trait
2. Define a `newFactory` method

```php
<?php

namespace App\MySite\Database\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use UserFrosting\Sprinkle\Core\Database\Models\Model;

class Article extends Model
{
    use HasFactory;

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory()
    {
        return ArticleFactory::new();
    }
}
```

## Using Factories in Tests

### Creating Single Models

```php
// Create one article and save it to the database
$article = Article::factory()->create();

// Create an article but don't save it
$article = Article::factory()->make();
```

### Creating Multiple Models

```php
// Create 10 articles
$articles = Article::factory()->count(10)->create();
```

### Overriding Attributes

```php
// Override specific attributes
$article = Article::factory()->create([
    'title' => 'Custom Title',
    'published' => false,
]);
```

### Factory States

Define different states for your models:

```php
class ArticleFactory extends Factory
{
    // ... definition method ...

    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'published' => false,
        ]);
    }

    public function featured(): static
    {
        return $this->state(fn (array $attributes) => [
            'featured' => true,
        ]);
    }
}
```

Use states in tests:

```php
// Create a draft article
$draft = Article::factory()->draft()->create();

// Chain multiple states
$featuredDraft = Article::factory()->draft()->featured()->create();
```

### Factory Relationships

Create related models together:

```php
// Create an article with a specific user
$article = Article::factory()
    ->for($user)
    ->create();

// Create multiple comments for an article
$article = Article::factory()
    ->has(Comment::factory()->count(3))
    ->create();
```

### Sequences

Generate progressive data:

```php
use Illuminate\Database\Eloquent\Factories\Sequence;

// Create articles with sequential titles
$articles = Article::factory()
    ->count(3)
    ->state(new Sequence(
        ['title' => 'First Article'],
        ['title' => 'Second Article'],
        ['title' => 'Third Article'],
    ))
    ->create();

// Sequence with index
$articles = Article::factory()
    ->count(5)
    ->state(new Sequence(
        fn ($sequence) => ['title' => 'Article ' . $sequence->index],
    ))
    ->create();
```

## Faker Integration

Factories have access to [Faker](https://fakerphp.github.io/) through `$this->faker` for generating realistic random data:

```php
public function definition(): array
{
    return [
        'title'      => $this->faker->sentence(),
        'slug'       => $this->faker->slug(),
        'content'    => $this->faker->paragraphs(3, true),
        'author'     => $this->faker->name(),
        'email'      => $this->faker->safeEmail(),
        'views'      => $this->faker->numberBetween(0, 10000),
        'rating'     => $this->faker->randomFloat(2, 1, 5),
        'tags'       => $this->faker->words(3),
        'published'  => $this->faker->boolean(80), // 80% chance of true
    ];
}
```

## Testing Example

Here's a complete example using factories in a test:

```php
<?php

namespace App\MySite\Tests\Database\Models;

use App\MySite\Database\Models\Article;
use App\MySite\Database\Models\User;
use App\MySite\Tests\MySiteTestCase;
use UserFrosting\Sprinkle\Core\Testing\RefreshDatabase;

class ArticleTest extends MySiteTestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();
        $this->refreshDatabase();
    }

    public function testArticleCreation(): void
    {
        $user = User::factory()->create();
        $article = Article::factory()
            ->for($user)
            ->create(['title' => 'Test Article']);

        $this->assertSame('Test Article', $article->title);
        $this->assertSame($user->id, $article->user_id);
    }
}
```

## Learn More

For complete details on factory capabilities, see the [Laravel Eloquent Factories documentation](https://laravel.com/docs/10.x/eloquent-factories).