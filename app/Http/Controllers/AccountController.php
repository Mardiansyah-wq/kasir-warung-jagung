<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class AccountController extends Controller
{
    public function edit(Request $request): View
    {
        return view('account.index', [
            'user' => $request->user(),
        ]);
    }

    public function updateProfile(Request $request): RedirectResponse
    {
        $user = $request->user();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user->id),
            ],
            'phone' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string', 'max:500'],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'remove_photo' => ['nullable', 'boolean'],
        ], [
            'name.required' => 'Nama lengkap wajib diisi.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email ini sudah dipakai akun lain.',
            'photo.image' => 'Berkas harus berupa gambar.',
            'photo.mimes' => 'Format foto harus JPG, PNG, atau WebP.',
            'photo.max' => 'Ukuran foto maksimal 2 MB.',
        ]);

        if ($request->boolean('remove_photo')) {
            if ($user->photo_path) {
                Storage::disk('local')->delete($user->photo_path);
            }
            $data['photo_path'] = null;
        }

        if ($request->hasFile('photo')) {
            if ($user->photo_path) {
                Storage::disk('local')->delete($user->photo_path);
            }

            $path = $request->file('photo')->store('avatars', 'local');
            $data['photo_path'] = $path;
        }

        unset($data['photo'], $data['remove_photo']);

        $user->update($data);

        return redirect()
            ->route('account.index')
            ->with('success', 'Profil berhasil diperbarui.');
    }

    public function avatar(Request $request, User $user): BinaryFileResponse
    {
        $viewer = $request->user();

        abort_unless($viewer->id === $user->id || $viewer->isOwner(), 403);
        abort_if(! $user->photo_path, 404);

        $disk = Storage::disk('local');
        abort_unless($disk->exists($user->photo_path), 404);

        $ext = strtolower(pathinfo($user->photo_path, PATHINFO_EXTENSION));
        $mime = match ($ext) {
            'png' => 'image/png',
            'webp' => 'image/webp',
            default => 'image/jpeg',
        };

        $response = response()->file($disk->path($user->photo_path), [
            'Content-Type' => $mime,
        ]);

        $response->setPrivate();
        $response->setMaxAge(86400);

        return $response;
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'current_password.required' => 'Password saat ini wajib diisi.',
            'current_password.current_password' => 'Password saat ini tidak cocok.',
            'password.required' => 'Password baru wajib diisi.',
            'password.min' => 'Password baru minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak sama.',
        ]);

        $request->user()->update([
            'password_hash' => $data['password'],
        ]);

        $request->session()->put('auth_password_hash', (string) $request->user()->getAuthPassword());

        return redirect()
            ->route('account.index')
            ->with('success', 'Password berhasil diperbarui.');
    }
}
