<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ReviewResource;
use App\Models\Review;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ReviewController extends Controller
{
    /**
     * All published reviews, in the manager's order.
     */
    public function __invoke(): AnonymousResourceCollection
    {
        return ReviewResource::collection(Review::query()->published()->ordered()->get());
    }
}
