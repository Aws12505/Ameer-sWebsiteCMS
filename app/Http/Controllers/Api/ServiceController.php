<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ServiceResource;
use App\Models\Service;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ServiceController extends Controller
{
    /**
     * All published services, in the manager's order.
     */
    public function __invoke(): AnonymousResourceCollection
    {
        $services = Service::query()
            ->published()
            ->ordered()
            ->with(['categories' => fn (BelongsToMany $query) => $query->published()])
            ->get();

        return ServiceResource::collection($services);
    }
}
