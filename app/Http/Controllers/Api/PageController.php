<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Support\PublicContent;
use Illuminate\Http\JsonResponse;

class PageController extends Controller
{
    /**
     * The editable text of one page.
     */
    public function __invoke(string $page): JsonResponse
    {
        return response()->json(match ($page) {
            'home' => PublicContent::homePage(),
            'services' => PublicContent::page(Setting::ServicesPage),
            'projects' => PublicContent::page(Setting::ProjectsPage),
            'contact' => PublicContent::page(Setting::ContactPage),
            default => abort(404),
        });
    }
}
