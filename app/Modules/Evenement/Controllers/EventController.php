<?php

namespace App\Modules\Evenement\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Evenement\Models\Event;
use App\Modules\Evenement\Models\EventImage;
use App\Modules\Evenement\Requests\StoreEventRequest;
use App\Modules\Evenement\Requests\UpdateEventRequest;
use App\Modules\Evenement\Requests\UploadEventDescriptionImageRequest;
use App\Modules\Evenement\Resources\EventResource;
use App\Traits\ApiResponses;
use App\Traits\CloudflareUpload;
use Illuminate\Http\Request;

class EventController extends Controller
{
    use ApiResponses, CloudflareUpload;

    public function index(Request $request)
    {
        $events = Event::with(['category', 'images'])->orderBy('created_at', 'desc')->get();

        return $this->successResponse(
            EventResource::collection($events),
            "Événements récupérés avec succès."
        );
    }

    public function show(Event $event)
    {
        $event->load(['category', 'images']);

        return $this->successResponse(
            new EventResource($event),
            "Événement récupéré avec succès."
        );
    }

    public function store(StoreEventRequest $request)
    {
        $data = $request->validated();

        $data['thumbnail'] = $this->uploadImage($request->file('thumbnail'), 'events');

        $event = Event::create($data);

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $event->images()->create([
                    'image_path' => $this->uploadImage($image, 'events'),
                ]);
            }
        }

        $this->linkDescriptionImages($event);

        $event->load(['category', 'images']);

        logActivity("Création de l'événement " . $event->title, $request->except('thumbnail', 'images'), $event);

        return $this->successResponse(
            new EventResource($event),
            "Événement créé avec succès.",
            201
        );
    }

    public function update(UpdateEventRequest $request, Event $event)
    {
        $data = $request->validated();

        if ($request->hasFile('thumbnail')) {
            $this->deleteImage($event->thumbnail, 'events');
            $data['thumbnail'] = $this->uploadImage($request->file('thumbnail'), 'events');
        }

        $event->update($data);

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $event->images()->create([
                    'image_path' => $this->uploadImage($image, 'events'),
                ]);
            }
        }

        if ($request->filled('description')) {
            $this->linkDescriptionImages($event);
        }

        $event->load(['category', 'images']);

        logActivity("Mise à jour de l'événement " . $event->title, $request->except('thumbnail', 'images'), $event);

        return $this->successResponse(
            new EventResource($event),
            "Événement mis à jour avec succès."
        );
    }

    public function destroy(Request $request, Event $event)
    {
        foreach ($event->images as $image) {
            $this->deleteImage($image->image_path, 'events');
        }
        $this->deleteImage($event->thumbnail, 'events');

        $title = $event->title;
        $event->delete();

        logActivity("Suppression de l'événement " . $title);

        return $this->noContentSuccessResponse("Événement supprimé avec succès.");
    }

    public function destroyImage(Request $request, EventImage $image)
    {
        $this->deleteImage($image->image_path, 'events');
        $image->delete();

        logActivity("Suppression d'une image d'événement");

        return $this->noContentSuccessResponse("Image supprimée avec succès.");
    }

    /**
     * Upload d'une image insérée dans la description (CKEditor 5, SimpleUploadAdapter).
     * L'image est stockée sans event_id (l'événement n'existe pas forcément encore) ;
     * elle est rattachée automatiquement via linkDescriptionImages() au store/update.
     */
    public function uploadDescriptionImage(UploadEventDescriptionImageRequest $request)
    {
        $path = $this->uploadImage($request->file('upload'), 'events');

        $image = EventImage::create([
            'event_id' => null,
            'image_path' => $path,
        ]);

        return response()->json([
            'url' => $image->image_url,
        ]);
    }

    /**
     * Rattache à $event les EventImage orphelines (event_id null, uploadées via CKEditor)
     * dont le chemin apparaît dans la description, pour éviter de dépendre du frontend.
     */
    protected function linkDescriptionImages(Event $event): void
    {
        if (!$event->description) {
            return;
        }

        EventImage::whereNull('event_id')
            ->get()
            ->each(function (EventImage $image) use ($event) {
                if (str_contains($event->description, $image->image_path)) {
                    $image->update(['event_id' => $event->id]);
                }
            });
    }
}
