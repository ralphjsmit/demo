<?php

namespace App\Support;

use GdImage;
use Spatie\Color\Hex;
use Spatie\Color\Hsl;

class GradientImageGenerator
{
    protected const int CANVAS_SIZE = 200;

    protected const int JPEG_QUALITY = 90;

    protected const int GRAIN_INTENSITY = 12;

    public static function generate(string $hexColor, int $width, int $height): string
    {
        $cachePath = static::getCachePath($hexColor, $width, $height);

        if (file_exists($cachePath)) {
            return $cachePath;
        }

        $seed = crc32($hexColor . $width . $height);
        mt_srand($seed);

        $palette = static::derivePalette($hexColor);
        $blobs = static::defineBlobs($palette);
        $noiseSeed = crc32("noise-{$hexColor}");
        $canvas = static::renderGradient($blobs, $noiseSeed);

        $output = imagecreatetruecolor($width, $height);
        imagecopyresampled($output, $canvas, 0, 0, 0, 0, $width, $height, static::CANVAS_SIZE, static::CANVAS_SIZE);
        unset($canvas);

        $grainSeed = crc32("grain-{$hexColor}-{$width}-{$height}");
        mt_srand($grainSeed);
        static::applyGrain($output, $width, $height);

        $directory = dirname($cachePath);

        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $tempPath = "{$cachePath}." . uniqid() . '.tmp';
        imagejpeg($output, $tempPath, static::JPEG_QUALITY);
        unset($output);
        rename($tempPath, $cachePath);

        mt_srand();

        return $cachePath;
    }

    /**
     * @param  array<int, array{x: int, y: int, r: int, g: int, b: int, sigma: float}>  $blobs
     */
    protected static function renderGradient(array $blobs, int $noiseSeed): GdImage
    {
        $size = static::CANVAS_SIZE;
        $canvas = imagecreatetruecolor($size, $size);

        $warpStrength = $size * 0.08;
        $warpScale = 4.0;
        $brightnessScale = 7.0;

        $precomputed = [];

        foreach ($blobs as $blob) {
            $precomputed[] = [
                'x' => $blob['x'],
                'y' => $blob['y'],
                'r' => $blob['r'],
                'g' => $blob['g'],
                'b' => $blob['b'],
                'inv2sigma2' => -1.0 / (2.0 * $blob['sigma'] * $blob['sigma']),
            ];
        }

        for ($y = 0; $y < $size; $y++) {
            for ($x = 0; $x < $size; $x++) {
                $nx = $x / $size * $warpScale;
                $ny = $y / $size * $warpScale;

                $warpX = $x + (static::fractalNoise($nx, $ny, $noiseSeed) - 0.5) * 2 * $warpStrength;
                $warpY = $y + (static::fractalNoise($nx + 50, $ny + 50, $noiseSeed + 1) - 0.5) * 2 * $warpStrength;

                $totalWeight = 0.0;
                $r = 0.0;
                $g = 0.0;
                $b = 0.0;

                foreach ($precomputed as $blob) {
                    $dx = $warpX - $blob['x'];
                    $dy = $warpY - $blob['y'];
                    $weight = exp(($dx * $dx + $dy * $dy) * $blob['inv2sigma2']);

                    $r += $blob['r'] * $weight;
                    $g += $blob['g'] * $weight;
                    $b += $blob['b'] * $weight;
                    $totalWeight += $weight;
                }

                $r /= $totalWeight;
                $g /= $totalWeight;
                $b /= $totalWeight;

                $bnx = $x / $size * $brightnessScale;
                $bny = $y / $size * $brightnessScale;
                $brightness = 0.92 + static::fractalNoise($bnx, $bny, $noiseSeed + 2, 3) * 0.16;

                $r = max(0, min(255, (int) ($r * $brightness)));
                $g = max(0, min(255, (int) ($g * $brightness)));
                $b = max(0, min(255, (int) ($b * $brightness)));

                imagesetpixel($canvas, $x, $y, ($r << 16) | ($g << 8) | $b);
            }
        }

        return $canvas;
    }

    protected static function fractalNoise(float $x, float $y, int $seed, int $octaves = 4): float
    {
        $value = 0.0;
        $amplitude = 1.0;
        $frequency = 1.0;
        $maxAmplitude = 0.0;

        for ($i = 0; $i < $octaves; $i++) {
            $value += static::valueNoise($x * $frequency, $y * $frequency, $seed + $i * 31) * $amplitude;
            $maxAmplitude += $amplitude;
            $amplitude *= 0.5;
            $frequency *= 2.0;
        }

        return $value / $maxAmplitude;
    }

