# ⏱ ZeitManager

> **All-in-one Freiberufler-Tool** – Kunden, Projekte, Zeiterfassung, Kanban, Helpdesk, Rechnungen und mehr.

---

## ✨ Features

### 📊 Dashboard
- Kennzahlen auf einen Blick: Kunden, aktive Projekte, Stunden im laufenden Monat, offene Rechnungen und offener Rechnungsbetrag
- Letzte 10 Zeiteinträge mit Projekt und Kategorie
- Offene Helpdesk-Tickets in der Übersicht
- Interaktive Monatscharts für die letzten 12 Monate: Arbeitsstunden, Einnahmen (Brutto) und Ausgaben

### 👥 Kunden & Projekte
- Kundenstamm mit automatisch generierter Kundennummer, Kontaktperson, Telefon, Adresse und Notizen
- Projekte mit projektspezifischem Stundensatz, Budget (in Stunden und Euro), Deadline und Status (aktiv / inaktiv)
- Datei-Upload pro Projekt (bis 50 MB, beliebige Dateitypen) mit Download-Funktion
- Projekt-To-dos: Aufgaben direkt im Projekt anlegen, abhaken und sortieren
- Nachrichten an Kunden senden (E-Mail mit konfigurierbarer Vorlage, inkl. Betreff und Ticketnummer)
- Detailansicht mit allen Projekten, Zeiteinträgen, Ausgaben, Rechnungen und Verträgen des Kunden
- SLA-Zeiten pro Kunde und Support-Kategorie konfigurierbar

### ⏱ Zeiterfassung
- **Live-Timer** mit Pause-Funktion direkt in der Topbar – immer sichtbar, auf jeder Seite
- Timer-Vollbild-Overlay mit laufender Kostenvorschau (Stunden × Stundensatz)
- Aufgabe beim Timer-Start vorauswählen – Kategorie und Beschreibung werden automatisch übernommen
- Zeiteinträge manuell erfassen, bearbeiten und filtern
- Zuordnung zu Projekt, Arbeitskategorie und optionalem Ticket
- Budget-Stunden je Aufgabe – Fortschrittsanzeige im Projekt
- Einträge werden beim Erstellen einer Rechnung als "abgerechnet" markiert

### 📋 Kanban-Board
- Aufgaben mit Drag & Drop zwischen den Spalten **Ready · In Arbeit · Testing · Abgeschlossen**
- Priorität (Niedrig / Mittel / Hoch), Fälligkeitsdatum, Zuweisung an Teammitglieder und Budget-Stunden
- Aufgaben direkt in Projekten anlegen – automatisch im Kanban sichtbar
- Filterung nach Projekt
- Reihenfolge innerhalb einer Spalte per Drag & Drop anpassbar

### 🔁 Wiederkehrende Aufgaben
- Aufgaben-Vorlagen pro Projekt mit konfigurierbarer Frequenz: täglich, wöchentlich oder monatlich
- Wochentag, Monatstag, Uhrzeit und Vorlaufzeit für die Fälligkeit einstellbar
- Automatische Generierung als Kanban-Tasks (via Scheduler, jede Minute)
- Können gleichzeitig als Wartungsereignisse im Wartungsplan erscheinen
- Manuelles Auslösen ("Jetzt ausführen") möglich

### 🛠 Wartungsplan
- Kalender-Ansicht (Monatsansicht) pro Projekt für geplante Wartungsereignisse
- Wartungsereignisse mit Titel, Beschreibung, Datum, Uhrzeit, Priorität und Zuweisung an Teammitglieder
- Ereignisse als erledigt markieren oder wieder öffnen
- E-Mail-Erinnerungen vor dem Termin (konfigurierbar)
- Wiederkehrende Aufgaben mit `is_maintenance`-Flag erscheinen automatisch im Kalender

