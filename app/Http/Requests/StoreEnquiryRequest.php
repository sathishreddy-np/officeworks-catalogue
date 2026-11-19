<?php

// PHP start tag.

// Form requests live in this folder. Laravel runs one before the controller action.

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
// FormRequest gives us rules(), authorize(), and the submitted input.
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

// This class checks the enquire form. The controller only runs when these rules pass.
class StoreEnquiryRequest extends FormRequest
{
    // This runs before the rules, so we tidy the text the customer typed.
    protected function prepareForValidation(): void
    {
        $this->merge([
            // trim() removes spaces at the start and end.
            'name' => $this->trimmed('name'),
            // Email is stored in lowercase so Ava@Example.com and ava@example.com match.
            'email' => is_string($this->input('email')) ? Str::lower(trim($this->input('email'))) : $this->input('email'),
            'phone' => $this->trimmed('phone'),
            'message' => $this->trimmed('message'),
        ]);
    }

    // true means any visitor may send an enquiry. They do not need an account.
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Each key is a form field. Each value is the list of checks for that field.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // required means the field cannot be blank. max is the character limit.
            'name' => ['required', 'string', 'max:120'],
            // email checks that the text looks like an email address.
            'email' => ['required', 'string', 'email', 'max:255'],
            // The regex allows digits and + ( ) . - and spaces, which covers an Australian mobile number.
            'phone' => ['required', 'string', 'max:30', 'regex:/^[0-9+().\\-\\s]+$/'],
            'message' => ['required', 'string', 'max:2000'],
        ];
    }

    /**
     * The sentence the customer sees when a rule fails.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Enter your name.',
            'email.required' => 'Enter your email address.',
            'email.email' => 'Enter a valid email address.',
            'phone.required' => 'Enter your phone number.',
            'phone.regex' => 'Enter a phone number using digits and the characters + ( ) . -.',
            'message.required' => 'Tell us what you would like to know.',
        ];
    }

    // Read one input and trim it when it is text. Leave other types unchanged.
    private function trimmed(string $key): mixed
    {
        $value = $this->input($key);

        return is_string($value) ? trim($value) : $value;
    }
}
