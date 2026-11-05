<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ContactRequestStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateContactRequestRequest;
use App\Models\ContactRequest;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use RuntimeException;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ContactRequestController extends Controller
{
    /**
     * The inbox, newest first, with search and a status filter.
     */
    public function index(Request $request): Response
    {
        $requests = $this->filtered($request)
            ->latest()
            ->latest('id')
            ->paginate(25)
            ->withQueryString()
            ->through(fn (ContactRequest $contactRequest): array => [
                'id' => $contactRequest->id,
                'createdAt' => $contactRequest->created_at->toIso8601String(),
                'name' => $contactRequest->name,
                'phone' => $contactRequest->phone,
                'email' => $contactRequest->email,
                'projectType' => $contactRequest->project_type,
                'status' => $contactRequest->status->value,
            ]);

        return Inertia::render('admin/contact-requests/index', [
            'requests' => $requests,
            'filters' => [
                'search' => $request->string('search')->toString(),
                'status' => $request->string('status')->toString(),
            ],
            'statuses' => $this->statusOptions(),
        ]);
    }

    /**
     * One request with all submitted fields.
     */
    public function show(ContactRequest $contactRequest): Response
    {
        $contactRequest->load('editor');

        return Inertia::render('admin/contact-requests/show', [
            'request' => [
                'id' => $contactRequest->id,
                'createdAt' => $contactRequest->created_at->toIso8601String(),
                'name' => $contactRequest->name,
                'phone' => $contactRequest->phone,
                'phoneHref' => $contactRequest->phoneHref(),
                'email' => $contactRequest->email,
                'projectType' => $contactRequest->project_type,
                'address' => $contactRequest->address,
                'message' => $contactRequest->message,
                'status' => $contactRequest->status->value,
                'notes' => (string) $contactRequest->notes,
                'lastEdited' => $contactRequest->lastEdited(),
            ],
            'statuses' => $this->statusOptions(),
        ]);
    }

    /**
     * Change the status or the internal notes.
     */
    public function update(UpdateContactRequestRequest $request, ContactRequest $contactRequest): RedirectResponse
    {
        $contactRequest->update($request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Request updated.']);

        return back();
    }

    /**
     * Delete a request for good (Admin only).
     */
    public function destroy(ContactRequest $contactRequest): RedirectResponse
    {
        $contactRequest->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Request deleted.']);

        return to_route('admin.contact-requests.index');
    }

    /**
     * Download the (filtered) requests as a CSV file.
     */
    public function export(Request $request): StreamedResponse
    {
        $query = $this->filtered($request)->latest()->latest('id');

        return response()->streamDownload(function () use ($query): void {
            $output = fopen('php://output', 'w');

            if ($output === false) {
                throw new RuntimeException('Could not open the CSV output stream.');
            }

            fputcsv($output, ['Date', 'Name', 'Phone', 'Email', 'Project type', 'Address or ZIP', 'Project details', 'Status', 'Internal notes']);

            $query->lazy()->each(function (ContactRequest $contactRequest) use ($output): void {
                fputcsv($output, array_map($this->safeCsvValue(...), [
                    $contactRequest->created_at->format('Y-m-d H:i'),
                    $contactRequest->name,
                    $contactRequest->phone,
                    $contactRequest->email,
                    (string) $contactRequest->project_type,
                    (string) $contactRequest->address,
                    $contactRequest->message,
                    $contactRequest->status->label(),
                    (string) $contactRequest->notes,
                ]));
            });

            fclose($output);
        }, 'contact-requests-'.now()->format('Y-m-d').'.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /**
     * The requests matching the search and status filter.
     *
     * @return Builder<ContactRequest>
     */
    protected function filtered(Request $request): Builder
    {
        $search = trim($request->string('search')->toString());
        $status = ContactRequestStatus::tryFrom($request->string('status')->toString());

        return ContactRequest::query()
            ->when($search !== '', fn (Builder $query) => $query->where(fn (Builder $query) => $query
                ->where('name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")
                ->orWhere('phone', 'like', "%{$search}%")))
            ->when($status, fn (Builder $query) => $query->where('status', $status));
    }

    /**
     * Stop spreadsheet apps from running visitor text as a formula.
     */
    protected function safeCsvValue(string $value): string
    {
        return preg_match('/^[=+\-@\t\r]/', $value) ? "'".$value : $value;
    }

    /**
     * @return list<array{value: string, label: string}>
     */
    protected function statusOptions(): array
    {
        return array_map(
            fn (ContactRequestStatus $status): array => ['value' => $status->value, 'label' => $status->label()],
            ContactRequestStatus::cases(),
        );
    }
}
