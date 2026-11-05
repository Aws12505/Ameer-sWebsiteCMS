<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\ProjectCategory;
use App\Models\Review;
use App\Models\Service;
use App\Models\ServiceCategory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Deleted content, kept for a while so it can be restored.
 */
class TrashController extends Controller
{
    /**
     * The trashable content types, their labels and title columns.
     *
     * @var array<string, array{label: string, title: string}>
     */
    protected const array Types = [
        'service-categories' => ['label' => 'Core service', 'title' => 'title'],
        'services' => ['label' => 'Service', 'title' => 'title'],
        'project-categories' => ['label' => 'Project category', 'title' => 'label'],
        'projects' => ['label' => 'Project', 'title' => 'title'],
        'reviews' => ['label' => 'Review', 'title' => 'name'],
    ];

    /**
     * List everything in the trash, most recently deleted first.
     */
    public function index(): Response
    {
        $items = collect(array_keys(self::Types))
            ->flatMap(fn (string $type): array => $this->trashedOf($type))
            ->sortByDesc('deletedAt')
            ->values();

        return Inertia::render('admin/trash', [
            'items' => $items,
            'trashDays' => config('cms.trash_days'),
        ]);
    }

    /**
     * Put an item back where it was.
     */
    public function restore(string $type, int $id): RedirectResponse
    {
        $this->find($type, $id)->restore();

        Inertia::flash('toast', ['type' => 'success', 'message' => self::Types[$type]['label'].' restored.']);

        return back();
    }

    /**
     * Remove an item for good (Admin only).
     */
    public function destroy(string $type, int $id): RedirectResponse
    {
        $this->find($type, $id)->forceDelete();

        Inertia::flash('toast', ['type' => 'success', 'message' => self::Types[$type]['label'].' deleted for good.']);

        return back();
    }

    /**
     * The trashed items of one type, as rows for the list.
     *
     * @return list<array{type: string, typeLabel: string, id: int, title: string, deletedAt: string|null, purgesAt: string|null}>
     */
    protected function trashedOf(string $type): array
    {
        $days = (int) config('cms.trash_days');

        return array_values($this->query($type)->get()->map(fn (ServiceCategory|Service|ProjectCategory|Project|Review $model): array => [
            'type' => $type,
            'typeLabel' => self::Types[$type]['label'],
            'id' => $model->id,
            'title' => (string) $model->getAttribute(self::Types[$type]['title']),
            'deletedAt' => $model->deleted_at?->toIso8601String(),
            'purgesAt' => $model->deleted_at?->addDays($days)->toIso8601String(),
        ])->all());
    }

    /**
     * Find a trashed item.
     */
    protected function find(string $type, int $id): ServiceCategory|Service|ProjectCategory|Project|Review
    {
        return $this->query($type)->findOrFail($id);
    }

    /**
     * The trashed records of one type.
     *
     * @return Builder<ServiceCategory>|Builder<Service>|Builder<ProjectCategory>|Builder<Project>|Builder<Review>
     */
    protected function query(string $type): Builder
    {
        return match ($type) {
            'service-categories' => ServiceCategory::onlyTrashed(),
            'services' => Service::onlyTrashed(),
            'project-categories' => ProjectCategory::onlyTrashed(),
            'projects' => Project::onlyTrashed(),
            'reviews' => Review::onlyTrashed(),
            default => abort(404),
        };
    }
}
