<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ContactPageRequest;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class ContactPageController extends Controller
{
    /**
     * Show the contact page text form.
     */
    public function edit(): Response
    {
        $setting = Setting::for(Setting::ContactPage);

        return Inertia::render('admin/pages/contact', [
            'page' => $setting->value,
            'lastEdited' => $setting->lastEdited(),
        ]);
    }

    /**
     * Save the contact page text.
     */
    public function update(ContactPageRequest $request): RedirectResponse
    {
        $value = $request->validated();
        $value['seo'] = ($value['seo'] ?? []) + ['title' => null, 'description' => null];

        Setting::for(Setting::ContactPage)->update(['value' => $value]);

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Contact page saved.']);

        return to_route('admin.pages.contact.edit');
    }
}
