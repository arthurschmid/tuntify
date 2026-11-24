@extends('admin.layout')

@section('title', 'Personen')

@section('content')
    <h1>Personen</h1>
    <div class="workspace">
        <section class="panel listing" aria-label="Personenliste">
            <form method="get" action="{{ route('admin.users.index') }}" class="search">
                <label for="q">Person suchen</label>
                <div class="inline">
                    <input id="q" name="q" type="search" value="{{ $query }}" maxlength="100" placeholder="Shortname oder Alias">
                    <button class="secondary">Suchen</button>
                </div>
            </form>
            <table>
                <thead><tr><th scope="col">Shortname</th><th scope="col">Alias</th></tr></thead>
                <tbody>
                    @forelse ($users as $user)
                        <tr>
                            <th scope="row"><a href="{{ route('admin.users.edit', $user) }}">{{ $user->shortname }}</a></th>
                            <td>{{ trim($user->alias_given_name.' '.$user->alias_family_name) ?: '—' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="2">Keine Personen gefunden.</td></tr>
                    @endforelse
                </tbody>
            </table>
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
        </section>
        <section class="panel">
            <h2>Person anlegen</h2>
            <form method="post" action="{{ route('admin.users.store') }}" class="stack">
                @csrf
                <label for="shortname">Shortname</label>
                <input id="shortname" name="shortname" value="{{ old('shortname') }}" pattern="[A-Za-z0-9_\\-]{1,32}" maxlength="32" required aria-describedby="shortname-hint">
                <small id="shortname-hint">Bis zu 32 Buchstaben (A–Z), Ziffern, Bindestriche oder Unterstriche.</small>
                <button>Person anlegen</button>
            </form>
        </section>
    </div>
@endsection
