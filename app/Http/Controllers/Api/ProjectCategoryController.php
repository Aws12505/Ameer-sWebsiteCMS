<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Models\ProjectCategory;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ProjectCategoryController extends Controller
{
    /**
     * The Projects page filter chips.
     */
    public function __invoke(): AnonymousResourceCollection
    {
        return CategoryResource::collection(ProjectCategory::query()->published()->ordered()->get());
    }
}
