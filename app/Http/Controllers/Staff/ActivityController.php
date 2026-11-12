<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ActivityController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $branchId = $user->branch_id ? (int) $user->branch_id : null;

        $data = $request->validate([
            'user_id' => ['nullable', 'integer', 'exists:users,id'],
            'from' => ['nullable', 'date'],
            'to' => [
                'nullable',
                'date',
                Rule::when($request->filled('from'), ['after_or_equal:from']),
            ],
            'search' => ['nullable', 'string', 'max:100'],
        ]);

        $userId = isset($data['user_id']) ? (int) $data['user_id'] : null;
        $from = $data['from'] ?? null;
        $to = $data['to'] ?? null;
        $search = trim($data['search'] ?? '');

        $logs = ActivityLog::query()
            ->with(['user', 'branch'])
            ->where('action', 'like', 'stock.%')
            ->when(
                $branchId,
                fn ($query) => $query->where('branch_id', $branchId),
                fn ($query) => $query->whereRaw('1 = 0'),
            )
            ->when($userId, fn ($query) => $query->where('user_id', $userId))
            ->when($from, fn ($query) => $query->whereDate('created_at', '>=', $from))
            ->when($to, fn ($query) => $query->whereDate('created_at', '<=', $to))
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('subject_label', 'like', '%'.$search.'%')
                        ->orWhere('description', 'like', '%'.$search.'%');
                });
            })
            ->latest('created_at')
            ->latest('id')
            ->paginate(25)
            ->withQueryString();

        $users = User::query()
            ->when(
                $branchId,
                fn ($query) => $query->where(fn ($query) => $query
                    ->where('branch_id', $branchId)
                    ->orWhere('role', User::ROLE_OWNER)),
                fn ($query) => $query->where('role', User::ROLE_OWNER),
            )
            ->orderBy('name')
            ->get(['id', 'name', 'role']);

        return view('staff.activity.index', [
            'logs' => $logs,
            'users' => $users,
            'branch' => $user->branch,
            'userId' => $userId,
            'from' => $from,
            'to' => $to,
            'search' => $search,
        ]);
    }
}
