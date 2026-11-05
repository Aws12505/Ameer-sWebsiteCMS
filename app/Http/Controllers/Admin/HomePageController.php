<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\HomePageRequest;
use App\Models\Setting;
use App\Support\MediaUrl;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Arr;
use Inertia\Inertia;
use Inertia\Response;

class HomePageController extends Controller
{
    /**
     * Show the home page text form.
     */
    public function edit(): Response
    {
        $setting = Setting::for(Setting::HomePage);
        $page = $setting->value;

        $page['hero']['headline'] = array_pad($page['hero']['headline'] ?? [], 2, '');
        $page['about']['image'] = MediaUrl::field($page['about']['image']['path'] ?? null, $page['about']['image']['alt'] ?? null);
        $page['seo']['image'] = MediaUrl::field($page['seo']['image']['path'] ?? null, $page['seo']['image']['alt'] ?? null);

        return Inertia::render('admin/pages/home', [
            'page' => $page,
            'lastEdited' => $setting->lastEdited(),
        ]);
    }

    /**
     * Save the home page text.
     */
    public function update(HomePageRequest $request): RedirectResponse
    {
        $page = $request->validated();

        $page['hero']['headline'] = array_values(array_filter($page['hero']['headline'], 'filled'));
        $page['about']['image'] = Arr::only($page['about']['image'], ['path', 'alt']);
        $page['seo']['image'] = isset($page['seo']['image']) ? Arr::only($page['seo']['image'], ['path', 'alt']) : null;
        $page['seo'] += ['title' => null, 'description' => null];

        Setting::for(Setting::HomePage)->update(['value' => $page]);

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Home page saved.']);

        return to_route('admin.pages.home.edit');
    }
}
