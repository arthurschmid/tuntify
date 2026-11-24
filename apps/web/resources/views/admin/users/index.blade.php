@extends('admin.layout')

@section('title', 'Personen')
@section('body-class', 'people-page')

@section('content')
    <div class="people-heading">
        <h1>Personen</h1>
        <span>{{ $users->total() }} {{ $users->total() === 1 ? 'Person' : 'Personen' }}</span>
    </div>
    <section class="panel grid-toolbar" aria-label="Personen suchen oder anlegen">
        <form id="grid-filters" method="get" action="{{ route('admin.users.index') }}">
            <input type="hidden" name="sort" value="{{ $sort }}">
            <input type="hidden" name="direction" value="{{ $direction }}">
            <label for="q">Person suchen</label>
            <div class="inline">
                <input id="q" name="q" type="search" value="{{ $query }}" maxlength="100" placeholder="Shortname oder Alias">
                <button class="secondary">Filtern</button>
                <a class="grid-reset" href="{{ route('admin.users.index') }}">Zurücksetzen</a>
            </div>
        </form>
        <form method="post" action="{{ route('admin.users.store') }}">
            @csrf
            <label for="shortname">Person anlegen</label>
            <div class="inline">
                <input id="shortname" name="shortname" value="{{ old('_grid') ? '' : old('shortname') }}" placeholder="Shortname" pattern="[A-Za-z0-9_\\-]{1,32}" maxlength="32" required aria-label="Shortname für neue Person">
                <button>Anlegen</button>
            </div>
        </form>
    </section>
    <p id="grid-help">Nach Spaltentitel sortieren. Änderungen je Zeile speichern.</p>
    <div class="grid-scroll panel" tabindex="0" role="region" aria-label="Personentabelle" aria-describedby="grid-help">
        <table class="people-grid">
            <thead>
                <tr>
                    <th scope="col" class="row-actions">Zeile</th>
                    @foreach ($columns as $key => [$label, $type])
                        <th scope="col" @class(['shortname-column' => $key === 'shortname']) @if ($sort === $key) aria-sort="{{ $direction === 'asc' ? 'ascending' : 'descending' }}" @endif>
                            <a href="{{ route('admin.users.index', array_replace($gridQuery, ['sort' => $key, 'direction' => $sort === $key && $direction === 'asc' ? 'desc' : 'asc'])) }}">
                                {{ $label }}@if ($sort === $key) <span aria-hidden="true">{{ $direction === 'asc' ? '↑' : '↓' }}</span>@endif
                            </a>
                        </th>
                    @endforeach
                    <th scope="col" class="account-tools">Kontowerkzeuge</th>
                </tr>
                <tr>
                    <td class="row-actions"><button class="secondary" form="grid-filters">Filtern</button></td>
                    @foreach ($columns as $key => [$label, $type])
                        <td @class(['shortname-column' => $key === 'shortname'])>
                            @if (is_array($type))
                                <select form="grid-filters" name="filter[{{ $key }}]" aria-label="{{ $label }} filtern">
                                    <option value="">Alle</option>
                                    <option value="__empty" @selected(($filters[$key] ?? null) === '__empty')>Ohne Angabe</option>
                                    @foreach ($type as $option => $caption)
                                        <option value="{{ $option }}" @selected(($filters[$key] ?? null) === (string) $option)>{{ $caption }}</option>
                                    @endforeach
                                </select>
                            @else
                                <input form="grid-filters" name="filter[{{ $key }}]" aria-label="{{ $label }} filtern"
                                    type="{{ in_array($type, ['date', 'read-date'], true) ? 'date' : 'search' }}"
                                    value="{{ $filters[$key] ?? '' }}" maxlength="255">
                            @endif
                        </td>
                    @endforeach
                    <td class="account-tools"></td>
                </tr>
            </thead>
            <tbody>
                @forelse ($users as $user)
                    @php($form = 'person-'.$user->id)
                    <tr>
                        <td class="row-actions">
                            <form id="{{ $form }}" method="post" action="{{ route('admin.users.update', ['user' => $user->id, ...$gridQuery, 'page' => $users->currentPage()]) }}">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="_grid" value="1">
                                <input type="hidden" name="_row" value="{{ $user->id }}">
                                <button class="icon-button" title="Zeile speichern" aria-label="{{ $user->shortname }} speichern">
                                    <x-admin-icon name="save"/>
                                </button>
                            </form>
                            <a class="icon-button" href="{{ route('admin.users.edit', $user->id) }}" title="Details öffnen" aria-label="Details für {{ $user->shortname }}">
                                <x-admin-icon name="details"/>
                            </a>
                        </td>
                        @foreach ($columns as $key => [$label, $type])
                            @php($value = $user->{str_replace('.', '_', $key)})
                            @php($name = str_starts_with($key, 'contact.') ? 'contact['.substr($key, 8).']' : $key)
                            @php($value = (string) old('_row') === (string) $user->id ? old($key, $value) : $value)
                            <td @class(['shortname-column' => $key === 'shortname'])>
                                @if (is_array($type))
                                    <select form="{{ $form }}" name="{{ $name }}" aria-label="{{ $label }} für {{ $user->shortname }}">
                                        <option value="">Ohne Angabe</option>
                                        @foreach ($type as $option => $caption)
                                            <option value="{{ $option }}" @selected($value !== null && (string) $value === (string) $option)>{{ $caption }}</option>
                                        @endforeach
                                    </select>
                                @elseif (str_starts_with($type, 'read-'))
                                    <span>{{ $value ?? '—' }}</span>
                                @else
                                    <input form="{{ $form }}" name="{{ $name }}" type="{{ $type }}" value="{{ $value }}"
                                        aria-label="{{ $label }} für {{ $user->shortname }}"
                                        @if ($key === 'shortname') required maxlength="32" pattern="[A-Za-z0-9_\\-]{1,32}" @endif>
                                @endif
                            </td>
                        @endforeach
                        <td class="account-tools">
                            @if ($user->login_email && $user->id !== auth()->id())
                                @php($params = ['user' => $user->id, ...$gridQuery, 'page' => $users->currentPage()])
                                @php($lockLabel = $user->disabled_at ? 'Login entsperren' : 'Login sperren')
                                <a class="icon-button" href="{{ route('admin.users.password.edit', $params) }}"
                                    title="Passwort neu setzen" aria-label="Passwort für {{ $user->shortname }} neu setzen">
                                    <x-admin-icon name="key"/>
                                </a>
                                <form method="post" action="{{ route('admin.users.lock', $params) }}">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="locked" value="{{ $user->disabled_at ? '0' : '1' }}">
                                    <button class="icon-button" title="{{ $lockLabel }}" aria-label="{{ $lockLabel }} für {{ $user->shortname }}">
                                        <x-admin-icon :name="$user->disabled_at ? 'unlock' : 'lock'"/>
                                    </button>
                                </form>
                            @else
                                <span class="unavailable-tools" title="{{ $user->login_email ? 'Eigenes Konto' : 'Kein Login eingerichtet' }}">—</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="{{ count($columns) + 2 }}">Keine Personen gefunden.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if ($users->hasPages())
        <nav class="pagination" aria-label="Seitennavigation">
            @if ($users->previousPageUrl())
                <a href="{{ $users->previousPageUrl() }}">Zurück</a>
            @endif
            <span>Seite {{ $users->currentPage() }} von {{ $users->lastPage() }}</span>
            @if ($users->nextPageUrl())
                <a href="{{ $users->nextPageUrl() }}">Weiter</a>
            @endif
        </nav>
    @endif
@endsection
