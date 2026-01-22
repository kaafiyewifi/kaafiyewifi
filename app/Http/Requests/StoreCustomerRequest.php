<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCustomerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
{
    return [
        'full_name'   => ['required','string','max:255'],
        'phone'       => ['required','string','unique:customers,phone'],
        'location_id' => ['required','exists:locations,id'],
        'is_active'   => ['nullable','boolean'],
        'status'      => ['nullable','in:active,inactive'],
    ];
}

}
