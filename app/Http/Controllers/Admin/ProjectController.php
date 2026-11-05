<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ProjectRequest;
use App\Http\Requests\Admin\ReorderRequest;
use App\Models\Project;
use App\Models\ProjectCategory;
use App\Support\MediaUrl;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class ProjectController extends Controller
{
    /**
     * List the projects in the manager's order.
     */
    public function index(): Response
    {
        $projects = Project::query()->ordered()->with('mainCategory:id,label')->get();

        return Inertia::render('admin/projects/index', [
            'items' => $projects->map(fn (Project $project): array => [
                'id' => $project->id,
                'title' => $project->title,
                'slug' => $project->slug,
                'location' => $project->location,
                'image' => MediaUrl::for($project->cover_path),
                'published' => $project->published,
                'featured' => $project->featured,
                'mainCategory' => $project->mainCategory?->label,
                'photosCount' => 1 + count($project->gallery ?? []),
            ]),
            'maxFeatured' => Project::MaxFeatured,
        ]);
    }

    /**
     * Show the form for a new project.
     */
    public function create(): Response
    {
        return Inertia::render('admin/projects/form', [
            'item' => null,
            'defaults' => [
                'slug' => '', 'title' => '', 'location' => '', 'description' => '', 'main_category_id' => null,
                'extra_category_ids' => [], 'featured' => false, 'cover' => null, 'gallery' => [], 'published' => false,
            ],
            'categories' => $this->categoryOptions(),
        ]);
    }

    /**
     * Save a new project.
     */
    public function store(ProjectRequest $request): RedirectResponse
    {
        $project = DB::transaction(function () use ($request): Project {
            $project = Project::query()->create($this->attributes($request));
            $project->extraCategories()->sync($this->extraCategoryIds($request));

            return $project;
        });

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Project created.']);

        return to_route('admin.projects.edit', $project);
    }

    /**
     * Show the form for a project.
     */
    public function edit(Project $project): Response
    {
        $project->load('editor', 'extraCategories:id');

        return Inertia::render('admin/projects/form', [
            'item' => [
                'id' => $project->id,
                'keyLocked' => $project->isKeyLocked(),
                'lastEdited' => $project->lastEdited(),
            ],
            'defaults' => [
                'slug' => $project->slug,
                'title' => $project->title,
                'location' => $project->location,
                'description' => $project->description,
                'main_category_id' => $project->main_category_id,
                'extra_category_ids' => $project->extraCategories->pluck('id'),
                'featured' => $project->featured,
                'cover' => MediaUrl::field($project->cover_path, $project->cover_alt),
                'gallery' => MediaUrl::fields($project->gallery),
                'published' => $project->published,
            ],
            'categories' => $this->categoryOptions(),
        ]);
    }

    /**
     * Save changes to a project.
     */
    public function update(ProjectRequest $request, Project $project): RedirectResponse
    {
        DB::transaction(function () use ($request, $project): void {
            $project->extraCategories()->sync($this->extraCategoryIds($request));
            $project->update($this->attributes($request));
        });

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Project saved.']);

        return to_route('admin.projects.edit', $project);
    }

    /**
     * Move a project to the trash.
     */
    public function destroy(Project $project): RedirectResponse
    {
        $project->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Project moved to the trash.']);

        return to_route('admin.projects.index');
    }

    /**
     * Save the manager's drag-and-drop order.
     */
    public function reorder(ReorderRequest $request): RedirectResponse
    {
        Project::reorder($request->orderedIds());

        return back();
    }

    /**
     * Switch a project on or off from the list.
     */
    public function publish(Request $request, Project $project): RedirectResponse
    {
        $project->update(['published' => $request->boolean('published')]);

        return back();
    }

    /**
     * The project categories to choose from.
     *
     * @return array<int, array{id: int, label: string}>
     */
    protected function categoryOptions(): array
    {
        return ProjectCategory::query()->ordered()->get(['id', 'label'])
            ->map(fn (ProjectCategory $category): array => ['id' => $category->id, 'label' => $category->label])
            ->all();
    }

    /**
     * The extra categories, without the main one.
     *
     * @return list<int>
     */
    protected function extraCategoryIds(ProjectRequest $request): array
    {
        $mainId = $request->integer('main_category_id');

        return array_values(array_filter(
            array_map('intval', $request->validated('extra_category_ids', [])),
            fn (int $id): bool => $id !== $mainId,
        ));
    }

    /**
     * The validated form mapped to database columns.
     *
     * @return array<string, mixed>
     */
    protected function attributes(ProjectRequest $request): array
    {
        $data = $request->validated();

        return [
            'slug' => $data['slug'],
            'title' => $data['title'],
            'location' => $data['location'],
            'description' => $data['description'],
            'main_category_id' => $data['main_category_id'],
            'featured' => $data['featured'] ?? false,
            'cover_path' => $data['cover']['path'],
            'cover_alt' => $data['cover']['alt'],
            'gallery' => MediaUrl::stored($data['gallery'] ?? []),
            'published' => $data['published'] ?? false,
        ];
    }
}
