<?php

namespace App\Http\Controllers;

use App\Models\User;
use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AdminAccountController extends Controller
{
    public function editPassword(Request $request, User $user): View
    {
        $this->authorizeTarget($request, $user);

        return view('admin.users.password', ['user' => $user, 'gridQuery' => $this->gridQuery($request)]);
    }

    public function resetPassword(Request $request, User $user): RedirectResponse
    {
        $this->authorizeTarget($request, $user);
        $values = $request->validate([
            'current_password' => ['required', 'current_password:web'],
            'password' => ['required', 'string', Password::min(12), 'confirmed'],
        ], [
            'current_password.current_password' => 'Dein aktuelles Admin-Passwort stimmt nicht.',
        ]);

        if (strlen($values['password']) > 72) {
            throw ValidationException::withMessages(['password' => 'Das neue Passwort darf höchstens 72 Bytes lang sein.']);
        }

        $this->changeAccount($request, $user, function (User $target) use ($values) {
            $target->forceFill([
                'password_hash' => $values['password'],
                'remember_token' => null,
                'auth_version' => (string) Str::uuid(),
            ])->save();
        });

        return to_route('admin.users.index', $this->gridQuery($request))->with('status', 'Passwort neu gesetzt. Alte Sitzungen wurden widerrufen.');
    }

    public function updateLock(Request $request, User $user): RedirectResponse
    {
        $this->authorizeTarget($request, $user);
        $request->validate(['locked' => ['required', 'boolean']]);
        $locked = $request->boolean('locked');

        $this->changeAccount($request, $user, function (User $target) use ($locked) {
            if ($locked === ($target->disabled_at !== null)) {
                return;
            }

            $target->forceFill([
                'disabled_at' => $locked ? now() : null,
                'remember_token' => null,
                'auth_version' => (string) Str::uuid(),
            ])->save();
        });

        return to_route('admin.users.index', $this->gridQuery($request))
            ->with('status', $locked ? 'Login gesperrt.' : 'Login entsperrt.');
    }

    private function authorizeTarget(Request $request, User $target): void
    {
        abort_unless($target->id !== $request->user()->id && filled($target->login_email) && filled($target->password_hash), 403);
    }

    private function changeAccount(Request $request, User $user, Closure $change): void
    {
        DB::transaction(function () use ($request, $user, $change) {
            DB::table('roles')->where('code', 'admin')->lockForUpdate()->sole();
            $actor = User::query()->lockForUpdate()->findOrFail($request->user()->id);
            abort_unless($actor->canAccessAdmin() && $actor->auth_version === $request->session()->get('admin_auth_version'), 403);

            $target = User::query()->lockForUpdate()->findOrFail($user->id);
            $this->authorizeTarget($request, $target);
            $change($target);
        });
    }

    private function gridQuery(Request $request): array
    {
        return array_intersect_key($request->query(), array_flip(['q', 'filter', 'sort', 'direction', 'page']));
    }
}
