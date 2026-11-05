<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UploadRequest;
use App\Support\MediaUrl;
use Illuminate\Http\JsonResponse;

class UploadController extends Controller
{
    /**
     * Store an uploaded photo or video as-is (the front end resizes photos).
     */
    public function __invoke(UploadRequest $request): JsonResponse
    {
        $file = $request->file('file');
        $dimensions = $request->input('kind') === 'video' ? false : @getimagesize($file->getRealPath());

        $path = $file->store('uploads/'.now()->format('Y/m'), [
            'disk' => config('cms.disk'),
            'visibility' => 'public',
        ]);

        abort_if($path === false, 500, 'The file could not be saved. Please try again.');

        return response()->json([
            'path' => $path,
            'url' => MediaUrl::for($path),
            'width' => $dimensions ? $dimensions[0] : null,
            'height' => $dimensions ? $dimensions[1] : null,
        ]);
    }
}
