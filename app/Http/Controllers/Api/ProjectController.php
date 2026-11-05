<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProjectResource;
use App\Models\Project;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ProjectController extends Controller
{
    /**
     * All published projects, in the manager's order.
     */
    public function __invoke(): AnonymousResourceCollection
    {
        $projects = Project::query()
            ->published()
            ->ordered()
            ->with(['mainCategory', 'extraCategories'])
            ->get();

        return ProjectResource::collection($projects);
    }
}
