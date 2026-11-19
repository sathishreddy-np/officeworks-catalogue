{{-- Blade comment: this email is built from Laravel's markdown mail components. --}}
<x-mail::message>
{{-- # starts a heading in a markdown email. --}}
# New product enquiry

{{-- {{ }} prints a value and escapes it, so a customer's text cannot inject HTML. --}}
A customer asked about **{{ $enquiry->product_title }}** (product {{ $enquiry->product_id }}).

**Name:** {{ $enquiry->name }}

**Email:** {{ $enquiry->email }}

**Phone:** {{ $enquiry->phone }}

{{ $enquiry->message }}

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
