<?php

namespace App\Modules\RelationExterne\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\RelationExterne\Models\TypePartner;
use App\Modules\RelationExterne\Requests\StoreTypePartnerRequest;
use App\Modules\RelationExterne\Requests\UpdateTypePartnerRequest;
use App\Modules\RelationExterne\Resources\TypePartnerResource;
use App\Traits\ApiResponses;
use Illuminate\Http\Request;

class TypePartnerController extends Controller
{
    use ApiResponses;

    public function index(Request $request)
    {
        $types = TypePartner::orderBy('created_at', 'desc')->get();

        return $this->successResponse(
            TypePartnerResource::collection($types),
            "Types de partenaires récupérés avec succès."
        );
    }

    public function show(TypePartner $typePartner)
    {
        return $this->successResponse(
            new TypePartnerResource($typePartner),
            "Type de partenaire récupéré avec succès."
        );
    }

    public function store(StoreTypePartnerRequest $request)
    {
        $data = $request->validated();
        $data['created_by'] = $request->user()->id;
        $data['updated_by'] = $request->user()->id;

        $type = TypePartner::create($data);

        logActivity("Création du type de partenaire " . $type->name, $data, $type);

        return $this->successResponse(
            new TypePartnerResource($type),
            "Type de partenaire créé avec succès.",
            201
        );
    }

    public function update(UpdateTypePartnerRequest $request, TypePartner $typePartner)
    {
        $data = $request->validated();
        $data['updated_by'] = $request->user()->id;

        $typePartner->update($data);

        logActivity("Mise à jour du type de partenaire " . $typePartner->name, $data, $typePartner);

        return $this->successResponse(
            new TypePartnerResource($typePartner),
            "Type de partenaire mis à jour avec succès."
        );
    }

    public function destroy(Request $request, TypePartner $typePartner)
    {
        $name = $typePartner->name;
        $typePartner->delete();

        logActivity("Suppression du type de partenaire " . $name);

        return $this->noContentSuccessResponse("Type de partenaire supprimé avec succès.");
    }
}
