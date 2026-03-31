# ⏱ ZeitManager

> **All-in-one Freiberufler-Tool** – Kunden, Projekte, Zeiterfassung, Kanban, Rechnungen und mehr.

---

## ✨ Features

### 👥 Kunden & Projekte
- Kundenstamm mit Kontaktdaten und Lieferadresse
- Projekte mit projektspezifischem Stundensatz, Budget und Deadline
- Angebote erstellen – bei Annahme automatisch in Projekt umwandeln

### ⏱ Zeiterfassung
- **Live-Timer** mit Pause-Funktion direkt in der Topbar
- Zeiteinträge manuell erfassen, filtern und bearbeiten
- Zuordnung zu Projekt und Arbeitskategorie

### 📋 Kanban-Board
- Aufgaben (Tasks) mit Drag & Drop zwischen den Spalten **Ready · In Arbeit · Testing · Abgeschlossen**
- Direkt in Projekten anlegen – automatisch im Kanban sichtbar
- Priorität (Niedrig / Mittel / Hoch), Fälligkeitsdatum und Zuweisung an Teammitglieder
- Filterung nach Projekt

### 🧾 Rechnungen & Angebote
- Rechnungen projektübergreifend aus erfassten Zeiten und Ausgaben zusammenstellen
- Automatischer Rechnungsnummernkreis (z.B. `R-Mär26-01`)
- Kleinunternehmerregelung (§ 19 UStG) unterstützt
- Drucklayout für sauberes PDF über den Browser
- Angebote mit Leistungspositionen und Lastenheft-Export

### 📄 Verträge
- Vertragsvorlagen verwalten und Verträge auf Basis von Vorlagen erstellen
- PDF-Upload und Druckansicht

### 💰 Ausgaben
- Projektbezogene Ausgaben erfassen (Fahrtkosten, Lizenzen, Material etc.)
- In Rechnungen einbindbar

### 👤 Multi-User & Team
- Mehrere Teammitglieder mit den Rollen **Admin** und **Mitglied**
- Login mit E-Mail und Passwort
- Timer und Aufgaben sind nutzerspezifisch
- Admins verwalten Team, Unternehmenseinstellungen und Abrechnungsparameter

### ⚙️ Einstellungen
- Unternehmensadresse, Stundenlohn, MwSt.-Satz, Zahlungsziel, Bankverbindung
- Dark Mode (immer hell / immer dunkel / automatisch nach Zeitplan)
- Eigenes Profil und Passwort für alle Nutzer änderbar

### 📤 Export / Import
- Daten exportieren und importieren

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

---

## 🗄 Datenbankstruktur

| Tabelle | Beschreibung |
|---|---|
| `users` | Teammitglieder mit Rolle und Status |
| `settings` | Key-Value-Einstellungen (Adresse, Stundenlohn, etc.) |
| `customers` | Kundenstamm |
| `work_categories` | Arbeitskategorien mit Farbe |
| `projects` | Projekte, einem Kunden zugeordnet |
| `tasks` | Aufgaben mit Kanban-Status und Priorität |
| `timers` | Laufende Live-Timer (nutzerspezifisch) |
| `time_entries` | Abgeschlossene Zeiteinträge |
| `expenses` | Projektausgaben |
| `quotes` | Angebote mit Leistungspositionen |
| `contracts` | Verträge auf Basis von Vorlagen |
| `invoices` | Rechnungen |

---

## 🔄 Typischer Workflow

```
Einstellungen → Kunden → Projekte → Aufgaben (Kanban) → Zeiterfassung → Rechnung
```

1. **Einstellungen** einrichten (Adresse, Stundenlohn, Bankdaten)
2. **Kunden** und **Projekte** anlegen
3. **Aufgaben** im Kanban-Board planen und verwalten
4. **Live-Timer** starten oder Zeiten manuell erfassen
5. **Rechnung** erstellen → Positionen auswählen → drucken / als PDF speichern

---

## 🛠 Tech-Stack

| Bereich | Technologie |
|---|---|
| Backend | PHP 8.2+ |
| Frontend | Blade, Tailwind CSS (CDN), Alpine.js |
| Drag & Drop | SortableJS |
| Icons | Phosphor Icons |
| Datenbank | SQLite / MySQL / PostgreSQL |
