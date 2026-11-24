@php($editing = $menu !== null)

<div class="field">
    <label for="name">Nama Menu</label>
    <input
        id="name"
        type="text"
        name="name"
        value="{{ old('name', $menu?->name) }}"
        maxlength="150"
        required
    >
</div>

<div class="grid-2">
    <div class="field">
        <label for="category">Kategori</label>
        <select id="category" name="category" required>
            @foreach (\App\Models\Menu::CATEGORIES as $item)
                <option value="{{ $item }}" @selected(old('category', $menu?->category ?? 'makanan') === $item)>
                    {{ \App\Models\Menu::categoryLabelFor($item) }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="field">
        <label>Status</label>
        <div style="padding-top: 10px">
            <input type="hidden" name="is_active" value="0">
            <label class="check">
                <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $menu?->is_active ?? true))>
                Aktif dijual
            </label>
        </div>
    </div>
</div>

<div class="field">
    <label for="description">Deskripsi (opsional)</label>
    <textarea id="description" name="description" maxlength="2000">{{ old('description', $menu?->description) }}</textarea>
</div>

<div class="field">
    <label for="image">Foto Menu (opsional)</label>
    <input
        id="image"
        type="file"
        name="image"
        accept="image/jpeg,image/png,image/webp"
        data-menu-image
    >
    <span class="hint">Format JPG/PNG/WebP. Foto otomatis dikompres; hasil upload maks 2MB.</span>

    <div class="image-preview" data-image-preview hidden>
        <img alt="Pratinjau foto" data-image-preview-img>
        <div class="image-preview-info">
            <span class="image-preview-tag">Foto Baru Dipilih</span>
            <span class="hint" data-image-info></span>
        </div>
    </div>

    @if ($editing && $menu->thumbUrl())
        <div class="current-image">
            <img src="{{ $menu->thumbUrl() }}" alt="Foto {{ $menu->name }}">
            <div class="image-preview-info">
                <span class="image-preview-tag">Foto Saat Ini</span>
                <span class="hint">Pilih file baru di atas jika ingin mengganti foto ini.</span>
            </div>
        </div>
    @endif
</div>
