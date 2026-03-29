# ⏱ ZeitManager – Laravel Zeitmanagement-Tool

Freiberufler-Tool zur Verwaltung von Kunden, Projekten, Arbeitszeiten und Rechnungen.

## Features

- **Kunden** mit Lieferadresse verwalten
- **Projekte** anlegen und Kunden zuweisen (inkl. projektspezifischem Stundensatz)
- **Arbeitskategorien** (z.B. Entwicklung, Support, Design)
- **Zeiterfassung** pro Projekt und Kategorie mit Filterung
- **Ausgaben** erfassen (Fahrtkosten, Lizenzen etc.)
- **Rechnungen** erstellen – projektübergreifend, Positionen nach Kategorie gruppiert
- Rechnung als **druckbares PDF** ausgeben (Browser-Druck)
- **Einstellungen**: eigene Adresse, Stundenlohn, Bankdaten, Rechnungsnummernkreis

## Setup

### Voraussetzungen

- PHP 8.2+
- Composer
- Node.js & npm
- SQLite oder MySQL/PostgreSQL

### Installation

```bash
# 1. Abhängigkeiten installieren
composer install
npm install

# 2. Umgebungsdatei anlegen
cp .env.example .env
php artisan key:generate

# 3. Datenbank konfigurieren (.env anpassen)
# Für SQLite (Standard):
# DB_CONNECTION=sqlite
# DB_DATABASE=/absoluter/pfad/zur/database/database.sqlite
touch database/database.sqlite

# Für MySQL:
# DB_CONNECTION=mysql
# DB_HOST=127.0.0.1
# DB_PORT=3306
# DB_DATABASE=zeitmanagement
# DB_USERNAME=root
# DB_PASSWORD=secret

# 4. Migrationen ausführen & Standarddaten laden
php artisan migrate --seed

# 5. Frontend-Assets bauen
npm run build

# 6. Server starten
php artisan serve
```

Anschließend unter **http://localhost:8000** aufrufen und zuerst die **Einstellungen** mit deinen Daten befüllen.

## Datenbankstruktur

| Tabelle | Beschreibung |
|---|---|
| `settings` | Key-Value-Einstellungen (Adresse, Stundenlohn, etc.) |
| `customers` | Kundenstamm inkl. Lieferadresse |
| `work_categories` | Arbeitskategorien mit Farbe |
| `projects` | Projekte, einem Kunden zugeordnet |
| `time_entries` | Zeiteinträge (Projekt + Kategorie + Stunden) |
| `expenses` | Projektausgaben |
| `invoices` | Rechnungen |
| `invoice_time_entry` | Pivot: Rechnung ↔ Zeiteinträge |
| `invoice_expense` | Pivot: Rechnung ↔ Ausgaben |

## Workflow

1. **Einstellungen** einrichten (Adresse, Stundenlohn, Bankdaten)
2. **Kunden** anlegen
3. **Projekte** erstellen und Kunden zuweisen
4. **Arbeitszeiten** und **Ausgaben** erfassen
5. **Rechnung** erstellen → Kunde wählen → Positionen auswählen → fertig
6. Rechnung **drucken** (Browser → PDF speichern)

## Technologie

- Laravel 12
- Blade Templates
- Tailwind CSS (CDN)
- Alpine.js (für reaktive Rechnungserstellung)
- SQLite / MySQL

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework. You can also check out [Laravel Learn](https://laravel.com/learn), where you will be guided through building a modern Laravel application.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the [Laravel Partners program](https://partners.laravel.com).

### Premium Partners

- **[Vehikl](https://vehikl.com)**
- **[Tighten Co.](https://tighten.co)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel)**
- **[DevSquad](https://devsquad.com/hire-laravel-developers)**
- **[Redberry](https://redberry.international/laravel-development)**
- **[Active Logic](https://activelogic.com)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
