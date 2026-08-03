<?php

namespace Tests\Feature;

use App\Models\Category;
use Database\Seeders\CategorySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategorySeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_first_run_creates_default_categories(): void
    {
        $this->assertDatabaseCount('categories', 0);

        $this->seed(CategorySeeder::class);

        $this->assertDatabaseCount('categories', 5);
        $this->assertDatabaseHas('categories', ['slug' => 'electronics']);
        $this->assertDatabaseHas('categories', ['slug' => 'fashion']);
        $this->assertDatabaseHas('categories', ['slug' => 'home-living']);
    }

    public function test_re_seed_is_idempotent_and_preserves_existing_category(): void
    {
        // An existing category (possibly edited in the admin panel) must
        // survive re-seeding without being duplicated or overwritten.
        Category::create([
            'name' => 'Electronics',
            'slug' => 'electronics',
            'description' => 'Edited category description',
        ]);

        $this->seed(CategorySeeder::class);
        $this->seed(CategorySeeder::class);

        $this->assertDatabaseCount('categories', 5);

        $electronics = Category::where('slug', 'electronics')->first();
        $this->assertNotNull($electronics);
        $this->assertSame('Edited category description', $electronics->description);
    }
}
