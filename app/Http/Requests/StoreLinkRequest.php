<?php
// app/Http/Requests/StoreLinkRequest.php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreLinkRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'original_url' => [
                'required',
                'url',
                'max:2048',
                function ($attribute, $value, $fail) {
                    // Check if the URL is trying to shorten a ZapLink URL
                    if ($this->isZapLinkUrl($value)) {
                        $fail('You cannot shorten a URL that is already a ZapLink short URL.');
                    }
                    
                    // Check if the URL is pointing to itself (infinite loop prevention)
                    if ($this->isSelfReferencing($value)) {
                        $fail('You cannot create a link that points back to itself.');
                    }
                },
            ],
            'title' => 'nullable|string|max:255',
            'custom_code' => 'nullable|alpha_num|min:4|max:20|unique:links,short_code',
            'expires_at' => 'nullable|date|after:now',
        ];
    }

    /**
     * Check if the URL is a ZapLink short URL.
     */
    protected function isZapLinkUrl(string $url): bool
    {
        $parsedUrl = parse_url($url);
        $host = $parsedUrl['host'] ?? '';
        $path = $parsedUrl['path'] ?? '';
        
        // Get the app domain
        $appUrl = parse_url(config('app.url'));
        $appHost = $appUrl['host'] ?? '';
        $appPath = $appUrl['path'] ?? '';
        
        // Check if it's the same domain
        if ($host === $appHost || $host === 'localhost' || $host === '127.0.0.1') {
            // Clean the path
            $path = ltrim($path, '/');
            
            // Check if the path looks like a short code (4-8 alphanumeric characters)
            if (preg_match('/^[a-zA-Z0-9]{4,8}$/', $path)) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Check if the URL is self-referencing (pointing to itself).
     */
    protected function isSelfReferencing(string $url): bool
    {
        $parsedUrl = parse_url($url);
        $path = ltrim($parsedUrl['path'] ?? '', '/');
        
        // Check if the path is a short code that might exist
        if (preg_match('/^[a-zA-Z0-9]{4,8}$/', $path)) {
            // Check if this short code already exists in the database
            $exists = \App\Models\Link::where('short_code', $path)->exists();
            if ($exists) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'original_url.required' => 'Please enter a URL to shorten.',
            'original_url.url' => 'Please enter a valid URL.',
            'original_url.max' => 'URL is too long. Please shorten it further.',
            'custom_code.alpha_num' => 'Custom code can only contain letters and numbers.',
            'custom_code.min' => 'Custom code must be at least 4 characters.',
            'custom_code.max' => 'Custom code cannot exceed 20 characters.',
            'custom_code.unique' => 'This custom code is already taken.',
            'expires_at.after' => 'Expiration date must be in the future.',
        ];
    }
}