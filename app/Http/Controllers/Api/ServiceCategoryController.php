<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ServiceCategoryResource;
use App\Models\ServiceCategory;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ServiceCategoryController extends Controller
{
    /**
     * All published core services, in the manager's order.
     */
    public function index(): AnonymousResourceCollection
    {
        return ServiceCategoryResource::collection(ServiceCategory::query()->published()->ordered()->get());
    }

    /**
     * One published core service with its page content.
     */
    public function show(string $key): ServiceCategoryResource
    {
        $category = ServiceCategory::query()->published()->where('slug', $key)->firstOrFail();

        return ServiceCategoryResource::make($category)->withDetail();
    }
}
