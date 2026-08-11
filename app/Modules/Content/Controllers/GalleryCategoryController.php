<?php

namespace App\Modules\Content\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Content\Models\CategoryGallery;
use App\Modules\Content\Requests\StoreGalleryCategoryRequest;
use App\Modules\Content\Requests\UpdateGalleryCategoryRequest;
use App\Modules\Content\Resources\GalleryCategoryResource;
use App\Traits\ApiResponses;
use Illuminate\Database\QueryException;

class GalleryCategoryController extends Controller
{
    use ApiResponses;

    private const ADMIN_RELATIONS = ['creator', 'updater'];

    public function index()
    {
        $categories = CategoryGallery::with(self::ADMIN_RELATIONS)->latest()->get();

        return $this->successResponse(
            GalleryCategoryResource::collection($categories),
            'Catégories de galerie récupérées avec succès.'
        );
    }

    public function publicIndex()
    {
        $categories = CategoryGallery::latest()->get();

        return $this->successResponse(
            GalleryCategoryResource::collection($categories),
            'Catégories de galerie récupérées avec succès.'
        );
    }

    public function store(StoreGalleryCategoryRequest $request)
    {
        $userId = $request->user()->getAuthIdentifier();
        $category = new CategoryGallery($request->validated());
        $category->created_by = $userId;
        $category->updated_by = $userId;
        $category->save();

        return $this->successResponse(
            new GalleryCategoryResource($category->load(self::ADMIN_RELATIONS)),
            'Catégorie de galerie créée avec succès.',
            201
        );
    }

    public function show(CategoryGallery $galleryCategory)
    {
        return $this->successResponse(
            new GalleryCategoryResource($galleryCategory->load(self::ADMIN_RELATIONS)),
            'Catégorie de galerie récupérée avec succès.'
        );
    }

    public function update(UpdateGalleryCategoryRequest $request, CategoryGallery $galleryCategory)
    {
        $galleryCategory->fill($request->validated());
        $galleryCategory->updated_by = $request->user()->getAuthIdentifier();
        $galleryCategory->save();

        return $this->successResponse(
            new GalleryCategoryResource(
                $galleryCategory->refresh()->load(self::ADMIN_RELATIONS)
            ),
            'Catégorie de galerie mise à jour avec succès.'
        );
    }

    public function destroy(CategoryGallery $galleryCategory)
    {
        if ($galleryCategory->galleries()->exists()) {
            return $this->referencedCategoryResponse();
        }

        try {
            $galleryCategory->delete();
        } catch (QueryException) {
            // A Gallery may have been created between the check and the deletion.
            return $this->referencedCategoryResponse();
        }

        return $this->noContentSuccessResponse('Catégorie de galerie supprimée avec succès.');
    }

    private function referencedCategoryResponse()
    {
        return $this->errorResponse(
            'Cette catégorie est utilisée et ne peut pas être supprimée.',
            [],
            409
        );
    }
}