    protected static function valueNoise(float $x, float $y, int $seed): float
    {
        $x0 = (int) floor($x);
        $y0 = (int) floor($y);
        $x1 = $x0 + 1;
        $y1 = $y0 + 1;

        $fx = $x - $x0;
        $fy = $y - $y0;
        $fx = $fx * $fx * (3 - 2 * $fx);
        $fy = $fy * $fy * (3 - 2 * $fy);

        $v00 = static::hash2d($x0, $y0, $seed);
        $v10 = static::hash2d($x1, $y0, $seed);
        $v01 = static::hash2d($x0, $y1, $seed);
        $v11 = static::hash2d($x1, $y1, $seed);

        $v0 = $v00 + $fx * ($v10 - $v00);
        $v1 = $v01 + $fx * ($v11 - $v01);

        return $v0 + $fy * ($v1 - $v0);
    }

    protected static function hash2d(int $x, int $y, int $seed): float
    {
        $h = (($x * 374761393 + $y * 668265263 + $seed) & 0x7FFFFFFF);
        $h = (($h ^ ($h >> 13)) & 0x7FFFFFFF);
        $h = ($h * 1274126177) & 0x7FFFFFFF;

        return ($h & 0xFFFF) / 65535.0;
    }

    protected static function applyGrain(GdImage $gd, int $width, int $height): void
    {
        $intensity = static::GRAIN_INTENSITY;

        for ($y = 0; $y < $height; $y++) {
            for ($x = 0; $x < $width; $x++) {
                $rgb = imagecolorat($gd, $x, $y);
                $noise = mt_rand(-$intensity, $intensity);

                $r = max(0, min(255, (($rgb >> 16) & 0xFF) + $noise));
                $g = max(0, min(255, (($rgb >> 8) & 0xFF) + $noise));
                $b = max(0, min(255, ($rgb & 0xFF) + $noise));

                imagesetpixel($gd, $x, $y, ($r << 16) | ($g << 8) | $b);
            }
        }
    }

    /**
     * @param  array<int, array{r: int, g: int, b: int}>  $palette
     * @return array<int, array{x: int, y: int, r: int, g: int, b: int, sigma: float}>
     */
    protected static function defineBlobs(array $palette): array
    {
        $size = static::CANVAS_SIZE;
        $blobs = [];

        foreach ($palette as $index => $color) {
            $isTight = $index % 3 === 0;
            $sigmaMin = $isTight ? (int) ($size * 0.15) : (int) ($size * 0.35);
            $sigmaMax = $isTight ? (int) ($size * 0.35) : (int) ($size * 0.65);

            $blobs[] = [
                'x' => mt_rand((int) ($size * -0.15), (int) ($size * 1.15)),
                'y' => mt_rand((int) ($size * -0.15), (int) ($size * 1.15)),
                'r' => $color['r'],
                'g' => $color['g'],
                'b' => $color['b'],
                'sigma' => mt_rand($sigmaMin, $sigmaMax),
            ];
        }

        return $blobs;
    }

    /**
     * @return array<int, array{r: int, g: int, b: int}>
     */
    protected static function derivePalette(string $hexColor): array
    {
        $hsl = Hex::fromString($hexColor)->toHsl();

        $hue = $hsl->hue();
        $saturation = $hsl->saturation();
        $lightness = $hsl->lightness();

        return [
            static::hslToRgb($hue, $saturation * 0.8, max(15, $lightness * 0.5)),
            static::hslToRgb($hue, min(100, $saturation * 1.15), $lightness),
            static::hslToRgb($hue + 30, min(100, $saturation * 1.05), min(65, $lightness + 10)),
            static::hslToRgb($hue + 55, $saturation * 0.85, min(60, $lightness + 5)),
            static::hslToRgb($hue - 35, min(100, $saturation * 0.95), min(55, $lightness + 5)),
            static::hslToRgb($hue - 55, $saturation * 0.8, min(55, $lightness - 5)),
            static::hslToRgb($hue + 80, $saturation * 0.7, min(60, $lightness + 12)),
            static::hslToRgb($hue - 15, min(100, $saturation * 1.1), min(70, $lightness + 18)),
        ];
    }

    /**
     * @return array{r: int, g: int, b: int}
     */
    protected static function hslToRgb(float $hue, float $saturation, float $lightness): array
    {
        $hue = fmod(fmod($hue, 360) + 360, 360);
        $saturation = max(0, min(100, $saturation));
        $lightness = max(0, min(100, $lightness));

        $hsl = new Hsl($hue, $saturation, $lightness);
        $rgb = $hsl->toRgb();

        return ['r' => $rgb->red(), 'g' => $rgb->green(), 'b' => $rgb->blue()];
    }

    protected static function getCachePath(string $hexColor, int $width, int $height): string
    {
        $sanitized = str_replace('#', '', $hexColor);

        return storage_path("framework/cache/gradients/{$sanitized}-{$width}x{$height}.jpg");
    }
}
