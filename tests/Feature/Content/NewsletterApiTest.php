<?php

namespace Tests\Feature\Content;

use App\Modules\Administration\Models\User;
use App\Modules\Content\Models\Newsletter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class NewsletterApiTest extends TestCase
{
    use RefreshDatabase;

    private int $userSequence = 0;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withHeader('Accept', 'application/json');
    }

    public function test_public_user_can_subscribe_with_a_name(): void
    {
        $response = $this->postJson('/api/v1/newsletters', [
            'name' => 'Mohamed',
            'email' => 'mohamed@example.com',
        ]);

        $response->assertCreated()
            ->assertJsonPath('status', 1)
            ->assertJsonPath('data.name', 'Mohamed')
            ->assertJsonPath('data.email', 'mohamed@example.com')
            ->assertJsonPath('data.status', 'attente');

        $this->assertDatabaseHas('newsletters', [
            'name' => 'Mohamed',
            'email' => 'mohamed@example.com',
            'status' => 'attente',
        ]);
    }

    public function test_public_user_can_subscribe_without_a_name(): void
    {
        $this->postJson('/api/v1/newsletters', [
            'email' => 'mohamed@example.com',
        ])->assertCreated()
            ->assertJsonPath('data.name', null)
            ->assertJsonPath('data.status', 'attente');

        $this->assertDatabaseHas('newsletters', [
            'name' => null,
            'email' => 'mohamed@example.com',
            'status' => 'attente',
        ]);
    }

    public function test_email_is_required_and_must_be_valid(): void
    {
        $this->postJson('/api/v1/newsletters', [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('email');

        $this->postJson('/api/v1/newsletters', ['email' => 'bonjour'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('email');

        $this->assertDatabaseCount('newsletters', 0);
    }

    public function test_public_user_cannot_force_the_verified_status(): void
    {
        $this->postJson('/api/v1/newsletters', [
            'email' => 'mohamed@example.com',
            'status' => 'verifier',
        ])->assertCreated()->assertJsonPath('data.status', 'attente');

        $this->assertDatabaseHas('newsletters', [
            'email' => 'mohamed@example.com',
            'status' => 'attente',
        ]);
    }

    public function test_duplicate_emails_are_allowed(): void
    {
        $payload = ['email' => 'mohamed@example.com'];

        $this->postJson('/api/v1/newsletters', $payload)->assertCreated();
        $this->postJson('/api/v1/newsletters', $payload)->assertCreated();

        $this->assertDatabaseCount('newsletters', 2);
    }

    public function test_admin_newsletter_routes_require_authentication(): void
    {
        $newsletter = $this->createNewsletter();

        $this->getJson('/api/v1/admin/newsletters')->assertUnauthorized();
        $this->getJson("/api/v1/admin/newsletters/{$newsletter->id}")->assertUnauthorized();
        $this->patchJson("/api/v1/admin/newsletters/{$newsletter->id}", [
            'status' => 'verifier',
        ])->assertUnauthorized();
        $this->deleteJson("/api/v1/admin/newsletters/{$newsletter->id}")->assertUnauthorized();

        $this->assertDatabaseHas('newsletters', [
            'id' => $newsletter->id,
            'status' => 'attente',
        ]);
    }

    public function test_authenticated_user_can_list_newsletters(): void
    {
        $newsletter = $this->createNewsletter();
        Sanctum::actingAs($this->createUser());

        $this->getJson('/api/v1/admin/newsletters')
            ->assertOk()
            ->assertJsonPath('status', 1)
            ->assertJsonPath('data.0.id', $newsletter->id)
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [[
                    'id',
                    'name',
                    'email',
                    'status',
                    'created_at',
                    'updated_at',
                ]],
            ]);
    }

    public function test_authenticated_user_can_show_a_newsletter(): void
    {
        $newsletter = $this->createNewsletter();
        Sanctum::actingAs($this->createUser());

        $this->getJson("/api/v1/admin/newsletters/{$newsletter->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $newsletter->id)
            ->assertJsonPath('data.name', $newsletter->name)
            ->assertJsonPath('data.email', $newsletter->email)
            ->assertJsonPath('data.status', 'attente');
    }

    public function test_unknown_newsletter_returns_not_found(): void
    {
        Sanctum::actingAs($this->createUser());

        $this->getJson('/api/v1/admin/newsletters/999999')->assertNotFound();
    }

    public function test_patch_only_updates_the_status(): void
    {
        $newsletter = $this->createNewsletter();
        Sanctum::actingAs($this->createUser());

        $this->patchJson("/api/v1/admin/newsletters/{$newsletter->id}", [
            'status' => 'verifier',
        ])->assertOk()->assertJsonPath('data.status', 'verifier');

        $newsletter->refresh();

        $this->assertSame('Mohamed', $newsletter->name);
        $this->assertSame('mohamed@example.com', $newsletter->email);
        $this->assertSame('verifier', $newsletter->status);
    }

    public function test_invalid_status_is_rejected_without_updating_the_newsletter(): void
    {
        $newsletter = $this->createNewsletter();
        Sanctum::actingAs($this->createUser());

        $this->patchJson("/api/v1/admin/newsletters/{$newsletter->id}", [
            'status' => 'active',
        ])->assertUnprocessable()->assertJsonValidationErrors('status');

        $this->assertSame('attente', $newsletter->fresh()->status);
    }

    public function test_authenticated_user_can_delete_a_newsletter(): void
    {
        $newsletter = $this->createNewsletter();
        Sanctum::actingAs($this->createUser());

        $this->deleteJson("/api/v1/admin/newsletters/{$newsletter->id}")
            ->assertOk()
            ->assertJsonPath('status', 1);

        $this->assertDatabaseMissing('newsletters', ['id' => $newsletter->id]);
    }

    private function createNewsletter(array $attributes = []): Newsletter
    {
        return Newsletter::create(array_merge([
            'name' => 'Mohamed',
            'email' => 'mohamed@example.com',
            'status' => 'attente',
        ], $attributes));
    }

    private function createUser(): User
    {
        $this->userSequence++;

        return User::create([
            'name' => "Administrateur {$this->userSequence}",
            'username' => "newsletteradmin{$this->userSequence}",
            'telephone' => "62900000{$this->userSequence}",
            'email' => "newsletteradmin{$this->userSequence}@example.com",
            'role' => 'admin',
            'is_active' => true,
            'password' => 'password',
        ]);
    }
}
