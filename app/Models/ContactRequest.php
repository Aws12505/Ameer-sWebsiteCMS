<?php

namespace App\Models;

use App\Concerns\TracksEditor;
use App\Enums\ContactRequestStatus;
use Carbon\CarbonImmutable;
use Database\Factories\ContactRequestFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * A request sent from the website's contact form.
 *
 * @property int $id
 * @property string $name
 * @property string $phone
 * @property string $email
 * @property string|null $project_type
 * @property string|null $address
 * @property string $message
 * @property ContactRequestStatus $status
 * @property string|null $notes
 * @property string $fingerprint
 * @property int|null $updated_by
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 */
#[Fillable(['name', 'phone', 'email', 'project_type', 'address', 'message', 'status', 'notes'])]
#[Hidden(['fingerprint'])]
class ContactRequest extends Model
{
    /** @use HasFactory<ContactRequestFactory> */
    use HasFactory, TracksEditor;

    /**
     * Boot the model.
     */
    protected static function booted(): void
    {
        static::saving(function (ContactRequest $request): void {
            $request->fingerprint = static::fingerprintFor($request->email, $request->message);
        });
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => ContactRequestStatus::class,
        ];
    }

    /**
     * A hash that identifies a repeated request (same email and message).
     */
    public static function fingerprintFor(string $email, string $message): string
    {
        return hash('sha256', Str::lower(trim($email)).'|'.Str::of($message)->squish()->lower());
    }

    /**
     * The phone number as a tap-to-call link.
     */
    public function phoneHref(): string
    {
        return 'tel:'.preg_replace('/[^\d+]/', '', $this->phone);
    }
}
