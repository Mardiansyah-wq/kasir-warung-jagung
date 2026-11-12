<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SettingController extends Controller
{
    public function index(): View
    {
        return view('owner.settings.index', [
            'discountsEnabled' => Setting::discountsEnabled(),
        ]);
    }

    public function updateDiscount(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'enabled' => ['required', 'boolean'],
        ]);

        Setting::put(Setting::DISCOUNTS_ENABLED, $data['enabled'] ? '1' : '0');

        return redirect()
            ->route('owner.settings.index')
            ->with(
                'status',
                $data['enabled']
                    ? 'Diskon manual di kasir diaktifkan.'
                    : 'Diskon manual di kasir dinonaktifkan.',
            );
    }
}
