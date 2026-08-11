<?php

namespace App\Modules\Content\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Content\Models\Newsletter;
use App\Modules\Content\Requests\StoreNewsletterRequest;
use App\Modules\Content\Requests\UpdateNewsletterRequest;
use App\Modules\Content\Resources\NewsletterResource;
use App\Traits\ApiResponses;

class NewsletterController extends Controller
{
    use ApiResponses;

    public function store(StoreNewsletterRequest $request)
    {
        $newsletter = Newsletter::create([
            ...$request->validated(),
            'status' => 'attente',
        ]);

        return $this->successResponse(
            new NewsletterResource($newsletter),
            'Inscription à la newsletter créée avec succès.',
            201
        );
    }

    public function index()
    {
        $newsletters = Newsletter::latest()->get();

        return $this->successResponse(
            NewsletterResource::collection($newsletters),
            'Inscriptions à la newsletter récupérées avec succès.'
        );
    }

    public function show(Newsletter $newsletter)
    {
        return $this->successResponse(
            new NewsletterResource($newsletter),
            'Inscription à la newsletter récupérée avec succès.'
        );
    }

    public function update(UpdateNewsletterRequest $request, Newsletter $newsletter)
    {
        $newsletter->fill($request->validated());
        $newsletter->save();

        return $this->successResponse(
            new NewsletterResource($newsletter->refresh()),
            'Inscription à la newsletter mise à jour avec succès.'
        );
    }

    public function destroy(Newsletter $newsletter)
    {
        $newsletter->delete();

        return $this->noContentSuccessResponse(
            'Inscription à la newsletter supprimée avec succès.'
        );
    }
}