### 🎧 Helpdesk
- **Öffentliches Ticket-Portal** (kein Login erforderlich): Kunden können Tickets einreichen und per Ticket-Nummer den Status verfolgen
- E-Mail-Benachrichtigungen: Eingangsbestätigung an Kunden, Benachrichtigung ans Admin-Team, Antwort-Mails
- Support-Kategorien mit individuellen SLA-Fristen pro Kunde
- Admin-Ansicht: Tickets filtern nach Status, Kategorie, Suchbegriff
- Admins können Tickets direkt im Backend anlegen (z.B. bei telefonischen Anfragen)
- Antworten im Konversationsverlauf – für Kunden und Admins
- Ticket direkt als Kanban-Task übernehmen
- E-Mail-Log: Protokoll aller versendeten Mails

### 🧾 Rechnungen
- Rechnungen projektübergreifend aus erfassten Zeiteinträgen und Ausgaben zusammenstellen
- Optionaler Projektfilter beim Erstellen
- Automatischer Rechnungsnummernkreis (Beispiel: `R-Apr26-01`), monatlich neu gezählt
- Kleinunternehmerregelung (§ 19 UStG) mit Hinweistext unterstützt
- Rabatt, Zahlungsziel, MwSt. und Notizen konfigurierbar
- Leistungsbeschreibung manuell oder per Auto-Generierung (nach Kategorie und Leistungszeitraum gruppiert)
- Drucklayout für sauberes PDF über den Browser
- Rechnungen bearbeiten (Status: Entwurf / Gesendet / Bezahlt / Storniert) und löschen

### 📝 Angebote
- Angebote mit einzelnen Leistungspositionen erstellen
- Lastenheft-Export als eigene Druckansicht
- PDF-Export über den Browser
- Bei Annahme per Knopfdruck in ein Projekt umwandeln

### 📄 Verträge
- Vertragsvorlagen mit Variablen-Platzhaltern verwalten
- Verträge auf Basis einer Vorlage erstellen; Variablen werden beim Erstellen befüllt
- Unterschriebenes PDF hochladen und wieder herunterladen
- Druckansicht für sauberes PDF über den Browser
- Verträge in der Kundendetailansicht sichtbar

### 💰 Ausgaben
- Projektbezogene Ausgaben erfassen (z.B. Fahrtkosten, Lizenzen, Material)
- Mit Datum, Kategorie, Betrag und Beschreibung
- Ausgaben können direkt beim Erstellen einer Rechnung ausgewählt und abgerechnet werden

### 🏷 Arbeitskategorien
- Beliebige Kategorien mit Farbe anlegen (z.B. Entwicklung, Beratung, Support)
- Werden bei Zeiteinträgen, Aufgaben und Rechnungen verwendet

### 👤 Multi-User & Team
- Mehrere Teammitglieder mit den Rollen **Admin** und **Mitglied**
- Admins verwalten Team, Unternehmenseinstellungen, Kategorien und Abrechnung
- Timer und Aufgaben sind nutzerspezifisch
- Login mit E-Mail und Passwort; eigenes Profil und Passwort für alle Nutzer änderbar
- Nutzer können deaktiviert werden

### ⚙️ Einstellungen
- Unternehmensname, Adresse, Steuernummer, USt-IdNr. und Bankverbindung
- Standard-Stundensatz, MwSt.-Satz, Zahlungsziel
- Kleinunternehmerregelung ein-/ausschalten
- E-Mail-Konfiguration (SMTP) mit Test-Mail-Funktion
- Vorlage für Kundennachrichten anpassen
- Dark Mode: immer hell / immer dunkel / automatisch nach Zeitplan
- Dark Mode Zeitplan (z.B. 20:00 – 07:00 Uhr)

### 📤 Export / Import
- Vollständiger Datenexport als JSON-Datei (Kunden, Projekte, Zeiteinträge, Ausgaben, Rechnungen)
- Import aus zuvor exportierter Datei zur Datenmigration oder Sicherung

