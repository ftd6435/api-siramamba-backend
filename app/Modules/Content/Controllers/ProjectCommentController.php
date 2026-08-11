<?php

namespace App\Modules\Content\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Content\Models\Project;
use App\Modules\Content\Models\ProjectComment;
use App\Modules\Content\Requests\StoreProjectCommentRequest;
use App\Modules\Content\Resources\ProjectCommentResource;
use App\Traits\ApiResponses;
use Illuminate\Support\Collection;

class ProjectCommentController extends Controller
{
    use ApiResponses;

    public function index(Project $project)
    {
        $comments = $project->comments()->oldest()->get();

        return $this->successResponse(
            ProjectCommentResource::collection($this->buildTree($comments)),
            'Commentaires récupérés avec succès.'
        );
    }

    public function store(StoreProjectCommentRequest $request, Project $project)
    {
        $comment = $project->comments()->create($request->validated());
        $comment->setRelation('replies', collect());

        return $this->successResponse(
            new ProjectCommentResource($comment),
            'Commentaire ajouté avec succès.',
            201
        );
    }

    private function buildTree(Collection $comments): Collection
    {
        $children = [];

        foreach ($comments as $comment) {
            $key = $comment->parent_id === null ? 'root' : (string) $comment->parent_id;
            $children[$key][] = $comment;
        }

        $attachReplies = function (ProjectComment $comment) use (&$attachReplies, $children): ProjectComment {
            $replies = collect($children[(string) $comment->id] ?? [])->map($attachReplies);
            $comment->setRelation('replies', $replies);

            return $comment;
        };

        return collect($children['root'] ?? [])->map($attachReplies);
    }
}
