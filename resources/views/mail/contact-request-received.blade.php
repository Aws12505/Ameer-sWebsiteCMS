<x-mail::message>
# New request from the website

Received {{ $request->created_at->timezone(config('app.timezone'))->format('l, F j, Y \a\t g:i A') }}.

<x-mail::table>
| | |
| :--- | :--- |
| **Name** | {{ $request->name }} |
| **Phone** | {{ $request->phone }} |
| **Email** | {{ $request->email }} |
| **Project type** | {{ $request->project_type ?: '—' }} |
| **Address or ZIP** | {{ $request->address ?: '—' }} |
</x-mail::table>

**Project details**

{{ $request->message }}

<x-mail::button :url="$inboxUrl">
Open in the inbox
</x-mail::button>

Reply to this email to answer {{ $request->name }} directly.
</x-mail::message>
