<?php

namespace App\Modules\Content\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Content\Models\Gallery;
use App\Modules\Content\Requests\StoreGalleryRequest;
use App\Modules\Content\Requests\UpdateGalleryRequest;
use App\Modules\Content\Resources\GalleryResource;
use App\Traits\ApiResponses;
use App\Traits\CloudflareUpload;
use Illuminate\Http\Request;
use Throwable;

class GalleryController extends Controller
{
    use ApiResponses, CloudflareUpload;

    private const IMAGE_PATH = 'galleries';

    private const ADMIN_RELATIONS = ['category', 'creator', 'updater'];

    public function index()
    {
        $galleries = Gallery::with(self::ADMIN_RELATIONS)->latest()->get();

        return $this->successResponse(
            GalleryResource::collection($galleries),
            'Galeries récupérées avec succès.'
        );
    }

    public function publicIndex(Request $request)
    {
        $filters = $request->validate([
            'category_id' => ['sometimes', 'integer', 'exists:category_galleries,id'],
        ]);

        $galleries = Gallery::with('category')
            ->where('is_active', true)
            ->when(
                isset($filters['category_id']),
                fn ($query) => $query->where('category_id', $filters['category_id'])
            )
            ->latest()
            ->get();

        return $this->successResponse(
            GalleryResource::collection($galleries),
            'Galeries récupérées avec succès.'
        );
    }

    public function store(StoreGalleryRequest $request)
    {
        $imagePath = $this->uploadImage($request->file('image'), self::IMAGE_PATH);
        $userId = $request->user()->getAuthIdentifier();
        $gallery = new Gallery($request->safe()->except('image'));
        $gallery->image_path = $imagePath;
        $gallery->created_by = $userId;
        $gallery->updated_by = $userId;

        try {
            $gallery->save();
        } catch (Throwable $exception) {
            $this->deleteImageSafely($imagePath);

            throw $exception;
        }

        return $this->successResponse(
            new GalleryResource($gallery->load(self::ADMIN_RELATIONS)),
            'Galerie créée avec succès.',
            201
        );
    }

    public function show(Gallery $gallery)
    {
        return $this->successResponse(
            new GalleryResource($gallery->load(self::ADMIN_RELATIONS)),
            'Galerie récupérée avec succès.'
        );
    }

    public function publicShow(Gallery $gallery)
    {
        abort_unless($gallery->is_active, 404);

        return $this->successResponse(
            new GalleryResource($gallery->load('category')),
            'Galerie récupérée avec succès.'
        );
    }

    public function update(UpdateGalleryRequest $request, Gallery $gallery)
    {
        $data = $request->safe()->except('image');
        $oldImage = $gallery->image_path;
        $newImage = null;

        if ($request->hasFile('image')) {
            $newImage = $this->uploadImage($request->file('image'), self::IMAGE_PATH);
        }

        $gallery->fill($data);

        if ($newImage) {
            $gallery->image_path = $newImage;
        }

        $gallery->updated_by = $request->user()->getAuthIdentifier();

        try {
            $gallery->save();
        } catch (Throwable $exception) {
            $this->deleteImageSafely($newImage);

            throw $exception;
        }

        if ($newImage) {
            $this->deleteImageSafely($oldImage);
        }

        return $this->successResponse(
            new GalleryResource($gallery->refresh()->load(self::ADMIN_RELATIONS)),
            'Galerie mise à jour avec succès.'
        );
    }

    public function destroy(Gallery $gallery)
    {
        $imagePath = $gallery->image_path;
        $gallery->delete();
        $this->deleteImageSafely($imagePath);

        return $this->noContentSuccessResponse('Galerie supprimée avec succès.');
    }

    private function deleteImageSafely(?string $imagePath): void
    {
        if (! $imagePath) {
            return;
        }

        try {
            $this->deleteImage($imagePath, self::IMAGE_PATH);
        } catch (Throwable $exception) {
            report($exception);
        }
    }
}
