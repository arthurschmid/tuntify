# Phase 1 · Verwaltung und historische Teilnahme

**P1DESIGN · Datenmodell v1 freigegeben**

Phase 1 ermöglicht die Verwaltung von USER, aktuellen Kontaktdaten, vergangenen und zukünftigen Events und historischen Teilnahmezuordnungen. Der Betreiber ist anfangs der einzige aktive Administrator. Weitere Personen werden als Stammdatensätze angelegt und können später einen regulären Login und Rollen erhalten.

## Abnahme

1. Der Administrator meldet sich mit einem Passwort an und kann „Angemeldet bleiben“ für 30 Tage wählen. Zwei-Faktor-Authentifizierung ist für später vorgesehen. Er kann Personen suchen, mit ID und Shortname anlegen, den aktuellen Kontaktdatensatz bearbeiten und das Verifizierungsdatum manuell pflegen.
2. Vergangene und zukünftige Events lassen sich anlegen. Eventzeiten behalten ihre Zeitzone. Einladung, Zusage und Teilnahme werden unabhängig erfasst. Die Ansicht zeigt den Verlauf und die erste erfasste ATT-Teilnahme.
3. XLSX und CSV folgen den freigegebenen DB-Tabellen und Spalten. Die Importvorschau zeigt Konflikte und Fehler. Wiederholter Import derselben Datei erzeugt keine Duplikate. Historische Zuordnungen werden direkt im Interface erfasst.
4. Der Postexport verwendet die richtige Namensform und erhält PLZ mit führenden Nullen. Unvollständige Anschriften werden zur Prüfung angezeigt.
5. Zugriffstests umfassen Anzeige, Änderung, Import, Export und Rollenverwaltung. Nach Entzug der Adminrolle endet der Verwaltungszugriff. Tests verwenden synthetische Daten.
6. Die mobile Oberfläche unterstützt de-DE und en-GB. Auswahlreihenfolge: explizite Einstellung, Browser, Deutsch. Briefsprache wird separat gespeichert und fällt auf Deutsch zurück.
7. Die Betriebsabnahme prüft Plesk, getrennte Datenbankrechte, verschlüsselte Backups und Wiederherstellung. Produktivdeployment und Übernahme echter Daten erfordern Freigabe.

## Meilensteine

| Meilenstein | Aufgaben und Stand |
| --- | --- |
| Design | P1DESIGN, P1REVIEW, P1ROLES, P1FORMAT, P1FIELDS und P1USER dokumentieren das freigegebene Datenmodell. |
| Fundament | APPBASE stellt das Laravel-13-Grundgerüst. MIGRATIONS ist umgesetzt und auf MariaDB 11.8 einschließlich Rollback und erneuter Migration geprüft. AUTH stellt Anmeldung, 30-Tage-Option und Adminprüfung bereit. GRIDTOOLS ergänzt in der Personenübersicht den letzten Login sowie Sperren, Entsperren und Passwortsetzen für andere eingerichtete Konten. I18N folgt. |
| Verwaltung | USERUI stellt Personenliste, Suche, Anlage und Kontaktpflege bereit. USERGRID ergänzt Inline-Bearbeitung, Sortierung und Filter pro Spalte sowie den optionalen manuellen DSGVO-Prüfstand. EVENTUI, PARTUI und FIRSTATT folgen. |
| Übernahme | IMPORTER und POSTEXPORT sind geplant. |
| Betriebsreife | DEPLOY, BACKUP, TESTS und ACCEPT sind geplant. |

Zielbetrieb ist Ubuntu/Plesk mit PHP 8.4.28, MariaDB 11.8.6 und Laravel-`public/` als Webroot. Blade ist für die erste Oberfläche vorgesehen. [Lokale Startanleitung](../apps/web/README.md)

Mit Phase 2 beginnt ein Pilot für selbstverwaltete Kontaktdaten. Postalische Einladungslinks, selbstverwaltete RSVP, Zahlungen und private Medien folgen in späteren Phasen. [Datenmodell](data-model.md) · [Entscheidungen](decisions.md)
