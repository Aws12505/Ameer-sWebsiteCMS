<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ReorderRequest;
use App\Http\Requests\Admin\ReviewRequest;
use App\Models\Review;
use App\Models\Setting;
use App\Support\MediaUrl;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ReviewController extends Controller
{
    /**
     * List the reviews in the manager's order.
     */
    public function index(): Response
    {
        return Inertia::render('admin/reviews/index', [
            'items' => Review::query()->ordered()->get()->map(fn (Review $review): array => [
                'id' => $review->id,
                'name' => $review->name,
                'initials' => $review->initials,
                'project' => $review->project,
                'rating' => $review->rating,
                'image' => MediaUrl::for($review->photo_path),
                'published' => $review->published,
            ]),
            'showReviews' => (bool) (Setting::valueOf(Setting::Hero)['showReviews'] ?? false),
        ]);
    }

    /**
     * Show the form for a new review.
     */
    public function create(): Response
    {
        return Inertia::render('admin/reviews/form', [
            'item' => null,
            'defaults' => ['slug' => '', 'name' => '', 'project' => '', 'rating' => 5, 'quote' => '', 'photo' => null, 'published' => false],
        ]);
    }

    /**
     * Save a new review.
     */
    public function store(ReviewRequest $request): RedirectResponse
    {
        $review = Review::query()->create($this->attributes($request));

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Review created.']);

        return to_route('admin.reviews.edit', $review);
    }

    /**
     * Show the form for a review.
     */
    public function edit(Review $review): Response
    {
        $review->load('editor');

        return Inertia::render('admin/reviews/form', [
            'item' => [
                'id' => $review->id,
                'keyLocked' => $review->isKeyLocked(),
                'lastEdited' => $review->lastEdited(),
            ],
            'defaults' => [
                ...$review->only(['slug', 'name', 'project', 'rating', 'quote', 'published']),
                'photo' => MediaUrl::field($review->photo_path),
            ],
        ]);
    }

    /**
     * Save changes to a review.
     */
    public function update(ReviewRequest $request, Review $review): RedirectResponse
    {
        $review->update($this->attributes($request));

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Review saved.']);

        return to_route('admin.reviews.edit', $review);
    }

    /**
     * Move a review to the trash.
     */
    public function destroy(Review $review): RedirectResponse
    {
        $review->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Review moved to the trash.']);

        return to_route('admin.reviews.index');
    }

    /**
     * Save the manager's drag-and-drop order.
     */
    public function reorder(ReorderRequest $request): RedirectResponse
    {
        Review::reorder($request->orderedIds());

        return back();
    }

    /**
     * Switch a review on or off from the list.
     */
    public function publish(Request $request, Review $review): RedirectResponse
    {
        $review->update(['published' => $request->boolean('published')]);

        return back();
    }

    /**
     * The validated form mapped to database columns.
     *
     * @return array<string, mixed>
     */
    protected function attributes(ReviewRequest $request): array
    {
        $data = $request->validated();

        return [
            'slug' => $data['slug'],
            'name' => $data['name'],
            'project' => $data['project'],
            'rating' => $data['rating'],
            'quote' => $data['quote'],
            'photo_path' => $data['photo']['path'] ?? null,
            'published' => $data['published'] ?? false,
        ];
    }
}
