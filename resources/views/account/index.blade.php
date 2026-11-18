@extends('layouts.app')

@section('title', 'Akun Saya - Jagung Bakar WJT')

@section('content')
    <div class="page-head">
        <div>
            <h1>Akun Saya</h1>
            <p class="muted">
                {{ $user->name }} · {{ $user->email }}
            </p>
        </div>
        @if ($user->isOwner())
            <span class="badge">Owner</span>
        @else
            <span class="badge">{{ $user->branch?->name ?? 'Staff' }}</span>
        @endif
    </div>

    @include('partials.alerts')

    <form method="POST" action="{{ route('account.update') }}" class="card form-card" enctype="multipart/form-data">
        @csrf
        @method('PATCH')

        <div class="form-section" style="margin-top: 0; padding-top: 0; border-top: 0">
            <h2>Profil</h2>
            <p class="muted" style="margin-top: -6px">
                Data ini dipakai untuk login dan ditampilkan di riwayat aktivitas.
            </p>

            <div class="field">
                <label>Foto Profil</label>
                <div class="account-avatar-wrap">
                    <div class="account-avatar-preview">
                        @if ($user->avatarUrl())
                            <img src="{{ $user->avatarUrl() }}" alt="{{ $user->name }}" class="account-avatar-img" id="avatar-preview">
                        @else
                            <span class="account-avatar-initials" id="avatar-initials">{{ $user->initials() }}</span>
                            <img src="" alt="" class="account-avatar-img" id="avatar-preview" style="display: none;">
                        @endif
                    </div>
                    <div class="account-avatar-actions">
                        <label for="photo" class="btn btn-sm btn-ghost" style="cursor: pointer; display: inline-flex; align-items: center; width: fit-content;">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 6px;">
                                <path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/>
                                <circle cx="12" cy="13" r="4"/>
                            </svg>
                            Pilih Foto
                        </label>
                        <input id="photo" type="file" name="photo" accept="image/png,image/jpeg,image/webp" style="display: none;">

                        @if ($user->photo_path)
                            <label class="account-avatar-remove-label">
                                <input type="checkbox" name="remove_photo" value="1">
                                <span>Hapus foto saat ini</span>
                            </label>
                        @endif
                        <span class="muted hint" style="display: block; font-size: 12px; margin-top: 4px;">
                            Maksimal 2 MB (format JPG, PNG, atau WebP).
                        </span>
                    </div>
                </div>
            </div>

            <div class="field">
                <label for="name">Nama Lengkap</label>
                <input id="name" type="text" name="name" value="{{ old('name', $user->name) }}" maxlength="120" required>
            </div>

            <div class="field">
                <label for="email">Email</label>
                <input id="email" type="email" name="email" value="{{ old('email', $user->email) }}" maxlength="255" required>
            </div>

            <div class="field">
                <label for="phone">Nomor Telepon</label>
                <input id="phone" type="text" name="phone" value="{{ old('phone', $user->phone) }}" maxlength="30">
            </div>

            <div class="field">
                <label for="address">Alamat</label>
                <textarea id="address" name="address" maxlength="500">{{ old('address', $user->address) }}</textarea>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn">Simpan Profil</button>
            </div>
        </div>
    </form>

    <form method="POST" action="{{ route('account.password') }}" class="card form-card" style="margin-top: 16px">
        @csrf
        @method('PATCH')

        <div class="form-section" style="margin-top: 0; padding-top: 0; border-top: 0">
            <h2>Ganti Password</h2>
            <p class="muted" style="margin-top: -6px">
                Minimal 8 karakter. Gunakan password yang tidak dipakai di akun lain.
            </p>

            <div class="field">
                <label for="current_password">Password Saat Ini</label>
                <input id="current_password" type="password" name="current_password" autocomplete="current-password" required>
            </div>

            <div class="field">
                <label for="password">Password Baru</label>
                <input id="password" type="password" name="password" minlength="8" autocomplete="new-password" required>
            </div>

            <div class="field">
                <label for="password_confirmation">Konfirmasi Password Baru</label>
                <input id="password_confirmation" type="password" name="password_confirmation" minlength="8" autocomplete="new-password" required>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn">Simpan Password</button>
            </div>
        </div>
    </form>

    <div class="card form-card" style="margin-top: 16px">
        <h2>Sesi</h2>
        <p class="muted">
            Keluar akan mengakhiri sesi di perangkat ini. Kamu perlu login ulang untuk masuk.
        </p>

        <form method="POST" action="{{ route('logout') }}" class="form-actions" style="margin-top: 0">
            @csrf
            <button type="submit" class="btn btn-ghost">Keluar</button>
        </form>
    </div>
@endsection

@push('scripts')
    <script>
        const photoInput = document.getElementById('photo');
        const previewImg = document.getElementById('avatar-preview');
        const initialsSpan = document.getElementById('avatar-initials');

        if (photoInput && previewImg) {
            photoInput.addEventListener('change', function () {
                const file = this.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function (e) {
                        previewImg.src = e.target.result;
                        previewImg.style.display = 'block';
                        if (initialsSpan) {
                            initialsSpan.style.display = 'none';
                        }
                    };
                    reader.readAsDataURL(file);
                }
            });
        }
    </script>
@endpush
