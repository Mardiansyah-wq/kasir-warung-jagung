<?php

namespace App\Http\Requests;

use App\Models\Menu;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMenuVariantRequest extends FormRequest
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
        $menu = $this->route('menu');
        $menuId = $menu instanceof Menu ? $menu->id : null;

        return [
            'variant_name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('menu_variants', 'variant_name')
                    ->where(fn ($query) => $query->where('menu_id', $menuId)),
            ],
            'selling_price' => ['required', 'numeric', 'min:0', 'max:9999999999'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'variant_name.required' => 'Nama varian wajib diisi.',
            'variant_name.max' => 'Nama varian maksimal 100 karakter.',
            'variant_name.unique' => 'Varian dengan nama itu sudah ada di menu ini.',
            'selling_price.required' => 'Harga wajib diisi.',
            'selling_price.numeric' => 'Harga harus berupa angka.',
            'selling_price.min' => 'Harga tidak boleh minus.',
            'selling_price.max' => 'Harga terlalu besar.',
        ];
    }
}
