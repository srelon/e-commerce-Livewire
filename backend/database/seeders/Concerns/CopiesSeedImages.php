<?php

namespace Database\Seeders\Concerns;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

trait CopiesSeedImages
{
    private function copySeedImage(string $filename, string $target_dir): void {
        $target = "{$target_dir}/{$filename}";

        if (Storage::disk('public')->exists($target)) {
            return;
        }

        $source = dirname(base_path())."/frontend/public/images/{$filename}";

        if (! File::exists($source)) {
            return;
        }

        Storage::disk('public')->makeDirectory($target_dir);
        File::copy($source, storage_path("app/public/{$target}"));
    }
}
