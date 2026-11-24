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
    private const PRIVACY_STATUSES = [
        'pending' => 'Prüfung offen',
        'reviewed' => 'Geprüft',
        'follow_up' => 'Klärungsbedarf',
    ];

    private const LOCALES = ['de-DE' => 'Deutsch', 'en-GB' => 'Englisch (UK)'];

    private const COLUMNS = [
        'shortname' => ['Shortname', 'text'],
        'id' => ['ID', 'read-number'],
        'salutation' => ['Anrede', 'text'],
        'alias_given_name' => ['Alias-Vorname', 'text'],
        'alias_family_name' => ['Alias-Nachname', 'text'],
        'preferred_locale' => ['Oberflächensprache', self::LOCALES],
        'postal_locale' => ['Briefsprache', self::LOCALES],
        'contact.legal_given_name' => ['Bürgerlicher Vorname', 'text'],
        'contact.legal_family_name' => ['Bürgerlicher Nachname', 'text'],
        'contact.contact_email' => ['Kontakt-E-Mail', 'email'],
        'contact.phone' => ['Telefon', 'tel'],
        'contact.line_1' => ['Adresszeile 1', 'text'],
        'contact.line_2' => ['Adresszeile 2', 'text'],
        'contact.postal_code' => ['PLZ', 'text'],
        'contact.city' => ['Stadt', 'text'],
        'contact.additional_line' => ['Adresszusatz', 'text'],
        'contact.country_code' => ['Ländercode', 'text'],
        'contact.allow_alias_for_post' => ['Name für die Post', [1 => 'Alias', 0 => 'Bürgerlicher Name']],
        'contact.contact_verified_on' => ['Kontakt geprüft am', 'date'],
        'login_email' => ['Login-E-Mail', 'read-text'],
        'email_verified_at' => ['Login-E-Mail bestätigt', 'read-date'],
        'disabled_at' => ['Login deaktiviert am', 'read-date'],
        'two_factor_confirmed_at' => ['Zwei-Faktor bestätigt', 'read-date'],
        'created_at' => ['Erstellt am', 'read-date'],
        'updated_at' => ['Geändert am', 'read-date'],
        'last_login_at' => ['Letzter Login (UTC)', 'read-date'],
        'privacy_status' => ['DSGVO-Status', self::PRIVACY_STATUSES],
    ];

    public function index(Request $request): View
    {
        $query = $request->query('q');
        $query = is_string($query) ? mb_substr(trim($query), 0, 100) : '';
        $sort = $request->query('sort');
        $sort = is_string($sort) && isset(self::COLUMNS[$sort]) ? $sort : 'shortname';
        $direction = $request->query('direction') === 'desc' ? 'desc' : 'asc';
        $requestedFilters = $request->query('filter', []);
        $requestedFilters = is_array($requestedFilters) ? $requestedFilters : [];
        $filters = [];
        $users = DB::table('users')->leftJoin('user_contacts', 'users.id', '=', 'user_contacts.user_id');
        $select = [];

        if ($query !== '') {
            $term = $this->likePattern($query);
            $users->where(function ($search) use ($term) {
                $search->whereRaw("users.shortname LIKE ? ESCAPE '!'", [$term])
                    ->orWhereRaw("users.alias_given_name LIKE ? ESCAPE '!'", [$term])
                    ->orWhereRaw("users.alias_family_name LIKE ? ESCAPE '!'", [$term]);
            });
        }

        foreach (self::COLUMNS as $key => [, $type]) {
            $column = $this->qualifiedColumn($key);
            $select[] = $column.' as '.str_replace('.', '_', $key);
            $value = $requestedFilters[$key] ?? null;
            if (! is_string($value) || trim($value) === '') {
                continue;
            }

            $value = mb_substr(trim($value), 0, 255);

            if (is_array($type)) {
                if ($value === '__empty') {
                    $users->whereNull($column);
                } elseif (array_key_exists($value, $type)) {
                    $users->where($column, $value);
                } else {
                    continue;
                }
            } elseif ($type === 'read-number') {
                if (! preg_match('/\A[0-9]{1,20}\z/', $value)) {
                    continue;
                }
                $users->where($column, $value);
            } elseif (in_array($type, ['date', 'read-date'], true)) {
                if (! preg_match('/\A(\d{4})-(\d{2})-(\d{2})\z/', $value, $parts)
                    || ! checkdate((int) $parts[2], (int) $parts[3], (int) $parts[1])) {
                    continue;
                }
                $users->whereDate($column, $value);
            } else {
                $users->whereRaw("$column LIKE ? ESCAPE '!'", [$this->likePattern($value)]);
            }
            $filters[$key] = $value;
        }

        $gridQuery = ['q' => $query, 'filter' => $filters, 'sort' => $sort, 'direction' => $direction];

        return view('admin.users.index', [
            'users' => $users->select($select)->orderBy($this->qualifiedColumn($sort), $direction)
                ->orderBy('users.id')->paginate(60)->appends($gridQuery),
            'columns' => self::COLUMNS,
            'gridQuery' => $gridQuery,
            'filters' => $filters,
            'query' => $query,
            'sort' => $sort,
            'direction' => $direction,
        ]);
    }

    private function qualifiedColumn(string $key): string
    {
        return str_starts_with($key, 'contact.')
            ? 'user_contacts.'.substr($key, strlen('contact.'))
            : 'users.'.$key;
    }

    private function likePattern(string $value): string
    {
        return '%'.strtr($value, ['!' => '!!', '%' => '!%', '_' => '!_']).'%';
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
            'postal_locale' => ['nullable', Rule::in(array_keys(self::LOCALES))],
            'preferred_locale' => ['nullable', Rule::in(array_keys(self::LOCALES))],
            'privacy_status' => ['nullable', Rule::in(array_keys(self::PRIVACY_STATUSES))],
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

        if ($request->boolean('_grid')) {
            return to_route('admin.users.index', array_intersect_key($request->query(), array_flip(['q', 'filter', 'sort', 'direction', 'page'])))
                ->with('status', 'Änderungen gespeichert.');
        }

        return to_route('admin.users.edit', $user)->with('status', 'Änderungen gespeichert.');
    }
}
