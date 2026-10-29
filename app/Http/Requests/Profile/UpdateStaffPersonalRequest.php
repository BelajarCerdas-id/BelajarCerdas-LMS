<?php

namespace App\Http\Requests\Profile;

use App\Http\Requests\BaseApiRequest;

class UpdateStaffPersonalRequest extends BaseApiRequest
{
    public function rules(): array
    {
        return [
            'nama_lengkap' => ['required', 'string'],
            'no_hp' => [
                'required',
                'regex:/^08\d{8,11}$/',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'nama_lengkap.required' => 'Nama lengkap harus diisi.',
            'no_hp.required' => 'Nomor HP harus diisi.',
            'no_hp.regex' => 'Nomor HP tidak valid.',
        ];
    }
}
