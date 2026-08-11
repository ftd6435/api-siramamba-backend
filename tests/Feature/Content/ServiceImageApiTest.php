<?php

namespace Tests\Feature\Content;

use App\Modules\Administration\Models\User;
use App\Modules\Content\Models\Service;
use App\Modules\Content\Models\ServiceImage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ServiceImageApiTest extends TestCase
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
        config(['filesystems.disks.r2.public_url' => 'https://media.test/']);
    }

    public function test_service_image_routes_require_authentication(): void
    {
        $service = $this->createService();
        $image = $this->createServiceImage($service);

        $this->post('/api/v1/admin/services/images', ['image' => $this->fakeImage()])
            ->assertUnauthorized();
        $this->deleteJson("/api/v1/admin/services/{$service->id}/images/{$image->id}")
            ->assertUnauthorized();
    }

    public function test_editor_upload_creates_orphan_with_permanent_url(): void
    {
        Sanctum::actingAs($this->createUser());

        $response = $this->post('/api/v1/admin/services/images', [
            'image' => $this->fakeImage(),
        ]);

        $response->assertCreated()->assertJsonPath('status', 1);

        $image = ServiceImage::firstOrFail();

        $this->assertNull($image->service_id);
        $this->assertSame(
            "https://media.test/images/services/images/{$image->image_path}",
            $response->json('data.url')
        );
        Storage::disk('r2')->assertExists("images/services/images/{$image->image_path}");
    }

    public function test_editor_upload_fails_explicitly_without_public_r2_url(): void
    {
        config(['filesystems.disks.r2.public_url' => null]);
        Sanctum::actingAs($this->createUser());

        $this->post('/api/v1/admin/services/images', [
            'image' => $this->fakeImage(),
        ])->assertStatus(503)->assertJsonPath('status', 0);

        $this->assertDatabaseCount('service_images', 0);
    }

    public function test_service_image_is_deleted_from_database_and_r2(): void
    {
        $service = $this->createService();
        $image = $this->createServiceImage($service);
        Sanctum::actingAs($this->createUser());

        $this->deleteJson("/api/v1/admin/services/{$service->id}/images/{$image->id}")
            ->assertOk()
            ->assertJsonPath('status', 1);

        $this->assertDatabaseMissing('service_images', ['id' => $image->id]);
        Storage::disk('r2')->assertMissing("images/services/images/{$image->image_path}");
    }

    public function test_image_from_another_service_cannot_be_deleted_through_wrong_service(): void
    {
        $service = $this->createService();
        $otherService = $this->createService();
        $image = $this->createServiceImage($otherService);
        Sanctum::actingAs($this->createUser());

        $this->deleteJson("/api/v1/admin/services/{$service->id}/images/{$image->id}")
            ->assertNotFound();

        $this->assertDatabaseHas('service_images', ['id' => $image->id]);
        Storage::disk('r2')->assertExists("images/services/images/{$image->image_path}");
    }

    private function createUser(): User
    {
        $this->userSequence++;

        return User::create([
            'name' => "Administrateur {$this->userSequence}",
            'username' => "editoradmin{$this->userSequence}",
            'telephone' => "62500000{$this->userSequence}",
            'email' => "editoradmin{$this->userSequence}@example.com",
            'role' => 'admin',
            'is_active' => true,
            'password' => 'password',
        ]);
    }

    private function createService(): Service
    {
        $user = $this->createUser();
        $service = new Service([
            'title' => 'Conseil minier',
            'short_description' => 'Description courte',
            'description' => 'Description complète',
            'sort_order' => 1,
            'thumbnail' => 'thumbnail-'.uniqid().'.jpg',
            'is_active' => true,
        ]);
        $service->created_by = $user->id;
        $service->updated_by = $user->id;
        $service->save();

        Storage::disk('r2')->put("images/services/thumbnails/{$service->thumbnail}", 'image');

        return $service;
    }

    private function createServiceImage(Service $service): ServiceImage
    {
        $image = ServiceImage::create([
            'service_id' => $service->id,
            'image_path' => 'service-'.uniqid().'.jpg',
        ]);

        Storage::disk('r2')->put("images/services/images/{$image->image_path}", 'image');

        return $image;
    }

    private function fakeImage(): UploadedFile
    {
        return UploadedFile::fake()->create('editor.jpg', 100, 'image/jpeg');
    }
}
