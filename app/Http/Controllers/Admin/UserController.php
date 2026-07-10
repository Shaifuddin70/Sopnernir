<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdminImportUsersRequest;
use App\Http\Requests\Admin\AdminResetUserPasswordRequest;
use App\Http\Requests\Admin\AdminStoreUserRequest;
use App\Http\Requests\Admin\AdminUpdateUserRequest;
use App\Models\User;
use App\Support\PaginationPerPage;
use App\Support\SqlLike;
use App\Services\UserCsvImportService;
use App\Services\UserMediaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class UserController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(User::class, 'user');
    }

    public function index(Request $request): View|JsonResponse
    {
        $like = SqlLike::term($request->query('search'));

        $users = User::query()
            ->with('nominee')
            ->withSum([
                'investmentParticipants as total_invested' => fn ($q) => $q->whereHas('investment', fn ($inv) => $inv->where('is_active', true)),
            ], 'contribution_amount')
            ->withSum([
                'investmentPeriodUsers as total_profit' => fn ($q) => $q->whereHas('period.investment', fn ($inv) => $inv->where('is_active', true)),
            ], 'profit_share')
            ->when($like, function ($q) use ($like): void {
                $q->where(function ($q) use ($like): void {
                    $q->where('name', 'like', $like)
                        ->orWhere('email', 'like', $like)
                        ->orWhere('phone', 'like', $like)
                        ->orWhere('nid_number', 'like', $like)
                        ->orWhereHas('nominee', function ($n) use ($like): void {
                            $n->where('name', 'like', $like)
                                ->orWhere('email', 'like', $like)
                                ->orWhere('phone', 'like', $like)
                                ->orWhere('nid_number', 'like', $like);
                        });
                });
            })
            ->orderBy('name')
            ->paginate(PaginationPerPage::resolve($request, 'per_page', 20))
            ->withQueryString();

        if ($request->ajax()) {
            return response()->json([
                'html' => view('admin.users.partials.table-fragment', compact('users'))->render(),
            ]);
        }

        return view('admin.users.index', compact('users'));
    }

    public function create(): View
    {
        return view('admin.users.create');
    }

    public function store(AdminStoreUserRequest $request, UserMediaService $media): RedirectResponse
    {
        $validated = $request->validated();
        $nomineeInput = $validated['nominee'];
        unset($validated['nominee'], $validated['password_confirmation'], $validated['image']);

        $validated['password'] = Hash::make($validated['password']);
        $validated['role'] = User::ROLE_INVESTOR;
        $validated['email_verified_at'] = now();

        $user = DB::transaction(function () use ($request, $media, $validated, $nomineeInput): User {
            $user = User::create($validated);

            if ($request->hasFile('image')) {
                $user->update([
                    'image' => $media->storeUserImage($request->file('image'), $user->id),
                ]);
            }

            $nominee = $user->nominee()->create([
                'name' => $nomineeInput['name'],
                'email' => $nomineeInput['email'],
                'phone' => $nomineeInput['phone'],
                'nid_number' => $nomineeInput['nid_number'],
                'address' => $nomineeInput['address'],
            ]);

            if ($request->hasFile('nominee.image')) {
                $nominee->update([
                    'image' => $media->storeNomineeImage($request->file('nominee.image'), $user->id),
                ]);
            }

            return $user;
        });

        return redirect()->route('admin.users.edit', $user)->with('status', __('User created.'));
    }

    public function importForm(): View
    {
        $this->authorize('create', User::class);

        return view('admin.users.import');
    }

    public function importStore(AdminImportUsersRequest $request, UserCsvImportService $csvImport): RedirectResponse
    {
        $this->authorize('create', User::class);

        $result = $csvImport->import($request->file('file'));

        $message = __('Imported :count user(s).', ['count' => $result['created']]);
        if ($result['created'] === 0 && count($result['errors']) === 0) {
            $message = __('No data rows found in the file.');
        }

        return redirect()
            ->route('admin.users.import')
            ->with('status', $message)
            ->with('import_errors', $result['errors']);
    }

    public function importTemplate(UserCsvImportService $csvImport): StreamedResponse
    {
        $this->authorize('create', User::class);

        $headers = $csvImport->csvTemplateHeaders();

        return response()->streamDownload(function () use ($headers): void {
            echo $headers;
        }, 'users-import-template.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function edit(User $user): View
    {
        $user->load('nominee');

        return view('admin.users.edit', compact('user'));
    }

    public function update(AdminUpdateUserRequest $request, User $user, UserMediaService $media): RedirectResponse
    {
        $validated = $request->validated();

        if ($request->hasFile('image')) {
            $media->deleteIfExists($user->image);
            $validated['image'] = $media->storeUserImage($request->file('image'), $user->id);
        } else {
            unset($validated['image']);
        }

        $nomineeInput = $validated['nominee'];
        unset($validated['nominee']);

        $user->fill($validated);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        $nominee = $user->nominee()->updateOrCreate(
            ['user_id' => $user->id],
            [
                'name' => $nomineeInput['name'],
                'email' => $nomineeInput['email'],
                'phone' => $nomineeInput['phone'],
                'nid_number' => $nomineeInput['nid_number'],
                'address' => $nomineeInput['address'],
            ]
        );

        if ($request->hasFile('nominee.image')) {
            $media->deleteIfExists($nominee->image);
            $nominee->update([
                'image' => $media->storeNomineeImage($request->file('nominee.image'), $user->id),
            ]);
        }

        return redirect()->route('admin.users.edit', $user)->with('status', __('User updated.'));
    }

    public function resetPassword(AdminResetUserPasswordRequest $request, User $user): RedirectResponse
    {
        $this->authorize('update', $user);

        $user->update([
            'password' => $request->validated('password'),
        ]);

        return back()->with('status', __('Password updated for :name.', ['name' => $user->name]));
    }

    public function toggleActive(Request $request, User $user): RedirectResponse
    {
        $this->authorize('update', $user);

        if ($user->id === $request->user()->id && $user->is_active) {
            return back()->withErrors([
                'active' => __('You cannot deactivate your own account.'),
            ]);
        }

        $nowActive = ! $user->is_active;
        $user->update(['is_active' => $nowActive]);

        return back()->with(
            'status',
            $nowActive
                ? __('User is now active.')
                : __('User is now inactive.')
        );
    }
}
