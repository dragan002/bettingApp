# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

---

## What This App Is

A private football prediction pool for ~15 people. Each gameweek every player predicts 1/X/2 for every match. Only a perfect prediction wins the jackpot; if nobody wins it rolls over. Tokens track real money managed offline by the admin. Direct APK distribution — no Play Store.

**Stack:** Laravel 13 / PHP 8.3, NativePHP Mobile v3.2, SQLite, Blade SPA, Vanilla JS, Tailwind CSS v4, Vite, Pest v4 (via PHPUnit 12).

---

## Commands

```bash
composer run setup        # First-time: install deps, create .env, migrate, build assets
composer run dev          # Start PHP server (8000) + Vite (5173) + queue + pail concurrently
composer run test         # Run full test suite (clears config first)
php artisan test --filter=TestName   # Run a single test
php artisan migrate       # Run pending migrations
vendor/bin/pint --dirty   # Fix code style (run before commit)
php artisan native:jump   # Preview on phone via Jump app
php artisan native:run android <udid> --build=debug   # Full Android build
```

After first `composer run setup`, fix `.env`:
```
APP_URL=http://localhost:8000
NATIVEPHP_APP_VERSION=1.0.0
FOOTBALL_DATA_API_KEY=your_key_here
```

---

## Architecture

### Blade SPA

The entire frontend lives in **one file**: `resources/views/welcome.blade.php`.

- Every screen is `<div id="screen-[name]" class="screen">` — toggled by `showScreen(id)`
- Screens: `login`, `home`, `predict`, `results`, `leaderboard`, `history`, `history-detail`, `admin`, `admin-players`, `admin-player-form`, `admin-rounds`, `admin-round-form`, `admin-season`
- A single global `state` object holds all app data; `localStorage` is its offline cache
- `init()` loads from `localStorage` first (immediate render), then fetches `/api/state` in background with retry loop (handles NativePHP cold-start delay)
- All UI mutations are **optimistic**: update `state` and re-render, then `api()` fires in background
- CSRF token from `<meta name="csrf-token">`, sent as `X-CSRF-TOKEN` on every fetch
- Auth: bearer token stored in `localStorage`, resolved server-side via `player_tokens` table — no Laravel sessions

### Backend

- All API routes in `routes/web.php` (never `routes/api.php`)
- Controllers in `app/Http/Controllers/Api/`
- Middleware aliases registered in `bootstrap/app.php`: `auth.token` (TokenAuth), `admin.only` (AdminOnly)
- `TokenAuth` reads `Authorization: Bearer {token}`, looks up `player_tokens`, attaches player to `$request->attributes->get('player')`
- Services bound as singletons in `AppServiceProvider::register()`
- SQLite WAL mode enabled in `AppServiceProvider::boot()`

### State Flow

`/api/state` is the master endpoint — called on login and after every admin action. Returns:
```json
{
  "player": { "id", "name", "isAdmin", "tokenBalance" },
  "season": { "id", "leagueId", "leagueName", "status", "jackpot", "entryTokens" },
  "round":  { "id", "number", "status", "locksAt", "isLocked", "fixtures": [...] },
  "predictions": { "fixtureId": "1|X|2" },
  "leaderboard": [...],
  "history": [...]
}
```
`season` and `round` are `null` when none are active. Frontend checks for null before rendering.

### Admin Workflow (in order)

1. **New Season** → POST `/api/admin/season` — ends any existing active season, creates new one
2. **New Round** → POST `/api/admin/rounds` — needs matchweek number + `locks_at` datetime
3. **Sync Fixtures** → POST `/api/admin/sync/fixtures` — calls football-data.org `/v4/competitions/{leagueId}/matches?matchday={n}`, sets round status to `active`
4. **[Round plays out]**
5. **Sync Results** → POST `/api/admin/sync/results` — fetches finished match scores
6. **Resolve Round** → POST `/api/admin/rounds/{id}/resolve` — scores predictions, updates leaderboard, awards jackpot if any perfect entries
7. **Charge Entry** → POST `/api/admin/charge-round` — deducts `entryTokens` from all complete entries, adds to jackpot

