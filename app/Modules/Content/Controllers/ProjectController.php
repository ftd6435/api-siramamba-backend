<?php

namespace App\Modules\Content\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Content\Models\Project;
use App\Modules\Content\Requests\StoreProjectRequest;
use App\Modules\Content\Requests\UpdateProjectRequest;
use App\Modules\Content\Resources\ProjectResource;
use App\Traits\ApiResponses;
use App\Traits\CloudflareUpload;
use Illuminate\Database\QueryException;
use Throwable;

class ProjectController extends Controller
{
    use ApiResponses, CloudflareUpload;

    private const THUMBNAIL_PATH = 'projects/thumbnails';

    public function index()
    {
        $projects = Project::with(['category', 'images'])->latest()->get();

        return $this->successResponse(
            ProjectResource::collection($projects),
            'Projets récupérés avec succès.'
        );
    }

    public function publicIndex()
    {
        $projects = Project::with(['category', 'images'])
            ->where('is_active', true)
            ->latest()
            ->get();

        return $this->successResponse(
            ProjectResource::collection($projects),
            'Projets récupérés avec succès.'
        );
    }

    public function store(StoreProjectRequest $request)
    {
        $thumbnail = $this->uploadImage($request->file('thumbnail'), self::THUMBNAIL_PATH);
        $data = $request->safe()->except('thumbnail');

        try {
            $project = Project::create([...$data, 'thumbnail' => $thumbnail]);
        } catch (Throwable $exception) {
            $this->deleteImageSafely($thumbnail, self::THUMBNAIL_PATH);

            throw $exception;
        }

        return $this->successResponse(
            new ProjectResource($project->load(['category', 'images'])),
            'Projet créé avec succès.',
            201
        );
    }

    public function show(Project $project)
    {
        return $this->successResponse(
            new ProjectResource($project->load(['category', 'images'])),
            'Projet récupéré avec succès.'
        );
    }

    public function publicShow(Project $project)
    {
        abort_unless($project->is_active, 404);

        return $this->successResponse(
            new ProjectResource($project->load(['category', 'images'])),
            'Projet récupéré avec succès.'
        );
    }

    public function update(UpdateProjectRequest $request, Project $project)
    {
        $data = $request->safe()->except('thumbnail');
        $oldThumbnail = $project->thumbnail;
        $newThumbnail = null;

        if ($request->hasFile('thumbnail')) {
            $newThumbnail = $this->uploadImage($request->file('thumbnail'), self::THUMBNAIL_PATH);
            $data['thumbnail'] = $newThumbnail;
        }

        try {
            $project->update($data);
        } catch (Throwable $exception) {
            if ($newThumbnail) {
                $this->deleteImageSafely($newThumbnail, self::THUMBNAIL_PATH);
            }

            throw $exception;
        }

        if ($newThumbnail) {
            $this->deleteImageSafely($oldThumbnail, self::THUMBNAIL_PATH);
        }

        return $this->successResponse(
            new ProjectResource($project->refresh()->load(['category', 'images'])),
            'Projet mis à jour avec succès.'
        );
    }

    public function destroy(Project $project)
    {
        if ($project->images()->exists() || $project->comments()->exists()) {
            return $this->referencedProjectResponse();
        }

        $thumbnail = $project->thumbnail;

        try {
            $project->delete();
        } catch (QueryException) {
            return $this->referencedProjectResponse();
        }

        $this->deleteImageSafely($thumbnail, self::THUMBNAIL_PATH);

        return $this->noContentSuccessResponse('Projet supprimé avec succès.');
    }

    private function referencedProjectResponse()
    {
        return $this->errorResponse(
            'Ce projet possède des dépendances et ne peut pas être supprimé.',
            [],
            409
        );
    }

    private function deleteImageSafely(?string $image, string $path): void
    {
        if (! $image) {
            return;
        }

        try {
            $this->deleteImage($image, $path);
        } catch (Throwable $exception) {
            report($exception);
        }
    }
}
