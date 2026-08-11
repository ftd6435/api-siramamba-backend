<?php

namespace App\Modules\Content\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Content\Models\Service;
use App\Modules\Content\Models\ServiceImage;
use App\Modules\Content\Requests\StoreServiceRequest;
use App\Modules\Content\Requests\UpdateServiceRequest;
use App\Modules\Content\Resources\PublicServiceResource;
use App\Modules\Content\Resources\ServiceResource;
use App\Traits\ApiResponses;
use App\Traits\CloudflareUpload;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Throwable;

class ServiceController extends Controller
{
    use ApiResponses, CloudflareUpload;

    private const IMAGE_PATH = 'services/images';

    private const THUMBNAIL_PATH = 'services/thumbnails';

    private const ADMIN_RELATIONS = ['images', 'creator', 'updater'];

    public function index()
    {
        $services = Service::with(self::ADMIN_RELATIONS)->latest()->get();

        return $this->successResponse(
            ServiceResource::collection($services),
            'Services récupérés avec succès.'
        );
    }

    public function publicIndex()
    {
        $services = Service::with('images')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        return $this->successResponse(
            PublicServiceResource::collection($services),
            'Services récupérés avec succès.'
        );
    }

    public function store(StoreServiceRequest $request)
    {
        $data = $request->safe()->except(['thumbnail', 'images', 'image_ids']);
        $imageIds = $request->validated()['image_ids'] ?? [];
        $userId = $request->user()->getAuthIdentifier();
        $thumbnail = null;
        $uploadedImages = [];

        try {
            $thumbnail = $this->uploadImage($request->file('thumbnail'), self::THUMBNAIL_PATH);
            $uploadedImages = $this->uploadImages($request->file('images', []));

            $service = DB::transaction(function () use ($data, $imageIds, $thumbnail, $uploadedImages, $userId) {
                $service = new Service([...$data, 'thumbnail' => $thumbnail]);
                $service->created_by = $userId;
                $service->updated_by = $userId;
                $service->save();

                $this->createImages($service, $uploadedImages);
                $this->attachImages($service, $imageIds, true);

                return $service;
            });
        } catch (Throwable $exception) {
            $this->deleteImageSafely($thumbnail, self::THUMBNAIL_PATH);
            $this->deleteImagesSafely($uploadedImages);

            throw $exception;
        }

        return $this->successResponse(
            new ServiceResource($service->load(self::ADMIN_RELATIONS)),
            'Service créé avec succès.',
            201
        );
    }

    public function show(Service $service)
    {
        return $this->successResponse(
            new ServiceResource($service->load(self::ADMIN_RELATIONS)),
            'Service récupéré avec succès.'
        );
    }

    public function publicShow(Service $service)
    {
        abort_unless($service->is_active, 404);

        return $this->successResponse(
            new PublicServiceResource($service->load('images')),
            'Service récupéré avec succès.'
        );
    }

    public function update(UpdateServiceRequest $request, Service $service)
    {
        $data = $request->safe()->except(['thumbnail', 'images', 'image_ids']);
        $imageIds = $request->validated()['image_ids'] ?? [];
        $oldThumbnail = $service->thumbnail;
        $newThumbnail = null;
        $uploadedImages = [];

        try {
            if ($request->hasFile('thumbnail')) {
                $newThumbnail = $this->uploadImage($request->file('thumbnail'), self::THUMBNAIL_PATH);
            }

            $uploadedImages = $this->uploadImages($request->file('images', []));

            DB::transaction(function () use ($data, $imageIds, $newThumbnail, $request, $service, $uploadedImages) {
                $service->fill($data);

                if ($newThumbnail) {
                    $service->thumbnail = $newThumbnail;
                }

                $service->updated_by = $request->user()->getAuthIdentifier();
                $service->save();

                $this->createImages($service, $uploadedImages);
                $this->attachImages($service, $imageIds, false);
            });
        } catch (Throwable $exception) {
            $this->deleteImageSafely($newThumbnail, self::THUMBNAIL_PATH);
            $this->deleteImagesSafely($uploadedImages);

            throw $exception;
        }

        if ($newThumbnail) {
            $this->deleteImageSafely($oldThumbnail, self::THUMBNAIL_PATH);
        }

        return $this->successResponse(
            new ServiceResource($service->refresh()->load(self::ADMIN_RELATIONS)),
            'Service mis à jour avec succès.'
        );
    }

    public function destroy(Service $service)
    {
        $service->load('images');
        $thumbnail = $service->thumbnail;
        $imagePaths = $service->images->pluck('image_path')->all();

        DB::transaction(function () use ($service) {
            $service->images()->delete();
            $service->delete();
        });

        $this->deleteImageSafely($thumbnail, self::THUMBNAIL_PATH);
        $this->deleteImagesSafely($imagePaths);

        return $this->noContentSuccessResponse('Service supprimé avec succès.');
    }

    private function uploadImages(array $images): array
    {
        $uploadedImages = [];

        try {
            foreach ($images as $image) {
                $uploadedImages[] = $this->uploadImage($image, self::IMAGE_PATH);
            }
        } catch (Throwable $exception) {
            $this->deleteImagesSafely($uploadedImages);

            throw $exception;
        }

        return $uploadedImages;
    }

    private function createImages(Service $service, array $imagePaths): void
    {
        if ($imagePaths === []) {
            return;
        }

        $service->images()->createMany(
            array_map(fn (string $imagePath) => ['image_path' => $imagePath], $imagePaths)
        );
    }

    private function attachImages(Service $service, array $imageIds, bool $onlyOrphans): void
    {
        if ($imageIds === []) {
            return;
        }

        $query = ServiceImage::whereIn('id', $imageIds);

        if ($onlyOrphans) {
            $query->whereNull('service_id');
        } else {
            $query->where(function ($query) use ($service) {
                $query->whereNull('service_id')->orWhere('service_id', $service->id);
            });
        }

        // Recheck under a row lock so another request cannot transfer an image concurrently.
        $attachableImages = $query->lockForUpdate()->get();

        if ($attachableImages->count() !== count($imageIds)) {
            throw ValidationException::withMessages([
                'image_ids' => "Une image n'existe pas ou appartient déjà à un autre service.",
            ]);
        }

        ServiceImage::whereIn('id', $imageIds)
            ->whereNull('service_id')
            ->update(['service_id' => $service->id]);
    }

    private function deleteImagesSafely(array $imagePaths): void
    {
        foreach ($imagePaths as $imagePath) {
            $this->deleteImageSafely($imagePath, self::IMAGE_PATH);
        }
    }

    private function deleteImageSafely(?string $image, string $path): void
    {
        if (! $image) {
            return;
        }

        try {
            $this->deleteImage($image, $path);
        } catch (Throwable $exception) {
            report($exception);
        }
    }
}
