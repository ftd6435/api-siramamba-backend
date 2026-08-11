<?php

namespace App\Modules\Content\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Content\Models\Service;
use App\Modules\Content\Models\ServiceImage;
use App\Modules\Content\Requests\UploadServiceImageRequest;
use App\Modules\Content\Resources\ServiceImageResource;
use App\Traits\ApiResponses;
use App\Traits\CloudflareUpload;
use Throwable;

class ServiceImageController extends Controller
{
    use ApiResponses, CloudflareUpload;

    private const IMAGE_PATH = 'services/images';

    public function uploadEditorImage(UploadServiceImageRequest $request)
    {
        if (! is_string(config('filesystems.disks.r2.public_url'))
            || trim(config('filesystems.disks.r2.public_url')) === '') {
            return $this->errorResponse(
                "L'URL publique R2 n'est pas configurée.",
                [],
                503
            );
        }

        $imagePath = $this->uploadImage($request->file('image'), self::IMAGE_PATH);

        try {
            $serviceImage = ServiceImage::create([
                'service_id' => null,
                'image_path' => $imagePath,
            ]);
        } catch (Throwable $exception) {
            $this->deleteImageSafely($imagePath);

            throw $exception;
        }

        return $this->successResponse(
            new ServiceImageResource($serviceImage),
            'Image téléversée avec succès.',
            201
        );
    }

    public function destroy(Service $service, ServiceImage $serviceImage)
    {
        abort_unless($serviceImage->service_id === $service->id, 404);

        $imagePath = $serviceImage->image_path;
        $serviceImage->delete();
        $this->deleteImageSafely($imagePath);

        return $this->noContentSuccessResponse('Image supprimée avec succès.');
    }

    private function deleteImageSafely(string $imagePath): void
    {
        try {
            $this->deleteImage($imagePath, self::IMAGE_PATH);
        } catch (Throwable $exception) {
            report($exception);
        }
    }
}
