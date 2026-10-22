<?php

namespace App\Http\Requests;

use App\Models\Menu;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMenuRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isOwner() ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:150'],
            'category' => ['required', Rule::in(Menu::CATEGORIES)],
            'description' => ['nullable', 'string', 'max:2000'],
            'is_active' => ['sometimes', 'boolean'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048', 'dimensions:max_width=6000,max_height=6000'],
            'variant_name' => ['required', 'string', 'max:100'],
            'selling_price' => ['required', 'numeric', 'min:0', 'max:9999999999'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Nama menu wajib diisi.',
            'name.max' => 'Nama menu maksimal 150 karakter.',
            'category.required' => 'Kategori wajib dipilih.',
            'category.in' => 'Kategori tidak valid.',
            'description.max' => 'Deskripsi maksimal 2.000 karakter.',
            'image.image' => 'File harus berupa gambar.',
            'image.mimes' => 'Format gambar harus JPG, PNG, atau WebP.',
            'image.max' => 'Ukuran gambar maksimal 2MB.',
            'image.dimensions' => 'Resolusi gambar maksimal 6000 x 6000 piksel.',
            'variant_name.required' => 'Nama varian pertama wajib diisi.',
            'variant_name.max' => 'Nama varian maksimal 100 karakter.',
            'selling_price.required' => 'Harga wajib diisi.',
            'selling_price.numeric' => 'Harga harus berupa angka.',
            'selling_price.min' => 'Harga tidak boleh minus.',
            'selling_price.max' => 'Harga terlalu besar.',
        ];
    }
}
