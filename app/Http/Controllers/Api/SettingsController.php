<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Support\PublicContent;
use Illuminate\Http\JsonResponse;

class SettingsController extends Controller
{
    /**
     * The home hero background and the Reviews switch.
     */
    public function __invoke(): JsonResponse
    {
        return response()->json(PublicContent::settings());
    }
}
