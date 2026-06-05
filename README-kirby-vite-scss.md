# Kirby 5 + Vite + SCSS

Minimales Grundsetup fuer ein Kirby-Projekt mit Vite und SCSS.

## Enthalten

- Kirby 5 via Composer
- Vite als Dev-Server und Build-Tool
- SCSS als zentraler Stylesheet-Entry
- einfache Kirby-Anbindung fuer Dev und Production-Build

## Installation

### 1. PHP-Abhaengigkeiten installieren

```bash
cd "/Users/jonasholfeld/workspace/26/KV Heilbronn/website"
composer install
```

### 2. Node-Abhaengigkeiten installieren

```bash
npm install
```

### 3. Kirby lokal starten

Zum Beispiel mit dem PHP-Entwicklungsserver:

```bash
php -S 127.0.0.1:8000 kirby/router.php
```

### 4. Vite starten

In einem zweiten Terminal:

```bash
npm run dev
```

Dann laeuft:

- Kirby unter `http://127.0.0.1:8000`
- Vite unter `http://127.0.0.1:5173`

## Production-Build

```bash
npm run build
```

Die gebauten Assets landen in:

```text
assets/dist
```

## Projektstruktur

- `site/snippets/vite.php`: bindet Vite im Dev-Modus oder den Build im Production-Modus ein
- `src/js/main.js`: JS-Einstiegspunkt
- `src/scss/main.scss`: SCSS-Einstiegspunkt
- `vite.config.js`: Vite-Konfiguration

## Hinweis

Der Check in `site/snippets/vite.php` ist bewusst minimal gehalten.
Wenn wir weitermachen, koennen wir daraus im naechsten Schritt ein etwas robusteres Boilerplate machen, zum Beispiel mit:

- mehreren Entry Points
- TypeScript
- View Transitions
- sauberem Asset-Helper
- SVG-Handling
- Bildkomponenten
