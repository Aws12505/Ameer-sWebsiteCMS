<?php

namespace App\Http\Controllers\Api;

use App\Enums\ContactRequestStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreContactRequest;
use App\Mail\ContactRequestReceived;
use App\Models\ContactRequest;
use App\Models\Setting;
use App\Support\TurnstileVerifier;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Mail;

class ContactController extends Controller
{
    /**
     * Save a contact form request and email the manager.
     */
    public function __invoke(StoreContactRequest $request, TurnstileVerifier $turnstile): JsonResponse
    {
        if (! $turnstile->passes($request->string('humanToken'), $request->ip())) {
            return $this->failure('We could not verify that you are human. Please try again or call us.');
        }

        $fields = $request->safe()->except('humanToken');

        $isRepeat = ContactRequest::query()
            ->where('fingerprint', ContactRequest::fingerprintFor($fields['email'], $fields['message']))
            ->where('created_at', '>=', now()->subHours(config('cms.contact.duplicate_window_hours')))
            ->exists();

        if ($isRepeat) {
            return $this->failure('We already received this request. We will be in touch soon.');
        }

        $contactRequest = ContactRequest::query()->create([
            'name' => $fields['name'],
            'phone' => $fields['phone'],
            'email' => $fields['email'],
            'project_type' => $fields['projectType'] ?? null,
            'address' => $fields['address'] ?? null,
            'message' => $fields['message'],
            'status' => ContactRequestStatus::New,
        ]);

        $recipients = Setting::valueOf(Setting::Site)['notificationEmails'] ?? [];

        if ($recipients !== []) {
            Mail::to($recipients)->queue(new ContactRequestReceived($contactRequest));
        }

        return response()->json(['ok' => true]);
    }

    /**
     * A failed request with a short message for the visitor.
     *
     * Sent with HTTP 200: the front end treats any other status as a network
     * failure and shows its generic message instead of this one.
     */
    protected function failure(string $message): JsonResponse
    {
        return response()->json(['ok' => false, 'error' => $message]);
    }
}
