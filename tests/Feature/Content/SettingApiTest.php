<?php

namespace Tests\Feature\Content;

use App\Modules\Administration\Models\User;
use App\Modules\Content\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use RuntimeException;
use Tests\TestCase;

class SettingApiTest extends TestCase
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

    public function test_admin_setting_routes_require_authentication(): void
    {
        $setting = $this->createSetting();

        $this->getJson('/api/v1/admin/settings')->assertUnauthorized();
        $this->postJson('/api/v1/admin/settings', $this->textPayload())->assertUnauthorized();
        $this->getJson("/api/v1/admin/settings/{$setting->id}")->assertUnauthorized();
        $this->patchJson("/api/v1/admin/settings/{$setting->id}", ['key' => 'new_key'])
            ->assertUnauthorized();
        $this->deleteJson("/api/v1/admin/settings/{$setting->id}")->assertUnauthorized();
    }

    public function test_authenticated_user_can_manage_text_settings(): void
    {
        Sanctum::actingAs($this->createUser());

        $response = $this->postJson('/api/v1/admin/settings', $this->textPayload());

        $response->assertCreated()
            ->assertJsonPath('status', 1)
            ->assertJsonPath('data.key', 'site_name')
            ->assertJsonPath('data.type', 'text')
            ->assertJsonPath('data.value', 'SPA Technology')
            ->assertJsonStructure(['data' => ['id', 'key', 'value', 'type', 'created_at', 'updated_at']]);

        $setting = Setting::firstOrFail();

        $this->assertSame('SPA Technology', $setting->value);

        $this->getJson('/api/v1/admin/settings')
            ->assertOk()
            ->assertJsonPath('data.0.id', $setting->id);

        $this->getJson("/api/v1/admin/settings/{$setting->id}")
            ->assertOk()
            ->assertJsonPath('data.value', 'SPA Technology');

        $this->deleteJson("/api/v1/admin/settings/{$setting->id}")
            ->assertOk()
            ->assertJsonPath('status', 1);

        $this->assertDatabaseMissing('settings', ['id' => $setting->id]);
    }

    public function test_duplicate_keys_are_allowed(): void
    {
        Sanctum::actingAs($this->createUser());

        $this->postJson('/api/v1/admin/settings', $this->textPayload())->assertCreated();
        $this->postJson('/api/v1/admin/settings', $this->textPayload([
            'value' => 'Autre valeur',
        ]))->assertCreated();

        $this->assertDatabaseCount('settings', 2);
        $this->assertSame(2, Setting::where('key', 'site_name')->count());
    }

    public function test_json_values_are_stored_as_text_and_returned_decoded(): void
    {
        Sanctum::actingAs($this->createUser());
        $json = '{"facebook":"https://example.com","networks":["linkedin","x"]}';

        $this->postJson('/api/v1/admin/settings', [
            'key' => 'social_links',
            'type' => 'json',
            'value' => $json,
        ])->assertCreated()
            ->assertJsonPath('data.value.facebook', 'https://example.com')
            ->assertJsonPath('data.value.networks.1', 'x');

        $this->assertDatabaseHas('settings', [
            'key' => 'social_links',
            'type' => 'json',
            'value' => $json,
        ]);
    }

    public function test_json_scalar_is_accepted_and_invalid_json_is_rejected(): void
    {
        Sanctum::actingAs($this->createUser());

        $this->postJson('/api/v1/admin/settings', [
            'key' => 'items_per_page',
            'type' => 'json',
            'value' => '42',
        ])->assertCreated()->assertJsonPath('data.value', 42);

        $this->postJson('/api/v1/admin/settings', [
            'key' => 'broken_json',
            'type' => 'json',
            'value' => '{"missing":}',
        ])->assertUnprocessable()->assertJsonValidationErrors('value');

        $this->assertDatabaseMissing('settings', ['key' => 'broken_json']);
    }

    public function test_empty_json_object_and_array_keep_their_original_shape(): void
    {
        Sanctum::actingAs($this->createUser());

        $objectResponse = $this->postJson('/api/v1/admin/settings', [
            'key' => 'empty_object',
            'type' => 'json',
            'value' => '{}',
        ])->assertCreated();

        $arrayResponse = $this->postJson('/api/v1/admin/settings', [
            'key' => 'empty_array',
            'type' => 'json',
            'value' => '[]',
        ])->assertCreated();

        $this->assertStringContainsString('"value":{}', $objectResponse->getContent());
        $this->assertStringContainsString('"value":[]', $arrayResponse->getContent());
    }

    public function test_boolean_contract_is_normalized_and_returned_as_boolean(): void
    {
        Sanctum::actingAs($this->createUser());
        $cases = [
            [true, '1', true],
            [false, '0', false],
            [1, '1', true],
            [0, '0', false],
            ['1', '1', true],
            ['0', '0', false],
        ];

        foreach ($cases as $index => [$input, $stored, $expected]) {
            $response = $this->postJson('/api/v1/admin/settings', [
                'key' => "boolean_{$index}",
                'type' => 'boolean',
                'value' => $input,
            ])->assertCreated();

            $this->assertSame($expected, $response->json('data.value'));
            $this->assertDatabaseHas('settings', [
                'key' => "boolean_{$index}",
                'value' => $stored,
            ]);
        }

        $this->postJson('/api/v1/admin/settings', [
            'key' => 'unsupported_boolean',
            'type' => 'boolean',
            'value' => 'yes',
        ])->assertUnprocessable()->assertJsonValidationErrors('value');
    }

    public function test_image_is_uploaded_and_returned_as_an_r2_url(): void
    {
        Sanctum::actingAs($this->createUser());

        $response = $this->post('/api/v1/admin/settings', [
            'key' => 'footer_logo',
            'type' => 'image',
            'value' => $this->fakeImage('footer.png', 'image/png'),
        ])->assertCreated();

        $setting = Setting::firstOrFail();

        Storage::disk('r2')->assertExists("images/settings/{$setting->value}");
        $this->assertSame(
            "https://r2.test/images/settings/{$setting->value}",
            $response->json('data.value')
        );
    }

    public function test_non_image_file_is_rejected_before_upload(): void
    {
        Sanctum::actingAs($this->createUser());

        $this->post('/api/v1/admin/settings', [
            'key' => 'footer_logo',
            'type' => 'image',
            'value' => UploadedFile::fake()->create('document.pdf', 100, 'application/pdf'),
        ])->assertUnprocessable()->assertJsonValidationErrors('value');

        $this->assertDatabaseCount('settings', 0);
        $this->assertSame([], Storage::disk('r2')->allFiles('images/settings'));
    }

    public function test_new_image_is_removed_when_database_creation_fails(): void
    {
        Sanctum::actingAs($this->createUser());
        Setting::creating(fn () => throw new RuntimeException('Échec BDD simulé.'));

        try {
            $this->post('/api/v1/admin/settings', [
                'key' => 'footer_logo',
                'type' => 'image',
                'value' => $this->fakeImage(),
            ])->assertInternalServerError();

            $this->assertDatabaseCount('settings', 0);
            $this->assertSame([], Storage::disk('r2')->allFiles('images/settings'));
        } finally {
            Setting::flushEventListeners();
        }
    }

    public function test_patch_is_partial_and_validates_value_against_the_effective_type(): void
    {
        $setting = $this->createSetting();
        Sanctum::actingAs($this->createUser());

        $this->patchJson("/api/v1/admin/settings/{$setting->id}", [
            'key' => 'renamed_setting',
        ])->assertOk()->assertJsonPath('data.value', 'Bonjour');

        $this->patchJson("/api/v1/admin/settings/{$setting->id}", [
            'type' => 'text',
        ])->assertOk();

        $this->patchJson("/api/v1/admin/settings/{$setting->id}", [
            'value' => 'Au revoir',
        ])->assertOk()->assertJsonPath('data.value', 'Au revoir');

        $setting->refresh();
        $this->assertSame('renamed_setting', $setting->key);
        $this->assertSame('text', $setting->type);
        $this->assertSame('Au revoir', $setting->value);
    }

    public function test_real_type_change_requires_a_compatible_new_value(): void
    {
        $setting = $this->createSetting();
        Sanctum::actingAs($this->createUser());

        $this->patchJson("/api/v1/admin/settings/{$setting->id}", [
            'type' => 'json',
        ])->assertUnprocessable()->assertJsonValidationErrors('value');

        $this->patchJson("/api/v1/admin/settings/{$setting->id}", [
            'type' => 'json',
            'value' => 'not-json',
        ])->assertUnprocessable()->assertJsonValidationErrors('value');

        $this->assertSame('text', $setting->fresh()->type);
        $this->assertSame('Bonjour', $setting->fresh()->value);
    }

    public function test_non_image_types_can_change_with_compatible_values(): void
    {
        $setting = $this->createSetting();
        Sanctum::actingAs($this->createUser());

        $this->patchJson("/api/v1/admin/settings/{$setting->id}", [
            'type' => 'json',
            'value' => '["first","second"]',
        ])->assertOk()->assertJsonPath('data.value.1', 'second');

        $this->patchJson("/api/v1/admin/settings/{$setting->id}", [
            'type' => 'boolean',
            'value' => false,
        ])->assertOk()->assertJsonPath('data.value', false);

        $this->patchJson("/api/v1/admin/settings/{$setting->id}", [
            'type' => 'text',
            'value' => 'Retour au texte',
        ])->assertOk()->assertJsonPath('data.value', 'Retour au texte');
    }

    public function test_image_can_be_kept_or_replaced_after_database_update(): void
    {
        $setting = $this->createImageSetting();
        $oldImage = $setting->value;
        Sanctum::actingAs($this->createUser());

        $this->patchJson("/api/v1/admin/settings/{$setting->id}", [
            'type' => 'image',
            'key' => 'renamed_logo',
        ])->assertOk();

        $this->assertSame($oldImage, $setting->fresh()->value);
        Storage::disk('r2')->assertExists("images/settings/{$oldImage}");

        $this->patch("/api/v1/admin/settings/{$setting->id}", [
            'value' => $this->fakeImage('replacement.png', 'image/png'),
        ])->assertOk();

        $setting->refresh();
        $this->assertNotSame($oldImage, $setting->value);
        Storage::disk('r2')->assertMissing("images/settings/{$oldImage}");
        Storage::disk('r2')->assertExists("images/settings/{$setting->value}");
    }

    public function test_failed_image_replacement_keeps_old_image_and_removes_new_file(): void
    {
        $setting = $this->createImageSetting();
        $oldImage = $setting->value;
        Sanctum::actingAs($this->createUser());
        Setting::updating(fn () => throw new RuntimeException('Échec BDD simulé.'));

        try {
            $this->patch("/api/v1/admin/settings/{$setting->id}", [
                'value' => $this->fakeImage('replacement.jpg'),
            ])->assertInternalServerError();

            $this->assertSame($oldImage, $setting->fresh()->value);
            Storage::disk('r2')->assertExists("images/settings/{$oldImage}");
            $this->assertSame(
                ["images/settings/{$oldImage}"],
                Storage::disk('r2')->allFiles('images/settings')
            );
        } finally {
            Setting::flushEventListeners();
        }
    }

    public function test_image_can_change_to_non_image_only_after_database_success(): void
    {
        $setting = $this->createImageSetting();
        $oldImage = $setting->value;
        Sanctum::actingAs($this->createUser());

        $this->patchJson("/api/v1/admin/settings/{$setting->id}", [
            'type' => 'json',
            'value' => '{"enabled":true}',
        ])->assertOk()->assertJsonPath('data.value.enabled', true);

        $setting->refresh();
        $this->assertSame('json', $setting->type);
        Storage::disk('r2')->assertMissing("images/settings/{$oldImage}");
    }

    public function test_failed_image_to_non_image_change_preserves_the_old_image(): void
    {
        $setting = $this->createImageSetting();
        $oldImage = $setting->value;
        Sanctum::actingAs($this->createUser());
        Setting::updating(fn () => throw new RuntimeException('Échec BDD simulé.'));

        try {
            $this->patchJson("/api/v1/admin/settings/{$setting->id}", [
                'type' => 'text',
                'value' => 'Texte de remplacement',
            ])->assertInternalServerError();

            $setting->refresh();
            $this->assertSame('image', $setting->type);
            $this->assertSame($oldImage, $setting->value);
            Storage::disk('r2')->assertExists("images/settings/{$oldImage}");
        } finally {
            Setting::flushEventListeners();
        }
    }

    public function test_non_image_can_change_to_image(): void
    {
        $setting = $this->createSetting();
        Sanctum::actingAs($this->createUser());

        $response = $this->patch("/api/v1/admin/settings/{$setting->id}", [
            'type' => 'image',
            'value' => $this->fakeImage('new-logo.jpg'),
        ])->assertOk();

        $setting->refresh();
        $this->assertSame('image', $setting->type);
        Storage::disk('r2')->assertExists("images/settings/{$setting->value}");
        $this->assertSame(
            "https://r2.test/images/settings/{$setting->value}",
            $response->json('data.value')
        );
    }

    public function test_destroy_removes_image_setting_from_database_and_r2(): void
    {
        $setting = $this->createImageSetting();
        $image = $setting->value;
        Sanctum::actingAs($this->createUser());

        $this->deleteJson("/api/v1/admin/settings/{$setting->id}")
            ->assertOk()
            ->assertJsonPath('status', 1);

        $this->assertDatabaseMissing('settings', ['id' => $setting->id]);
        Storage::disk('r2')->assertMissing("images/settings/{$image}");
    }

    public function test_no_public_settings_endpoint_exists(): void
    {
        $this->createSetting();

        $this->getJson('/api/v1/settings')->assertNotFound();
    }

    private function createUser(): User
    {
        $this->userSequence++;

        return User::create([
            'name' => "Administrateur {$this->userSequence}",
            'username' => "settingadmin{$this->userSequence}",
            'telephone' => "62800000{$this->userSequence}",
            'email' => "settingadmin{$this->userSequence}@example.com",
            'role' => 'admin',
            'is_active' => true,
            'password' => 'password',
        ]);
    }

    private function createSetting(array $attributes = []): Setting
    {
        return Setting::create(array_merge([
            'key' => 'welcome_message',
            'type' => 'text',
            'value' => 'Bonjour',
        ], $attributes));
    }

    private function createImageSetting(): Setting
    {
        $setting = $this->createSetting([
            'key' => 'footer_logo',
            'type' => 'image',
            'value' => 'logo-'.uniqid().'.jpg',
        ]);

        Storage::disk('r2')->put("images/settings/{$setting->value}", 'image');

        return $setting;
    }

    private function fakeImage(
        string $name = 'setting.jpg',
        string $mimeType = 'image/jpeg'
    ): UploadedFile {
        return UploadedFile::fake()->create($name, 100, $mimeType);
    }

    private function textPayload(array $attributes = []): array
    {
        return array_merge([
            'key' => 'site_name',
            'type' => 'text',
            'value' => 'SPA Technology',
        ], $attributes);
    }
}
