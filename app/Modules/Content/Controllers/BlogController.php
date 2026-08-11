<?php

namespace App\Modules\Content\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Content\Models\Blog;
use App\Modules\Content\Models\BlogImage;
use App\Modules\Content\Requests\StoreBlogRequest;
use App\Modules\Content\Requests\UpdateBlogRequest;
use App\Modules\Content\Resources\BlogResource;
use App\Traits\ApiResponses;
use App\Traits\CloudflareUpload;
use Throwable;

class BlogController extends Controller
{
    use ApiResponses, CloudflareUpload;

    private const THUMBNAIL_PATH = 'blogs/thumbnails';

    private const IMAGES_PATH = 'blogs/images';

    public function index()
    {
        $blogs = Blog::with('images')->latest()->get();

        return $this->successResponse(
            BlogResource::collection($blogs),
            'Blogs récupérés avec succès.'
        );
    }

    public function publicIndex()
    {
        $blogs = Blog::with('images')->where('is_active', true)->latest()->get();

        return $this->successResponse(
            BlogResource::collection($blogs),
            'Blogs récupérés avec succès.'
        );
    }

    public function show(Blog $blog)
    {
        return $this->successResponse(
            new BlogResource($blog->load('images')),
            'Blog récupéré avec succès.'
        );
    }

    public function publicShow(Blog $blog)
    {
        abort_unless($blog->is_active, 404);

        return $this->successResponse(
            new BlogResource($blog->load('images')),
            'Blog récupéré avec succès.'
        );
    }

    public function store(StoreBlogRequest $request)
    {
        $thumbnail = $this->uploadImage($request->file('thumbnail'), self::THUMBNAIL_PATH);
        $userId = $request->user()->getAuthIdentifier();

        $blog = new Blog($request->safe()->except(['thumbnail', 'image_ids', 'images']));
        $blog->thumbnail = $thumbnail;
        $blog->created_by = $userId;
        $blog->updated_by = $userId;

        try {
            $blog->save();
        } catch (Throwable $exception) {
            $this->deleteImageSafely($thumbnail, self::THUMBNAIL_PATH);

            throw $exception;
        }

        $this->syncImages($blog, $request->validated('image_ids') ?? [], $request->file('images') ?? []);

        return $this->successResponse(
            new BlogResource($blog->load('images')),
            'Blog créé avec succès.',
            201
        );
    }

    public function update(UpdateBlogRequest $request, Blog $blog)
    {
        $data = $request->safe()->except(['thumbnail', 'image_ids', 'images']);
        $oldThumbnail = $blog->thumbnail;
        $newThumbnail = null;

        if ($request->hasFile('thumbnail')) {
            $newThumbnail = $this->uploadImage($request->file('thumbnail'), self::THUMBNAIL_PATH);
            $data['thumbnail'] = $newThumbnail;
        }

        $blog->fill($data);
        $blog->updated_by = $request->user()->getAuthIdentifier();

        try {
            $blog->save();
        } catch (Throwable $exception) {
            if ($newThumbnail) {
                $this->deleteImageSafely($newThumbnail, self::THUMBNAIL_PATH);
            }

            throw $exception;
        }

        if ($newThumbnail) {
            $this->deleteImageSafely($oldThumbnail, self::THUMBNAIL_PATH);
        }

        $this->syncImages($blog, $request->validated('image_ids') ?? [], $request->file('images') ?? []);

        return $this->successResponse(
            new BlogResource($blog->refresh()->load('images')),
            'Blog mis à jour avec succès.'
        );
    }

    public function destroy(Blog $blog)
    {
        $thumbnail = $blog->thumbnail;

        foreach ($blog->images as $image) {
            $image->delete();
            $this->deleteImageSafely($image->image_path, self::IMAGES_PATH);
        }

        $blog->delete();

        $this->deleteImageSafely($thumbnail, self::THUMBNAIL_PATH);

        return $this->noContentSuccessResponse('Blog supprimé avec succès.');
    }

    private function syncImages(Blog $blog, array $imageIds, array $imageFiles): void
    {
        if (! empty($imageIds)) {
            BlogImage::whereIn('id', $imageIds)->update(['blog_id' => $blog->id]);
        }

        foreach ($imageFiles as $file) {
            $imagePath = $this->uploadImage($file, self::IMAGES_PATH);
            $blog->images()->create(['image_path' => $imagePath]);
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
