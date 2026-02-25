<?php

namespace App\Services\Upload;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class FileUploadService
{
    public const MAX_UPLOAD_KB = 3072; // 3MB
    public const MAX_UPLOAD_BYTES = self::MAX_UPLOAD_KB * 1024;

    private const ALLOWED_IMAGE_MIMES = [
        'image/jpeg',
        'image/png',
        'image/webp',
        'image/gif',
        'image/svg+xml',
    ];

    public function storeUploadedFile(
        UploadedFile $file,
        string $directory,
        string $disk = 'public',
        ?string $filenamePrefix = null,
        int $maxBytes = self::MAX_UPLOAD_BYTES,
        ?array $allowedExtensions = null
    ): string {
        if (!$file->isValid()) {
            throw ValidationException::withMessages([
                'file' => ['Failed to upload file.'],
            ]);
        }

        $size = (int) $file->getSize();
        if ($size > $maxBytes) {
            throw ValidationException::withMessages([
                'file' => ['The uploaded file must not exceed 3MB.'],
            ]);
        }

        $extension = strtolower($file->extension());
        if ($allowedExtensions !== null && !in_array($extension, $allowedExtensions, true)) {
            throw ValidationException::withMessages([
                'file' => ['Invalid file type.'],
            ]);
        }

        $filename = $this->buildFilename($extension, $filenamePrefix);
        $directory = trim($directory, '/');
        $path = $directory . '/' . $filename;

        $stored = Storage::disk($disk)->putFileAs($directory, $file, $filename);
        if (!$stored) {
            throw ValidationException::withMessages([
                'file' => ['Failed to save uploaded file.'],
            ]);
        }

        return $path;
    }

    public function storeOptimizedUploadedImage(
        UploadedFile $file,
        string $directory,
        string $disk = 'public',
        ?string $filenamePrefix = null,
        int $maxBytes = self::MAX_UPLOAD_BYTES
    ): string {
        if (!$file->isValid()) {
            throw ValidationException::withMessages([
                'image' => ['Failed to upload image.'],
            ]);
        }

        $rawData = @file_get_contents($file->getRealPath());
        if ($rawData === false) {
            throw ValidationException::withMessages([
                'image' => ['Failed to read uploaded image.'],
            ]);
        }

        $mimeType = $this->normalizeMime($file->getMimeType() ?: $this->detectMimeType($rawData));
        if (!in_array($mimeType, self::ALLOWED_IMAGE_MIMES, true)) {
            throw ValidationException::withMessages([
                'image' => ['The image must be JPEG, PNG, GIF, WEBP, or SVG.'],
            ]);
        }

        return $this->storeOptimizedImageBinary(
            $rawData,
            $mimeType,
            $directory,
            $disk,
            $filenamePrefix,
            $maxBytes
        );
    }

    public function storeOptimizedBase64Image(
        string $base64Image,
        string $directory,
        string $disk = 'public',
        ?string $filenamePrefix = null,
        int $maxBytes = self::MAX_UPLOAD_BYTES
    ): string {
        $mimeType = null;
        $payload = $base64Image;

        if (preg_match('/^data:([a-zA-Z0-9\/\-\+\.]+);base64,/', $base64Image, $matches)) {
            $mimeType = $this->normalizeMime(strtolower($matches[1]));
            $payload = substr($base64Image, strpos($base64Image, ',') + 1);
        }

        $rawData = base64_decode($payload, true);
        if ($rawData === false) {
            throw ValidationException::withMessages([
                'image' => ['Invalid base64 image data.'],
            ]);
        }

        $detectedMime = $this->normalizeMime($this->detectMimeType($rawData));
        $mimeType = $this->normalizeMime($mimeType ?? $detectedMime);

        if (!in_array($mimeType, self::ALLOWED_IMAGE_MIMES, true)) {
            throw ValidationException::withMessages([
                'image' => ['The image must be JPEG, PNG, GIF, WEBP, or SVG.'],
            ]);
        }

        return $this->storeOptimizedImageBinary(
            $rawData,
            $mimeType,
            $directory,
            $disk,
            $filenamePrefix,
            $maxBytes
        );
    }

