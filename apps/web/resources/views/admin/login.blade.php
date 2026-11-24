@extends('admin.layout')

@section('title', 'Anmelden')

@section('content')
    <div class="login">
        <h1>Admin-Anmeldung</h1>
        <form method="post" action="{{ route('login') }}" class="panel stack">
            @csrf
            <label>E-Mail <input type="email" name="email" value="{{ old('email') }}" autocomplete="username" required></label>
            <label>Passwort <input type="password" name="password" autocomplete="current-password" required></label>
            <label class="check"><input type="checkbox" name="remember" value="1" @checked(old('remember'))> 30 Tage angemeldet bleiben</label>
            <button type="submit">Anmelden</button>
        </form>
    </div>
@endsection
