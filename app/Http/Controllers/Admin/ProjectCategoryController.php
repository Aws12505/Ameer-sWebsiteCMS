<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ProjectCategoryRequest;
use App\Http\Requests\Admin\ReorderRequest;
use App\Models\ProjectCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProjectCategoryController extends Controller
{
    /**
     * List the project categories in the manager's order.
     */
    public function index(): Response
    {
        return Inertia::render('admin/project-categories/index', [
            'items' => ProjectCategory::query()->ordered()->get()->map(fn (ProjectCategory $category): array => [
                'id' => $category->id,
                'label' => $category->label,
                'slug' => $category->slug,
                'published' => $category->published,
                'projectsCount' => $category->projectsUsingIt()->count(),
            ]),
        ]);
    }

    /**
     * Show the form for a new project category.
     */
    public function create(): Response
    {
        return Inertia::render('admin/project-categories/form', [
            'item' => null,
            'defaults' => ['slug' => '', 'label' => '', 'published' => true],
        ]);
    }

    /**
     * Save a new project category.
     */
    public function store(ProjectCategoryRequest $request): RedirectResponse
    {
        ProjectCategory::query()->create($request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Project category created.']);

        return to_route('admin.project-categories.index');
    }

    /**
     * Show the form for a project category.
     */
    public function edit(ProjectCategory $projectCategory): Response
    {
        $projectCategory->load('editor');

        return Inertia::render('admin/project-categories/form', [
            'item' => [
                'id' => $projectCategory->id,
                'keyLocked' => $projectCategory->isKeyLocked(),
                'lastEdited' => $projectCategory->lastEdited(),
            ],
            'defaults' => $projectCategory->only(['slug', 'label', 'published']),
        ]);
    }

    /**
     * Save changes to a project category.
     */
    public function update(ProjectCategoryRequest $request, ProjectCategory $projectCategory): RedirectResponse
    {
        $projectCategory->update($request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Project category saved.']);

        return to_route('admin.project-categories.index');
    }

    /**
     * Move a project category to the trash, unless projects still use it.
     */
    public function destroy(ProjectCategory $projectCategory): RedirectResponse
    {
        $inUse = $projectCategory->projectsUsingIt()->count();

        if ($inUse > 0) {
            Inertia::flash('toast', [
                'type' => 'error',
                'message' => trans_choice('{1} 1 project uses this category. Move it first.|[2,*] :count projects use this category. Move them first.', $inUse),
            ]);

            return back();
        }

        $projectCategory->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Project category moved to the trash.']);

        return to_route('admin.project-categories.index');
    }

    /**
     * Save the manager's drag-and-drop order.
     */
    public function reorder(ReorderRequest $request): RedirectResponse
    {
        ProjectCategory::reorder($request->orderedIds());

        return back();
    }

    /**
     * Switch a project category on or off from the list.
     */
    public function publish(Request $request, ProjectCategory $projectCategory): RedirectResponse
    {
        $projectCategory->update(['published' => $request->boolean('published')]);

        return back();
    }
}
