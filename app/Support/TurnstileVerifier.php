<?php

namespace App\Support;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Checks the contact form's human check with Cloudflare Turnstile.
 */
class TurnstileVerifier
{
    public const string VerifyUrl = 'https://challenges.cloudflare.com/turnstile/v0/siteverify';

    /**
     * Whether Turnstile accepts the token. Fails closed when not configured.
     */
    public function passes(string $token, ?string $visitorIp = null): bool
    {
        $secret = config('services.turnstile.secret_key');

        if (blank($secret)) {
            Log::error('Turnstile secret key is missing; contact requests are being rejected.');

            return false;
        }

        try {
            $response = Http::asForm()->timeout(10)->post(self::VerifyUrl, array_filter([
                'secret' => $secret,
                'response' => $token,
                'remoteip' => $visitorIp,
            ]));
        } catch (Throwable $exception) {
            Log::warning('Turnstile verification could not be reached.', ['error' => $exception->getMessage()]);

            return false;
        }

        return $response->successful() && $response->json('success') === true;
    }
}
