<?php

namespace App\Modules\Content\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Content\Models\Setting;
use App\Modules\Content\Requests\StoreSettingRequest;
use App\Modules\Content\Requests\UpdateSettingRequest;
use App\Modules\Content\Resources\SettingResource;
use App\Traits\ApiResponses;
use App\Traits\CloudflareUpload;
use Throwable;

class SettingController extends Controller
{
    use ApiResponses, CloudflareUpload;

    private const IMAGE_PATH = 'settings';

    public function index()
    {
        return $this->successResponse(
            SettingResource::collection(Setting::latest()->get()),
            'Paramètres récupérés avec succès.'
        );
    }

    public function store(StoreSettingRequest $request)
    {
        $data = $request->validated();
        $newImage = null;

        if ($data['type'] === 'image') {
            $newImage = $this->uploadImage($request->file('value'), self::IMAGE_PATH);
            $data['value'] = $newImage;
        } else {
            $data['value'] = $this->normalizeValue($data['type'], $data['value']);
        }

        try {
            $setting = Setting::create($data);
        } catch (Throwable $exception) {
            $this->deleteImageSafely($newImage);

            throw $exception;
        }

        return $this->successResponse(
            new SettingResource($setting),
            'Paramètre créé avec succès.',
            201
        );
    }

    public function show(Setting $setting)
    {
        return $this->successResponse(
            new SettingResource($setting),
            'Paramètre récupéré avec succès.'
        );
    }

    public function update(UpdateSettingRequest $request, Setting $setting)
    {
        $data = $request->validated();
        $oldType = $setting->type;
        $oldImage = $oldType === 'image' ? $setting->value : null;
        $effectiveType = $data['type'] ?? $oldType;
        $newImage = null;

        if (array_key_exists('value', $data)) {
            if ($effectiveType === 'image') {
                $newImage = $this->uploadImage($request->file('value'), self::IMAGE_PATH);
                $data['value'] = $newImage;
            } else {
                $data['value'] = $this->normalizeValue($effectiveType, $data['value']);
            }
        }

        try {
            $setting->update($data);
        } catch (Throwable $exception) {
            $this->deleteImageSafely($newImage);

            throw $exception;
        }

        if ($oldImage && ($effectiveType !== 'image' || $newImage)) {
            $this->deleteImageSafely($oldImage);
        }

        return $this->successResponse(
            new SettingResource($setting->refresh()),
            'Paramètre mis à jour avec succès.'
        );
    }

    public function destroy(Setting $setting)
    {
        $image = $setting->type === 'image' ? $setting->value : null;

        $setting->delete();
        $this->deleteImageSafely($image);

        return $this->noContentSuccessResponse('Paramètre supprimé avec succès.');
    }

    private function normalizeValue(string $type, mixed $value): mixed
    {
        if ($type !== 'boolean') {
            return $value;
        }

        return in_array($value, [true, 1, '1'], true) ? '1' : '0';
    }

    private function deleteImageSafely(?string $image): void
    {
        if (! $image) {
            return;
        }

        try {
            $this->deleteImage($image, self::IMAGE_PATH);
        } catch (Throwable $exception) {
            report($exception);
        }
    }
}
