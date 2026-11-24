<!doctype html>
<html lang="de">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>@yield('title') · Tuntify</title>
    <link rel="stylesheet" href="{{ asset('admin.css') }}">
</head>
<body class="@yield('body-class')">
    <header class="topbar">
        <div class="shell topbar-inner">
            <a class="brand" href="{{ route('admin.home') }}">Tuntify</a>
            @auth
                <form method="post" action="{{ route('logout') }}">
                    @csrf
                    <button class="secondary" type="submit">Abmelden</button>
                </form>
            @endauth
        </div>
    </header>
    <main class="shell content">
        @if (session('status'))
            <p class="notice" role="status">{{ session('status') }}</p>
        @endif
        @if ($errors->any())
            <div class="notice error" role="alert">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        @yield('content')
    </main>
</body>
</html>
