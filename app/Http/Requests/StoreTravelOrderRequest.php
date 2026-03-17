<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreTravelOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'destination' => ['required', 'string', 'max:255'],
            'departure_date' => ['required', 'date', 'date_format:Y-m-d', 'after_or_equal:today'],
            'return_date' => ['required', 'date', 'date_format:Y-m-d', 'after:departure_date'],
        ];
    }

    public function messages(): array
    {
        return [
            'destination.required' => 'O destino é obrigatório.',
            'destination.max' => 'O destino não pode ter mais que 255 caracteres.',
            'departure_date.required' => 'A data de ida é obrigatória.',
            'departure_date.date' => 'A data de ida deve ser uma data válida.',
            'departure_date.date_format' => 'A data de ida deve estar no formato YYYY-MM-DD.',
            'departure_date.after_or_equal' => 'A data de ida deve ser hoje ou uma data futura.',
            'return_date.required' => 'A data de volta é obrigatória.',
            'return_date.date' => 'A data de volta deve ser uma data válida.',
            'return_date.date_format' => 'A data de volta deve estar no formato YYYY-MM-DD.',
            'return_date.after' => 'A data de volta deve ser posterior à data de ida.',
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
