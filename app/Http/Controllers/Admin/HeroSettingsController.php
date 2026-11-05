<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\HeroSettingsRequest;
use App\Models\Setting;
use App\Support\MediaUrl;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class HeroSettingsController extends Controller
{
    /**
     * Show the hero background and Reviews switch form.
     */
    public function edit(): Response
    {
        $setting = Setting::for(Setting::Hero);
        $hero = $setting->value;
        $video = $hero['heroVideo'] ?? null;

        return Inertia::render('admin/hero-settings', [
            'settings' => [
                'showReviews' => (bool) ($hero['showReviews'] ?? false),
                'heroMode' => $hero['heroMode'],
                'heroSlides' => MediaUrl::fields($hero['heroSlides'] ?? []),
                'heroVideo' => $video === null ? null : [
                    'video' => MediaUrl::field($video['path'] ?? null),
                    'poster' => MediaUrl::field($video['poster'] ?? null),
                ],
            ],
            'lastEdited' => $setting->lastEdited(),
        ]);
    }

    /**
     * Save the hero background and Reviews switch.
     */
    public function update(HeroSettingsRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $video = $data['heroVideo'] ?? null;

        Setting::for(Setting::Hero)->update(['value' => [
            'showReviews' => (bool) $data['showReviews'],
            'heroMode' => $data['heroMode'],
            'heroSlides' => MediaUrl::stored($data['heroSlides'] ?? []),
            'heroVideo' => $video === null ? null : ['path' => $video['path'], 'poster' => $video['poster']],
        ]]);

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Hero and reviews settings saved.']);

        return to_route('admin.hero-settings.edit');
    }
}
