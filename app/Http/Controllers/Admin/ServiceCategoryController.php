<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ReorderRequest;
use App\Http\Requests\Admin\ServiceCategoryRequest;
use App\Models\ServiceCategory;
use App\Support\MediaUrl;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ServiceCategoryController extends Controller
{
    /**
     * List the core services in the manager's order.
     */
    public function index(): Response
    {
        $categories = ServiceCategory::query()->ordered()->withCount('services')->get();

        return Inertia::render('admin/service-categories/index', [
            'items' => $categories->map(fn (ServiceCategory $category): array => [
                'id' => $category->id,
                'title' => $category->title,
                'slug' => $category->slug,
                'image' => MediaUrl::for($category->image_path),
                'published' => $category->published,
                'showOnHome' => $category->show_on_home,
                'isPinned' => $category->is_pinned,
                'externalUrl' => $category->external_url,
                'servicesCount' => $category->services_count,
            ]),
            'maxOnHome' => ServiceCategory::MaxOnHome,
        ]);
    }

    /**
     * Show the form for a new core service.
     */
    public function create(): Response
    {
        return Inertia::render('admin/service-categories/form', [
            'item' => null,
            'defaults' => [
                'slug' => '', 'title' => '', 'filter_label' => '', 'summary' => '', 'image' => null,
                'body' => ['', ''], 'gallery' => [], 'external_url' => '', 'show_on_home' => false,
                'is_pinned' => false, 'seo_title' => '', 'seo_description' => '', 'seo_image' => null,
                'published' => false,
            ],
        ]);
    }

    /**
     * Save a new core service.
     */
    public function store(ServiceCategoryRequest $request): RedirectResponse
    {
        $category = ServiceCategory::query()->create($this->attributes($request));

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Core service created.']);

        return to_route('admin.service-categories.edit', $category);
    }

    /**
     * Show the form for a core service.
     */
    public function edit(ServiceCategory $serviceCategory): Response
    {
        $serviceCategory->load('editor')->loadCount('services');

        return Inertia::render('admin/service-categories/form', [
            'item' => [
                'id' => $serviceCategory->id,
                'keyLocked' => $serviceCategory->isKeyLocked(),
                'lastEdited' => $serviceCategory->lastEdited(),
                'servicesCount' => $serviceCategory->services_count,
            ],
            'defaults' => [
                'slug' => $serviceCategory->slug,
                'title' => $serviceCategory->title,
                'filter_label' => $serviceCategory->filter_label,
                'summary' => $serviceCategory->summary,
                'image' => MediaUrl::field($serviceCategory->image_path, $serviceCategory->image_alt),
                'body' => $serviceCategory->body ?? [],
                'gallery' => MediaUrl::fields($serviceCategory->gallery),
                'external_url' => (string) $serviceCategory->external_url,
                'show_on_home' => $serviceCategory->show_on_home,
                'is_pinned' => $serviceCategory->is_pinned,
                'seo_title' => (string) $serviceCategory->seo_title,
                'seo_description' => (string) $serviceCategory->seo_description,
                'seo_image' => MediaUrl::field($serviceCategory->seo_image_path, $serviceCategory->seo_image_alt),
                'published' => $serviceCategory->published,
            ],
        ]);
    }

    /**
     * Save changes to a core service.
     */
    public function update(ServiceCategoryRequest $request, ServiceCategory $serviceCategory): RedirectResponse
    {
        $serviceCategory->update($this->attributes($request));

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Core service saved.']);

        return to_route('admin.service-categories.edit', $serviceCategory);
    }

    /**
     * Move a core service to the trash, unless services still use it.
     */
    public function destroy(ServiceCategory $serviceCategory): RedirectResponse
    {
        $inUse = $serviceCategory->services()->count();

        if ($inUse > 0) {
            Inertia::flash('toast', [
                'type' => 'error',
                'message' => trans_choice('{1} 1 service uses this core service. Move it first.|[2,*] :count services use this core service. Move them first.', $inUse),
            ]);

            return back();
        }

        $serviceCategory->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Core service moved to the trash.']);

        return to_route('admin.service-categories.index');
    }

    /**
     * Save the manager's drag-and-drop order.
     */
    public function reorder(ReorderRequest $request): RedirectResponse
    {
        ServiceCategory::reorder($request->orderedIds());

        return back();
    }

    /**
     * Switch a core service on or off from the list.
     */
    public function publish(Request $request, ServiceCategory $serviceCategory): RedirectResponse
    {
        $serviceCategory->update(['published' => $request->boolean('published')]);

        return back();
    }

    /**
     * The validated form mapped to database columns.
     *
     * @return array<string, mixed>
     */
    protected function attributes(ServiceCategoryRequest $request): array
    {
        $data = $request->validated();
        $isExternal = filled($data['external_url'] ?? null);

        return [
            'slug' => $data['slug'],
            'title' => $data['title'],
            'filter_label' => $data['filter_label'],
            'summary' => $data['summary'],
            'image_path' => $data['image']['path'],
            'image_alt' => $data['image']['alt'],
            'body' => $isExternal ? [] : array_values($data['body']),
            'gallery' => MediaUrl::stored($data['gallery'] ?? []),
            'external_url' => $data['external_url'] ?? null,
            'show_on_home' => $data['show_on_home'] ?? false,
            'is_pinned' => $data['is_pinned'] ?? false,
            'seo_title' => $data['seo_title'] ?? null,
            'seo_description' => $data['seo_description'] ?? null,
            'seo_image_path' => $data['seo_image']['path'] ?? null,
            'seo_image_alt' => $data['seo_image']['alt'] ?? null,
            'published' => $data['published'] ?? false,
        ];
    }
}
