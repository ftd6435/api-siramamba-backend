<?php

namespace App\Modules\RelationExterne\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\RelationExterne\Models\Partner;
use App\Modules\RelationExterne\Requests\StorePartnerRequest;
use App\Modules\RelationExterne\Requests\UpdatePartnerRequest;
use App\Modules\RelationExterne\Resources\PartnerResource;
use App\Traits\ApiResponses;
use App\Traits\CloudflareUpload;
use Illuminate\Http\Request;

class PartnerController extends Controller
{
    use ApiResponses, CloudflareUpload;

    public function index(Request $request)
    {
        $partners = Partner::with('typePartner')->orderBy('created_at', 'desc')->get();

        return $this->successResponse(
            PartnerResource::collection($partners),
            "Partenaires récupérés avec succès."
        );
    }

    public function show(Partner $partner)
    {
        $partner->load('typePartner');

        return $this->successResponse(
            new PartnerResource($partner),
            "Partenaire récupéré avec succès."
        );
    }

    public function store(StorePartnerRequest $request)
    {
        $data = $request->validated();
        $data['logo'] = $this->uploadImage($request->file('logo'), 'partners');
        $data['created_by'] = $request->user()->id;
        $data['updated_by'] = $request->user()->id;

        $partner = Partner::create($data);
        $partner->load('typePartner');

        logActivity("Création du partenaire " . $partner->company, $request->except('logo'), $partner);

        return $this->successResponse(
            new PartnerResource($partner),
            "Partenaire créé avec succès.",
            201
        );
    }

    public function update(UpdatePartnerRequest $request, Partner $partner)
    {
        $data = $request->validated();
        $data['updated_by'] = $request->user()->id;

        if ($request->hasFile('logo')) {
            $this->deleteImage($partner->logo, 'partners');
            $data['logo'] = $this->uploadImage($request->file('logo'), 'partners');
        }

        $partner->update($data);
        $partner->load('typePartner');

        logActivity("Mise à jour du partenaire " . $partner->company, $request->except('logo'), $partner);

        return $this->successResponse(
            new PartnerResource($partner),
            "Partenaire mis à jour avec succès."
        );
    }

    public function destroy(Request $request, Partner $partner)
    {
        $this->deleteImage($partner->logo, 'partners');

        $company = $partner->company;
        $partner->delete();

        logActivity("Suppression du partenaire " . $company);

        return $this->noContentSuccessResponse("Partenaire supprimé avec succès.");
    }
}
