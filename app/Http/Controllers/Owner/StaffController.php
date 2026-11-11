<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StaffController extends Controller
{
    public function index(): View
    {
        $staff = User::where('role', User::ROLE_STAFF)
            ->with('branch')
            ->orderBy('created_at', 'desc')
            ->get();

        $branches = Branch::where('is_active', true)->get();

        return view('owner.staff.index', [
            'staff' => $staff,
            'branches' => $branches,
        ]);
    }

    public function create(): View
    {
        $branches = Branch::where('is_active', true)->get();

        return view('owner.staff.create', [
            'branches' => $branches,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['required', 'string', 'max:30'],
            'address' => ['required', 'string', 'max:500'],
            'password' => ['required', 'string', 'min:8', 'max:255'],
            'branch_id' => ['required', 'exists:branches,id'],
        ]);

        User::create([
            'branch_id' => $data['branch_id'],
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'address' => $data['address'],
            'password_hash' => $data['password'],
            'role' => User::ROLE_STAFF,
            'is_active' => true,
        ]);

        return redirect()->route('owner.staff.index')->with('success', '✅ Staff baru berhasil ditambahkan.');
    }

    public function edit(User $user): View
    {
        if ($user->role !== User::ROLE_STAFF) {
            abort(403);
        }

        $branches = Branch::where('is_active', true)->get();

        return view('owner.staff.edit', [
            'user' => $user,
            'branches' => $branches,
        ]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        if ($user->role !== User::ROLE_STAFF) {
            abort(403);
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,'.$user->id],
            'phone' => ['required', 'string', 'max:30'],
            'address' => ['required', 'string', 'max:500'],
            'branch_id' => ['required', 'exists:branches,id'],
        ]);

        $user->update([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'address' => $data['address'],
            'branch_id' => $data['branch_id'],
        ]);

        return redirect()->route('owner.staff.index')->with('success', '✅ Data staff berhasil diperbarui.');
    }

    public function toggle(Request $request, User $user): RedirectResponse
    {
        if ($user->role !== User::ROLE_STAFF) {
            abort(403);
        }

        if ($user->is_active) {
            $user->update(['is_active' => false]);

            return redirect()->route('owner.staff.index')->with('success', '✅ Staff berhasil dinonaktifkan.');
        }

        $data = $request->validate([
            'branch_id' => ['required', 'exists:branches,id'],
        ]);

        $user->update([
            'is_active' => true,
            'branch_id' => $data['branch_id'],
        ]);

        return redirect()->route('owner.staff.index')->with('success', '✅ Staff berhasil diaktifkan dan diassign ke cabang.');
    }

    public function resetPassword(Request $request, User $user): RedirectResponse
    {
        if ($user->role !== User::ROLE_STAFF) {
            abort(403);
        }

        $request->validate([
            'password' => ['required', 'string', 'min:8', 'max:255'],
        ]);

        $user->update([
            'password_hash' => $request->password,
        ]);

        return redirect()->route('owner.staff.index')->with('success', '✅ Password staff berhasil direset.');
    }
}
