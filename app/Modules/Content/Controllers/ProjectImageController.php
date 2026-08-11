<?php

namespace App\Modules\Content\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Content\Models\Project;
use App\Modules\Content\Models\ProjectImage;
use App\Modules\Content\Requests\StoreProjectImageRequest;
use App\Modules\Content\Resources\ProjectImageResource;
use App\Traits\ApiResponses;
use App\Traits\CloudflareUpload;
use Throwable;

class ProjectImageController extends Controller
{
    use ApiResponses, CloudflareUpload;

    private const GALLERY_PATH = 'projects/gallery';

    public function index(Project $project)
    {
        return $this->successResponse(
            ProjectImageResource::collection($project->images()->latest()->get()),
            'Images du projet récupérées avec succès.'
        );
    }

    public function store(StoreProjectImageRequest $request, Project $project)
    {
        $imagePath = $this->uploadImage($request->file('image'), self::GALLERY_PATH);

        try {
            $projectImage = $project->images()->create(['image_path' => $imagePath]);
        } catch (Throwable $exception) {
            $this->deleteImageSafely($imagePath);

            throw $exception;
        }

        return $this->successResponse(
            new ProjectImageResource($projectImage),
            'Image ajoutée avec succès.',
            201
        );
    }

    public function destroy(Project $project, ProjectImage $projectImage)
    {
        abort_unless($projectImage->project_id === $project->id, 404);

        $imagePath = $projectImage->image_path;
        $projectImage->delete();
        $this->deleteImageSafely($imagePath);

        return $this->noContentSuccessResponse('Image supprimée avec succès.');
    }

    private function deleteImageSafely(string $imagePath): void
    {
        try {
            $this->deleteImage($imagePath, self::GALLERY_PATH);
        } catch (Throwable $exception) {
            report($exception);
        }
    }
}
