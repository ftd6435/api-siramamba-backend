<?php

namespace Tests\Feature\Content;

use App\Modules\Administration\Models\User;
use App\Modules\Content\Models\CategoryGallery;
use App\Modules\Content\Models\Gallery;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class GalleryCategoryApiTest extends TestCase
{
    use RefreshDatabase;

    private int $userSequence = 0;

    public function test_admin_gallery_category_routes_require_authentication(): void
    {
        $category = $this->createCategory($this->createUser());

        $this->getJson('/api/v1/admin/gallery-categories')->assertUnauthorized();
        $this->postJson('/api/v1/admin/gallery-categories', ['name' => 'Événements'])
            ->assertUnauthorized();
        $this->getJson("/api/v1/admin/gallery-categories/{$category->id}")
            ->assertUnauthorized();
        $this->patchJson("/api/v1/admin/gallery-categories/{$category->id}", ['name' => 'Équipe'])
            ->assertUnauthorized();
        $this->deleteJson("/api/v1/admin/gallery-categories/{$category->id}")
            ->assertUnauthorized();
    }

    public function test_authenticated_user_can_create_a_category_with_server_controlled_audit(): void
    {
        $authenticatedUser = $this->createUser();
        $spoofedUser = $this->createUser();
        Sanctum::actingAs($authenticatedUser);

        $this->postJson('/api/v1/admin/gallery-categories', [
            'name' => 'Événements',
            'created_by' => $spoofedUser->id,
            'updated_by' => $spoofedUser->id,
        ])->assertCreated()
            ->assertJsonPath('status', 1)
            ->assertJsonPath('data.name', 'Événements')
            ->assertJsonPath('data.created_by.id', $authenticatedUser->id)
            ->assertJsonPath('data.updated_by.id', $authenticatedUser->id);

        $this->assertDatabaseHas('category_galleries', [
            'name' => 'Événements',
            'created_by' => $authenticatedUser->id,
            'updated_by' => $authenticatedUser->id,
        ]);
    }

    public function test_authenticated_user_can_list_and_show_categories_with_audit(): void
    {
        $creator = $this->createUser();
        $category = $this->createCategory($creator);
        Sanctum::actingAs($creator);

        $this->getJson('/api/v1/admin/gallery-categories')
            ->assertOk()
            ->assertJsonPath('data.0.id', $category->id)
            ->assertJsonPath('data.0.created_by.id', $creator->id)
            ->assertJsonPath('data.0.updated_by.id', $creator->id);

        $this->getJson("/api/v1/admin/gallery-categories/{$category->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $category->id)
            ->assertJsonPath('data.created_by.id', $creator->id);
    }

    public function test_patch_is_partial_and_preserves_creator(): void
    {
        $creator = $this->createUser();
        $updater = $this->createUser();
        $spoofedUser = $this->createUser();
        $category = $this->createCategory($creator);
        Sanctum::actingAs($updater);

        $this->patchJson("/api/v1/admin/gallery-categories/{$category->id}", [
            'name' => 'Nouvelle catégorie',
            'created_by' => $spoofedUser->id,
            'updated_by' => $spoofedUser->id,
        ])->assertOk()
            ->assertJsonPath('data.name', 'Nouvelle catégorie')
            ->assertJsonPath('data.created_by.id', $creator->id)
            ->assertJsonPath('data.updated_by.id', $updater->id);

        $category->refresh();

        $this->assertSame($creator->id, $category->created_by);
        $this->assertSame($updater->id, $category->updated_by);
    }

    public function test_empty_category_can_be_deleted(): void
    {
        $user = $this->createUser();
        $category = $this->createCategory($user);
        Sanctum::actingAs($user);

        $this->deleteJson("/api/v1/admin/gallery-categories/{$category->id}")
            ->assertOk()
            ->assertJsonPath('status', 1);

        $this->assertDatabaseMissing('category_galleries', ['id' => $category->id]);
    }

    public function test_used_category_cannot_be_deleted(): void
    {
        $user = $this->createUser();
        $category = $this->createCategory($user);
        $gallery = $this->createGallery($category, $user);
        Sanctum::actingAs($user);

        $this->deleteJson("/api/v1/admin/gallery-categories/{$category->id}")
            ->assertConflict()
            ->assertJsonPath('status', 0)
            ->assertJsonPath(
                'message',
                'Cette catégorie est utilisée et ne peut pas être supprimée.'
            );

        $this->assertDatabaseHas('category_galleries', ['id' => $category->id]);
        $this->assertDatabaseHas('galleries', ['id' => $gallery->id]);
    }

    public function test_foreign_key_race_returns_a_clean_conflict_response(): void
    {
        $user = $this->createUser();
        $category = $this->createCategory($user);
        Sanctum::actingAs($user);

        CategoryGallery::deleting(function (CategoryGallery $deletingCategory) use ($user): void {
            DB::table('galleries')->insert([
                'category_id' => $deletingCategory->id,
                'image_path' => 'concurrent.jpg',
                'short_description' => 'Référence créée pendant la suppression.',
                'is_active' => true,
                'created_by' => $user->id,
                'updated_by' => $user->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        });

        try {
            $this->deleteJson("/api/v1/admin/gallery-categories/{$category->id}")
                ->assertConflict()
                ->assertJsonPath('status', 0);

            $this->assertDatabaseHas('category_galleries', ['id' => $category->id]);
        } finally {
            CategoryGallery::flushEventListeners();
        }
    }

    public function test_public_list_returns_all_categories_without_audit_relations(): void
    {
        $user = $this->createUser();
        $category = $this->createCategory($user);

        $response = $this->getJson('/api/v1/gallery-categories')
            ->assertOk()
            ->assertJsonPath('data.0.id', $category->id);

        $this->assertArrayNotHasKey('created_by', $response->json('data.0'));
        $this->assertArrayNotHasKey('updated_by', $response->json('data.0'));
    }

    private function createUser(): User
    {
        $this->userSequence++;

        return User::create([
            'name' => "Administrateur {$this->userSequence}",
            'username' => "gallerycategoryadmin{$this->userSequence}",
            'telephone' => "62600000{$this->userSequence}",
            'email' => "gallerycategoryadmin{$this->userSequence}@example.com",
            'role' => 'admin',
            'is_active' => true,
            'password' => 'password',
        ]);
    }

    private function createCategory(User $user, array $attributes = []): CategoryGallery
    {
        $category = new CategoryGallery(array_merge([
            'name' => 'Événements',
        ], $attributes));
        $category->created_by = $user->id;
        $category->updated_by = $user->id;
        $category->save();

        return $category;
    }

    private function createGallery(CategoryGallery $category, User $user): Gallery
    {
        $gallery = new Gallery([
            'category_id' => $category->id,
            'short_description' => 'Photo de galerie',
            'is_active' => true,
        ]);
        $gallery->image_path = 'gallery.jpg';
        $gallery->created_by = $user->id;
        $gallery->updated_by = $user->id;
        $gallery->save();

        return $gallery;
    }
}