### Database

- SQLite only — `database/database.sqlite`
- No `enum` columns (use `string` with validation), no MySQL-specific types
- Never modify existing migrations — create new alter migrations
- Tests run against in-memory SQLite (configured in `phpunit.xml`)
- The APK bundles a fresh empty database. On first launch, migrations run automatically — including `2026_04_07_000010_seed_default_admin.php` which creates the admin player if `Player::count() === 0`. Season/rounds/fixtures must be set up via the admin panel on the device after install.

### Key business rules

| Table | Key constraint |
|---|---|
| `rounds` | `locks_at` timestamp — `isLocked()` returns true when past OR status is `locked`/`resolved` |
| `fixtures` | `status` = scheduled/live/finished/postponed/cancelled — postponed/cancelled excluded from scoring and prediction counts |
| `predictions` | `updateOrCreate` per player/fixture — re-checks lock inside DB transaction |
| `round_entries` | `is_complete` = true only when all non-cancelled fixtures have a pick |
| `season_points` | Leaderboard source — 1 pt per correct prediction, `rounds_played` incremented on resolve |

---

## Models

| Model | Table | Notable methods |
|---|---|---|
| `Player` | `players` | `verifyPin(string)`, `toApiArray()`, `toAdminArray()` |
| `Season` | `seasons` | `scopeActive()`, `toApiArray()` |
| `Round` | `rounds` | `isLocked()`, `activeFixtures()` (excludes postponed/cancelled), `toApiArray()` |
| `Fixture` | `fixtures` | `isActive()`, `getResult()` → `'1'|'X'|'2'|null`, `toApiArray()` |
| `Prediction` | `predictions` | `toApiArray()` |
| `RoundEntry` | `round_entries` | `toApiArray()` |
| `TokenTransaction` | `token_transactions` | `toApiArray()` |
| `SeasonPoints` | `season_points` | `toApiArray()` |
| `PlayerToken` | `player_tokens` | Auth tokens — no expiry |

---

## Services

- **`FootballDataService`** — wraps football-data.org API. `FOOTBALL_DATA_API_KEY` in `.env` is optional — if empty, requests are sent unauthenticated (100 req/day free tier). When set, `X-Auth-Token` header is added. Maps API status strings (e.g. `TIMED` → `scheduled`) and uses `shortName` for team names.
- **`RoundSyncService`** — calls `FootballDataService`, upserts fixtures by `external_id`, activates pending round when fixtures arrive
- **`RoundResolveService`** — scores predictions via `Fixture::getResult()`, updates `round_entries.points` + `is_perfect`, increments `season_points`, awards jackpot tokens to perfect predictors

---

## NativePHP / WebView Constraints

- **No browser back button** — every screen has an explicit back arrow or bottom nav
- Touch-only — 44×44px minimum targets enforced in CSS (`btn` min-height 48px)
- Offline-first — `localStorage` shown immediately on `init()`; API refresh in background
- Cold-start retry — `init()` retries `/api/state` up to 6 times with back-off (PHP server may not be ready)
- Safe areas — `env(safe-area-inset-*)` used in CSS for notched phones

---

## Android Build Critical Gotchas

See `../setup_android_lessons.md` for full details.

1. `native:install` wipes `nativephp/android/local.properties` — re-set `sdk.dir` after every install
2. `NATIVEPHP_APP_VERSION=1.0.0` in `.env` — never use `DEBUG`
3. Remove `URL::forceHttps()` from `AppServiceProvider::boot()` if present
4. Composer build timeout is 300s — change to 900s in `vendor/nativephp/mobile/src/Traits/PreparesBuild.php` after every `composer update`
5. Run `native:run android` before Gradle — it substitutes `REPLACE_APP_ID` and other placeholders
6. `ANDROID_HOME`: `export ANDROID_HOME="C:/Users/pclogiklabs/AppData/Local/Android/Sdk"`
