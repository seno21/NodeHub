<?php

namespace App\Http\Requests;

use App\Models\Computer;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreComputerRequest extends FormRequest
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
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'ip_address' => ['required', 'ip'],
            'vnc_port' => ['required', 'integer', 'min:1', 'max:65535'],
            'os_type' => ['required', Rule::in(Computer::OS_TYPES)],
            'location' => ['nullable', 'string', 'max:255'],
            'tags' => ['nullable', 'string', 'max:255'],
            'tag_ids' => ['required_without:tags', 'array', 'min:1'],
            'tag_ids.*' => ['integer', 'exists:tags,id'],
            'description' => ['nullable', 'string', 'max:1000'],
            'vnc_password' => ['nullable', 'string', 'max:255'],
            'ssh_port' => ['nullable', 'integer', 'min:1', 'max:65535'],
            'ssh_user' => ['nullable', 'string', 'max:100'],
            'ssh_password' => ['nullable', 'string', 'max:255'],
            'refresh_command' => ['nullable', 'string', 'max:500'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'tag_ids.required' => __('Minimal harus memilih 1 tag untuk perangkat.'),
            'tag_ids.required_without' => __('Minimal harus memilih 1 tag untuk perangkat.'),
            'tag_ids.min' => __('Minimal harus memilih 1 tag untuk perangkat.'),
            'tag_ids.*.exists' => __('Tag yang dipilih tidak valid.'),
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'vnc_port' => $this->input('vnc_port') !== null && $this->input('vnc_port') !== ''
                ? (int) $this->input('vnc_port')
                : 5900,
            'ssh_port' => $this->input('ssh_port') !== null && $this->input('ssh_port') !== ''
                ? (int) $this->input('ssh_port')
                : 22,
        ]);
    }
}
