<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SiteSettingsRequest;
use App\Models\Setting;
use App\Support\FaviconGenerator;
use App\Support\MediaUrl;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SiteSettingsController extends Controller
{
    /**
     * Show the site settings form.
     */
    public function edit(Request $request): Response
    {
        $setting = Setting::for(Setting::Site);
        $site = $setting->value;

        return Inertia::render('admin/site-settings', [
            'settings' => [
                ...$site,
                'favicon' => MediaUrl::field($site['favicon']['path'] ?? null),
                'defaultShareImage' => MediaUrl::field($site['defaultShareImage']['path'] ?? null, $site['defaultShareImage']['alt'] ?? null),
                'notificationEmails' => $request->user()->isAdmin() ? ($site['notificationEmails'] ?? []) : null,
            ],
            'lastEdited' => $setting->lastEdited(),
        ]);
    }

    /**
     * Save the site settings and generate the favicon sizes.
     */
    public function update(SiteSettingsRequest $request, FaviconGenerator $favicons): RedirectResponse
    {
        $setting = Setting::for(Setting::Site);
        $current = $setting->value;
        $data = $request->validated();

        $faviconPath = $data['favicon']['path'];
        $sizes = ($current['favicon']['path'] ?? null) === $faviconPath && filled($current['favicon']['sizes'] ?? null)
            ? $current['favicon']['sizes']
            : $favicons->generate($faviconPath);

        $setting->update(['value' => [
            'name' => $data['name'],
            'description' => $data['description'],
            'favicon' => ['path' => $faviconPath, 'sizes' => $sizes],
            'serviceArea' => $data['serviceArea'],
            'phone' => ['display' => $data['phone']['display']],
            'email' => $data['email'],
            'cta' => ['label' => $data['cta']['label']],
            'footerBlurb' => $data['footerBlurb'],
            'ctaBand' => ['heading' => $data['ctaBand']['heading'], 'text' => $data['ctaBand']['text']],
            'notificationEmails' => $request->user()->isAdmin()
                ? array_values($data['notificationEmails'] ?? [])
                : ($current['notificationEmails'] ?? []),
            'defaultShareImage' => isset($data['defaultShareImage'])
                ? ['path' => $data['defaultShareImage']['path'], 'alt' => $data['defaultShareImage']['alt']]
                : null,
        ]]);

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Site settings saved.']);

        return to_route('admin.site-settings.edit');
    }
}
