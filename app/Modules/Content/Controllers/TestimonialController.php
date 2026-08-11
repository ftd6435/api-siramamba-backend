<?php

namespace App\Modules\Content\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Content\Models\Testimonial;
use App\Modules\Content\Requests\StoreTestimonialRequest;
use App\Modules\Content\Requests\UpdateTestimonialRequest;
use App\Modules\Content\Resources\TestimonialResource;
use App\Traits\ApiResponses;

class TestimonialController extends Controller
{
    use ApiResponses;

    public function publicIndex()
    {
        $testimonials = Testimonial::latest()->get();

        return $this->successResponse(
            TestimonialResource::collection($testimonials),
            'Témoignages récupérés avec succès.'
        );
    }

    public function index()
    {
        $testimonials = Testimonial::latest()->get();

        return $this->successResponse(
            TestimonialResource::collection($testimonials),
            'Témoignages récupérés avec succès.'
        );
    }

    public function show(Testimonial $testimonial)
    {
        return $this->successResponse(
            new TestimonialResource($testimonial),
            'Témoignage récupéré avec succès.'
        );
    }

    public function store(StoreTestimonialRequest $request)
    {
        $testimonial = Testimonial::create($request->validated());

        return $this->successResponse(
            new TestimonialResource($testimonial),
            'Témoignage créé avec succès.',
            201
        );
    }

    public function update(UpdateTestimonialRequest $request, Testimonial $testimonial)
    {
        $testimonial->fill($request->validated());
        $testimonial->save();

        return $this->successResponse(
            new TestimonialResource($testimonial->refresh()),
            'Témoignage mis à jour avec succès.'
        );
    }

    public function destroy(Testimonial $testimonial)
    {
        $testimonial->delete();

        return $this->noContentSuccessResponse('Témoignage supprimé avec succès.');
    }
}
