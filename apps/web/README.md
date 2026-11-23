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

Das Verzeichnis `public/` ist der Webroot. Die fachlichen Migrationen folgen in MIGRATIONS. [Datenmodell v1](../../docs/data-model.md)
