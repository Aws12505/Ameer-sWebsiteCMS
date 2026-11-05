<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ReorderRequest;
use App\Http\Requests\Admin\ServiceRequest;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Support\MediaUrl;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class ServiceController extends Controller
{
    /**
     * List the services, optionally only those of one core service.
     */
    public function index(Request $request): Response
    {
        $categoryId = $request->integer('category') ?: null;

        $services = Service::query()
            ->ordered()
            ->with('categories:id,title')
            ->when($categoryId, fn (Builder $query) => $query->whereHas('categories', fn (Builder $query) => $query->whereKey($categoryId)))
            ->get();

        return Inertia::render('admin/services/index', [
            'items' => $services->map(fn (Service $service): array => [
                'id' => $service->id,
                'title' => $service->title,
                'slug' => $service->slug,
                'image' => MediaUrl::for($service->image_path),
                'published' => $service->published,
                'categories' => $service->categories->pluck('title'),
            ]),
            'categories' => $this->categoryOptions(),
            'filter' => ['category' => $categoryId],
        ]);
    }

    /**
     * Show the form for a new service.
     */
    public function create(Request $request): Response
    {
        return Inertia::render('admin/services/form', [
            'item' => null,
            'defaults' => [
                'slug' => '', 'title' => '', 'description' => '',
                'category_ids' => $request->integer('category') ? [$request->integer('category')] : [],
                'includes' => ['', '', ''], 'image' => null, 'published' => false,
            ],
            'categories' => $this->categoryOptions(),
        ]);
    }

    /**
     * Save a new service.
     */
    public function store(ServiceRequest $request): RedirectResponse
    {
        $service = DB::transaction(function () use ($request): Service {
            $service = Service::query()->create($this->attributes($request));
            $service->categories()->sync($request->validated('category_ids'));

            return $service;
        });

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Service created.']);

        return to_route('admin.services.edit', $service);
    }

    /**
     * Show the form for a service.
     */
    public function edit(Service $service): Response
    {
        $service->load('editor', 'categories:id');

        return Inertia::render('admin/services/form', [
            'item' => [
                'id' => $service->id,
                'keyLocked' => $service->isKeyLocked(),
                'lastEdited' => $service->lastEdited(),
            ],
            'defaults' => [
                'slug' => $service->slug,
                'title' => $service->title,
                'description' => $service->description,
                'category_ids' => $service->categories->pluck('id'),
                'includes' => $service->includes,
                'image' => MediaUrl::field($service->image_path, $service->image_alt),
                'published' => $service->published,
            ],
            'categories' => $this->categoryOptions(),
        ]);
    }

    /**
     * Save changes to a service.
     */
    public function update(ServiceRequest $request, Service $service): RedirectResponse
    {
        DB::transaction(function () use ($request, $service): void {
            $service->categories()->sync($request->validated('category_ids'));
            $service->update($this->attributes($request));
        });

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Service saved.']);

        return to_route('admin.services.edit', $service);
    }

    /**
     * Move a service to the trash.
     */
    public function destroy(Service $service): RedirectResponse
    {
        $service->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Service moved to the trash.']);

        return to_route('admin.services.index');
    }

    /**
     * Save the manager's drag-and-drop order (of the whole list or one core service's services).
     */
    public function reorder(ReorderRequest $request): RedirectResponse
    {
        Service::reorder($request->orderedIds());

        return back();
    }

    /**
     * Switch a service on or off from the list.
     */
    public function publish(Request $request, Service $service): RedirectResponse
    {
        $service->update(['published' => $request->boolean('published')]);

        return back();
    }

    /**
     * The core services to choose from.
     *
     * @return array<int, array{id: int, title: string}>
     */
    protected function categoryOptions(): array
    {
        return ServiceCategory::query()->ordered()->get(['id', 'title'])
            ->map(fn (ServiceCategory $category): array => ['id' => $category->id, 'title' => $category->title])
            ->all();
    }

    /**
     * The validated form mapped to database columns.
     *
     * @return array<string, mixed>
     */
    protected function attributes(ServiceRequest $request): array
    {
        $data = $request->validated();

        return [
            'slug' => $data['slug'],
            'title' => $data['title'],
            'description' => $data['description'],
            'includes' => array_values($data['includes']),
            'image_path' => $data['image']['path'],
            'image_alt' => $data['image']['alt'],
            'published' => $data['published'] ?? false,
        ];
    }
}
