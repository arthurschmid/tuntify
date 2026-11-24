@extends('admin.layout')

@section('title', $user->shortname)

@section('content')
    <a href="{{ route('admin.users.index') }}">← Personen</a>
    <h1>{{ $user->shortname }}</h1>
    <form method="post" action="{{ route('admin.users.update', $user) }}" class="stack">
        @csrf
        @method('PATCH')
        <section class="panel">
            <h2>Person</h2>
            <div class="fields">
                <label>Shortname
                    <input name="shortname" value="{{ old('shortname', $user->shortname) }}" pattern="[A-Za-z0-9_\\-]{1,32}" maxlength="32" required>
                </label>
                @foreach (['salutation' => 'Anrede', 'alias_given_name' => 'Alias-Vorname', 'alias_family_name' => 'Alias-Nachname'] as $field => $label)
                    <label>{{ $label }}
                        <input name="{{ $field }}" value="{{ old($field, $user->{$field}) }}" maxlength="255">
                    </label>
                @endforeach
                <label>Briefsprache
                    <select name="postal_locale">
                        <option value="">Standard (Deutsch)</option>
                        <option value="de-DE" @selected(old('postal_locale', $user->postal_locale) === 'de-DE')>Deutsch</option>
                        <option value="en-GB" @selected(old('postal_locale', $user->postal_locale) === 'en-GB')>Englisch (UK)</option>
                    </select>
                </label>
            </div>
        </section>
        <section class="panel">
            <h2>Kontaktdaten</h2>
            <div class="fields">
                @foreach ([
                    'legal_given_name' => ['Bürgerlicher Vorname', 'text', 255],
                    'legal_family_name' => ['Bürgerlicher Nachname', 'text', 255],
                    'contact_email' => ['Kontakt-E-Mail', 'email', 255],
                    'phone' => ['Telefon', 'tel', 64],
                    'line_1' => ['Adresszeile 1', 'text', 255],
                    'line_2' => ['Adresszeile 2', 'text', 255],
                    'postal_code' => ['Postleitzahl', 'text', 32],
                    'city' => ['Stadt', 'text', 255],
                    'additional_line' => ['Adresszusatz', 'text', 255],
                    'country_code' => ['Ländercode (z. B. DE)', 'text', 2],
                    'contact_verified_on' => ['Zuletzt geprüft am', 'date', null],
                ] as $field => [$label, $type, $max])
                    <label>{{ $label }}
                        <input type="{{ $type }}" name="contact[{{ $field }}]" value="{{ old('contact.'.$field, $contact->{$field} ?? null) }}" @if ($max) maxlength="{{ $max }}" @endif>
                    </label>
                @endforeach
                @php($post = old('contact.allow_alias_for_post', $contact->allow_alias_for_post ?? null))
                <label>Name für die Post
                    <select name="contact[allow_alias_for_post]">
                        <option value="" @selected($post === null || $post === '')>Ungeklärt</option>
                        <option value="1" @selected((string) $post === '1')>Alias</option>
                        <option value="0" @selected((string) $post === '0')>Bürgerlicher Name</option>
                    </select>
                </label>
            </div>
        </section>
        <div><button>Änderungen speichern</button></div>
    </form>
@endsection
