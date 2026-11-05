<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ServiceFilterResource;
use App\Models\ServiceCategory;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ServiceFilterController extends Controller
{
    /**
     * The Services page filter chips, one per published core service.
     */
    public function __invoke(): AnonymousResourceCollection
    {
        return ServiceFilterResource::collection(ServiceCategory::query()->published()->ordered()->get());
    }
}