    private function storeOptimizedImageBinary(
        string $rawData,
        string $mimeType,
        string $directory,
        string $disk,
        ?string $filenamePrefix,
        int $maxBytes
    ): string {
        $directory = trim($directory, '/');

        if ($mimeType === 'image/svg+xml') {
            if (strlen($rawData) > $maxBytes) {
                throw ValidationException::withMessages([
                    'image' => ['The image must not exceed 3MB.'],
                ]);
            }

            $extension = 'svg';
            $filename = $this->buildFilename($extension, $filenamePrefix);
            $path = $directory . '/' . $filename;
            $stored = Storage::disk($disk)->put($path, $rawData);

            if (!$stored) {
                throw ValidationException::withMessages([
                    'image' => ['Failed to save image.'],
                ]);
            }

            return $path;
        }

        $optimizedData = $this->optimizeRasterImage($rawData, $mimeType, $maxBytes);
        if (strlen($optimizedData) > $maxBytes) {
            throw ValidationException::withMessages([
                'image' => ['The image must not exceed 3MB.'],
            ]);
        }

        $extension = $this->mimeToExtension($mimeType);
        $filename = $this->buildFilename($extension, $filenamePrefix);
        $path = $directory . '/' . $filename;
        $stored = Storage::disk($disk)->put($path, $optimizedData);

        if (!$stored) {
            throw ValidationException::withMessages([
                'image' => ['Failed to save image.'],
            ]);
        }

        return $path;
    }

    private function optimizeRasterImage(string $rawData, string $mimeType, int $maxBytes): string
    {
        if (strlen($rawData) <= $maxBytes) {
            return $rawData;
        }

        if (!function_exists('imagecreatefromstring')) {
            return $rawData;
        }

        $image = @imagecreatefromstring($rawData);
        if (!$image) {
            return $rawData;
        }

        $candidates = [];

        if ($mimeType === 'image/jpeg' || $mimeType === 'image/webp') {
            $qualities = [92, 88, 84, 80, 76, 72, 68, 64, 60];
            foreach ($qualities as $quality) {
                $encoded = $this->encodeImage($image, $mimeType, $quality);
                if ($encoded === null) {
                    continue;
                }
                $candidates[] = $encoded;
                if (strlen($encoded) <= $maxBytes) {
                    imagedestroy($image);
                    return $encoded;
                }
            }
        } elseif ($mimeType === 'image/png') {
            $encoded = $this->encodeImage($image, $mimeType, 100);
            if ($encoded !== null) {
                $candidates[] = $encoded;
                if (strlen($encoded) <= $maxBytes) {
                    imagedestroy($image);
                    return $encoded;
                }
            }
        } elseif ($mimeType === 'image/gif') {
            $encoded = $this->encodeImage($image, $mimeType, 100);
            if ($encoded !== null) {
                $candidates[] = $encoded;
                if (strlen($encoded) <= $maxBytes) {
                    imagedestroy($image);
                    return $encoded;
                }
            }
        }

        imagedestroy($image);

        if (empty($candidates)) {
            return $rawData;
        }

        usort($candidates, fn (string $a, string $b) => strlen($a) <=> strlen($b));
        return $candidates[0];
    }

    private function encodeImage($image, string $mimeType, int $quality): ?string
    {
        ob_start();
        $result = false;

        if ($mimeType === 'image/jpeg') {
            $result = imagejpeg($image, null, $quality);
        } elseif ($mimeType === 'image/png') {
            // PNG compression level: 0 (none) to 9 (max)
            $compression = 9;
            $result = imagepng($image, null, $compression);
        } elseif ($mimeType === 'image/webp' && function_exists('imagewebp')) {
            $result = imagewebp($image, null, $quality);
        } elseif ($mimeType === 'image/gif') {
            $result = imagegif($image);
        }

        $data = ob_get_clean();
        if (!$result || !is_string($data)) {
            return null;
        }

        return $data;
    }

    private function detectMimeType(string $data): string
    {
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        return (string) $finfo->buffer($data);
    }

    private function normalizeMime(?string $mimeType): string
    {
        $mimeType = strtolower((string) $mimeType);

        return match ($mimeType) {
            'image/jpg' => 'image/jpeg',
            default => $mimeType,
        };
    }

    private function mimeToExtension(string $mimeType): string
    {
        return match ($mimeType) {
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
            'image/gif' => 'gif',
            'image/svg+xml' => 'svg',
            default => 'jpg',
        };
    }

    private function buildFilename(string $extension, ?string $filenamePrefix = null): string
    {
        $prefix = $filenamePrefix ? trim($filenamePrefix, '_') : 'upload';
        return $prefix . '_' . Str::uuid() . '.' . strtolower($extension);
    }
}

