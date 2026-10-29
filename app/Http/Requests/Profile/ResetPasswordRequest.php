<?php

namespace App\Http\Requests\Profile;

use App\Http\Requests\BaseApiRequest;

class ResetPasswordRequest extends BaseApiRequest
{
    public function rules(): array
    {
        return [
            'current_password' => ['required'],
            'new_password' => ['required', 'min:8'],
            'new_password_confirmation' => ['required'],
        ];
    }

    public function messages(): array
    {
        return [
            'current_password.required' => 'Kata sandi saat ini harus diisi.',
            'new_password.required' => 'Kata sandi baru harus diisi.',
            'new_password.min' => 'Kata sandi baru minimal 8 karakter.',
            'new_password_confirmation.required' => 'Konfirmasi kata sandi baru harus diisi.',
        ];
    }
}
