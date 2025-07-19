<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Category;

class CategoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_edit_and_delete_category(): void
    {
        $response = $this->post('/categories', [
            'name' => 'Teste',
            'description' => 'Desc',
        ]);
        $response->assertRedirect('/categories');
        $category = Category::first();
        $this->assertSame('Teste', $category->name);

        $response = $this->put('/categories/'.$category->id, [
            'name' => 'Atualizado',
            'description' => 'Nova',
        ]);
        $response->assertRedirect('/categories/'.$category->id);
        $category->refresh();
        $this->assertSame('Atualizado', $category->name);

        $response = $this->delete('/categories/'.$category->id);
        $response->assertRedirect('/categories');
        $this->assertDatabaseCount('categories', 0);
    }

    public function test_index_shows_categories(): void
    {
        Category::factory()->create(['name' => 'Teste']);
        $response = $this->get('/categories');
        $response->assertSee('Teste');
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);
    }

}

