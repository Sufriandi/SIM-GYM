<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ImageUploadService
{
    /**
     * Upload, compress, and automatically convert an uploaded image to WebP format.
     *
     * @param UploadedFile $file
     * @param string $folder Relative directory on public disk (e.g. 'photos/produks', 'users/foto')
     * @param string|null $oldPath Optional old file path to delete from storage
     * @param int $maxDimension Max width or height in pixels (e.g. 1920 for full HD, 1200 for products, 800 for avatars)
     * @param int $quality WebP compression quality (80-85 recommended for crystal clear HD with 70-85% size reduction)
     * @return string Relative path stored in public storage (e.g. 'photos/produks/unique_hash.webp')
     */
    public static function uploadAsWebp(
        UploadedFile $file,
        string $folder,
        ?string $oldPath = null,
        int $maxDimension = 1920,
        int $quality = 82
    ): string {
        $folder = trim($folder, '/\\');
        $randomName = Str::random(40) . '.webp';
        $relativeWebpPath = $folder . '/' . $randomName;

        // Ensure target directory exists on disk
        $disk = Storage::disk('public');
        $fullTargetDir = $disk->path($folder);
        if (!is_dir($fullTargetDir)) {
            @mkdir($fullTargetDir, 0755, true);
        }

        $fullDestinationPath = $disk->path($relativeWebpPath);
        $sourcePath = $file->getRealPath();

        try {
            self::processAndSaveWebp($sourcePath, $fullDestinationPath, $maxDimension, $quality);

            // Delete old file if replacing
            if ($oldPath) {
                self::delete($oldPath);
            }

            return $relativeWebpPath;
        } catch (\Throwable $e) {
            Log::error("ImageUploadService: Failed to convert image to WebP: " . $e->getMessage(), [
                'source' => $file->getClientOriginalName(),
                'folder' => $folder,
            ]);

            // Fallback: store original file if GD processing encountered an unexpected issue
            if ($oldPath) {
                self::delete($oldPath);
            }
            return $file->store($folder, 'public');
        }
    }

    /**
     * Upload a file that could be either an image (converted to WebP) or a document (PDF, DOC, etc.).
     *
     * @param UploadedFile $file
     * @param string $folder
     * @param string|null $oldPath
     * @param int $maxDimension
     * @param int $quality
     * @return string
     */
    public static function uploadOrConvertAsWebp(
        UploadedFile $file,
        string $folder,
        ?string $oldPath = null,
        int $maxDimension = 1920,
        int $quality = 82
    ): string {
        $mime = $file->getMimeType();
        $isImage = str_starts_with((string) $mime, 'image/');

        if ($isImage && function_exists('imagewebp')) {
            return self::uploadAsWebp($file, $folder, $oldPath, $maxDimension, $quality);
        }

        // Non-image document or fallback: store as normal
        if ($oldPath) {
            self::delete($oldPath);
        }
        return $file->store($folder, 'public');
    }

    /**
     * Core GD processing: handles auto-orientation, smart resizing to max bounds, alpha channel, and WebP compression.
     */
    protected static function processAndSaveWebp(
        string $sourcePath,
        string $destinationPath,
        int $maxDimension,
        int $quality
    ): void {
        $imageInfo = @getimagesize($sourcePath);
        $mime = $imageInfo['mime'] ?? '';

        $source = match ($mime) {
            'image/jpeg' => @imagecreatefromjpeg($sourcePath),
            'image/png'  => @imagecreatefrompng($sourcePath),
            'image/webp' => @imagecreatefromwebp($sourcePath),
            'image/gif'  => @imagecreatefromgif($sourcePath),
            'image/bmp'  => @imagecreatefrombmp($sourcePath),
            default      => @imagecreatefromstring((string) file_get_contents($sourcePath)),
        };

        if (!$source) {
            throw new \RuntimeException("Unable to open image resource from MIME: {$mime}");
        }

        // 1. Auto-rotate based on EXIF orientation (mobile phone camera shots)
        if ($mime === 'image/jpeg' && function_exists('exif_read_data')) {
            $exif = @exif_read_data($sourcePath);
            if (!empty($exif['Orientation'])) {
                $source = match ($exif['Orientation']) {
                    3 => imagerotate($source, 180, 0),
                    6 => imagerotate($source, -90, 0),
                    8 => imagerotate($source, 90, 0),
                    default => $source,
                };
            }
        }

        $origWidth = imagesx($source);
        $origHeight = imagesy($source);

        // 2. Calculate proportional dimensions preserving aspect ratio
        $targetWidth = $origWidth;
        $targetHeight = $origHeight;

        if ($origWidth > $maxDimension || $origHeight > $maxDimension) {
            if ($origWidth >= $origHeight) {
                $targetWidth = $maxDimension;
                $targetHeight = (int) round(($origHeight / $origWidth) * $maxDimension);
            } else {
                $targetHeight = $maxDimension;
                $targetWidth = (int) round(($origWidth / $origHeight) * $maxDimension);
            }
        }

        // 3. Create target canvas
        $target = imagecreatetruecolor($targetWidth, $targetHeight);

        // 4. Preserve alpha transparency for PNG / WebP / transparent assets
        imagealphablending($target, false);
        imagesavealpha($target, true);
        $transparent = imagecolorallocatealpha($target, 0, 0, 0, 127);
        imagefilledrectangle($target, 0, 0, $targetWidth, $targetHeight, $transparent);

        // 5. High-quality bicubic/bilinear resampling
        imagecopyresampled(
            $target,
            $source,
            0, 0, 0, 0,
            $targetWidth, $targetHeight,
            $origWidth, $origHeight
        );

        // 6. Save as WebP with specified HD quality
        $quality = max(1, min(100, $quality));
        $saved = imagewebp($target, $destinationPath, $quality);

        // Clean up resources immediately
        imagedestroy($source);
        imagedestroy($target);

        if (!$saved || !file_exists($destinationPath)) {
            throw new \RuntimeException("Failed to encode or write WebP image to: {$destinationPath}");
        }
    }

    /**
     * Delete a file safely from storage.
     */
    public static function delete(?string $path, string $disk = 'public'): bool
    {
        if (!$path) {
            return false;
        }

        $storage = Storage::disk($disk);
        if ($storage->exists($path)) {
            return $storage->delete($path);
        }

        return false;
    }
}
