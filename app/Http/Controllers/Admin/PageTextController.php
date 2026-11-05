<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PageTextRequest;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

/**
 * The Services and Projects page texts (title, intro and SEO).
 */
class PageTextController extends Controller
{
    /**
     * The setting key and screen title for each page.
     *
     * @var array<string, array{key: string, title: string}>
     */
    protected const array Pages = [
        'services' => ['key' => Setting::ServicesPage, 'title' => 'Services page'],
        'projects' => ['key' => Setting::ProjectsPage, 'title' => 'Projects page'],
    ];

    /**
     * Show the page text form.
     */
    public function edit(string $page): Response
    {
        $setting = Setting::for(self::Pages[$page]['key']);

        return Inertia::render('admin/pages/text', [
            'pageKey' => $page,
            'title' => self::Pages[$page]['title'],
            'page' => $setting->value,
            'lastEdited' => $setting->lastEdited(),
        ]);
    }

    /**
     * Save the page text.
     */
    public function update(PageTextRequest $request, string $page): RedirectResponse
    {
        $value = $request->validated();
        $value['seo'] = ($value['seo'] ?? []) + ['title' => null, 'description' => null];

        Setting::for(self::Pages[$page]['key'])->update(['value' => $value]);

        Inertia::flash('toast', ['type' => 'success', 'message' => self::Pages[$page]['title'].' saved.']);

        return to_route('admin.pages.text.edit', $page);
    }
}
