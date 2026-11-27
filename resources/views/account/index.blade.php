@extends('layouts.app')

@section('title', 'Akun Saya - Jagung Bakar WJT')

@section('body_class', 'page-account')

@push('styles')
    <style>
        body.page-account {
            background: var(--bg-warm);
        }

        .pa-hero {
            position: relative;
            display: flex;
            flex-direction: column;
            gap: 16px;
            overflow: hidden;
            margin-bottom: 16px;
            padding: 22px;
            border-radius: 16px;
            background: linear-gradient(135deg, #36251e 0%, #4a342a 58%, #6b4426 100%);
            color: #f9f2e4;
            box-shadow: 0 6px 20px rgba(54, 37, 30, .16);
        }

        .pa-hero::after {
            position: absolute;
            top: -80px;
            right: -60px;
            width: 210px;
            height: 210px;
            border-radius: 50%;
            background: rgba(224, 183, 71, .16);
            content: "";
            pointer-events: none;
        }

        .pa-hero > * {
            position: relative;
            z-index: 1;
        }

        .pa-hero-main {
            display: flex;
            align-items: center;
            gap: 14px;
            min-width: 0;
        }

        .pa-avatar {
            display: flex;
            flex: 0 0 76px;
            width: 76px;
            height: 76px;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            border: 2px solid rgba(232, 205, 140, .55);
            border-radius: 50%;
            background: #f2e6c4;
            box-shadow: 0 0 0 4px rgba(224, 183, 71, .14);
        }

        .pa-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .pa-avatar-person {
            display: flex;
            width: 100%;
            height: 100%;
            align-items: center;
            justify-content: center;
            color: #8a6636;
        }

        .pa-avatar-person .nav-icon {
            width: 42px;
            height: 42px;
            stroke-width: 1.4;
        }

        .pa-identity {
            min-width: 0;
        }

        .pa-eyebrow {
            color: #e8cd8c;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: .09em;
            text-transform: uppercase;
        }

        .pa-identity h1 {
            margin: 4px 0;
            font-family: var(--font-serif);
            font-size: clamp(1.45rem, 4vw, 1.9rem);
            line-height: 1.2;
            overflow-wrap: anywhere;
        }

        .pa-identity p {
            margin: 0;
            color: #e2d5c1;
            font-size: 13px;
            overflow-wrap: anywhere;
        }

        .pa-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 6px 12px;
            margin-top: 9px;
            color: #d9c8ae;
            font-size: 12px;
        }

        .pa-meta span {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            overflow-wrap: anywhere;
        }

        .pa-meta .nav-icon {
            flex: 0 0 auto;
            width: 14px;
            height: 14px;
        }

        .pa-hero-side {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .pa-grid {
            display: grid;
            gap: 16px;
            align-items: start;
        }

        .pa-stack {
            display: grid;
            gap: 16px;
        }

        body.page-account .form-card {
            max-width: none;
            margin: 0;
        }

        .pa-card {
            padding: 20px;
            border: 1px solid var(--border-warm);
            border-radius: 14px;
            background: var(--surface-warm);
            box-shadow: 0 1px 3px rgba(54, 37, 30, .04);
        }

        .pa-card-head {
            display: flex;
            align-items: flex-start;
            gap: 11px;
            margin-bottom: 16px;
        }

        .pa-card-icon {
            display: grid;
            flex: 0 0 auto;
            width: 34px;
            height: 34px;
            border-radius: 10px;
            background: #f7ecd4;
            color: var(--brand-brown);
            place-items: center;
        }

        .pa-card-icon .nav-icon {
            width: 17px;
            height: 17px;
        }

        .pa-card-head h2 {
            margin: 0;
            font-family: var(--font-serif);
            font-size: 1.1rem;
        }

        .pa-card-sub {
            margin: 3px 0 0;
            color: var(--muted-warm);
            font-size: 13px;
            line-height: 1.4;
        }

        .pa-photo {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            gap: 8px;
            margin-bottom: 18px;
            padding: 14px;
            border: 1px dashed var(--border-warm);
            border-radius: 12px;
            background: #fdfaf2;
        }

        .pa-upload-btn {
            display: inline-flex;
            min-height: 44px;
            align-items: center;
            gap: 8px;
            width: fit-content;
            cursor: pointer;
        }

        .pa-upload-btn .nav-icon {
            width: 16px;
            height: 16px;
        }

        .pa-file-input {
            position: absolute;
            width: 1px;
            height: 1px;
            overflow: hidden;
            opacity: 0;
            pointer-events: none;
        }

        .pa-file-name {
            margin: 0;
            color: var(--muted-warm);
            font-size: 12px;
            overflow-wrap: anywhere;
        }

        .pa-remove {
            display: inline-flex;
            min-height: 32px;
            align-items: center;
            gap: 7px;
            color: #b43020;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
        }

        .pa-remove input {
            width: auto;
            margin: 0;
        }

        .pa-input-wrap {
            position: relative;
        }

        .pa-input-wrap input {
            padding-right: 48px;
        }

        .pa-input-wrap .toggle-password {
            right: 2px;
        }

        .pa-hidden {
            display: none !important;
        }

        .pa-hint {
            margin-top: 0;
        }

        .pa-session p {
            margin: 0 0 14px;
            color: var(--muted-warm);
            font-size: 13px;
            line-height: 1.45;
        }

        .pa-session .form-actions {
            margin-top: 0;
        }

        @media (max-width: 767px) {
            .pa-remove {
                min-height: 44px;
            }
        }

        @media (min-width: 760px) {
            .pa-hero {
                flex-direction: row;
                align-items: center;
                justify-content: space-between;
                padding: 26px 30px;
            }

            .pa-avatar {
                flex-basis: 88px;
                width: 88px;
                height: 88px;
            }

            .pa-hero-side {
                flex-direction: column;
                align-items: flex-end;
                gap: 8px;
            }
        }

        @media (min-width: 900px) {
            .pa-grid {
                grid-template-columns: minmax(0, 1.1fr) minmax(0, .9fr);
            }
        }
    </style>
@endpush

@section('content')
    <section class="pa-hero">
        <div class="pa-hero-main">
            <div class="pa-avatar">
                @if ($user->avatarUrl())
                    <img src="{{ $user->avatarUrl() }}" alt="{{ $user->name }}" id="avatar-preview">
                    <span class="pa-avatar-person pa-hidden" id="avatar-initials" aria-hidden="true">
                        @include('partials.nav-icon', ['name' => 'user'])
                    </span>
                @else
                    <span class="pa-avatar-person" id="avatar-initials" aria-hidden="true">
                        @include('partials.nav-icon', ['name' => 'user'])
                    </span>
                    <img alt="" id="avatar-preview" class="pa-hidden">
                @endif
            </div>

            <div class="pa-identity">
                <span class="pa-eyebrow">Akun Saya</span>
                <h1>{{ $user->name }}</h1>
                <p>{{ $user->email }}</p>
                <div class="pa-meta">
                    <span>
                        @include('partials.nav-icon', ['name' => 'store'])
                        {{ $user->isOwner() ? 'Akses semua cabang' : ($user->branch?->name ?? 'Cabang belum ditentukan') }}
                    </span>
                    @if ($user->created_at)
                        <span>
                            @include('partials.nav-icon', ['name' => 'calendar'])
                            Bergabung {{ $user->created_at->format('d/m/Y') }}
                        </span>
                    @endif
                </div>
            </div>
        </div>

        <div class="pa-hero-side">
            @include('partials.role-chip', ['role' => $user->isOwner() ? 'owner' : 'staff'])
        </div>
    </section>

    @include('partials.alerts')

    <div class="pa-grid">
        <form method="POST" action="{{ route('account.update') }}" class="card form-card pa-card" enctype="multipart/form-data">
            @csrf
            @method('PATCH')

            <div class="pa-card-head">
                <span class="pa-card-icon">@include('partials.nav-icon', ['name' => 'user'])</span>
                <div>
                    <h2>Profil</h2>
                    <p class="pa-card-sub">Data ini dipakai untuk login dan ditampilkan di riwayat aktivitas.</p>
                </div>
            </div>

            <div class="pa-photo">
                <label for="photo" class="btn btn-sm btn-ghost pa-upload-btn">
                    @include('partials.nav-icon', ['name' => 'user'])
                    <span>Pilih Foto</span>
                </label>
                <input
                    id="photo"
                    class="pa-file-input"
                    type="file"
                    name="photo"
                    accept="image/png,image/jpeg,image/webp"
                    aria-describedby="photo-hint photo-name"
                >
                <p class="pa-file-name" id="photo-name">Belum ada foto baru dipilih.</p>

                @if ($user->photo_path)
                    <label class="pa-remove">
                        <input type="checkbox" name="remove_photo" value="1">
                        <span>Hapus foto saat ini</span>
                    </label>
                @endif

                <span class="hint pa-hint" id="photo-hint">Maksimal 2 MB (JPG, PNG, atau WebP).</span>
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
                <input id="phone" type="text" name="phone" value="{{ old('phone', $user->phone) }}" maxlength="30" inputmode="tel">
            </div>

            <div class="field">
                <label for="address">Alamat</label>
                <textarea id="address" name="address" maxlength="500">{{ old('address', $user->address) }}</textarea>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn">Simpan Profil</button>
            </div>
        </form>

        <div class="pa-stack">
            <form method="POST" action="{{ route('account.password') }}" class="card form-card pa-card">
                @csrf
                @method('PATCH')

                <div class="pa-card-head">
                    <span class="pa-card-icon">@include('partials.nav-icon', ['name' => 'cog'])</span>
                    <div>
                        <h2>Ganti Password</h2>
                        <p class="pa-card-sub">Minimal 8 karakter. Gunakan password yang tidak dipakai di akun lain.</p>
                    </div>
                </div>

                <div class="field">
                    <label for="current_password">Password Saat Ini</label>
                    <div class="pa-input-wrap">
                        <input id="current_password" type="password" name="current_password" autocomplete="current-password" required>
                        @include('partials.password-toggle', ['target' => 'current_password'])
                    </div>
                </div>

                <div class="field">
                    <label for="password">Password Baru</label>
                    <div class="pa-input-wrap">
                        <input id="password" type="password" name="password" minlength="8" autocomplete="new-password" required>
                        @include('partials.password-toggle', ['target' => 'password'])
                    </div>
                </div>

                <div class="field">
                    <label for="password_confirmation">Konfirmasi Password Baru</label>
                    <div class="pa-input-wrap">
                        <input id="password_confirmation" type="password" name="password_confirmation" minlength="8" autocomplete="new-password" required>
                        @include('partials.password-toggle', ['target' => 'password_confirmation'])
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn">Simpan Password</button>
                </div>
            </form>

            <div class="card form-card pa-card pa-session">
                <div class="pa-card-head">
                    <span class="pa-card-icon">@include('partials.nav-icon', ['name' => 'logout'])</span>
                    <div>
                        <h2>Sesi</h2>
                        <p class="pa-card-sub">Keluar akan mengakhiri sesi di perangkat ini. Kamu perlu login ulang untuk masuk.</p>
                    </div>
                </div>

                <form method="POST" action="{{ route('logout') }}" class="form-actions">
                    @csrf
                    <button type="submit" class="btn btn-ghost">Keluar</button>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        const photoInput = document.getElementById('photo');
        const previewImg = document.getElementById('avatar-preview');
        const defaultAvatar = document.getElementById('avatar-initials');
        const photoName = document.getElementById('photo-name');
        const removePhoto = document.querySelector('input[name="remove_photo"]');

        if (photoInput && previewImg) {
            photoInput.addEventListener('change', function () {
                const file = this.files[0];

                if (!file) {
                    previewImg.removeAttribute('src');
                    previewImg.classList.add('pa-hidden');

                    if (defaultAvatar) {
                        defaultAvatar.classList.remove('pa-hidden');
                    }

                    if (photoName) {
                        photoName.textContent = 'Belum ada foto baru dipilih.';
                    }

                    return;
                }

                if (removePhoto) {
                    removePhoto.checked = false;
                }

                const reader = new FileReader();
                reader.onload = function (event) {
                    previewImg.src = event.target.result;
                    previewImg.classList.remove('pa-hidden');

                    if (defaultAvatar) {
                        defaultAvatar.classList.add('pa-hidden');
                    }
                };
                reader.readAsDataURL(file);

                if (photoName) {
                    photoName.textContent = file.name;
                }
            });
        }

        if (removePhoto && previewImg && defaultAvatar) {
            removePhoto.addEventListener('change', function () {
                if (!this.checked) {
                    return;
                }

                if (photoInput) {
                    photoInput.value = '';
                }

                previewImg.classList.add('pa-hidden');
                defaultAvatar.classList.remove('pa-hidden');

                if (photoName) {
                    photoName.textContent = 'Belum ada foto baru dipilih.';
                }
            });
        }
    </script>
@endpush
