<?php

namespace Tests\Feature\Content;

use App\Modules\Administration\Models\User;
use App\Modules\Content\Models\Team;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use RuntimeException;
use Tests\TestCase;

class TeamApiTest extends TestCase
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

    public function test_admin_team_routes_require_authentication(): void
    {
        $team = $this->createTeam();

        $this->getJson('/api/v1/admin/teams')->assertUnauthorized();
        $this->post('/api/v1/admin/teams', $this->validPayload())->assertUnauthorized();
        $this->getJson("/api/v1/admin/teams/{$team->id}")->assertUnauthorized();
        $this->patchJson("/api/v1/admin/teams/{$team->id}", ['post' => 'Lead Developer'])
            ->assertUnauthorized();
        $this->deleteJson("/api/v1/admin/teams/{$team->id}")->assertUnauthorized();
    }

    public function test_authenticated_user_can_create_a_team_member_with_avatar_and_secure_audit(): void
    {
        $authenticatedUser = $this->createUser();
        $spoofedUser = $this->createUser();
        Sanctum::actingAs($authenticatedUser);

        $response = $this->post('/api/v1/admin/teams', $this->validPayload([
            'created_by' => $spoofedUser->id,
            'updated_by' => $spoofedUser->id,
        ]));

        $response->assertCreated()
            ->assertJsonPath('status', 1)
            ->assertJsonPath('data.name', 'Mamadou Diallo')
            ->assertJsonPath('data.post', 'Backend Developer')
            ->assertJsonPath('data.short_description', 'Développe les API de la plateforme.')
            ->assertJsonPath('data.is_active', true)
            ->assertJsonPath('data.created_by.id', $authenticatedUser->id)
            ->assertJsonPath('data.updated_by.id', $authenticatedUser->id);

        $team = Team::firstOrFail();

        $this->assertSame($authenticatedUser->id, $team->created_by);
        $this->assertSame($authenticatedUser->id, $team->updated_by);
        Storage::disk('r2')->assertExists("images/teams/avatars/{$team->avatar}");
        $this->assertSame(
            "https://r2.test/images/teams/avatars/{$team->avatar}",
            $response->json('data.avatar_url')
        );
    }

    public function test_invalid_avatar_is_rejected_without_creating_a_team_member(): void
    {
        Sanctum::actingAs($this->createUser());

        $this->post('/api/v1/admin/teams', $this->validPayload([
            'avatar' => UploadedFile::fake()->create('document.pdf', 100, 'application/pdf'),
        ]))->assertUnprocessable()->assertJsonValidationErrors('avatar');

        $this->assertDatabaseCount('teams', 0);
        $this->assertSame([], Storage::disk('r2')->allFiles('images/teams/avatars'));
    }

    public function test_new_avatar_is_removed_when_database_creation_fails(): void
    {
        Sanctum::actingAs($this->createUser());
        Team::creating(fn () => throw new RuntimeException('Échec BDD simulé.'));

        try {
            $this->post('/api/v1/admin/teams', $this->validPayload())
                ->assertInternalServerError();

            $this->assertDatabaseCount('teams', 0);
            $this->assertSame([], Storage::disk('r2')->allFiles('images/teams/avatars'));
        } finally {
            Team::flushEventListeners();
        }
    }

    public function test_admin_can_list_and_show_active_and_inactive_team_members(): void
    {
        $user = $this->createUser();
        $active = $this->createTeam(['name' => 'Membre actif'], $user);
        $inactive = $this->createTeam([
            'name' => 'Membre inactif',
            'is_active' => false,
        ], $user);
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/admin/teams')->assertOk();
        $ids = collect($response->json('data'))->pluck('id')->all();

        $this->assertContains($active->id, $ids);
        $this->assertContains($inactive->id, $ids);

        $this->getJson("/api/v1/admin/teams/{$inactive->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $inactive->id)
            ->assertJsonPath('data.is_active', false)
            ->assertJsonPath('data.created_by.id', $user->id)
            ->assertJsonPath('data.updated_by.id', $user->id);
    }

    public function test_patch_is_partial_preserves_avatar_and_creator_and_updates_updater(): void
    {
        $creator = $this->createUser();
        $updater = $this->createUser();
        $spoofedUser = $this->createUser();
        $team = $this->createTeam([], $creator);
        $oldAvatar = $team->avatar;
        Sanctum::actingAs($updater);

        $this->patchJson("/api/v1/admin/teams/{$team->id}", [
            'post' => 'Lead Developer',
            'created_by' => $spoofedUser->id,
            'updated_by' => $spoofedUser->id,
        ])->assertOk()
            ->assertJsonPath('data.post', 'Lead Developer')
            ->assertJsonPath('data.created_by.id', $creator->id)
            ->assertJsonPath('data.updated_by.id', $updater->id);

        $team->refresh();

        $this->assertSame('Mamadou Diallo', $team->name);
        $this->assertSame('Lead Developer', $team->post);
        $this->assertSame($oldAvatar, $team->avatar);
        $this->assertSame($creator->id, $team->created_by);
        $this->assertSame($updater->id, $team->updated_by);
        Storage::disk('r2')->assertExists("images/teams/avatars/{$oldAvatar}");
    }

    public function test_avatar_can_be_replaced_after_database_update(): void
    {
        $team = $this->createTeam();
        $oldAvatar = $team->avatar;
        Sanctum::actingAs($this->createUser());

        $this->patch("/api/v1/admin/teams/{$team->id}", [
            'avatar' => $this->fakeImage('replacement.png', 'image/png'),
        ])->assertOk();

        $team->refresh();

        $this->assertNotSame($oldAvatar, $team->avatar);
        Storage::disk('r2')->assertMissing("images/teams/avatars/{$oldAvatar}");
        Storage::disk('r2')->assertExists("images/teams/avatars/{$team->avatar}");
    }

    public function test_failed_avatar_replacement_keeps_old_avatar_and_removes_new_file(): void
    {
        $team = $this->createTeam();
        $oldAvatar = $team->avatar;
        Sanctum::actingAs($this->createUser());
        Team::updating(fn () => throw new RuntimeException('Échec BDD simulé.'));

        try {
            $this->patch("/api/v1/admin/teams/{$team->id}", [
                'avatar' => $this->fakeImage('replacement.jpg'),
            ])->assertInternalServerError();

            $this->assertSame($oldAvatar, $team->fresh()->avatar);
            Storage::disk('r2')->assertExists("images/teams/avatars/{$oldAvatar}");
            $this->assertSame(
                ["images/teams/avatars/{$oldAvatar}"],
                Storage::disk('r2')->allFiles('images/teams/avatars')
            );
        } finally {
            Team::flushEventListeners();
        }
    }

    public function test_destroy_removes_team_member_and_avatar(): void
    {
        $team = $this->createTeam();
        $avatar = $team->avatar;
        Sanctum::actingAs($this->createUser());

        $this->deleteJson("/api/v1/admin/teams/{$team->id}")
            ->assertOk()
            ->assertJsonPath('status', 1);

        $this->assertDatabaseMissing('teams', ['id' => $team->id]);
        Storage::disk('r2')->assertMissing("images/teams/avatars/{$avatar}");
    }

    public function test_public_index_only_returns_active_team_members(): void
    {
        $active = $this->createTeam(['name' => 'Membre public']);
        $inactive = $this->createTeam([
            'name' => 'Membre privé',
            'is_active' => false,
        ]);

        $response = $this->getJson('/api/v1/teams')->assertOk();
        $ids = collect($response->json('data'))->pluck('id')->all();

        $this->assertContains($active->id, $ids);
        $this->assertNotContains($inactive->id, $ids);
    }

    public function test_public_show_returns_active_member_and_hides_inactive_member(): void
    {
        $active = $this->createTeam(['name' => 'Membre public']);
        $inactive = $this->createTeam([
            'name' => 'Membre privé',
            'is_active' => false,
        ]);

        $this->getJson("/api/v1/teams/{$active->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $active->id);

        $this->getJson("/api/v1/teams/{$inactive->id}")->assertNotFound();
    }

    public function test_public_resource_exposes_business_fields_without_internal_data(): void
    {
        $team = $this->createTeam();

        $response = $this->getJson("/api/v1/teams/{$team->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $team->id)
            ->assertJsonPath('data.name', $team->name)
            ->assertJsonPath('data.post', $team->post)
            ->assertJsonPath('data.short_description', $team->short_description)
            ->assertJsonPath(
                'data.avatar_url',
                "https://r2.test/images/teams/avatars/{$team->avatar}"
            );
        $data = $response->json('data');

        foreach (['avatar', 'is_active', 'created_by', 'updated_by', 'creator', 'updater'] as $field) {
            $this->assertArrayNotHasKey($field, $data);
        }

        $this->assertArrayHasKey('created_at', $data);
        $this->assertArrayHasKey('updated_at', $data);
    }

    private function createUser(): User
    {
        $this->userSequence++;

        return User::create([
            'name' => "Administrateur {$this->userSequence}",
            'username' => "teamadmin{$this->userSequence}",
            'telephone' => "62800000{$this->userSequence}",
            'email' => "teamadmin{$this->userSequence}@example.com",
            'role' => 'admin',
            'is_active' => true,
            'password' => 'password',
        ]);
    }

    private function createTeam(array $attributes = [], ?User $user = null): Team
    {
        $user ??= $this->createUser();
        $team = new Team(array_merge([
            'name' => 'Mamadou Diallo',
            'post' => 'Backend Developer',
            'short_description' => 'Développe les API de la plateforme.',
            'is_active' => true,
        ], $attributes));
        $team->avatar = 'team-'.uniqid().'.jpg';
        $team->created_by = $user->id;
        $team->updated_by = $user->id;
        $team->save();

        Storage::disk('r2')->put("images/teams/avatars/{$team->avatar}", 'image');

        return $team;
    }

    private function fakeImage(
        string $name = 'avatar.jpg',
        string $mimeType = 'image/jpeg'
    ): UploadedFile {
        return UploadedFile::fake()->create($name, 100, $mimeType);
    }

    private function validPayload(array $attributes = []): array
    {
        return array_merge([
            'name' => 'Mamadou Diallo',
            'post' => 'Backend Developer',
            'short_description' => 'Développe les API de la plateforme.',
            'avatar' => $this->fakeImage(),
            'is_active' => true,
        ], $attributes);
    }
}
