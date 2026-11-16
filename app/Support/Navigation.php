<?php

namespace App\Support;

use App\Models\User;

class Navigation
{
    /**
     * Main and secondary navigation for the app shell.
     *
     * @return array{main: list<array{label: string, route: string, active: string, icon: string}>, secondary: list<array{label: string, route: string, active: string, icon: string}>}
     */
    public static function for(User $user): array
    {
        $secondary = [
            ['label' => 'Akun Saya', 'route' => 'account.index', 'active' => 'account.*', 'icon' => 'user'],
        ];

        if ($user->isOwner()) {
            return [
                'main' => [
                    ['label' => 'Total Penjualan', 'route' => 'owner.dashboard', 'active' => 'owner.dashboard', 'icon' => 'chart'],
                    ['label' => 'Laporan', 'route' => 'owner.reports.index', 'active' => 'owner.reports.*', 'icon' => 'doc'],
                    ['label' => 'Riwayat Pesanan', 'route' => 'owner.orders.index', 'active' => 'owner.orders.*', 'icon' => 'receipt'],
                    ['label' => 'Kelola Menu', 'route' => 'owner.menus.index', 'active' => 'owner.menus.*', 'icon' => 'menu'],
                    ['label' => 'Kelola Stok', 'route' => 'owner.stock.index', 'active' => 'owner.stock.*', 'icon' => 'box'],
                    ['label' => 'Kelola Akun Pegawai', 'route' => 'owner.staff.index', 'active' => 'owner.staff.*', 'icon' => 'users'],
                    ['label' => 'Log Aktivitas', 'route' => 'owner.activity.index', 'active' => 'owner.activity.*', 'icon' => 'clock'],
                ],
                'secondary' => array_merge([
                    ['label' => 'Cabang', 'route' => 'owner.branches.index', 'active' => 'owner.branches.*', 'icon' => 'store'],
                    ['label' => 'Pengaturan', 'route' => 'owner.settings.index', 'active' => 'owner.settings.*', 'icon' => 'cog'],
                ], $secondary),
            ];
        }

        return [
            'main' => [
                ['label' => 'Dashboard', 'route' => 'staff.dashboard', 'active' => 'staff.dashboard', 'icon' => 'chart'],
                ['label' => 'Kasir', 'route' => 'staff.pos.index', 'active' => 'staff.pos.*', 'icon' => 'cart'],
                ['label' => 'Riwayat Pesanan', 'route' => 'staff.orders.index', 'active' => 'staff.orders.*', 'icon' => 'receipt'],
                ['label' => 'Kelola Stok', 'route' => 'staff.stock.index', 'active' => 'staff.stock.*', 'icon' => 'box'],
                ['label' => 'Kelola Menu', 'route' => 'staff.menus.index', 'active' => 'staff.menus.*', 'icon' => 'menu'],
                ['label' => 'Log Aktivitas', 'route' => 'staff.activity.index', 'active' => 'staff.activity.*', 'icon' => 'clock'],
            ],
            'secondary' => $secondary,
        ];
    }

    /**
     * Breadcrumb label for the current page, falling back to the app name.
     */
    public static function currentLabel(User $user): string
    {
        $items = array_merge(...array_values(self::for($user)));

        foreach ($items as $item) {
            if (request()->routeIs($item['active'])) {
                return $item['label'];
            }
        }

        return 'Beranda';
    }
}
