<?php

declare(strict_types=1);

namespace Canalizador\VideoProduction\Video\Infrastructure\Services;

final class GdChromaKeyCompositor
{
    /**
     * Composites $overlayPath onto the green (#00FF00) region of $backgroundPath.
     * Returns the path to the resulting composite image, or null on failure.
     */
    public function composite(string $backgroundPath, string $overlayPath, string $outputPath): ?string
    {
        $bg = $this->loadImage($backgroundPath);
        $overlay = $this->loadImage($overlayPath);

        if ($bg === null || $overlay === null) {
            return null;
        }

        $bgWidth = imagesx($bg);
        $bgHeight = imagesy($bg);

        $bounds = $this->findGreenBounds($bg, $bgWidth, $bgHeight);

        if ($bounds === null) {
            imagedestroy($bg);
            imagedestroy($overlay);

            return null;
        }

        [$minX, $minY, $maxX, $maxY] = $bounds;
        $regionW = $maxX - $minX + 1;
        $regionH = $maxY - $minY + 1;

        $resized = imagecreatetruecolor($regionW, $regionH);
        imagecopyresampled($resized, $overlay, 0, 0, 0, 0, $regionW, $regionH, imagesx($overlay), imagesy($overlay));

        for ($y = $minY; $y <= $maxY; $y++) {
            for ($x = $minX; $x <= $maxX; $x++) {
                if ($this->isGreen(imagecolorat($bg, $x, $y))) {
                    $color = imagecolorat($resized, $x - $minX, $y - $minY);
                    imagesetpixel($bg, $x, $y, $color);
                }
            }
        }

        imagedestroy($resized);
        imagepng($bg, $outputPath);

        imagedestroy($bg);
        imagedestroy($overlay);

        return $outputPath;
    }

    /** @return array{int, int, int, int}|null [minX, minY, maxX, maxY] */
    private function findGreenBounds(\GdImage $img, int $width, int $height): ?array
    {
        $minX = $width;
        $minY = $height;
        $maxX = 0;
        $maxY = 0;
        $found = false;

        for ($y = 0; $y < $height; $y++) {
            for ($x = 0; $x < $width; $x++) {
                if ($this->isGreen(imagecolorat($img, $x, $y))) {
                    $minX = min($minX, $x);
                    $minY = min($minY, $y);
                    $maxX = max($maxX, $x);
                    $maxY = max($maxY, $y);
                    $found = true;
                }
            }
        }

        return $found ? [$minX, $minY, $maxX, $maxY] : null;
    }

    private function isGreen(int $rgb): bool
    {
        $r = ($rgb >> 16) & 0xFF;
        $g = ($rgb >> 8) & 0xFF;
        $b = $rgb & 0xFF;

        return $g > 180 && $r < 80 && $b < 80;
    }

    private function loadImage(string $path): ?\GdImage
    {
        $info = @getimagesize($path);

        if ($info === false) {
            return null;
        }

        return match ($info[2]) {
            IMAGETYPE_PNG => @imagecreatefrompng($path),
            IMAGETYPE_JPEG => @imagecreatefromjpeg($path),
            IMAGETYPE_WEBP => @imagecreatefromwebp($path),
            default => null,
        } ?: null;
    }
}
