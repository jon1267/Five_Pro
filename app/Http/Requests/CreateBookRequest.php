<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateBookRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        if ($this->method() === 'POST') {
            return [
                'title' => 'required|string|max:255',
                'description' => 'required|string',
                'year' => 'required|date',
                'edition' => 'nullable|string|max:255',
                'format' => 'nullable|string|max:255',
                'pages' => 'nullable|string',
                'country' => 'nullable|string|max:255',
                'isbn' => 'nullable|string|max:255',

                'authors' => 'required|array',
                'authors.*' => 'required|integer|exists:authors,id',
                'genres' => 'required|array',
                'genres.*' => 'required|integer|exists:genres,id',
                'publishers' => 'required|array',
                'publishers.*' => 'required|integer|exists:publishers,id',
            ];

        } elseif ($this->method() === 'PUT' || $this->method() === 'PATCH') {
            return [
                'title' => 'sometimes|string|max:255',
                'description' => 'sometimes|required|string',
                'year' => 'sometimes|required|date',
                'edition' => 'sometimes|nullable|string|max:255',
                'format' => 'sometimes|nullable|string|max:255',
                'pages' => 'sometimes|nullable|string',
                'country' => 'sometimes|nullable|string|max:255',
                'isbn' => 'sometimes|nullable|string|max:255',

                'authors' => 'sometimes|array',
                'authors.*' => 'sometimes|integer|exists:authors,id',
                'genres' => 'sometimes|array',
                'genres.*' => 'sometimes|integer|exists:genres,id',
                'publishers' => 'sometimes|array',
                'publishers.*' => 'sometimes|integer|exists:publishers,id',
            ];
        }

    }
}
