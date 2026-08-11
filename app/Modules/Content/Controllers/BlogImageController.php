<?php

namespace App\Modules\Content\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Content\Models\Blog;
use App\Modules\Content\Models\BlogImage;
use App\Modules\Content\Requests\StoreBlogImageRequest;
use App\Modules\Content\Resources\BlogImageResource;
use App\Traits\ApiResponses;
use App\Traits\CloudflareUpload;
use Throwable;

class BlogImageController extends Controller
{
    use ApiResponses, CloudflareUpload;

    private const IMAGES_PATH = 'blogs/images';

    public function storeUnattached(StoreBlogImageRequest $request)
    {
        $imagePath = $this->uploadImage($request->file('image'), self::IMAGES_PATH);

        try {
            $blogImage = BlogImage::create(['blog_id' => null, 'image_path' => $imagePath]);
        } catch (Throwable $exception) {
            $this->deleteImageSafely($imagePath);

            throw $exception;
        }

        return $this->successResponse(
            new BlogImageResource($blogImage),
            'Image ajoutée avec succès.',
            201
        );
    }

    public function destroyUnattached(BlogImage $blogImage)
    {
        abort_unless($blogImage->blog_id === null, 404);

        $imagePath = $blogImage->image_path;
        $blogImage->delete();
        $this->deleteImageSafely($imagePath);

        return $this->noContentSuccessResponse('Image supprimée avec succès.');
    }

    public function index(Blog $blog)
    {
        return $this->successResponse(
            BlogImageResource::collection($blog->images()->latest()->get()),
            'Images du blog récupérées avec succès.'
        );
    }

    public function storeForBlog(StoreBlogImageRequest $request, Blog $blog)
    {
        $imagePath = $this->uploadImage($request->file('image'), self::IMAGES_PATH);

        try {
            $blogImage = $blog->images()->create(['image_path' => $imagePath]);
        } catch (Throwable $exception) {
            $this->deleteImageSafely($imagePath);

            throw $exception;
        }

        return $this->successResponse(
            new BlogImageResource($blogImage),
            'Image ajoutée avec succès.',
            201
        );
    }

    public function destroyForBlog(Blog $blog, BlogImage $blogImage)
    {
        abort_unless($blogImage->blog_id === $blog->id, 404);

        $imagePath = $blogImage->image_path;
        $blogImage->delete();
        $this->deleteImageSafely($imagePath);

        return $this->noContentSuccessResponse('Image supprimée avec succès.');
    }

    private function deleteImageSafely(string $imagePath): void
    {
        try {
            $this->deleteImage($imagePath, self::IMAGES_PATH);
        } catch (Throwable $exception) {
            report($exception);
        }
    }
}
