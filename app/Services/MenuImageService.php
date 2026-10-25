<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use Symfony\Component\Process\Process;

class MenuImageService
{
    public const DISK = 'local';

    public const FULL_MAX_WIDTH = 1200;

    public const THUMB_SIZE = 480;

    /**
     * Process an uploaded menu image into a private full image and thumbnail.
     *
     * @return array{image_path: string, thumb_path: string}
     */
    public function store(UploadedFile $file): array
    {
        $workDir = sys_get_temp_dir().'/menu-image-'.Str::uuid();
        File::ensureDirectoryExists($workDir);

        $input = $file->getRealPath();
        $fullOutput = $workDir.'/full.webp';
        $thumbOutput = $workDir.'/thumb.webp';

        $orientation = $this->orientationFilter($input);
        $disableAutorotate = $orientation !== null;

        try {
            $this->runFfmpeg($this->filterChain($orientation, false), $input, $fullOutput, 80, $disableAutorotate);
            $this->runFfmpeg($this->filterChain($orientation, true), $input, $thumbOutput, 75, $disableAutorotate);

            $base = 'menus/'.Str::uuid();
            $imagePath = $base.'.webp';
            $thumbPath = $base.'-thumb.webp';

            $disk = Storage::disk(self::DISK);

            try {
                $stored = $disk->put($imagePath, File::get($fullOutput))
                    && $disk->put($thumbPath, File::get($thumbOutput));
            } catch (\Throwable $exception) {
                $disk->delete([$imagePath, $thumbPath]);

                throw $exception;
            }

            if (! $stored) {
                $disk->delete([$imagePath, $thumbPath]);

                throw new RuntimeException('Gambar gagal disimpan.');
            }

            return [
                'image_path' => $imagePath,
                'thumb_path' => $thumbPath,
            ];
        } finally {
            File::deleteDirectory($workDir);
        }
    }

    public function delete(?string $imagePath, ?string $thumbPath): void
    {
        $paths = array_values(array_filter([$imagePath, $thumbPath]));

        if ($paths !== []) {
            Storage::disk(self::DISK)->delete($paths);
        }
    }

    private function runFfmpeg(
        string $filter,
        string $input,
        string $output,
        int $quality,
        bool $disableAutorotate = false,
    ): void {
        $command = [
            (string) config('menu.ffmpeg_path', 'ffmpeg'),
            '-hide_banner',
            '-loglevel', 'error',
            '-y',
        ];

        if ($disableAutorotate) {
            $command[] = '-noautorotate';
        }

        array_push(
            $command,
            '-i', $input,
            '-vf', $filter,
            '-c:v', 'libwebp',
            '-quality', (string) $quality,
            '-compression_level', '6',
            '-map_metadata', '-1',
            '-an',
            '-sn',
            $output,
        );

        $process = new Process($command);

        $process->setTimeout(30);
        $process->run();

        if (! $process->isSuccessful() || ! is_file($output)) {
            throw new RuntimeException('Gambar gagal diproses: '.trim($process->getErrorOutput()));
        }
    }

    private function filterChain(?string $orientation, bool $thumbnail): string
    {
        $filters = [];

        if ($orientation !== null) {
            $filters[] = $orientation;
        }

        if ($thumbnail) {
            $size = self::THUMB_SIZE;
            $filters[] = "scale={$size}:{$size}:force_original_aspect_ratio=increase";
            $filters[] = "crop={$size}:{$size}";
        } else {
            $max = self::FULL_MAX_WIDTH;
            $filters[] = "scale=w='min(iw,{$max})':h='min(ih,{$max})':force_original_aspect_ratio=decrease";
        }

        return implode(',', $filters);
    }

    private function orientationFilter(string $input): ?string
    {
        if (! function_exists('exif_read_data')) {
            return null;
        }

        $exif = @exif_read_data($input);

        if (! is_array($exif) || ! isset($exif['Orientation'])) {
            return null;
        }

        return match ((int) $exif['Orientation']) {
            2 => 'hflip',
            3 => 'hflip,vflip',
            4 => 'vflip',
            5 => 'transpose=clock_flip',
            6 => 'transpose=clock',
            7 => 'transpose=cclock_flip',
            8 => 'transpose=cclock',
            default => null,
        };
    }
}
