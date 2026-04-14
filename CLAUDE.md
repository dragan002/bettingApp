# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

---

## What This App Is

A private football prediction pool for ~15 people. Each gameweek every player predicts 1/X/2 for every match. Only a perfect prediction wins the jackpot; if nobody wins it rolls over. Tokens track real money managed offline by the admin (1 token = 1 KM).

**Deployment:** Shared Laravel server (Railway.app) — all players connect via browser. NativePHP Android APK also exists for local/single-device use, but the server deployment is the intended multi-player path.

**Stack:** Laravel 13 / PHP 8.4, NativePHP Mobile v3.2, SQLite, Blade SPA, Vanilla JS, Tailwind CSS v4, Vite, Pest v4 (via PHPUnit 12).

---

## Commands

```bash
composer run setup        # First-time: install deps, create .env, migrate, build assets
composer run dev          # Start PHP server (8000) + Vite (5173) + queue + pail concurrently
composer run test         # Run full test suite (clears config first)
php artisan test --filter=TestName   # Run a single test
php artisan migrate       # Run pending migrations
vendor/bin/pint --dirty   # Fix code style (run before commit)
php artisan backup:database   # Manual backup to Cloudflare R2 (runs automatically at 03:00 UTC)
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
- Screens: `login`, `home`, `predict`, `results`, `leaderboard`, `history`, `history-detail`, `admin`, `admin-players`, `admin-player-form`, `admin-rounds`, `admin-round-form`, `admin-season`, `balances`, `ledger`, `badges`, `hall-of-fame`, `admin-settlement`, `admin-credit`
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
  "player": { "id", "name", "nickname", "displayName", "isAdmin", "tokenBalance" },
  "season": { "id", "leagueId", "leagueName", "status", "jackpot", "entryTokens", "isPendingSettlement" },
  "round":  { "id", "number", "status", "locksAt", "isLocked", "fixtures": [...] },
  "predictions": { "fixtureId": "1|X|2" },
  "leaderboard": [{ "playerId", "displayName", "points", "roundsPlayed", "badgeCount", "streaks": { "onFire", "cold", "ironMan", "perfectRounds" } }],
  "history": [...],
  "balances": [{ "id", "displayName", "tokenBalance" }]
}
```
`season` and `round` are `null` when none are active. `isPendingSettlement` is true when season status is `pending_settlement` — this blocks new season creation until admin settles all balances.

### Admin Workflow (in order)

1. **New Season** → POST `/api/admin/season` — ends any existing active season, auto-creates first round via `getCurrentMatchday()` + `syncFixtures()`. Blocked if any season is `pending_settlement`.
2. **[Fixtures auto-arrive]** — `syncFixtures()` activates the `pending` round and auto-sets `locks_at` from earliest fixture kickoff. No manual round form needed for the first round.
3. **[Round plays out]** — Results sync runs every 3 hours via scheduler. When all active fixtures are `finished`, `maybeAutoResolve()` fires automatically.
4. **Auto-resolve** → `RoundResolveService::resolve()` — scores predictions, writes `SeasonRoundPoints`, awards badges via `BadgeService`, awards jackpot if any perfect entries. Then auto-creates next round.
5. **Charge Entry** → POST `/api/admin/charge-round` — deducts `entryTokens` from all complete entries, adds to jackpot. Has `alreadyCharged` deduplication guard (same as prediction submit flow). Entry fee is also auto-charged when a player completes predictions via `PredictionController`.
6. **[If jackpot won mid-season]** → POST `/api/admin/season/pending-settlement` → admin settles each player via POST `/api/admin/season/settlements/{playerId}` → POST `/api/admin/season/close` — writes Hall of Fame, sets season to `ended`

### Token Ledger Rules

