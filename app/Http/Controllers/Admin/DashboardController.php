<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ContactRequestStatus;
use App\Http\Controllers\Controller;
use App\Models\ContactRequest;
use App\Models\Project;
use App\Models\Review;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\Setting;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    /**
     * An overview of the content and the latest contact requests.
     */
    public function __invoke(): Response
    {
        return Inertia::render('dashboard', [
            'stats' => [
                'newRequests' => ContactRequest::query()->where('status', ContactRequestStatus::New)->count(),
                'coreServices' => ServiceCategory::query()->published()->count(),
                'services' => Service::query()->published()->count(),
                'projects' => Project::query()->published()->count(),
                'featuredProjects' => Project::query()->published()->where('featured', true)->count(),
                'reviews' => Review::query()->published()->count(),
                'showReviews' => (bool) (Setting::valueOf(Setting::Hero)['showReviews'] ?? false),
            ],
            'latestRequests' => ContactRequest::query()->latest()->latest('id')->limit(5)->get()
                ->map(fn (ContactRequest $contactRequest): array => [
                    'id' => $contactRequest->id,
                    'name' => $contactRequest->name,
                    'projectType' => $contactRequest->project_type,
                    'status' => $contactRequest->status->value,
                    'createdAt' => $contactRequest->created_at->toIso8601String(),
                ]),
        ]);
    }
}
