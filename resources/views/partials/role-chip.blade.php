@php
    $role = ($role ?? 'staff') === 'owner' ? 'owner' : 'staff';
    $label = $label ?? ($role === 'owner' ? 'Owner' : 'Staff');
    $hero = (bool) ($hero ?? false);
    $classes = 'role-chip role-chip-'.$role.($hero ? ' role-chip-hero' : '');
@endphp
<span class="{{ $classes }}">
    <span class="role-chip-dot" aria-hidden="true"></span>
    {{ $label }}
</span>
