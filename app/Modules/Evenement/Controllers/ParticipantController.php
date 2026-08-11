<?php

namespace App\Modules\Evenement\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Evenement\Models\Participant;
use App\Modules\Evenement\Requests\StoreParticipantRequest;
use App\Modules\Evenement\Requests\UpdateParticipantRequest;
use App\Modules\Evenement\Resources\ParticipantResource;
use App\Traits\ApiResponses;
use Illuminate\Http\Request;

class ParticipantController extends Controller
{
    use ApiResponses;

    public function index(Request $request)
    {
        $participants = Participant::with('event')->orderBy('created_at', 'desc')->get();

        return $this->successResponse(
            ParticipantResource::collection($participants),
            "Participants récupérés avec succès."
        );
    }

    public function show(Participant $participant)
    {
        $participant->load('event');

        return $this->successResponse(
            new ParticipantResource($participant),
            "Participant récupéré avec succès."
        );
    }

    public function store(StoreParticipantRequest $request)
    {
        $data = $request->validated();
        $data['created_by'] = $request->user()->id;
        $data['updated_by'] = $request->user()->id;

        $participant = Participant::create($data);
        $participant->load('event');

        logActivity("Création du participant " . $participant->name, $data, $participant);

        return $this->successResponse(
            new ParticipantResource($participant),
            "Participant créé avec succès.",
            201
        );
    }

    public function update(UpdateParticipantRequest $request, Participant $participant)
    {
        $data = $request->validated();
        $data['updated_by'] = $request->user()->id;

        $participant->update($data);
        $participant->load('event');

        logActivity("Mise à jour du participant " . $participant->name, $data, $participant);

        return $this->successResponse(
            new ParticipantResource($participant),
            "Participant mis à jour avec succès."
        );
    }

    public function destroy(Request $request, Participant $participant)
    {
        $name = $participant->name;
        $participant->delete();

        logActivity("Suppression du participant " . $name);

        return $this->noContentSuccessResponse("Participant supprimé avec succès.");
    }
}
