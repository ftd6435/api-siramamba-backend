<?php

namespace Tests\Feature\Content;

use App\Modules\Administration\Models\User;
use App\Modules\Content\Models\Category;
use App\Modules\Content\Models\Project;
use App\Modules\Content\Models\ProjectComment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ProjectCommentApiTest extends TestCase
{
    use RefreshDatabase;

    private int $userSequence = 0;

    public function test_public_user_can_create_a_root_comment_without_email(): void
    {
        $project = $this->createProject();

        $this->postJson("/api/v1/projects/{$project->id}/comments", [
            'name' => 'Mamadou',
            'content' => 'Très beau projet',
        ])->assertCreated()
            ->assertJsonPath('status', 1)
            ->assertJsonPath('data.email', null)
            ->assertJsonPath('data.parent_id', null)
            ->assertJsonPath('data.replies', []);

        $this->assertDatabaseHas('project_comments', [
            'project_id' => $project->id,
            'name' => 'Mamadou',
            'email' => null,
            'parent_id' => null,
        ]);
    }

    public function test_valid_email_is_accepted(): void
    {
        $project = $this->createProject();

        $this->postJson("/api/v1/projects/{$project->id}/comments", [
            'name' => 'Aïssatou',
            'email' => 'aissatou@example.com',
            'content' => 'Merci pour ces informations.',
        ])->assertCreated()->assertJsonPath('data.email', 'aissatou@example.com');
    }

    public function test_invalid_email_is_rejected(): void
    {
        $project = $this->createProject();

        $this->postJson("/api/v1/projects/{$project->id}/comments", [
            'name' => 'Aïssatou',
            'email' => 'adresse-invalide',
            'content' => 'Commentaire',
        ])->assertUnprocessable()->assertJsonValidationErrors('email');
    }

    public function test_unknown_parent_is_rejected(): void
    {
        $project = $this->createProject();

        $this->postJson("/api/v1/projects/{$project->id}/comments", [
            'name' => 'Mamadou',
            'content' => 'Réponse',
            'parent_id' => 999999,
        ])->assertUnprocessable()->assertJsonValidationErrors('parent_id');
    }

    public function test_parent_from_the_same_project_is_accepted(): void
    {
        $project = $this->createProject();
        $parent = $this->createComment($project);

        $this->postJson("/api/v1/projects/{$project->id}/comments", [
            'name' => 'Mamadou',
            'content' => 'Réponse',
            'parent_id' => $parent->id,
        ])->assertCreated()->assertJsonPath('data.parent_id', $parent->id);

        $this->assertDatabaseHas('project_comments', [
            'project_id' => $project->id,
            'parent_id' => $parent->id,
        ]);
    }

    public function test_parent_from_another_project_is_rejected(): void
    {
        $project = $this->createProject();
        $otherProject = $this->createProject();
        $parent = $this->createComment($otherProject);

        $this->postJson("/api/v1/projects/{$project->id}/comments", [
            'name' => 'Mamadou',
            'content' => 'Réponse interdite',
            'parent_id' => $parent->id,
        ])->assertUnprocessable()->assertJsonValidationErrors('parent_id');
    }

    public function test_project_id_from_payload_is_ignored(): void
    {
        $project = $this->createProject();
        $otherProject = $this->createProject();

        $this->postJson("/api/v1/projects/{$project->id}/comments", [
            'name' => 'Mamadou',
            'content' => 'Commentaire',
            'project_id' => $otherProject->id,
        ])->assertCreated();

        $this->assertDatabaseHas('project_comments', [
            'project_id' => $project->id,
            'content' => 'Commentaire',
        ]);
        $this->assertDatabaseMissing('project_comments', [
            'project_id' => $otherProject->id,
            'content' => 'Commentaire',
        ]);
    }

    public function test_unknown_project_returns_not_found(): void
    {
        $this->getJson('/api/v1/projects/999999/comments')->assertNotFound();
        $this->postJson('/api/v1/projects/999999/comments', [
            'name' => 'Mamadou',
            'content' => 'Commentaire',
        ])->assertNotFound();
    }

    public function test_comments_are_returned_as_a_multilevel_tree_without_recursive_queries(): void
    {
        $project = $this->createProject();
        $root = $this->createComment($project, ['name' => 'Racine']);
        $reply = $this->createComment($project, [
            'name' => 'Réponse',
            'parent_id' => $root->id,
        ]);
        $nestedReply = $this->createComment($project, [
            'name' => 'Réponse imbriquée',
            'parent_id' => $reply->id,
        ]);

        DB::flushQueryLog();
        DB::enableQueryLog();

        $response = $this->getJson("/api/v1/projects/{$project->id}/comments")
            ->assertOk()
            ->assertJsonPath('data.0.id', $root->id)
            ->assertJsonPath('data.0.replies.0.id', $reply->id)
            ->assertJsonPath('data.0.replies.0.replies.0.id', $nestedReply->id);

        $this->assertCount(1, $response->json('data'));
        $this->assertCount(2, DB::getQueryLog());
    }

    public function test_comments_remain_available_for_an_inactive_project(): void
    {
        $project = $this->createProject(['is_active' => false]);
        $comment = $this->createComment($project);

        $this->getJson("/api/v1/projects/{$project->id}/comments")
            ->assertOk()
            ->assertJsonPath('data.0.id', $comment->id);

        $this->postJson("/api/v1/projects/{$project->id}/comments", [
            'name' => 'Mamadou',
            'content' => 'Nouveau commentaire',
        ])->assertCreated();
    }

    private function createComment(Project $project, array $attributes = []): ProjectComment
    {
        return ProjectComment::create(array_merge([
            'project_id' => $project->id,
            'name' => 'Mamadou',
            'email' => null,
            'content' => 'Commentaire',
            'parent_id' => null,
        ], $attributes));
    }

    private function createProject(array $attributes = []): Project
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

        return Project::create(array_merge([
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
        ], $attributes));
    }

    private function createUser(): User
    {
        $this->userSequence++;

        return User::create([
            'name' => "Administrateur {$this->userSequence}",
            'username' => "commentadmin{$this->userSequence}",
            'telephone' => "62300000{$this->userSequence}",
            'email' => "commentadmin{$this->userSequence}@example.com",
            'role' => 'admin',
            'is_active' => true,
            'password' => 'password',
        ]);
    }
}
