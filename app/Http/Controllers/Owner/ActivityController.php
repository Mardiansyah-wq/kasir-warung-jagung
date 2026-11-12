<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Branch;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ActivityController extends Controller
{
    public function index(Request $request): View
    {
        $data = $request->validate([
            'action' => ['nullable', 'in:menu,variant,stock,order'],
            'branch_id' => ['nullable', 'integer', 'exists:branches,id'],
            'user_id' => ['nullable', 'integer', 'exists:users,id'],
            'from' => ['nullable', 'date'],
            'to' => [
                'nullable',
                'date',
                Rule::when($request->filled('from'), ['after_or_equal:from']),
            ],
            'search' => ['nullable', 'string', 'max:100'],
        ]);

        $action = $data['action'] ?? null;
        $branchId = isset($data['branch_id']) ? (int) $data['branch_id'] : null;
        $userId = isset($data['user_id']) ? (int) $data['user_id'] : null;
        $from = $data['from'] ?? null;
        $to = $data['to'] ?? null;
        $search = trim($data['search'] ?? '');

        $logs = ActivityLog::query()
            ->with(['user', 'branch'])
            ->when($action === 'menu', fn ($query) => $query->where('action', 'like', 'menu.%'))
            ->when($action === 'variant', fn ($query) => $query->where('action', 'like', 'menu_variant.%'))
            ->when($action === 'stock', fn ($query) => $query->where('action', 'like', 'stock.%'))
            ->when($action === 'order', fn ($query) => $query->where('action', 'like', 'order.%'))
            ->when($branchId, fn ($query) => $query->where('branch_id', $branchId))
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

        return view('owner.activity.index', [
            'logs' => $logs,
            'users' => User::query()->orderBy('name')->get(['id', 'name', 'role']),
            'branches' => Branch::query()->orderBy('name')->get(['id', 'name']),
            'action' => $action,
            'branchId' => $branchId,
            'userId' => $userId,
            'from' => $from,
            'to' => $to,
            'search' => $search,
        ]);
    }
}
