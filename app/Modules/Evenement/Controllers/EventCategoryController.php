<?php

namespace App\Modules\Evenement\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Evenement\Models\EventCategory;
use App\Modules\Evenement\Requests\StoreEventCategoryRequest;
use App\Modules\Evenement\Requests\UpdateEventCategoryRequest;
use App\Modules\Evenement\Resources\EventCategoryResource;
use App\Traits\ApiResponses;
use Illuminate\Http\Request;

class EventCategoryController extends Controller
{
    use ApiResponses;

    public function index(Request $request)
    {
        $categories = EventCategory::orderBy('created_at', 'desc')->get();

        return $this->successResponse(
            EventCategoryResource::collection($categories),
            "Catégories d'événements récupérées avec succès."
        );
    }

    public function show(EventCategory $eventCategory)
    {
        return $this->successResponse(
            new EventCategoryResource($eventCategory),
            "Catégorie d'événement récupérée avec succès."
        );
    }

    public function store(StoreEventCategoryRequest $request)
    {
        $data = $request->validated();
        $data['created_by'] = $request->user()->id;
        $data['updated_by'] = $request->user()->id;

        $category = EventCategory::create($data);

        logActivity("Création de la catégorie d'événement " . $category->name, $data, $category);

        return $this->successResponse(
            new EventCategoryResource($category),
            "Catégorie d'événement créée avec succès.",
            201
        );
    }

    public function update(UpdateEventCategoryRequest $request, EventCategory $eventCategory)
    {
        $data = $request->validated();
        $data['updated_by'] = $request->user()->id;

        $eventCategory->update($data);

        logActivity("Mise à jour de la catégorie d'événement " . $eventCategory->name, $data, $eventCategory);

        return $this->successResponse(
            new EventCategoryResource($eventCategory),
            "Catégorie d'événement mise à jour avec succès."
        );
    }

    public function destroy(Request $request, EventCategory $eventCategory)
    {
        $name = $eventCategory->name;
        $eventCategory->delete();

        logActivity("Suppression de la catégorie d'événement " . $name);

        return $this->noContentSuccessResponse("Catégorie d'événement supprimée avec succès.");
    }
}
