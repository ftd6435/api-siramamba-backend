<?php

namespace Tests\Feature\Content;

use App\Modules\Administration\Models\User;
use App\Modules\Content\Models\CategoryGallery;
use App\Modules\Content\Models\Gallery;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use RuntimeException;
use Tests\TestCase;

class GalleryApiTest extends TestCase
{
    use RefreshDatabase;

    private int $userSequence = 0;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withHeader('Accept', 'application/json');
        Storage::fake('r2');
        Storage::disk('r2')->buildTemporaryUrlsUsing(
            fn (string $path) => "https://r2.test/{$path}"
        );
    }

    public function test_admin_gallery_routes_require_authentication(): void
    {
        $gallery = $this->createGallery();

        $this->getJson('/api/v1/admin/galleries')->assertUnauthorized();
        $this->post('/api/v1/admin/galleries', $this->validPayload())->assertUnauthorized();
        $this->getJson("/api/v1/admin/galleries/{$gallery->id}")->assertUnauthorized();
        $this->patchJson("/api/v1/admin/galleries/{$gallery->id}", [
            'short_description' => 'Nouvelle description',
        ])->assertUnauthorized();
        $this->deleteJson("/api/v1/admin/galleries/{$gallery->id}")->assertUnauthorized();
    }

    public function test_authenticated_user_can_create_a_gallery_with_image_relation_and_audit(): void
    {
        $authenticatedUser = $this->createUser();
        $spoofedUser = $this->createUser();
        $category = $this->createCategory($authenticatedUser);
        Sanctum::actingAs($authenticatedUser);

        $response = $this->post('/api/v1/admin/galleries', $this->validPayload([
            'category_id' => $category->id,
            'image_path' => 'spoofed.jpg',
            'created_by' => $spoofedUser->id,
            'updated_by' => $spoofedUser->id,
        ]));

        $response->assertCreated()
            ->assertJsonPath('status', 1)
            ->assertJsonPath('data.category_id', $category->id)
            ->assertJsonPath('data.category.id', $category->id)
            ->assertJsonPath('data.created_by.id', $authenticatedUser->id)
            ->assertJsonPath('data.updated_by.id', $authenticatedUser->id);

        $gallery = Gallery::firstOrFail();

        $this->assertNotSame('spoofed.jpg', $gallery->image_path);
        $this->assertSame($authenticatedUser->id, $gallery->created_by);
        $this->assertSame($authenticatedUser->id, $gallery->updated_by);
        Storage::disk('r2')->assertExists("images/galleries/{$gallery->image_path}");
        $this->assertSame(
            "https://r2.test/images/galleries/{$gallery->image_path}",
            $response->json('data.image_url')
        );
    }

    public function test_unknown_category_is_rejected(): void
    {
        Sanctum::actingAs($this->createUser());

        $this->post('/api/v1/admin/galleries', $this->validPayload([
            'category_id' => 999999,
        ]))->assertUnprocessable()->assertJsonValidationErrors('category_id');

        $this->assertDatabaseCount('galleries', 0);
        $this->assertSame([], Storage::disk('r2')->allFiles('images/galleries'));
    }

    public function test_new_image_is_removed_when_database_creation_fails(): void
    {
        Sanctum::actingAs($this->createUser());
        Gallery::creating(fn () => throw new RuntimeException('Échec BDD simulé.'));

        try {
            $this->post('/api/v1/admin/galleries', $this->validPayload())
                ->assertInternalServerError();

            $this->assertDatabaseCount('galleries', 0);
            $this->assertSame([], Storage::disk('r2')->allFiles('images/galleries'));
        } finally {
            Gallery::flushEventListeners();
        }
    }

    public function test_admin_can_list_and_show_active_and_inactive_galleries(): void
    {
        $creator = $this->createUser();
        $active = $this->createGallery(['short_description' => 'Active'], null, $creator);
        $inactive = $this->createGallery([
            'short_description' => 'Inactive',
            'is_active' => false,
        ], null, $creator);
        Sanctum::actingAs($creator);

        $response = $this->getJson('/api/v1/admin/galleries')->assertOk();
        $ids = collect($response->json('data'))->pluck('id')->all();

        $this->assertContains($active->id, $ids);
        $this->assertContains($inactive->id, $ids);

        $this->getJson("/api/v1/admin/galleries/{$inactive->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $inactive->id)
            ->assertJsonPath('data.category.id', $inactive->category_id)
            ->assertJsonPath('data.created_by.id', $creator->id)
            ->assertJsonPath('data.updated_by.id', $creator->id);
    }

    public function test_patch_is_partial_changes_category_and_preserves_server_controlled_fields(): void
    {
        $creator = $this->createUser();
        $updater = $this->createUser();
        $spoofedUser = $this->createUser();
        $gallery = $this->createGallery([], null, $creator);
        $newCategory = $this->createCategory($creator, ['name' => 'Nouvelle catégorie']);
        $oldImage = $gallery->image_path;
        Sanctum::actingAs($updater);

        $this->patchJson("/api/v1/admin/galleries/{$gallery->id}", [
            'category_id' => $newCategory->id,
            'short_description' => 'Description modifiée',
            'image_path' => 'spoofed.jpg',
            'created_by' => $spoofedUser->id,
            'updated_by' => $spoofedUser->id,
        ])->assertOk()
            ->assertJsonPath('data.category.id', $newCategory->id)
            ->assertJsonPath('data.short_description', 'Description modifiée')
            ->assertJsonPath('data.created_by.id', $creator->id)
            ->assertJsonPath('data.updated_by.id', $updater->id);

        $gallery->refresh();

        $this->assertSame($newCategory->id, $gallery->category_id);
        $this->assertSame($oldImage, $gallery->image_path);
        $this->assertTrue($gallery->is_active);
        $this->assertSame($creator->id, $gallery->created_by);
        $this->assertSame($updater->id, $gallery->updated_by);
    }

    public function test_patch_rejects_an_unknown_category(): void
    {
        $gallery = $this->createGallery();
        $originalCategoryId = $gallery->category_id;
        Sanctum::actingAs($this->createUser());

        $this->patchJson("/api/v1/admin/galleries/{$gallery->id}", [
            'category_id' => 999999,
        ])->assertUnprocessable()->assertJsonValidationErrors('category_id');

        $this->assertSame($originalCategoryId, $gallery->refresh()->category_id);
    }

    public function test_image_can_be_replaced_after_database_update(): void
    {
        $gallery = $this->createGallery();
        $oldImage = $gallery->image_path;
        Sanctum::actingAs($this->createUser());

        $this->patch("/api/v1/admin/galleries/{$gallery->id}", [
            'image' => $this->fakeImage('replacement.png', 'image/png'),
        ])->assertOk();

        $gallery->refresh();

        $this->assertNotSame($oldImage, $gallery->image_path);
        Storage::disk('r2')->assertMissing("images/galleries/{$oldImage}");
        Storage::disk('r2')->assertExists("images/galleries/{$gallery->image_path}");
    }

    public function test_failed_image_replacement_removes_new_image_and_keeps_old_image(): void
    {
        $gallery = $this->createGallery();
        $oldImage = $gallery->image_path;
        Sanctum::actingAs($this->createUser());
        Gallery::updating(fn () => throw new RuntimeException('Échec BDD simulé.'));

        try {
            $this->patch("/api/v1/admin/galleries/{$gallery->id}", [
                'image' => $this->fakeImage('replacement.jpg'),
            ])->assertInternalServerError();

            $this->assertSame($oldImage, $gallery->fresh()->image_path);
            Storage::disk('r2')->assertExists("images/galleries/{$oldImage}");
            $this->assertSame(
                ["images/galleries/{$oldImage}"],
                Storage::disk('r2')->allFiles('images/galleries')
            );
        } finally {
            Gallery::flushEventListeners();
        }
    }

    public function test_gallery_can_be_deleted_from_database_and_r2(): void
    {
        $gallery = $this->createGallery();
        $imagePath = $gallery->image_path;
        Sanctum::actingAs($this->createUser());

        $this->deleteJson("/api/v1/admin/galleries/{$gallery->id}")
            ->assertOk()
            ->assertJsonPath('status', 1);

        $this->assertDatabaseMissing('galleries', ['id' => $gallery->id]);
        Storage::disk('r2')->assertMissing("images/galleries/{$imagePath}");
    }

    public function test_public_list_only_returns_active_galleries_with_formatted_categories(): void
    {
        $active = $this->createGallery(['short_description' => 'Publique']);
        $inactive = $this->createGallery([
            'short_description' => 'Privée',
            'is_active' => false,
        ]);

        $response = $this->getJson('/api/v1/galleries')
            ->assertOk()
            ->assertJsonPath('data.0.id', $active->id)
            ->assertJsonPath('data.0.category.id', $active->category_id)
            ->assertJsonPath(
                'data.0.image_url',
                "https://r2.test/images/galleries/{$active->image_path}"
            );
        $ids = collect($response->json('data'))->pluck('id')->all();

        $this->assertNotContains($inactive->id, $ids);
        $this->assertArrayNotHasKey('created_by', $response->json('data.0'));
        $this->assertArrayNotHasKey('updated_by', $response->json('data.0'));
    }

    public function test_public_show_hides_inactive_gallery_while_admin_can_access_it(): void
    {
        $active = $this->createGallery(['short_description' => 'Publique']);
        $inactive = $this->createGallery([
            'short_description' => 'Privée',
            'is_active' => false,
        ]);

        $this->getJson("/api/v1/galleries/{$active->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $active->id)
            ->assertJsonPath('data.category.id', $active->category_id);
        $this->getJson("/api/v1/galleries/{$inactive->id}")->assertNotFound();

        Sanctum::actingAs($this->createUser());
        $this->getJson("/api/v1/admin/galleries/{$inactive->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $inactive->id);
    }

    public function test_public_category_filter_keeps_only_active_galleries_from_that_category(): void
    {
        $user = $this->createUser();
        $firstCategory = $this->createCategory($user, ['name' => 'Première']);
        $secondCategory = $this->createCategory($user, ['name' => 'Deuxième']);
        $expected = $this->createGallery([], $firstCategory, $user);
        $this->createGallery([], $secondCategory, $user);
        $inactive = $this->createGallery(['is_active' => false], $firstCategory, $user);

        $response = $this->getJson("/api/v1/galleries?category_id={$firstCategory->id}")
            ->assertOk();
        $ids = collect($response->json('data'))->pluck('id')->all();

        $this->assertSame([$expected->id], $ids);
        $this->assertNotContains($inactive->id, $ids);
    }

    private function createUser(): User
    {
        $this->userSequence++;

        return User::create([
            'name' => "Administrateur {$this->userSequence}",
            'username' => "galleryadmin{$this->userSequence}",
            'telephone' => "62700000{$this->userSequence}",
            'email' => "galleryadmin{$this->userSequence}@example.com",
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

    private function createGallery(
        array $attributes = [],
        ?CategoryGallery $category = null,
        ?User $user = null
    ): Gallery {
        $user ??= $this->createUser();
        $category ??= $this->createCategory($user);
        $gallery = new Gallery(array_merge([
            'category_id' => $category->id,
            'short_description' => 'Photo de galerie',
            'is_active' => true,
        ], $attributes));
        $gallery->image_path = 'gallery-'.uniqid().'.jpg';
        $gallery->created_by = $user->id;
        $gallery->updated_by = $user->id;
        $gallery->save();

        Storage::disk('r2')->put("images/galleries/{$gallery->image_path}", 'image');

        return $gallery;
    }

    private function fakeImage(
        string $name = 'gallery.jpg',
        string $mimeType = 'image/jpeg'
    ): UploadedFile {
        return UploadedFile::fake()->create($name, 100, $mimeType);
    }

    private function validPayload(array $attributes = []): array
    {
        $user = $this->createUser();

        return array_merge([
            'category_id' => $this->createCategory($user)->id,
            'image' => $this->fakeImage(),
            'short_description' => 'Photo de galerie',
            'is_active' => true,
        ], $attributes);
    }
}