- 1 token = 1 KM — admin credits players manually via POST `/api/admin/players/{id}/credit`
- Every `TokenTransaction` records `balance_before` and `balance_after` (nullable on legacy rows pre-2026-04-08)
- Valid `TokenTransaction::TYPES`: `credit`, `debit_round`, `payout_jackpot`, `payout_season_winner`, `settlement_refund`, `settlement_collected`, `adjustment`. Never use `'debit'` — use `'adjustment'` for admin manual edits.
- Debt cap: players with `token_balance <= -(entry_tokens * 3)` cannot submit predictions (HTTP 422, `debtCapExceeded: true`). Admin can set negative balances directly (no `min:0` validation).
- Settlement flow: when jackpot won mid-season, season enters `pending_settlement`. Admin must settle every player (zeroes their balance, writes a `settlement_refund` or `settlement_collected` transaction). Only then can season be closed and a new one started.

### Badge & Streak System

**BadgeService** (`awardBadges(Round, Season)`) — called at end of every round resolution. Evaluates 6 categories × 3 tiers from `SeasonRoundPoints`:
- `sniper`: rounds with high points (kafa=2×4+, rakija=3×6+, zlato=3×8+)
- `perfectionist`: perfect rounds (kafa=1, rakija=2, zlato=3)
- `iron_man`: max consecutive rounds submitted (kafa=3, rakija=5, zlato=8)
- `comeback_kid`: ≤2 pts round followed by ≥5 pts round (kafa=1×, rakija=2×, zlato=3×)
- `jackpot`: `payout_jackpot` transactions across ALL seasons (kafa=1, rakija=2, zlato=3)
- `ledeni`: cold rounds ≤2 pts this season (kafa=6, rakija=9, zlato=12)

Tiers: `kafa` (1) < `rakija` (2) < `zlato` (3). Only upgrades — never downgrades. One row per `(player_id, season_id, category)`.

**StreakService** (`computeForSeason(int $seasonId)`) — loads all `SeasonRoundPoints` in 2 queries, groups in PHP. Returns array keyed by `player_id` with: `onFire` (consecutive rounds ≥5pts from most recent), `cold` (consecutive rounds ≤2pts), `ironMan` (consecutive rounds submitted), `perfectRounds` (total count). Used by `StateController` to extend leaderboard entries.

### Auto Round Lifecycle

The system is mostly hands-off after a season is created:

1. **Season created** → `AdminSeasonController::store()` calls `FootballDataService::getCurrentMatchday()` + `RoundSyncService::syncFixtures()`. Round created as `pending`, activated and `locks_at` set when fixtures arrive.
2. **Fixtures sync** (daily 08:00 UTC, via `schedule:work`) → `RoundSyncService::syncFixtures()` upserts fixtures, activates pending round, updates `locks_at`.
3. **Results sync** (every 3 hours) → `RoundSyncService::syncResults()` updates fixture scores, then calls `maybeAutoResolve()`.
4. **Auto-resolve** → when all non-cancelled/postponed fixtures are `finished`, calls `RoundResolveService::resolve()` then `createNextRound()` (which fetches fixtures for the next matchday). Errors are caught and logged — auto-resolve failure does not crash the sync.
5. **Entry charge** — two paths: auto-charged via `PredictionController` when a player's entry becomes complete; or manually via `AdminChargeController`. Both have `alreadyCharged` guards.
6. **Database backup** (daily 03:00 UTC) → `backup:database` command copies `database.sqlite` to Cloudflare R2 bucket `bettingapp-backups`, keeps last 30 daily backups. Implemented in `app/Console/Commands/BackupDatabase.php`, uses the `r2` disk in `config/filesystems.php`.

### API Routes (all in `routes/web.php`)

