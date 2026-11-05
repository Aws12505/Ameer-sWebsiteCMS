<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Support\PublicContent;
use Illuminate\Http\JsonResponse;

class SiteController extends Controller
{
    /**
     * The site settings used on every page.
     */
    public function __invoke(): JsonResponse
    {
        return response()->json(PublicContent::site());
    }
}
