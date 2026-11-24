# Phase 1 · Entscheidungen

Das fachliche Datenmodell v1 ist zur Umsetzung freigegeben.

| Aufgabe | Entscheidung |
| --- | --- |
| P1REVIEW | ID und Shortname genügen für USER. Manuelle Kontaktprüfung. Excel und CSV für Personen- und Kontaktdaten, historische Zuordnungen im Interface. |
| P1ROLES | Ein aktiver Betreiber mit Adminrolle zum Start. Die Rolle kann weiteren regulären Anmeldungen zugewiesen werden. Höchstens ein aktueller Kontaktstand je USER. |
| P1FORMAT | `user_participation` verbindet USER und EVENT. `invite` und `rsvp` sind DATE, `participate` ist BOOL. Alle drei sind nullable mit Default NULL. Der Dateiaustausch folgt dem DB-Schema. |
| P1FIELDS | USER und EVENT benötigen nur ID und Shortname. Shortnames sind ASCII, maximal 32 Zeichen und case-insensitiv eindeutig. Eventzeiten sind optionale TIMESTAMP-Zeitpunkte mit Zeitzone. RSVP erfasst ausschließlich eine positive Zusage. |
| P1USER | Persona und optionale Anmeldung liegen direkt in `users`. `user_roles` verweist direkt auf USER. Der optionale Kontaktdatensatz bleibt separat. |
| APPBASE | Laravel 13 mit Composer-Lockdatei, PHP 8.4 und MariaDB 11.8. Der Plesk-Zielbetrieb ist gesondert zu prüfen. |

**AUTH** führt in Phase 1 Passwort-Login mit optionalem „Angemeldet bleiben“ für 30 Tage ein. Zwei-Faktor-Authentifizierung ist eine spätere Erweiterung.

## Umsetzungsdetails

Vorgeschlagen sind ein ASCII-Alphabet aus Buchstaben, Ziffern, Bindestrichen und Unterstrichen, eine Importvorschau mit ausdrücklicher Konfliktauflösung und ein Postexport mit freigegebenen Schemaspalten. Konkrete CSV-Konventionen, Quellschlüssel und Druckvorlage werden vor dem Dateiaustausch geprüft.

Die Adminrolle soll Import und Export sowie Vergabe und Entzug der Rolle erlauben. Der letzte aktive Administrator soll seine Rolle erst abgeben können, sobald ein weiteres aktives Konto Adminrechte hat. Zu klären sind Kontowiederherstellung, Archivierung, Löschung oder Anonymisierung sowie Zugriff auf und Aufbewahrung von Auditdaten.

Vor dem Umgang mit echten Daten sind Verantwortlichkeit, Datenschutzhinweise, Rechtsgrundlagen, Aufbewahrung und Betroffenenrechte zu klären. Kontakttrennung ermöglicht gezieltere Zugriffskontrolle. Ob eine Einwilligung erforderlich ist, hängt vom Verarbeitungszweck und der Rechtsgrundlage ab. Wohnkoordinaten, Aufbewahrung von Exporten und Sicherungen sowie Medienfreigaben brauchen eigene Regeln.

Für spätere Phasen sind gehashte ereignisspezifische Einladungstoken mit Ablauf und Widerruf vorgesehen. Einladungstoken dürfen ausschließlich begrenzten Zugriff für Einladung, RSVP und Zahlungsinformationen gewähren. Zahlungen werden manuell erfasst. Ein allgemeiner Kanal und private ereignisbezogene Inhalte sind vorgesehen. Zugriff für zukünftige Events folgt der bestätigten Anmeldung, Zugriff für vergangene Events der erfassten Teilnahme und einem weiterhin gültigen Konto.

[Datenmodell v1](data-model.md) · [Phase-1-Plan](phase1-plan.md)
