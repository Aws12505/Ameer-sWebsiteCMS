<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UserRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;
use Inertia\Response;

/**
 * CMS logins (Admin only). There is no public sign-up.
 */
class UserController extends Controller
{
    /**
     * List the users.
     */
    public function index(): Response
    {
        return Inertia::render('admin/users/index', [
            'users' => User::query()->orderBy('name')->get()->map(fn (User $user): array => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role->value,
                'roleLabel' => $user->role->label(),
                'twoFactor' => $user->two_factor_confirmed_at !== null,
            ]),
        ]);
    }

    /**
     * Show the form for a new user.
     */
    public function create(): Response
    {
        return Inertia::render('admin/users/form', [
            'user' => null,
            'roles' => $this->roleOptions(),
            'passwordRules' => Password::defaults()->toPasswordRulesString(),
        ]);
    }

    /**
     * Save a new user.
     */
    public function store(UserRequest $request): RedirectResponse
    {
        User::query()->make($request->validated())->forceFill(['email_verified_at' => now()])->save();

        Inertia::flash('toast', ['type' => 'success', 'message' => 'User created. Share the password with them securely.']);

        return to_route('admin.users.index');
    }

    /**
     * Show the form for a user.
     */
    public function edit(User $user): Response
    {
        return Inertia::render('admin/users/form', [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role->value,
            ],
            'roles' => $this->roleOptions(),
            'passwordRules' => Password::defaults()->toPasswordRulesString(),
        ]);
    }

    /**
     * Save changes to a user.
     */
    public function update(UserRequest $request, User $user): RedirectResponse
    {
        $data = $request->validated();

        if ($user->is($request->user()) && $data['role'] !== UserRole::Admin->value) {
            return back()->withErrors(['role' => 'You cannot remove your own Admin role.']);
        }

        if (blank($data['password'] ?? null)) {
            unset($data['password']);
        }

        $user->update($data);

        Inertia::flash('toast', ['type' => 'success', 'message' => 'User saved.']);

        return to_route('admin.users.index');
    }

    /**
     * Remove a user's login.
     */
    public function destroy(Request $request, User $user): RedirectResponse
    {
        if ($user->is($request->user())) {
            Inertia::flash('toast', ['type' => 'error', 'message' => 'You cannot delete your own login.']);

            return back();
        }

        $user->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => 'User deleted.']);

        return to_route('admin.users.index');
    }

    /**
     * @return list<array{value: string, label: string}>
     */
    protected function roleOptions(): array
    {
        return array_map(
            fn (UserRole $role): array => ['value' => $role->value, 'label' => $role->label()],
            UserRole::cases(),
        );
    }
}
