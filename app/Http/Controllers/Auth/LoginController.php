<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class LoginController extends Controller
{
    public function create(): View
    {
        return view('auth.login');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt([
            'email' => $data['email'],
            'password' => $data['password'],
            'is_active' => true,
        ])) {
            $pending = User::query()
                ->where('email', $data['email'])
                ->where('is_active', false)
                ->first();

            if ($pending && Hash::check($data['password'], $pending->getAuthPassword())) {
                throw ValidationException::withMessages([
                    'email' => 'Akun kamu sudah terdaftar dan sedang menunggu persetujuan pemilik cabang. Hubungi owner untuk mengaktifkannya.',
                ]);
            }

            throw ValidationException::withMessages([
                'email' => 'Email atau password salah, atau akun sedang tidak aktif.',
            ]);
        }

        $request->session()->regenerate();

        return redirect()->intended(route($request->user()->homeRouteName()));
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
