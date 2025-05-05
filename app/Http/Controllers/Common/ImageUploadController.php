<?php

namespace App\Http\Controllers\Common;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;
use App\Models\UploadTypes\UserAvatar;
use App\Models\UploadTypes\TeacherAvatar;
use App\Models\UploadTypes\TeacherPortfolio;
use App\Models\UploadTypes\CourseImage;
use App\Models\UploadTypes\BrandLogo;
use App\Models\UploadTypes\BrandBackground;
use App\Models\UploadTypes\ProductImage;
use App\Models\UploadTypes\ClassroomImage;
use App\Models\UploadTypes\BaseUploadType;
use Closure;

class ImageUploadController extends Controller
{
    private $supportedTypes = [
        'user_avatar' => UserAvatar::class,
        'teacher_avatar' => TeacherAvatar::class,
        'teacher_portfolio' => TeacherPortfolio::class,
        'course_image' => CourseImage::class,
        'brand_logo' => BrandLogo::class,
        'brand_background' => BrandBackground::class,
        'product_image' => ProductImage::class,
        'classroom_image' => ClassroomImage::class,
    ];

    public function uploadImage(Request $request): \Illuminate\Http\JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'type' => ['required', 'string', 'in:' . implode(',', array_keys($this->supportedTypes))],
            'identifier' => ['required', 'string'],
            'brand_symbol' => ['required', 'string', 'regex:/^[a-zA-Z]{4,10}$/'],
            'image' => ['required', 'file', 'image', 'mimes:jpeg,png,jpg,gif,webp'],
        ]);

        if ($validator->fails()) {
            return apiResponse(4001, null, $validator->errors()->first(), 400);
        }

        $type = $request->input('type');
        $identifier = $request->input('identifier');
        $brandSymbol = $request->input('brand_symbol');
        $file = $request->file('image');
        $uploadType = app($this->supportedTypes[$type]);

        $maxSizeBytes = $uploadType->getMaxSizeMb() * 1024 * 1024;
        if ($file->getSize() > $maxSizeBytes) {
            return apiResponse(4002, null, "File size exceeds the limit of {$uploadType->getMaxSizeMb()}MB.", 400);
        }

        $recordExists = DB::table($uploadType->getTable())
            ->where($uploadType->getIdentifierField(), $identifier)
            ->exists();

        if (!$recordExists && !$uploadType->allowUploadWithoutRecord()) {
            return apiResponse(4041, null, "Record not found for identifier: {$identifier}.", 404);
        }

        $folder = $uploadType->getFolder($brandSymbol);
        if (!Storage::disk('public')->exists($folder)) {
            Storage::disk('public')->makeDirectory($folder);
        }

        $currentImage = DB::table($uploadType->getTable())
            ->where($uploadType->getIdentifierField(), $identifier)
            ->value($uploadType->getColumn());

        $fileName = $this->generateFileName($uploadType, $identifier, $file->getClientOriginalExtension());
        $path = $file->storeAs($folder, $fileName, 'public');

        if (!$path || !Storage::disk('public')->exists($path)) {
            return apiResponse(5001, null, "Failed to store the image file.", 500);
        }

        $fullPath = "storage/{$path}";

        if ($uploadType->shouldAppendToArray()) {
            $images = json_decode($currentImage, true) ?? [];
            if (!is_array($images)) $images = [];
            if ($uploadType->getMaxArraySize() && count($images) >= $uploadType->getMaxArraySize()) {
                return apiResponse(4003, null, "Cannot append: maximum of {$uploadType->getMaxArraySize()} images reached.", 400);
            }
            if (!in_array($fullPath, $images)) {
                $images[] = $fullPath; 
            }
            DB::table($uploadType->getTable())
                ->updateOrInsert(
                    [$uploadType->getIdentifierField() => $identifier],
                    [$uploadType->getColumn() => json_encode($images), 'updated_at' => now()]
                );
        } else {
            DB::table($uploadType->getTable())
                ->updateOrInsert(
                    [$uploadType->getIdentifierField() => $identifier],
                    [$uploadType->getColumn() => $fullPath, 'updated_at' => now()]
                );

            if ($currentImage && $currentImage !== $uploadType->getDefaultValue()) {
                $normalizedCurrentImage = str_starts_with($currentImage, 'storage/')
                    ? substr($currentImage, 8)
                    : $currentImage;
                if (Storage::disk('public')->exists($normalizedCurrentImage)) {
                    Storage::disk('public')->delete($normalizedCurrentImage);
                }
            }
        }

        return apiResponse(2001, [
            'image_path' => $fullPath,
            'type' => $type,
            'identifier' => $identifier,
            'file_name' => $fileName,
        ], 'Image uploaded successfully.', 200);
    }

    public function deleteImage(Request $request): \Illuminate\Http\JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'type' => ['required', 'string', 'in:' . implode(',', array_keys($this->supportedTypes))],
            'identifier' => ['required', 'string'],
            'brand_symbol' => ['required', 'string', 'regex:/^[a-zA-Z]{4,10}$/'],
            'image_path' => ['required', 'string', 'regex:/^storage\/[a-zA-Z]{4,10}\/.*$/'],
        ]);

        if ($validator->fails()) {
            return apiResponse(4001, null, $validator->errors()->first(), 400);
        }

        $type = $request->input('type');
        $identifier = $request->input('identifier');
        $brandSymbol = $request->input('brand_symbol');
        $imagePath = $request->input('image_path');
        $uploadType = app($this->supportedTypes[$type]);

        $recordExists = DB::table($uploadType->getTable())
            ->where($uploadType->getIdentifierField(), $identifier)
            ->exists();

        if (!$recordExists) {
            return apiResponse(4041, null, "Record not found for identifier: {$identifier}.", 404);
        }

        $filePath = str_starts_with($imagePath, 'storage/') ? substr($imagePath, 8) : $imagePath;

        if (!Storage::disk('public')->exists($filePath)) {
            return apiResponse(4042, null, "File not found: {$imagePath}.", 404);
        }

        $currentImage = DB::table($uploadType->getTable())
            ->where($uploadType->getIdentifierField(), $identifier)
            ->value($uploadType->getColumn());

        if ($uploadType->shouldAppendToArray()) {
            $images = json_decode($currentImage, true) ?? [];
            if (!is_array($images)) $images = [];
            if (!in_array($imagePath, $images)) {
                return apiResponse(4003, null, "Image {$imagePath} not found in the array.", 400);
            }
            $images = array_filter($images, fn($img) => $img !== $imagePath);
            DB::table($uploadType->getTable())
                ->where($uploadType->getIdentifierField(), $identifier)
                ->update([$uploadType->getColumn() => json_encode(array_values($images)), 'updated_at' => now()]);
        } else {
            if ($currentImage !== $imagePath) {
                return apiResponse(4003, null, "Image {$imagePath} does not match the current image in the database.", 400);
            }
            DB::table($uploadType->getTable())
                ->where($uploadType->getIdentifierField(), $identifier)
                ->update([$uploadType->getColumn() => $uploadType->getDefaultValue(), 'updated_at' => now()]);
        }

        try {
            Storage::disk('public')->delete($filePath);
        } catch (\Exception $e) {
            return apiResponse(5001, null, "Failed to delete file: {$e->getMessage()}", 500);
        }

        return apiResponse(2000, ['image_path' => $imagePath], 'Image deleted successfully.', 200);
    }

    private function generateFileName(BaseUploadType $uploadType, string $identifier, string $extension): string
    {
        $fileNameFormat = $uploadType->getFileNameFormat();
        if ($fileNameFormat instanceof Closure) {
            return $fileNameFormat($uploadType->getTable(), $identifier, $extension);
        }

        $timestamp = now()->timestamp;
        return "{$uploadType->getTable()}_{$identifier}_{$timestamp}.{$extension}";
    }
}