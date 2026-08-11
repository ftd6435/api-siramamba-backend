<?php

namespace Tests\Feature\Content;

use App\Modules\Administration\Models\User;
use App\Modules\Content\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class CategoryApiTest extends TestCase
{
    use RefreshDatabase;

    private int $userSequence = 0;

    public function test_admin_category_routes_require_authentication(): void
    {
        $category = $this->createCategory($this->createUser());

        $this->getJson('/api/v1/admin/categories')->assertUnauthorized();
        $this->postJson('/api/v1/admin/categories', $this->validPayload())->assertUnauthorized();
        $this->getJson("/api/v1/admin/categories/{$category->id}")->assertUnauthorized();
        $this->patchJson("/api/v1/admin/categories/{$category->id}", ['is_active' => false])
            ->assertUnauthorized();
        $this->deleteJson("/api/v1/admin/categories/{$category->id}")->assertUnauthorized();

        $this->assertDatabaseHas('categories', ['id' => $category->id]);
    }

    public function test_allowed_category_types_are_accepted(): void
    {
        $user = $this->createUser();
        Sanctum::actingAs($user);

        foreach (['mix', 'projet', 'blog'] as $type) {
            $this->postJson('/api/v1/admin/categories', $this->validPayload([
                'name' => "Catégorie {$type}",
                'type' => $type,
            ]))->assertCreated();

            $this->assertDatabaseHas('categories', ['type' => $type]);
        }
    }

    public function test_invalid_category_type_is_rejected(): void
    {
        Sanctum::actingAs($this->createUser());

        $this->postJson('/api/v1/admin/categories', $this->validPayload(['type' => 'autre']))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('type');
    }

    public function test_authenticated_user_can_create_a_category_with_server_controlled_audit(): void
    {
        $authenticatedUser = $this->createUser();
        $otherUser = $this->createUser();
        Sanctum::actingAs($authenticatedUser);

        $response = $this->postJson('/api/v1/admin/categories', $this->validPayload([
            'created_by' => $otherUser->id,
            'updated_by' => $otherUser->id,
        ]));

        $response->assertCreated()
            ->assertJsonPath('status', 1)
            ->assertJsonPath('data.name', 'Développement durable');

        $this->assertDatabaseHas('categories', [
            'name' => 'Développement durable',
            'created_by' => $authenticatedUser->id,
            'updated_by' => $authenticatedUser->id,
        ]);
    }

    public function test_authenticated_user_can_list_categories(): void
    {
        $user = $this->createUser();
        $category = $this->createCategory($user);
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/admin/categories')
            ->assertOk()
            ->assertJsonPath('status', 1)
            ->assertJsonPath('data.0.id', $category->id);
    }

    public function test_update_preserves_creator_and_uses_authenticated_updater(): void
    {
        $creator = $this->createUser();
        $updater = $this->createUser();
        $spoofedUser = $this->createUser();
        $category = $this->createCategory($creator);
        Sanctum::actingAs($updater);

        $this->patchJson("/api/v1/admin/categories/{$category->id}", [
            'name' => 'Nouveau nom',
            'created_by' => $spoofedUser->id,
            'updated_by' => $spoofedUser->id,
        ])->assertOk();

        $category->refresh();

        $this->assertSame('Nouveau nom', $category->name);
        $this->assertSame($creator->id, $category->created_by);
        $this->assertSame($updater->id, $category->updated_by);
    }

    public function test_patch_is_really_partial(): void
    {
        $user = $this->createUser();
        $category = $this->createCategory($user);
        Sanctum::actingAs($user);

        $this->patchJson("/api/v1/admin/categories/{$category->id}", ['is_active' => false])
            ->assertOk()
            ->assertJsonPath('data.is_active', false);

        $category->refresh();

        $this->assertFalse($category->is_active);
        $this->assertSame('Développement durable', $category->name);
        $this->assertSame('Description de la catégorie', $category->description);
        $this->assertSame('mix', $category->type);
    }

    public function test_authenticated_user_can_show_an_existing_category(): void
    {
        $user = $this->createUser();
        $category = $this->createCategory($user);
        Sanctum::actingAs($user);

        $this->getJson("/api/v1/admin/categories/{$category->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $category->id)
            ->assertJsonPath('data.name', $category->name);
    }

    public function test_show_returns_not_found_for_an_unknown_category(): void
    {
        Sanctum::actingAs($this->createUser());

        $this->getJson('/api/v1/admin/categories/999999')->assertNotFound();
    }

    public function test_public_list_only_returns_active_categories(): void
    {
        $user = $this->createUser();
        $active = $this->createCategory($user, ['name' => 'Active']);
        $inactive = $this->createCategory($user, ['name' => 'Inactive', 'is_active' => false]);

        $response = $this->getJson('/api/v1/categories')->assertOk();
        $returnedIds = collect($response->json('data'))->pluck('id')->all();

        $this->assertContains($active->id, $returnedIds);
        $this->assertNotContains($inactive->id, $returnedIds);
    }

    public function test_unreferenced_category_can_be_deleted(): void
    {
        $user = $this->createUser();
        $category = $this->createCategory($user);
        Sanctum::actingAs($user);

        $this->deleteJson("/api/v1/admin/categories/{$category->id}")
            ->assertOk()
            ->assertJsonPath('status', 1);

        $this->assertDatabaseMissing('categories', ['id' => $category->id]);
    }

    public function test_category_referenced_by_a_project_cannot_be_deleted(): void
    {
        $user = $this->createUser();
        $category = $this->createCategory($user);
        Sanctum::actingAs($user);

        DB::table('projects')->insert([
            'category_id' => $category->id,
            'title' => 'Projet minier',
            'short_description' => 'Description courte',
            'description' => 'Description complète',
            'status' => 'encours',
            'is_featured' => false,
            'country' => null,
            'city' => null,
            'address' => 'Conakry',
            'start_date' => '2026-08-01',
            'end_date' => null,
            'progess_percentage' => 20,
            'list_details' => null,
            'is_active' => true,
            'thumbnail' => 'thumbnail.jpg',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->deleteJson("/api/v1/admin/categories/{$category->id}")
            ->assertConflict()
            ->assertJsonPath('status', 0);

        $this->assertDatabaseHas('categories', ['id' => $category->id]);
    }

    public function test_category_referenced_by_a_blog_cannot_be_deleted(): void
    {
        $user = $this->createUser();
        $category = $this->createCategory($user);
        Sanctum::actingAs($user);

        DB::table('blogs')->insert([
            'category_id' => $category->id,
            'title' => 'Actualité minière',
            'short_description' => 'Description courte',
            'description' => 'Description complète',
            'thumbnail' => 'thumbnail.jpg',
            'is_featured' => false,
            'is_active' => true,
            'created_by' => $user->id,
            'updated_by' => $user->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->deleteJson("/api/v1/admin/categories/{$category->id}")
            ->assertConflict()
            ->assertJsonPath('status', 0);

        $this->assertDatabaseHas('categories', ['id' => $category->id]);
    }

    private function createUser(): User
    {
        $this->userSequence++;

        return User::create([
            'name' => "Administrateur {$this->userSequence}",
            'username' => "admin{$this->userSequence}",
            'telephone' => "62000000{$this->userSequence}",
            'email' => "admin{$this->userSequence}@example.com",
            'role' => 'admin',
            'is_active' => true,
            'password' => 'password',
        ]);
    }

    private function createCategory(User $user, array $attributes = []): Category
    {
        $category = new Category(array_merge([
            'name' => 'Développement durable',
            'description' => 'Description de la catégorie',
            'type' => 'mix',
            'is_active' => true,
        ], $attributes));
        $category->created_by = $user->id;
        $category->updated_by = $user->id;
        $category->save();

        return $category;
    }

    private function validPayload(array $attributes = []): array
    {
        return array_merge([
            'name' => 'Développement durable',
            'description' => 'Description de la catégorie',
            'type' => 'mix',
            'is_active' => true,
        ], $attributes);
    }
}
