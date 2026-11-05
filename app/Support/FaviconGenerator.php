<?php

namespace App\Support;

use Illuminate\Support\Facades\Storage;
use RuntimeException;

/**
 * Creates the 32, 180, 192 and 512 px favicon files from one square PNG.
 */
class FaviconGenerator
{
    /**
     * Generate the sizes and return their stored paths, keyed by size.
     *
     * @return array<int, string>
     */
    public function generate(string $sourcePath): array
    {
        $disk = Storage::disk(config('cms.disk'));
        $source = imagecreatefromstring($disk->get($sourcePath) ?? '');

        if ($source === false) {
            throw new RuntimeException('The favicon could not be read as an image.');
        }

        $name = pathinfo($sourcePath, PATHINFO_FILENAME);
        $paths = [];

        foreach (PublicContent::FaviconSizes as $size) {
            $canvas = imagecreatetruecolor($size, $size);
            imagealphablending($canvas, false);
            imagesavealpha($canvas, true);
            $transparent = imagecolorallocatealpha($canvas, 0, 0, 0, 127);

            if ($transparent !== false) {
                imagefill($canvas, 0, 0, $transparent);
            }
            imagecopyresampled($canvas, $source, 0, 0, 0, 0, $size, $size, imagesx($source), imagesy($source));

            ob_start();
            imagepng($canvas);
            $png = (string) ob_get_clean();

            $path = "favicons/{$name}-{$size}.png";
            $disk->put($path, $png, 'public');
            $paths[$size] = $path;
        }

        return $paths;
    }
}
