<?php

namespace App\Http\Controllers\Owner;

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
        $data = $request->validate([
            'action' => ['nullable', 'in:menu,variant'],
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
        $userId = isset($data['user_id']) ? (int) $data['user_id'] : null;
        $from = $data['from'] ?? null;
        $to = $data['to'] ?? null;
        $search = trim($data['search'] ?? '');

        $logs = ActivityLog::query()
            ->with('user')
            ->when($action === 'menu', fn ($query) => $query->where('action', 'like', 'menu.%'))
            ->when($action === 'variant', fn ($query) => $query->where('action', 'like', 'menu_variant.%'))
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
            'action' => $action,
            'userId' => $userId,
            'from' => $from,
            'to' => $to,
            'search' => $search,
        ]);
    }
}
