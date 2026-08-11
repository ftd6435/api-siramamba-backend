<?php

namespace App\Modules\Evenement\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Evenement\Models\EventTestimonial;
use App\Modules\Evenement\Requests\StoreEventTestimonialRequest;
use App\Modules\Evenement\Requests\UpdateEventTestimonialRequest;
use App\Modules\Evenement\Resources\EventTestimonialResource;
use App\Traits\ApiResponses;
use Illuminate\Http\Request;

class EventTestimonialController extends Controller
{
    use ApiResponses;

    public function index(Request $request)
    {
        $testimonials = EventTestimonial::orderBy('created_at', 'desc')->get();

        return $this->successResponse(
            EventTestimonialResource::collection($testimonials),
            "Témoignages récupérés avec succès."
        );
    }

    public function store(StoreEventTestimonialRequest $request)
    {
        $data = $request->validated();

        $testimonial = EventTestimonial::create($data);

        logActivity("Création d'un témoignage de " . $testimonial->name, $data, $testimonial);

        return $this->successResponse(
            new EventTestimonialResource($testimonial),
            "Témoignage créé avec succès.",
            201
        );
    }

    public function update(UpdateEventTestimonialRequest $request, EventTestimonial $eventTestimonial)
    {
        $data = $request->validated();

        $eventTestimonial->update($data);

        logActivity("Mise à jour du témoignage de " . $eventTestimonial->name, $data, $eventTestimonial);

        return $this->successResponse(
            new EventTestimonialResource($eventTestimonial),
            "Témoignage mis à jour avec succès."
        );
    }

    public function destroy(Request $request, EventTestimonial $eventTestimonial)
    {
        $name = $eventTestimonial->name;
        $eventTestimonial->delete();

        logActivity("Suppression du témoignage de " . $name);

        return $this->noContentSuccessResponse("Témoignage supprimé avec succès.");
    }
}