| Method | Path | Controller |
|---|---|---|
| GET | `/api/state` | `StateController::index` |
| POST | `/api/predictions` | `PredictionController::store` |
| GET | `/api/round/{id}/results` | `ResultsController::show` |
| GET | `/api/players/balances` | `PlayerBalancesController::index` |
| GET | `/api/players/{id}/ledger` | `PlayerLedgerController::show` |
| GET | `/api/players/{id}/badges` | `PlayerBadgesController::show` |
| GET | `/api/rounds/{id}/pot` | `RoundPotController::show` |
| GET | `/api/hall-of-fame` | `HallOfFameController::index` |
| POST | `/api/admin/season` | `AdminSeasonController::store` |
| POST | `/api/admin/season/pending-settlement` | `AdminSeasonController::pendingSettlement` |
| POST | `/api/admin/season/close` | `AdminSeasonController::close` |
| GET/POST | `/api/admin/players` | `AdminPlayerController` |
| PUT | `/api/admin/players/{id}` | `AdminPlayerController::update` |
| PUT | `/api/admin/players/{id}/nickname` | `AdminPlayerController::updateNickname` |
| POST | `/api/admin/players/{id}/credit` | `AdminTokenCreditController::store` |
| GET/POST | `/api/admin/rounds` | `AdminRoundController` |
| PUT | `/api/admin/rounds/{id}` | `AdminRoundController::update` |
| POST | `/api/admin/rounds/{id}/resolve` | `AdminRoundController::resolve` |
| POST | `/api/admin/sync/fixtures` | `AdminSyncController::syncFixtures` |
| POST | `/api/admin/sync/results` | `AdminSyncController::syncResults` |
| POST | `/api/admin/charge-round` | `AdminChargeController::charge` |
| GET/POST | `/api/admin/season/settlements` | `AdminSettlementController` |

### Database

- SQLite only — `database/database.sqlite`
- No `enum` columns (use `string` with validation), no MySQL-specific types
- Never modify existing migrations — create new alter migrations
- Tests run against in-memory SQLite (configured in `phpunit.xml`)
- The APK bundles a fresh empty database. On first launch, migrations run automatically — including `2026_04_07_000010_seed_default_admin.php` which creates the admin player if `Player::count() === 0`.

### Key business rules

| Table | Key constraint |
|---|---|
| `rounds` | `locks_at` timestamp — `isLocked()` returns true when past OR status is `locked`/`resolved` |
| `fixtures` | `status` = scheduled/live/finished/postponed/cancelled — postponed/cancelled excluded from scoring |
| `predictions` | `updateOrCreate` per player/fixture — re-checks lock inside DB transaction |
| `round_entries` | `is_complete` = true only when all non-cancelled fixtures have a pick |
| `season_points` | Leaderboard source — 1 pt per correct prediction, `rounds_played` incremented on resolve |
| `season_round_points` | Per-round points snapshot written at resolve — source of truth for BadgeService and StreakService |
| `player_badges` | UNIQUE `(player_id, season_id, category)` — one badge per category, upgrade-only |
| `season_settlements` | UNIQUE `(season_id, player_id)` — one row per player per settled season |

---

## Models

| Model | Table | Notable methods / constants |
|---|---|---|
| `Player` | `players` | `verifyPin(string)`, `displayName()` → nickname ?? name, `toApiArray()`, `toAdminArray()` |
| `Season` | `seasons` | `scopeActive()`, `toApiArray()` — status: `active` \| `pending_settlement` \| `ended`. `toApiArray()` always includes `isPendingSettlement`. |
| `Round` | `rounds` | `isLocked()`, `activeFixtures()` (excludes postponed/cancelled), `toApiArray()` |
| `Fixture` | `fixtures` | `isActive()`, `getResult()` → `'1'\|'X'\|'2'\|null`, `toApiArray()` |
| `Prediction` | `predictions` | `toApiArray()` |
| `RoundEntry` | `round_entries` | `toApiArray()` |
| `TokenTransaction` | `token_transactions` | `TYPES` const, `toApiArray()` — includes `balanceBefore`, `balanceAfter`, `roundId` |
| `SeasonPoints` | `season_points` | `toApiArray()` — includes `displayName` via player relation |
| `SeasonRoundPoints` | `season_round_points` | Per-round points snapshot per player — streak/badge engine input |
| `PlayerBadge` | `player_badges` | `CATEGORIES` const, `TIERS` const (`kafa`=1, `rakija`=2, `zlato`=3), `toApiArray()` |
| `SeasonSettlement` | `season_settlements` | `toApiArray()` — includes `displayName` via player relation |
| `SeasonHallOfFame` | `season_hall_of_fame` | Named relationships: `jackpotWinner()`, `leaderboardWinner()`, `playerOfSeason()`, `toApiArray()` |
| `PlayerToken` | `player_tokens` | Auth tokens — no expiry |

