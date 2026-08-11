<?php

namespace Tests\Feature\Content;

use App\Modules\Administration\Models\User;
use App\Modules\Content\Models\Category;
use App\Modules\Content\Models\Project;
use App\Modules\Content\Models\ProjectImage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ProjectImageApiTest extends TestCase
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

    public function test_project_image_routes_require_authentication(): void
    {
        $project = $this->createProject();
        $image = $this->createProjectImage($project);

        $this->getJson("/api/v1/admin/projects/{$project->id}/images")->assertUnauthorized();
        $this->post("/api/v1/admin/projects/{$project->id}/images", [
            'image' => $this->fakeImage(),
        ])->assertUnauthorized();
        $this->deleteJson("/api/v1/admin/projects/{$project->id}/images/{$image->id}")
            ->assertUnauthorized();
    }

    public function test_authenticated_user_can_list_project_images(): void
    {
        $project = $this->createProject();
        $image = $this->createProjectImage($project);
        Sanctum::actingAs($this->createUser());

        $this->getJson("/api/v1/admin/projects/{$project->id}/images")
            ->assertOk()
            ->assertJsonPath('data.0.id', $image->id)
            ->assertJsonPath(
                'data.0.url',
                "https://r2.test/images/projects/gallery/{$image->image_path}"
            );
    }

    public function test_valid_image_is_uploaded_and_associated_with_route_project(): void
    {
        $project = $this->createProject();
        $otherProject = $this->createProject();
        Sanctum::actingAs($this->createUser());

        $response = $this->post("/api/v1/admin/projects/{$project->id}/images", [
            'image' => $this->fakeImage(),
            'project_id' => $otherProject->id,
        ]);

        $response->assertCreated()
            ->assertJsonPath('status', 1);

        $projectImage = ProjectImage::firstOrFail();

        $this->assertSame($project->id, $projectImage->project_id);
        Storage::disk('r2')->assertExists("images/projects/gallery/{$projectImage->image_path}");
        $this->assertStringContainsString(
            "images/projects/gallery/{$projectImage->image_path}",
            $response->json('data.url')
        );
    }

    public function test_invalid_image_is_rejected(): void
    {
        $project = $this->createProject();
        Sanctum::actingAs($this->createUser());

        $this->post("/api/v1/admin/projects/{$project->id}/images", [
            'image' => UploadedFile::fake()->create('document.pdf', 100, 'application/pdf'),
        ])->assertUnprocessable()->assertJsonValidationErrors('image');
    }

    public function test_unknown_project_returns_not_found_for_image_routes(): void
    {
        Sanctum::actingAs($this->createUser());

        $this->getJson('/api/v1/admin/projects/999999/images')->assertNotFound();
        $this->post('/api/v1/admin/projects/999999/images', [
            'image' => $this->fakeImage(),
        ])->assertNotFound();
    }

    public function test_project_image_can_be_deleted_from_database_and_r2(): void
    {
        $project = $this->createProject();
        $image = $this->createProjectImage($project);
        Sanctum::actingAs($this->createUser());

        $this->deleteJson("/api/v1/admin/projects/{$project->id}/images/{$image->id}")
            ->assertOk()
            ->assertJsonPath('status', 1);

        $this->assertDatabaseMissing('project_images', ['id' => $image->id]);
        Storage::disk('r2')->assertMissing("images/projects/gallery/{$image->image_path}");
    }

    public function test_image_from_another_project_cannot_be_deleted_through_wrong_project(): void
    {
        $project = $this->createProject();
        $otherProject = $this->createProject();
        $image = $this->createProjectImage($otherProject);
        Sanctum::actingAs($this->createUser());

        $this->deleteJson("/api/v1/admin/projects/{$project->id}/images/{$image->id}")
            ->assertNotFound();

        $this->assertDatabaseHas('project_images', ['id' => $image->id]);
        Storage::disk('r2')->assertExists("images/projects/gallery/{$image->image_path}");
    }

    private function fakeImage(): UploadedFile
    {
        return UploadedFile::fake()->create('gallery.jpg', 100, 'image/jpeg');
    }

    private function createProjectImage(Project $project): ProjectImage
    {
        $image = ProjectImage::create([
            'project_id' => $project->id,
            'image_path' => 'gallery-'.uniqid().'.jpg',
        ]);
        Storage::disk('r2')->put("images/projects/gallery/{$image->image_path}", 'image');

        return $image;
    }

    private function createUser(): User
    {
        $this->userSequence++;

        return User::create([
            'name' => "Administrateur {$this->userSequence}",
            'username' => "imageadmin{$this->userSequence}",
            'telephone' => "62200000{$this->userSequence}",
            'email' => "imageadmin{$this->userSequence}@example.com",
            'role' => 'admin',
            'is_active' => true,
            'password' => 'password',
        ]);
    }

    private function createProject(): Project
    {
        $user = $this->createUser();
        $category = new Category([
            'name' => 'Projets',
            'description' => 'Catégorie de projets',
            'type' => 'mix',
            'is_active' => true,
        ]);
        $category->created_by = $user->id;
        $category->updated_by = $user->id;
        $category->save();

        return Project::create([
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
        ]);
    }
}
