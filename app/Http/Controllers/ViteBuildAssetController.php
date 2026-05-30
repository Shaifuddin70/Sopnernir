<?php

namespace App\Http\Controllers;

use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ViteBuildAssetController extends Controller
{
    public function show(string $path): BinaryFileResponse
    {
        if (str_contains($path, '..')) {
            abort(404);
        }

        $file = public_path('build/'.$path);

        if (! is_file($file)) {
            abort(404);
        }

        return response()->file($file, [
            'Content-Type' => $this->mimeType($file),
            'Cache-Control' => 'public, max-age=31536000, immutable',
        ]);
    }

    private function mimeType(string $file): string
    {
        return match (strtolower(pathinfo($file, PATHINFO_EXTENSION))) {
            'css' => 'text/css; charset=UTF-8',
            'js' => 'application/javascript; charset=UTF-8',
            'json' => 'application/json',
            default => 'application/octet-stream',
        };
    }
}