---

## Services

- **`FootballDataService`** — wraps football-data.org API. `FOOTBALL_DATA_API_KEY` optional — unauthenticated if absent (100 req/day). Maps API status strings and uses `shortName` for team names.
- **`RoundSyncService`** — calls `FootballDataService`, upserts fixtures by `external_id`, activates pending round when fixtures arrive.
- **`RoundResolveService`** — scores predictions, updates `round_entries.points` + `is_perfect`, increments `season_points`, awards jackpot, writes `SeasonRoundPoints` rows, calls `BadgeService::awardBadges()`.
- **`BadgeService`** — evaluates all 6 badge categories from `SeasonRoundPoints`, upgrade-only upsert into `player_badges`. Called after every round resolution.
- **`StreakService`** — computes live streaks for all players in a season from `SeasonRoundPoints`. 2 queries + PHP grouping. Called by `StateController`.

---

## NativePHP / WebView Constraints

- **No browser back button** — every screen has an explicit back arrow or bottom nav
- Touch-only — 44×44px minimum targets enforced in CSS (`btn` min-height 48px)
- Offline-first — `localStorage` shown immediately on `init()`; API refresh in background
- Cold-start retry — `init()` retries `/api/state` up to 6 times with back-off (PHP server may not be ready)
- Safe areas — `env(safe-area-inset-*)` used in CSS for notched phones

---

## Multi-Tenancy (Not Yet Built)

Currently **single-tenant** — one database, one shared pool per deployment. Two paths discussed:
- **Multiple Railway deployments** — one per group, no code changes
- **Multi-tenancy** — add `groups` table, scope all data through `$player->group_id`, enforce in `TokenAuth` middleware

---

## Railway Deployment

Deploys via `Dockerfile` using PHP 8.4-cli. Required environment variables:

```
APP_ENV=production
APP_KEY=base64:...
APP_URL=https://your-railway-url.up.railway.app
DB_CONNECTION=sqlite
DB_DATABASE=/data/database.sqlite   # persistent volume at /data
SESSION_DRIVER=file
QUEUE_CONNECTION=sync
FOOTBALL_DATA_API_KEY=...
R2_ACCESS_KEY_ID=...                # Cloudflare R2 API token Access Key ID
R2_SECRET_ACCESS_KEY=...            # Cloudflare R2 API token Secret Access Key
R2_ENDPOINT=https://<account-id>.r2.cloudflarestorage.com
R2_BUCKET=bettingapp-backups
```

- SQLite lives on a Railway persistent volume — not inside the container
- `composer install --no-scripts` at build time; `package:discover` runs at startup
- **Do not set a Custom Start Command in Railway** — the Dockerfile CMD handles everything (migrations, cache warm, scheduler, web server)
- To run a one-off artisan command on Railway: use `railway run php artisan <command>` via the Railway CLI

---

## Android Build Critical Gotchas

See `../setup_android_lessons.md` for full details.

1. `native:install` wipes `nativephp/android/local.properties` — re-set `sdk.dir` after every install
2. `NATIVEPHP_APP_VERSION=1.0.0` in `.env` — never use `DEBUG`
3. Remove `URL::forceHttps()` from `AppServiceProvider::boot()` if present
4. Composer build timeout is 300s — change to 900s in `vendor/nativephp/mobile/src/Traits/PreparesBuild.php` after every `composer update`
5. Run `native:run android` before Gradle — it substitutes `REPLACE_APP_ID` and other placeholders
6. `ANDROID_HOME`: `export ANDROID_HOME="C:/Users/pclogiklabs/AppData/Local/Android/Sdk"`
7. NativePHP APK has isolated SQLite per device — not suitable for multi-player use. Use Railway for shared gameplay.