---

## 🚀 Setup

### Voraussetzungen

- PHP 8.2+
- Composer
- SQLite (Standard, keine weitere Konfiguration nötig)

### Installation

```bash
# 1. Abhängigkeiten installieren
composer install

# 2. Umgebungsdatei anlegen
cp .env.example .env
php artisan key:generate

# 3. Datenbank anlegen (SQLite)
touch database/database.sqlite

# 4. Migrationen ausführen
php artisan migrate

# 5. Server starten
php artisan serve
```

Anschließend **http://localhost:8000** aufrufen. Beim ersten Start wird automatisch ein Admin-Konto eingerichtet.

Für wiederkehrende Aufgaben und automatische Erinnerungen den Laravel-Scheduler aktivieren:

```bash
# Linux/macOS: Cron-Eintrag hinzufügen
* * * * * cd /pfad/zum/projekt && php artisan schedule:run >> /dev/null 2>&1
```

---

## 🗄 Datenbankstruktur

| Tabelle | Beschreibung |
|---|---|
| `users` | Teammitglieder mit Rolle und Aktivierungsstatus |
| `settings` | Key-Value-Einstellungen (Adresse, Stundenlohn, E-Mail etc.) |
| `customers` | Kundenstamm mit Kundennummer und SLA-Einstellungen |
| `customer_sla_settings` | SLA-Zeiten pro Kunde und Support-Kategorie |
| `work_categories` | Arbeitskategorien mit Farbe |
| `projects` | Projekte, einem Kunden zugeordnet |
| `project_files` | Hochgeladene Dateien pro Projekt |
| `tasks` | Aufgaben mit Kanban-Status, Priorität und Budget-Stunden |
| `timers` | Laufende Live-Timer (nutzerspezifisch) |
| `time_entries` | Abgeschlossene Zeiteinträge |
| `expenses` | Projektausgaben |
| `quotes` | Angebote mit Leistungspositionen |
| `contracts` | Verträge auf Basis von Vorlagen |
| `contract_templates` | Vertragsvorlagen mit Platzhaltern |
| `invoices` | Rechnungen |
| `invoice_time_entry` | Pivot: Rechnung ↔ Zeiteintrag |
| `invoice_expense` | Pivot: Rechnung ↔ Ausgabe |
| `recurring_tasks` | Vorlagen für wiederkehrende Kanban-Aufgaben |
| `maintenance_events` | Geplante Wartungsereignisse pro Projekt |
| `support_categories` | Kategorien für Helpdesk-Tickets |
| `tickets` | Helpdesk-Tickets (öffentlich einreichbar) |
| `ticket_messages` | Konversationsverlauf pro Ticket |
| `email_logs` | Protokoll versendeter E-Mails |

---

## 🔄 Typischer Workflow

```
Einstellungen → Kunden → Projekte → Aufgaben (Kanban) → Zeiterfassung → Rechnung
```

1. **Einstellungen** einrichten (Adresse, Stundenlohn, Bankdaten, SMTP)
2. **Kunden** anlegen und SLA-Zeiten konfigurieren
3. **Projekte** anlegen, Aufgaben und Dateien hinzufügen
4. **Aufgaben** im Kanban-Board planen – oder wiederkehrende Vorlagen definieren
5. **Live-Timer** starten oder Zeiten manuell erfassen
6. **Rechnung** erstellen → Zeiteinträge und Ausgaben auswählen → drucken / als PDF speichern

---

## 🛠 Tech-Stack

| Bereich | Technologie |
|---|---|
| Backend | PHP 8.2+, Laravel |
| Frontend | Blade, Tailwind CSS (CDN), Alpine.js |
| Drag & Drop | SortableJS |
| Icons | Phosphor Icons |
| Datenbank | SQLite / MySQL / PostgreSQL |
| Scheduler | Laravel Task Scheduling (Cron) |
