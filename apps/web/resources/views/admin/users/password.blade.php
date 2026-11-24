@extends('admin.layout')

@section('title', 'Passwort neu setzen')

@section('content')
    <a href="{{ route('admin.users.index', $gridQuery) }}">← Personen</a>
    <h1>Passwort neu setzen</h1>
    <form method="post" class="panel stack login" action="{{ route('admin.users.password.update', ['user' => $user->id, ...$gridQuery]) }}">
        @csrf
        @method('PUT')
        <p>Neues Passwort für <strong>{{ $user->shortname }}</strong>. Bestehende Sitzungen werden widerrufen.</p>
        <label>Dein aktuelles Admin-Passwort
            <input type="password" name="current_password" autocomplete="current-password" required>
        </label>
        <label>Neues Passwort für {{ $user->shortname }}
            <input type="password" name="password" autocomplete="new-password" minlength="12" maxlength="72" required>
        </label>
        <label>Neues Passwort wiederholen
            <input type="password" name="password_confirmation" autocomplete="new-password" minlength="12" maxlength="72" required>
        </label>
        <button type="submit">Passwort neu setzen</button>
    </form>
@endsection
