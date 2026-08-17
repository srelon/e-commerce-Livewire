<?php

namespace App\Livewire\Traits;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

trait HandlesRichText
{
    protected function handleRichTextImageUpload(?UploadedFile $file, string $field, string $folder): void {
        if (! $file) {
            return;
        }

        $path = $file->store($folder, 'public');
        $url = Storage::disk('public')->url($path);

        $this->dispatch('richtext-image-inserted', field: $field, url: $url);
    }

    protected function sanitizeContent(string $html): string {
        $allowed_tags = ['p', 'br', 'h2', 'h3', 'ul', 'ol', 'li', 'blockquote', 'strong', 'b', 'em', 'i', 'u', 'a', 'img'];

        $stripped = strip_tags($html, $allowed_tags);

        return preg_replace_callback('/<(a|img)\b[^>]*>/i', function (array $matches): string {
            $tag = strtolower($matches[1]);

            if ($tag === 'a') {
                preg_match('/href\s*=\s*"([^"]*)"/i', $matches[0], $href);
                $url = $href[1] ?? '';

                return preg_match('~^(https?:|/|#)~i', $url)
                    ? '<a href="'.e($url).'" target="_blank" rel="noopener noreferrer">'
                    : '<a>';
            }

            preg_match('/src\s*=\s*"([^"]*)"/i', $matches[0], $src);
            preg_match('/alt\s*=\s*"([^"]*)"/i', $matches[0], $alt);
            $url = $src[1] ?? '';

            return preg_match('~^(https?:|/)~i', $url)
                ? '<img src="'.e($url).'" alt="'.e($alt[1] ?? '').'">'
                : '';
        }, $stripped);
    }
}
