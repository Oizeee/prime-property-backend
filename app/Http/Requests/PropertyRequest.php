<?php

namespace App\Http\Requests;

use App\Models\Property;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Shared validation rules for property create & update endpoints.
 */
class PropertyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'nama_property' => ['required', 'string', 'min:3', 'max:100'],
            'group' => ['nullable', 'string', 'max:100'],
            'lebar' => ['required', 'numeric', 'gt:0'],
            'panjang' => ['required', 'numeric', 'gt:0'],
            'hadap' => ['required', 'array', 'min:1'],
            'hadap.*' => ['required', 'string', Rule::in(Property::HADAP_OPTIONS)],
            'tipe' => ['required', 'string', Rule::in(Property::TIPE_OPTIONS)],
            'tingkat' => ['required', 'numeric', 'between:1,10'],
            'price' => ['required', 'integer', 'gt:0'],
            'carport' => ['sometimes', 'boolean'],
            'status' => ['sometimes', 'string', Rule::in(Property::STATUS_OPTIONS)],
            'siap' => ['required', 'string', Rule::in(Property::SIAP_OPTIONS)],
            'maps_link' => [
                'nullable',
                'url',
                'regex:/(googleusercontent\.com|maps\.google\.com)/i',
            ],
            'kawasan' => ['required', 'array', 'min:1'],
            'kawasan.*' => ['required', 'string', 'max:100'],
            'unit' => ['nullable', 'string', 'max:100'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'nama_property.min' => 'Nama property minimal 3 karakter.',
            'nama_property.max' => 'Nama property maksimal 100 karakter.',
            'lebar.gt' => 'Lebar harus lebih besar dari 0.',
            'panjang.gt' => 'Panjang harus lebih besar dari 0.',
            'price.integer' => 'Harga harus berupa angka bulat (Rupiah).',
            'price.gt' => 'Harga harus lebih besar dari 0.',
            'tingkat.between' => 'Tingkat harus antara 1 dan 10.',
            'maps_link.regex' => 'Link maps harus dari domain googleusercontent.com atau maps.google.com.',
            'hadap.*.in' => 'Hadap harus salah satu dari: Utara, Selatan, Timur, Barat.',
            'tipe.in' => 'Tipe harus Ruko atau Villa.',
            'status.in' => 'Status tidak valid.',
            'siap.in' => 'Status kesiapan tidak valid.',
        ];
    }
}
