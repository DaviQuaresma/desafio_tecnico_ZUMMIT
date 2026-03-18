<?php

namespace App\Http\Requests;

use App\Enums\TravelOrderStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class ListTravelOrdersRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => ['nullable', 'string', Rule::in(TravelOrderStatus::values())],
            'destination' => ['nullable', 'string', 'max:255'],
            'start_date' => ['nullable', 'date', 'date_format:Y-m-d'],
            'end_date' => ['nullable', 'date', 'date_format:Y-m-d', 'after_or_equal:start_date'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ];
    }

    public function messages(): array
    {
        return [
            'status.in' => 'O status deve ser: requested, approved ou canceled.',
            'start_date.date_format' => 'A data inicial deve estar no formato YYYY-MM-DD.',
            'end_date.date_format' => 'A data final deve estar no formato YYYY-MM-DD.',
            'end_date.after_or_equal' => 'A data final deve ser igual ou posterior à data inicial.',
            'per_page.min' => 'O número de itens por página deve ser no mínimo 1.',
            'per_page.max' => 'O número de itens por página deve ser no máximo 100.',
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(
            response()->json([
                'success' => false,
                'message' => 'Erro de validação.',
                'errors' => $validator->errors(),
            ], 422)
        );
    }
}
