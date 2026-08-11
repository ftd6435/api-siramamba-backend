<?php

namespace App\Modules\Content\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Content\Models\Category;
use App\Modules\Content\Requests\StoreCategoryRequest;
use App\Modules\Content\Requests\UpdateCategoryRequest;
use App\Modules\Content\Resources\CategoryResource;
use App\Traits\ApiResponses;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

class CategoryController extends Controller
{
    use ApiResponses;

    public function index()
    {
        $categories = Category::latest()->get();

        return $this->successResponse(
            CategoryResource::collection($categories),
            'Catégories récupérées avec succès.'
        );
    }

    public function publicIndex()
    {
        $categories = Category::where('is_active', true)->latest()->get();

        return $this->successResponse(
            CategoryResource::collection($categories),
            'Catégories récupérées avec succès.'
        );
    }

    public function store(StoreCategoryRequest $request)
    {
        $userId = $request->user()->getAuthIdentifier();
        $category = new Category($request->validated());
        $category->created_by = $userId;
        $category->updated_by = $userId;
        $category->save();

        return $this->successResponse(
            new CategoryResource($category),
            'Catégorie créée avec succès.',
            201
        );
    }

    public function show(Category $category)
    {
        return $this->successResponse(
            new CategoryResource($category),
            'Catégorie récupérée avec succès.'
        );
    }

    public function update(UpdateCategoryRequest $request, Category $category)
    {
        $category->fill($request->validated());
        $category->updated_by = $request->user()->getAuthIdentifier();
        $category->save();

        return $this->successResponse(
            new CategoryResource($category->refresh()),
            'Catégorie mise à jour avec succès.'
        );
    }

    public function destroy(Category $category)
    {
        $isReferenced = DB::table('projects')->where('category_id', $category->id)->exists()
            || DB::table('blogs')->where('category_id', $category->id)->exists();

        if ($isReferenced) {
            return $this->referencedCategoryResponse();
        }

        try {
            $category->delete();
        } catch (QueryException) {
            // A reference may have been created between the check and the deletion.
            return $this->referencedCategoryResponse();
        }

        return $this->noContentSuccessResponse('Catégorie supprimée avec succès.');
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
