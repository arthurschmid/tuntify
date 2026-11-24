# Tuntify · Laravel-Anwendung

Voraussetzungen sind PHP 8.4 mit PDO MySQL, Composer und MariaDB 11.8. Aus diesem Verzeichnis:

```bash
composer install
test -f .env || cp .env.example .env
php artisan key:generate
php artisan serve
```

Den Schlüssel nur für eine neue lokale Umgebung erzeugen. Datenbankzugangsdaten über die lokale `.env` oder die Prozessumgebung setzen. Die Startseite und `/up` benötigen keine Datenbanktabellen und keinen Frontend-Build.

```bash
composer check-platform-reqs
php artisan test
```

Das Verzeichnis `public/` ist der Webroot. Die fachlichen Migrationen sind enthalten. Gegen eine lokale Entwicklungsdatenbank ausführen:

```bash
php artisan migrate
php artisan db:seed --class=DemoSeeder
```

Der optionale DemoSeeder legt synthetische Daten ohne Login an und ist auf lokale und Testumgebungen begrenzt. [Datenmodell v1](../../docs/data-model.md)

## Admin-Zugang

Nach der Migration das erste Admin-Konto im Terminal anlegen:

```bash
php artisan tuntify:first-admin
```

Der Befehl fragt Shortname, Login-E-Mail und Passwort ab. Das Passwort wird verdeckt eingegeben. Danach ist der Login unter `/admin/login` verfügbar. „Angemeldet bleiben“ verwendet einen 30 Tage gültigen Laravel-Cookie. „Abmelden“ beendet die Sitzung und widerruft den Remember-Token. Auf einem HTTPS-Zielserver `SESSION_SECURE_COOKIE=true` setzen.

Nach dem Login öffnet `/admin` die Personenverwaltung. Die Suche filtert nach Shortname oder Alias. Die Tabelle erlaubt Sortieren und Filtern je Spalte sowie das Speichern je Zeile. Der optionale DSGVO-Status dokumentiert den manuell erfassten Prüfstand. Für neue Personen genügt ein Shortname. Die Bearbeitungsansicht enthält die Persona-Felder, Briefsprache und den aktuellen Kontaktstand.

Am rechten Tabellenende stehen letzter Passwort-Login (UTC), DSGVO-Prüfstand und Kontowerkzeuge. Ein Administrator kann andere eingerichtete Logins sperren, entsperren und nach Bestätigung seines eigenen Passworts ein neues Passwort setzen. Sperren und Passwortwechsel widerrufen frühere Sitzungen.
