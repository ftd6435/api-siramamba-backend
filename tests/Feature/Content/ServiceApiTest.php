<?php

namespace Tests\Feature\Content;

use App\Modules\Administration\Models\User;
use App\Modules\Content\Models\Service;
use App\Modules\Content\Models\ServiceImage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use RuntimeException;
use Tests\TestCase;

class ServiceApiTest extends TestCase
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
        config(['filesystems.disks.r2.public_url' => 'https://media.test']);
    }

    public function test_admin_service_routes_require_authentication(): void
    {
        $service = $this->createService();

        $this->getJson('/api/v1/admin/services')->assertUnauthorized();
        $this->post('/api/v1/admin/services', $this->validPayload())->assertUnauthorized();
        $this->getJson("/api/v1/admin/services/{$service->id}")->assertUnauthorized();
        $this->patchJson("/api/v1/admin/services/{$service->id}", ['sort_order' => 2])
            ->assertUnauthorized();
        $this->deleteJson("/api/v1/admin/services/{$service->id}")->assertUnauthorized();
    }

    public function test_public_index_only_returns_active_services_in_sort_order(): void
    {
        $second = $this->createService(['title' => 'Deuxième', 'sort_order' => 2]);
        $first = $this->createService(['title' => 'Premier', 'sort_order' => 1]);
        $sameOrder = $this->createService(['title' => 'Même ordre', 'sort_order' => 2]);
        $inactive = $this->createService([
            'title' => 'Inactif',
            'sort_order' => 0,
            'is_active' => false,
        ]);

        $response = $this->getJson('/api/v1/services')->assertOk();
        $ids = collect($response->json('data'))->pluck('id')->all();

        $this->assertSame([$first->id, $second->id, $sameOrder->id], $ids);
        $this->assertNotContains($inactive->id, $ids);
    }

    public function test_public_show_returns_active_service_and_hides_inactive_service(): void
    {
        $active = $this->createService();
        $inactive = $this->createService(['title' => 'Privé', 'is_active' => false]);

        $this->getJson("/api/v1/services/{$active->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $active->id);

        $this->getJson("/api/v1/services/{$inactive->id}")->assertNotFound();
    }

    public function test_public_index_does_not_expose_internal_user_data(): void
    {
        $this->createService();

        $response = $this->getJson('/api/v1/services')->assertOk();
        $serviceData = $response->json('data.0');

        $this->assertNoSensitiveAuditData($serviceData);
        $this->assertArrayNotHasKey('is_active', $serviceData);
    }

    public function test_public_show_does_not_expose_internal_user_data(): void
    {
        $service = $this->createService();

        $response = $this->getJson("/api/v1/services/{$service->id}")->assertOk();
        $serviceData = $response->json('data');

        $this->assertNoSensitiveAuditData($serviceData);
        $this->assertArrayNotHasKey('is_active', $serviceData);
    }

    public function test_admin_responses_keep_service_audit_information(): void
    {
        $creator = $this->createUser();
        $service = $this->createService([], $creator);
        Sanctum::actingAs($creator);

        $this->getJson('/api/v1/admin/services')
            ->assertOk()
            ->assertJsonPath('data.0.created_by.username', $creator->username)
            ->assertJsonPath('data.0.created_by.telephone', $creator->telephone)
            ->assertJsonPath('data.0.created_by.email', $creator->email)
            ->assertJsonPath('data.0.created_by.role', $creator->role)
            ->assertJsonPath('data.0.updated_by.id', $creator->id);

        $this->getJson("/api/v1/admin/services/{$service->id}")
            ->assertOk()
            ->assertJsonPath('data.created_by.id', $creator->id)
            ->assertJsonPath('data.updated_by.id', $creator->id);
    }

    public function test_creation_controls_audit_fields_and_uploads_thumbnail(): void
    {
        $authenticatedUser = $this->createUser();
        $spoofedUser = $this->createUser();
        Sanctum::actingAs($authenticatedUser);

        $response = $this->post('/api/v1/admin/services', $this->validPayload([
            'created_by' => $spoofedUser->id,
            'updated_by' => $spoofedUser->id,
        ]));

        $response->assertCreated()
            ->assertJsonPath('status', 1)
            ->assertJsonPath('data.created_by.id', $authenticatedUser->id)
            ->assertJsonPath('data.updated_by.id', $authenticatedUser->id);

        $service = Service::firstOrFail();

        $this->assertSame($authenticatedUser->id, $service->created_by);
        $this->assertSame($authenticatedUser->id, $service->updated_by);
        Storage::disk('r2')->assertExists("images/services/thumbnails/{$service->thumbnail}");
    }

    public function test_new_r2_files_are_removed_when_database_creation_fails(): void
    {
        Sanctum::actingAs($this->createUser());
        Service::creating(fn () => throw new RuntimeException('Échec BDD simulé.'));

        try {
            $this->post('/api/v1/admin/services', $this->validPayload([
                'images' => [$this->fakeImage('gallery.jpg')],
            ]))->assertInternalServerError();

            $this->assertSame([], Storage::disk('r2')->allFiles('images/services/thumbnails'));
            $this->assertSame([], Storage::disk('r2')->allFiles('images/services/images'));
            $this->assertDatabaseCount('services', 0);
            $this->assertDatabaseCount('service_images', 0);
        } finally {
            Service::flushEventListeners();
        }
    }

    public function test_patch_is_partial_and_preserves_creator(): void
    {
        $creator = $this->createUser();
        $updater = $this->createUser();
        $spoofedUser = $this->createUser();
        $service = $this->createService([], $creator);
        Sanctum::actingAs($updater);

        $this->patchJson("/api/v1/admin/services/{$service->id}", [
            'sort_order' => 3,
            'created_by' => $spoofedUser->id,
            'updated_by' => $spoofedUser->id,
        ])->assertOk()->assertJsonPath('data.sort_order', 3);

        $service->refresh();

        $this->assertSame('Conseil minier', $service->title);
        $this->assertSame(3, $service->sort_order);
        $this->assertSame($creator->id, $service->created_by);
        $this->assertSame($updater->id, $service->updated_by);
    }

    public function test_thumbnail_can_be_replaced_after_database_update(): void
    {
        $service = $this->createService();
        $oldThumbnail = $service->thumbnail;
        Sanctum::actingAs($this->createUser());

        $this->patch("/api/v1/admin/services/{$service->id}", [
            'thumbnail' => $this->fakeImage('replacement.jpg'),
        ])->assertOk();

        $service->refresh();

        $this->assertNotSame($oldThumbnail, $service->thumbnail);
        Storage::disk('r2')->assertMissing("images/services/thumbnails/{$oldThumbnail}");
        Storage::disk('r2')->assertExists("images/services/thumbnails/{$service->thumbnail}");
    }

    public function test_multiple_images_are_uploaded_and_linked_to_created_service(): void
    {
        Sanctum::actingAs($this->createUser());

        $this->post('/api/v1/admin/services', $this->validPayload([
            'images' => [
                $this->fakeImage('first.jpg'),
                $this->fakeImage('second.png', 'image/png'),
            ],
        ]))->assertCreated()->assertJsonCount(2, 'data.images');

        $service = Service::firstOrFail();
        $images = $service->images()->get();

        $this->assertCount(2, $images);

        foreach ($images as $image) {
            Storage::disk('r2')->assertExists("images/services/images/{$image->image_path}");
        }
    }

    public function test_preuploaded_image_can_be_attached_during_creation(): void
    {
        $image = $this->createServiceImage();
        Sanctum::actingAs($this->createUser());

        $this->post('/api/v1/admin/services', $this->validPayload([
            'image_ids' => [$image->id],
        ]))->assertCreated();

        $this->assertSame(Service::firstOrFail()->id, $image->refresh()->service_id);
    }

    public function test_unknown_image_cannot_be_attached(): void
    {
        Sanctum::actingAs($this->createUser());

        $this->post('/api/v1/admin/services', $this->validPayload([
            'image_ids' => [999999],
        ]))->assertUnprocessable()->assertJsonValidationErrors('image_ids.0');

        $this->assertDatabaseCount('services', 0);
    }

    public function test_image_from_another_service_cannot_be_attached(): void
    {
        $otherService = $this->createService();
        $image = $this->createServiceImage($otherService);
        Sanctum::actingAs($this->createUser());

        $this->post('/api/v1/admin/services', $this->validPayload([
            'image_ids' => [$image->id],
        ]))->assertUnprocessable()->assertJsonValidationErrors('image_ids.0');

        $this->assertSame($otherService->id, $image->refresh()->service_id);
    }

    public function test_update_attaches_images_additively(): void
    {
        $service = $this->createService();
        $existingImage = $this->createServiceImage($service);
        $orphanImage = $this->createServiceImage();
        Sanctum::actingAs($this->createUser());

        $this->patchJson("/api/v1/admin/services/{$service->id}", [
            'image_ids' => [$existingImage->id, $orphanImage->id],
        ])->assertOk()->assertJsonCount(2, 'data.images');

        $this->assertSame($service->id, $existingImage->refresh()->service_id);
        $this->assertSame($service->id, $orphanImage->refresh()->service_id);
        $this->assertCount(2, $service->images()->get());
    }

    public function test_update_rejects_image_from_another_service(): void
    {
        $service = $this->createService();
        $otherService = $this->createService();
        $image = $this->createServiceImage($otherService);
        Sanctum::actingAs($this->createUser());

        $this->patchJson("/api/v1/admin/services/{$service->id}", [
            'image_ids' => [$image->id],
        ])->assertUnprocessable()->assertJsonValidationErrors('image_ids.0');

        $this->assertSame($otherService->id, $image->refresh()->service_id);
    }

    public function test_destroy_removes_service_images_and_all_r2_media(): void
    {
        $service = $this->createService();
        $firstImage = $this->createServiceImage($service);
        $secondImage = $this->createServiceImage($service);
        Sanctum::actingAs($this->createUser());

        $this->deleteJson("/api/v1/admin/services/{$service->id}")
            ->assertOk()
            ->assertJsonPath('status', 1);

        $this->assertDatabaseMissing('services', ['id' => $service->id]);
        $this->assertDatabaseMissing('service_images', ['service_id' => $service->id]);
        Storage::disk('r2')->assertMissing("images/services/thumbnails/{$service->thumbnail}");
        Storage::disk('r2')->assertMissing("images/services/images/{$firstImage->image_path}");
        Storage::disk('r2')->assertMissing("images/services/images/{$secondImage->image_path}");
    }

    private function createUser(): User
    {
        $this->userSequence++;

        return User::create([
            'name' => "Administrateur {$this->userSequence}",
            'username' => "serviceadmin{$this->userSequence}",
            'telephone' => "62400000{$this->userSequence}",
            'email' => "serviceadmin{$this->userSequence}@example.com",
            'role' => 'admin',
            'is_active' => true,
            'password' => 'password',
        ]);
    }

    private function assertNoSensitiveAuditData(array $serviceData): void
    {
        $json = json_encode($serviceData, JSON_THROW_ON_ERROR);

        foreach (['created_by', 'updated_by', 'creator', 'updater', 'username', 'telephone', 'email', 'role'] as $field) {
            $this->assertStringNotContainsString('"'.$field.'":', $json);
        }
    }

    private function createService(array $attributes = [], ?User $user = null): Service
    {
        $user ??= $this->createUser();
        $service = new Service(array_merge([
            'title' => 'Conseil minier',
            'short_description' => 'Description courte',
            'description' => 'Description complète',
            'sort_order' => 1,
            'thumbnail' => 'thumbnail-'.uniqid().'.jpg',
            'is_active' => true,
        ], $attributes));
        $service->created_by = $user->id;
        $service->updated_by = $user->id;
        $service->save();

        Storage::disk('r2')->put("images/services/thumbnails/{$service->thumbnail}", 'image');

        return $service;
    }

    private function createServiceImage(?Service $service = null): ServiceImage
    {
        $image = ServiceImage::create([
            'service_id' => $service?->id,
            'image_path' => 'service-'.uniqid().'.jpg',
        ]);

        Storage::disk('r2')->put("images/services/images/{$image->image_path}", 'image');

        return $image;
    }

    private function fakeImage(string $name = 'image.jpg', string $mimeType = 'image/jpeg'): UploadedFile
    {
        return UploadedFile::fake()->create($name, 100, $mimeType);
    }

    private function validPayload(array $attributes = []): array
    {
        return array_merge([
            'title' => 'Conseil minier',
            'short_description' => 'Description courte',
            'description' => 'Description complète',
            'sort_order' => 1,
            'thumbnail' => $this->fakeImage('thumbnail.jpg'),
            'is_active' => true,
        ], $attributes);
    }
}
