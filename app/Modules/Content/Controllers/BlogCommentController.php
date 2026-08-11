<?php

namespace App\Modules\Content\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Content\Models\Blog;
use App\Modules\Content\Models\BlogComment;
use App\Modules\Content\Requests\StoreBlogCommentRequest;
use App\Modules\Content\Resources\BlogCommentResource;
use App\Traits\ApiResponses;
use Illuminate\Support\Collection;

class BlogCommentController extends Controller
{
    use ApiResponses;

    public function index(Blog $blog)
    {
        $comments = $blog->comments()->oldest()->get();

        return $this->successResponse(
            BlogCommentResource::collection($this->buildTree($comments)),
            'Commentaires récupérés avec succès.'
        );
    }

    public function store(StoreBlogCommentRequest $request, Blog $blog)
    {
        $comment = $blog->comments()->create($request->validated());
        $comment->setRelation('replies', collect());

        return $this->successResponse(
            new BlogCommentResource($comment),
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

        $attachReplies = function (BlogComment $comment) use (&$attachReplies, $children): BlogComment {
            $replies = collect($children[(string) $comment->id] ?? [])->map($attachReplies);
            $comment->setRelation('replies', $replies);

            return $comment;
        };

        return collect($children['root'] ?? [])->map($attachReplies);
    }
}
