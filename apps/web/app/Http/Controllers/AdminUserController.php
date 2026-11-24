<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AdminUserController extends Controller
{
    public function index(Request $request): View
    {
        $query = trim($request->validate(['q' => ['nullable', 'string', 'max:100']])['q'] ?? '');
        $users = User::query()->select(['id', 'shortname', 'alias_given_name', 'alias_family_name']);

        if ($query !== '') {
            $term = '%'.strtr($query, ['!' => '!!', '%' => '!%', '_' => '!_']).'%';
            $users->where(function ($search) use ($term) {
                $search->whereRaw("shortname LIKE ? ESCAPE '!'", [$term])
                    ->orWhereRaw("alias_given_name LIKE ? ESCAPE '!'", [$term])
                    ->orWhereRaw("alias_family_name LIKE ? ESCAPE '!'", [$term]);
            });
        }

        return view('admin.users.index', [
            'users' => $users->orderBy('shortname')->orderBy('id')->paginate(25)->withQueryString(),
            'query' => $query,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $values = $request->validate([
            'shortname' => ['required', 'regex:/\A[A-Za-z0-9_-]{1,32}\z/', Rule::unique('users')],
        ]);

        try {
            $user = User::create($values);
        } catch (UniqueConstraintViolationException) {
            throw ValidationException::withMessages(['shortname' => 'Dieser Shortname ist bereits vergeben.']);
        }

        return to_route('admin.users.edit', $user)->with('status', 'Person angelegt.');
    }

    public function edit(User $user): View
    {
        return view('admin.users.edit', [
            'user' => $user,
            'contact' => DB::table('user_contacts')->where('user_id', $user->id)->first(),
        ]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $values = $request->validate([
            'shortname' => ['required', 'regex:/\A[A-Za-z0-9_-]{1,32}\z/', Rule::unique('users')->ignore($user)],
            'salutation' => ['nullable', 'string', 'max:255'],
            'alias_given_name' => ['nullable', 'string', 'max:255'],
            'alias_family_name' => ['nullable', 'string', 'max:255'],
            'postal_locale' => ['nullable', Rule::in(['de-DE', 'en-GB'])],
            'contact' => ['sometimes', 'array'],
            'contact.legal_given_name' => ['nullable', 'string', 'max:255'],
            'contact.legal_family_name' => ['nullable', 'string', 'max:255'],
            'contact.contact_email' => ['nullable', 'email', 'max:255'],
            'contact.phone' => ['nullable', 'string', 'max:64'],
            'contact.contact_verified_on' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:1000-01-01', 'before_or_equal:9999-12-31'],
            'contact.line_1' => ['nullable', 'string', 'max:255'],
            'contact.line_2' => ['nullable', 'string', 'max:255'],
            'contact.postal_code' => ['nullable', 'string', 'max:32'],
            'contact.city' => ['nullable', 'string', 'max:255'],
            'contact.additional_line' => ['nullable', 'string', 'max:255'],
            'contact.country_code' => ['nullable', 'regex:/\A[A-Za-z]{2}\z/'],
            'contact.allow_alias_for_post' => ['nullable', 'boolean'],
        ], [
            'shortname.unique' => 'Dieser Shortname ist bereits vergeben.',
            'shortname.regex' => 'Der Shortname darf nur Buchstaben, Ziffern, Bindestriche und Unterstriche enthalten und höchstens 32 Zeichen lang sein.',
            'contact.country_code.regex' => 'Der Ländercode muss aus zwei Buchstaben bestehen.',
        ], [
            'contact.contact_email' => 'Kontakt-E-Mail',
            'contact.contact_verified_on' => 'Prüfdatum',
        ]);

        $contact = $values['contact'] ?? [];
        unset($values['contact']);
        if (isset($contact['country_code'])) {
            $contact['country_code'] = strtoupper($contact['country_code']);
        }

        try {
            DB::transaction(function () use ($user, $values, $contact) {
                $locked = User::query()->lockForUpdate()->findOrFail($user->id);
                $locked->update($values);

                if ($contact === []) {
                    return;
                }

                $existing = DB::table('user_contacts')->where('user_id', $locked->id);
                if ($existing->exists()) {
                    $existing->update($contact);
                } elseif (array_filter($contact, fn ($value) => $value !== null) !== []) {
                    DB::table('user_contacts')->insert(['user_id' => $locked->id, ...$contact]);
                }
            });
        } catch (UniqueConstraintViolationException) {
            throw ValidationException::withMessages(['shortname' => 'Dieser Shortname ist bereits vergeben.']);
        }

        return to_route('admin.users.edit', $user)->with('status', 'Änderungen gespeichert.');
    }
}
