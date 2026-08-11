<?php

namespace Tests\Feature\Content;

use App\Modules\Administration\Models\User;
use App\Modules\Content\Models\Category;
use App\Modules\Content\Models\Project;
use App\Modules\Content\Models\ProjectComment;
use App\Modules\Content\Models\ProjectImage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ProjectApiTest extends TestCase
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

    public function test_admin_project_routes_require_authentication(): void
    {
        $project = $this->createProject();

        $this->getJson('/api/v1/admin/projects')->assertUnauthorized();
        $this->post('/api/v1/admin/projects', $this->validPayload())->assertUnauthorized();
        $this->getJson("/api/v1/admin/projects/{$project->id}")->assertUnauthorized();
        $this->patchJson("/api/v1/admin/projects/{$project->id}", ['title' => 'Nouveau titre'])
            ->assertUnauthorized();
        $this->deleteJson("/api/v1/admin/projects/{$project->id}")->assertUnauthorized();
    }

    public function test_authenticated_user_can_create_a_valid_project(): void
    {
        Sanctum::actingAs($this->createUser());
        $category = $this->createCategory();

        $response = $this->post('/api/v1/admin/projects', $this->validPayload([
            'category_id' => $category->id,
            'country' => null,
            'city' => null,
            'end_date' => null,
            'list_details' => null,
        ]));

        $response->assertCreated()
            ->assertJsonPath('status', 1)
            ->assertJsonPath('data.category.id', $category->id)
            ->assertJsonPath('data.country', null)
            ->assertJsonPath('data.city', null)
            ->assertJsonPath('data.end_date', null)
            ->assertJsonPath('data.list_details', null);

        $project = Project::firstOrFail();

        Storage::disk('r2')->assertExists("images/projects/thumbnails/{$project->thumbnail}");
        $this->assertStringContainsString(
            "images/projects/thumbnails/{$project->thumbnail}",
            $response->json('data.thumbnail_url')
        );
    }

    public function test_unknown_category_is_rejected(): void
    {
        Sanctum::actingAs($this->createUser());

        $this->post('/api/v1/admin/projects', $this->validPayload(['category_id' => 999999]))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('category_id');
    }

    public function test_allowed_project_statuses_are_accepted(): void
    {
        Sanctum::actingAs($this->createUser());
        $category = $this->createCategory();

        foreach (['encours', 'terminer', 'planifier'] as $status) {
            $this->post('/api/v1/admin/projects', $this->validPayload([
                'category_id' => $category->id,
                'title' => "Projet {$status}",
                'status' => $status,
            ]))->assertCreated();

            $this->assertDatabaseHas('projects', ['status' => $status]);
        }
    }

    public function test_invalid_status_and_booleans_are_rejected(): void
    {
        Sanctum::actingAs($this->createUser());
        $category = $this->createCategory();

        $this->post('/api/v1/admin/projects', $this->validPayload([
            'category_id' => $category->id,
            'status' => 'publier',
        ]))->assertUnprocessable()->assertJsonValidationErrors('status');

        $this->post('/api/v1/admin/projects', $this->validPayload([
            'category_id' => $category->id,
            'is_featured' => 'oui',
        ]))->assertUnprocessable()->assertJsonValidationErrors('is_featured');

        $this->post('/api/v1/admin/projects', $this->validPayload([
            'category_id' => $category->id,
            'is_active' => 'oui',
        ]))->assertUnprocessable()->assertJsonValidationErrors('is_active');
    }

    public function test_end_date_must_not_be_before_start_date(): void
    {
        Sanctum::actingAs($this->createUser());

        $this->post('/api/v1/admin/projects', $this->validPayload([
            'start_date' => '2026-08-10',
            'end_date' => '2026-08-09',
        ]))->assertUnprocessable()->assertJsonValidationErrors('end_date');
    }

    public function test_progress_percentage_accepts_its_boundaries(): void
    {
        Sanctum::actingAs($this->createUser());
        $category = $this->createCategory();

        foreach ([0, 100] as $percentage) {
            $this->post('/api/v1/admin/projects', $this->validPayload([
                'category_id' => $category->id,
                'title' => "Projet {$percentage}",
                'progess_percentage' => $percentage,
            ]))->assertCreated();
        }

        $this->assertDatabaseHas('projects', ['progess_percentage' => 0]);
        $this->assertDatabaseHas('projects', ['progess_percentage' => 100]);
    }

    public function test_progress_percentage_rejects_values_outside_its_boundaries(): void
    {
        Sanctum::actingAs($this->createUser());

        foreach ([-1, 101] as $percentage) {
            $this->post('/api/v1/admin/projects', $this->validPayload([
                'progess_percentage' => $percentage,
            ]))->assertUnprocessable()->assertJsonValidationErrors('progess_percentage');
        }
    }

    public function test_list_details_accepts_null_and_an_array(): void
    {
        Sanctum::actingAs($this->createUser());
        $category = $this->createCategory();

        $this->post('/api/v1/admin/projects', $this->validPayload([
            'category_id' => $category->id,
            'title' => 'Projet sans détails',
            'list_details' => null,
        ]))->assertCreated();

        $this->post('/api/v1/admin/projects', $this->validPayload([
            'category_id' => $category->id,
            'title' => 'Projet avec détails',
            'list_details' => ['emploi' => 100, 'durée' => '2 ans'],
        ]))->assertCreated()->assertJsonPath('data.list_details.emploi', 100);
    }

    public function test_invalid_thumbnail_is_rejected(): void
    {
        Sanctum::actingAs($this->createUser());

        $this->postJson('/api/v1/admin/projects', [
            ...$this->validPayload(['thumbnail' => null]),
            'thumbnail' => 'document.pdf',
        ])->assertUnprocessable()->assertJsonValidationErrors('thumbnail');
    }

    public function test_patch_is_partial(): void
    {
        $project = $this->createProject();
        Sanctum::actingAs($this->createUser());

        $this->patchJson("/api/v1/admin/projects/{$project->id}", [
            'progess_percentage' => 65,
        ])->assertOk()->assertJsonPath('data.progess_percentage', 65);

        $project->refresh();
        $this->assertSame('Projet minier', $project->title);
        $this->assertSame(65, $project->progess_percentage);
    }

    public function test_admin_index_returns_active_and_inactive_projects(): void
    {
        $active = $this->createProject(['title' => 'Projet actif', 'is_active' => true]);
        $inactive = $this->createProject(['title' => 'Projet inactif', 'is_active' => false]);
        Sanctum::actingAs($this->createUser());

        $response = $this->getJson('/api/v1/admin/projects')->assertOk();
        $ids = collect($response->json('data'))->pluck('id')->all();

        $this->assertContains($active->id, $ids);
        $this->assertContains($inactive->id, $ids);
    }

    public function test_patch_start_date_uses_the_existing_end_date(): void
    {
        $project = $this->createProject([
            'start_date' => '2026-01-01',
            'end_date' => '2026-12-31',
        ]);
        Sanctum::actingAs($this->createUser());

        $this->patchJson("/api/v1/admin/projects/{$project->id}", [
            'start_date' => '2027-01-01',
        ])->assertUnprocessable()->assertJsonValidationErrors('start_date');
    }

    public function test_patch_end_date_uses_the_existing_start_date(): void
    {
        $project = $this->createProject([
            'start_date' => '2026-01-01',
            'end_date' => '2026-12-31',
        ]);
        Sanctum::actingAs($this->createUser());

        $this->patchJson("/api/v1/admin/projects/{$project->id}", [
            'end_date' => '2025-12-01',
        ])->assertUnprocessable()->assertJsonValidationErrors('end_date');
    }

    public function test_thumbnail_can_be_replaced_without_losing_it_on_validation_failure(): void
    {
        $project = $this->createProject();
        $oldThumbnail = $project->thumbnail;
        Sanctum::actingAs($this->createUser());

        $response = $this->patch("/api/v1/admin/projects/{$project->id}", [
            'thumbnail' => UploadedFile::fake()->create('replacement.jpg', 100, 'image/jpeg'),
        ]);

        $response->assertOk();
        $project->refresh();

        $this->assertNotSame($oldThumbnail, $project->thumbnail);
        Storage::disk('r2')->assertMissing("images/projects/thumbnails/{$oldThumbnail}");
        Storage::disk('r2')->assertExists("images/projects/thumbnails/{$project->thumbnail}");

        $currentThumbnail = $project->thumbnail;

        $this->patch("/api/v1/admin/projects/{$project->id}", [
            'status' => 'invalide',
            'thumbnail' => UploadedFile::fake()->create('invalid-replacement.jpg', 100, 'image/jpeg'),
        ])->assertUnprocessable();

        $this->assertSame($currentThumbnail, $project->refresh()->thumbnail);
        Storage::disk('r2')->assertExists("images/projects/thumbnails/{$currentThumbnail}");
    }

    public function test_admin_show_returns_category_and_images(): void
    {
        $project = $this->createProject();
        $image = $project->images()->create(['image_path' => 'gallery.jpg']);
        Sanctum::actingAs($this->createUser());

        $this->getJson("/api/v1/admin/projects/{$project->id}")
            ->assertOk()
            ->assertJsonPath('data.category.id', $project->category_id)
            ->assertJsonPath('data.images.0.id', $image->id)
            ->assertJsonPath('data.start_date', '2026-08-01');
    }

    public function test_unknown_project_returns_not_found(): void
    {
        Sanctum::actingAs($this->createUser());

        $this->getJson('/api/v1/admin/projects/999999')->assertNotFound();
        $this->patchJson('/api/v1/admin/projects/999999', ['title' => 'Inconnu'])->assertNotFound();
    }

    public function test_project_without_dependencies_can_be_deleted_after_its_thumbnail(): void
    {
        $project = $this->createProject();
        $thumbnail = $project->thumbnail;
        Sanctum::actingAs($this->createUser());

        $this->deleteJson("/api/v1/admin/projects/{$project->id}")
            ->assertOk()
            ->assertJsonPath('status', 1);

        $this->assertDatabaseMissing('projects', ['id' => $project->id]);
        Storage::disk('r2')->assertMissing("images/projects/thumbnails/{$thumbnail}");
    }

    public function test_project_with_dependencies_cannot_be_deleted(): void
    {
        $project = $this->createProject();
        ProjectImage::create(['project_id' => $project->id, 'image_path' => 'gallery.jpg']);
        Sanctum::actingAs($this->createUser());

        $this->deleteJson("/api/v1/admin/projects/{$project->id}")
            ->assertConflict()
            ->assertJsonPath('status', 0);

        $this->assertDatabaseHas('projects', ['id' => $project->id]);
        Storage::disk('r2')->assertExists("images/projects/thumbnails/{$project->thumbnail}");
    }

    public function test_project_with_a_comment_cannot_be_deleted(): void
    {
        $project = $this->createProject();
        ProjectComment::create([
            'project_id' => $project->id,
            'name' => 'Mamadou',
            'email' => null,
            'content' => 'Commentaire',
            'parent_id' => null,
        ]);
        Sanctum::actingAs($this->createUser());

        $this->deleteJson("/api/v1/admin/projects/{$project->id}")
            ->assertConflict()
            ->assertJsonPath('status', 0);

        $this->assertDatabaseHas('projects', ['id' => $project->id]);
        Storage::disk('r2')->assertExists("images/projects/thumbnails/{$project->thumbnail}");
    }

    public function test_public_index_only_returns_active_projects_even_with_an_inactive_category(): void
    {
        $inactiveCategory = $this->createCategory(['is_active' => false]);
        $active = $this->createProject([
            'category_id' => $inactiveCategory->id,
            'title' => 'Projet public',
            'is_active' => true,
        ]);
        $inactive = $this->createProject(['title' => 'Projet privé', 'is_active' => false]);

        $response = $this->getJson('/api/v1/projects')->assertOk();
        $ids = collect($response->json('data'))->pluck('id')->all();

        $this->assertContains($active->id, $ids);
        $this->assertNotContains($inactive->id, $ids);
    }

    public function test_public_show_returns_an_active_project_and_hides_an_inactive_one(): void
    {
        $active = $this->createProject(['is_active' => true]);
        $inactive = $this->createProject(['title' => 'Projet privé', 'is_active' => false]);

        $this->getJson("/api/v1/projects/{$active->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $active->id)
            ->assertJsonPath('data.category.id', $active->category_id);

        $this->getJson("/api/v1/projects/{$inactive->id}")->assertNotFound();
    }

    private function createUser(): User
    {
        $this->userSequence++;

        return User::create([
            'name' => "Administrateur {$this->userSequence}",
            'username' => "projectadmin{$this->userSequence}",
            'telephone' => "62100000{$this->userSequence}",
            'email' => "projectadmin{$this->userSequence}@example.com",
            'role' => 'admin',
            'is_active' => true,
            'password' => 'password',
        ]);
    }

    private function createCategory(array $attributes = []): Category
    {
        $user = $this->createUser();
        $category = new Category(array_merge([
            'name' => 'Projets',
            'description' => 'Catégorie de projets',
            'type' => 'mix',
            'is_active' => true,
        ], $attributes));
        $category->created_by = $user->id;
        $category->updated_by = $user->id;
        $category->save();

        return $category;
    }

    private function createProject(array $attributes = []): Project
    {
        $categoryId = $attributes['category_id'] ?? $this->createCategory()->id;
        $project = Project::create(array_merge([
            'category_id' => $categoryId,
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
            'thumbnail' => 'thumbnail-'.uniqid().'.jpg',
        ], $attributes));

        Storage::disk('r2')->put("images/projects/thumbnails/{$project->thumbnail}", 'image');

        return $project;
    }

    private function validPayload(array $attributes = []): array
    {
        return array_merge([
            'category_id' => $this->createCategory()->id,
            'title' => 'Projet minier',
            'short_description' => 'Description courte',
            'description' => 'Description complète',
            'status' => 'encours',
            'is_featured' => false,
            'country' => 'Guinée',
            'city' => 'Conakry',
            'address' => 'Kaloum',
            'start_date' => '2026-08-01',
            'end_date' => '2026-12-31',
            'progess_percentage' => 20,
            'list_details' => ['emplois' => 100],
            'is_active' => true,
            'thumbnail' => UploadedFile::fake()->create('thumbnail.jpg', 100, 'image/jpeg'),
        ], $attributes);
    }
}
