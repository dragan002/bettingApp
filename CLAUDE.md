# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

---

## What This App Is

A private football prediction pool for ~15 people. Each gameweek every player predicts 1/X/2 for every match. Only a perfect prediction wins the jackpot; if nobody wins it rolls over. Tokens track real money managed offline by the admin (1 token = 1 KM).

**Deployment:** Shared Laravel server (Railway.app) — all players connect via browser. NativePHP Android APK also exists for local/single-device use, but the server deployment is the intended multi-player path.

**Stack:** Laravel 13 / PHP 8.4, PostgreSQL (production) / SQLite (local dev), Blade SPA, Vanilla JS, Tailwind CSS v4, Vite, Pest v4 (via PHPUnit 12).

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
php artisan fixtures:cleanup-duplicates            # Remove football-data.org fixtures when a round also has FlashScore fixtures
php artisan fixtures:cleanup-duplicates --dry-run  # Preview what would be deleted
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

## Design System

**Theme:** Kafana — warm parchment café board aesthetic. All colors are CSS custom properties defined in `resources/css/app.css`. **Never use hardcoded hex colors in new HTML or JS template strings — always use CSS vars.**

```css
--bg: #f0e6d2       /* parchment */
--bg-deep: #e6d9bb
--surface: #fdf6e6
--surface-alt: #ebdfc4
--ink: #1f1810      /* espresso — primary text */
--ink-soft: #5a4938
--ink-faint: #8a7860
--rule: #3a2818     /* border color everywhere */
--accent: #a8341a   /* oxblood — CTAs, active states */
--accent-deep: #7a2412
--gold: #b08a3e
--green: #2e5d3a    /* correct predictions, positive balances */
--blue: #1d3557
--win: #2e5d3a      /* correct pick */
--lose: #a8341a     /* wrong pick, negative balance */
--draw: #b08a3e
```

**Fonts** (loaded from Google Fonts):
- `'Fraunces', 'Times New Roman', serif` — headers, big numbers, pick buttons
- `'DM Mono', ui-monospace, monospace` — labels, eyebrows, mono values
- `'Inter', system-ui, sans-serif` — body text

**Design prototype** lives at `../design/` (parent repo, not in git). The `src/` subfolder has the original React/JSX prototype screens — useful reference when building new screens or components.

**Key CSS classes:** `.card`, `.btn` / `.btn-primary` / `.btn-secondary` / `.btn-sm`, `.input`, `.field-label`, `.pick-btn` (with `.selected` / `.correct` / `.wrong`), `.fixture-card`, `.balance-pill`, `.badge-kf` (with colour variants), `.screen-header`, `.back-btn`, `.stamp`, `.lb-row`.

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

**Login PIN numpad:** The login screen uses a visual numpad (not a password input). `_pinValue` (module-level string) is managed by `pinTap(d)` / `pinDel()` / `syncPin()` / `resetPin()`. `syncPin()` writes to the hidden `#login-pin` input and updates the four `.pin-dot` boxes. The existing `doLogin()` reads `#login-pin` normally — the numpad is purely a UI layer on top.

### Backend

- All API routes in `routes/web.php` (never `routes/api.php`)
- Controllers in `app/Http/Controllers/Api/`
- Middleware aliases registered in `bootstrap/app.php`: `auth.token` (TokenAuth), `admin.only` (AdminOnly)
- `TokenAuth` reads `Authorization: Bearer {token}`, looks up `player_tokens`, attaches player to `$request->attributes->get('player')`
- Services bound as singletons in `AppServiceProvider::register()`
- SQLite WAL mode enabled in `AppServiceProvider::boot()` — guarded, no-op in production (PostgreSQL)

### State Flow

`/api/state` is the master endpoint — called on login and after every admin action. Returns:

> **Round selection logic in `StateController`:** queries `whereIn('status', ['pending', 'active', 'locked'])` ordered by status priority (`active` → `pending` → `locked`), then by round number descending. This means a newly created `pending` round (no fixtures yet) IS returned and visible. The admin sync buttons only appear when `state.round` is non-null — if the round is `pending` and has no fixtures, the admin can still click sync.
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
2. **Fixtures sync** (daily 08:00 UTC, via `schedule:work`) → `RoundSyncService::syncFixtures()` upserts fixtures, activates pending round, updates `locks_at`. If football-data.org returns empty, FlashScore is tried as fallback (deletes any existing football-data fixtures first to avoid duplication).
3. **Results sync** (every 3 hours) → `RoundSyncService::syncResults()` updates fixture scores, then calls `maybeAutoResolve()`.
4. **Auto-resolve** → when all non-cancelled/postponed fixtures are `finished`, calls `RoundResolveService::resolve()` then `createNextRound()` (which fetches fixtures for the next matchday). Errors are caught and logged — auto-resolve failure does not crash the sync.
5. **Entry charge** — two paths: auto-charged via `PredictionController` when a player's entry becomes complete; or manually via `AdminChargeController`. Both have `alreadyCharged` guards.
6. **Database backup** (daily 03:00 UTC) → `backup:database` command backs up to Cloudflare R2 bucket `bettingapp-backups`, keeps last 30 daily backups. Implemented in `app/Console/Commands/BackupDatabase.php`. Note: was written for SQLite — may need updating for PostgreSQL dump format.

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
| POST | `/api/admin/rounds/{id}/reset` | `AdminRoundController::reset` — deletes all fixtures/predictions/entries, re-syncs |
| DELETE | `/api/admin/rounds/{id}` | `AdminRoundController::destroy` — deletes round; reverses season_points if resolved |
| POST | `/api/admin/sync/fixtures` | `AdminSyncController::syncFixtures` |
| POST | `/api/admin/sync/results` | `AdminSyncController::syncResults` |
| POST | `/api/admin/charge-round` | `AdminChargeController::charge` |
| GET/POST | `/api/admin/season/settlements` | `AdminSettlementController` |

### Database

- **Production:** PostgreSQL on Railway (managed service, persists across deployments). `DB_CONNECTION=pgsql`, `DATABASE_URL` injected automatically by Railway.
- **Local dev:** SQLite — `database/database.sqlite`. Never change `DB_CONNECTION` locally.
- No `enum` columns (use `string` with validation), no MySQL/SQLite-specific types or raw SQL
- Never modify existing migrations — create new alter migrations
- Tests run against in-memory SQLite (configured in `phpunit.xml`)
- `2026_04_07_000010_seed_default_admin.php` creates the default admin player if `Player::count() === 0` — runs once on fresh database

### Key business rules

| Table | Key constraint |
|---|---|
| `rounds` | `locks_at` timestamp — `isLocked()` returns true when past OR status is `locked`/`resolved` |
| `fixtures` | `status` = scheduled/live/finished/postponed/cancelled — postponed/cancelled/finished excluded from the predict screen; postponed/cancelled excluded from scoring |
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
- **`FlashScoreService`** — wraps RapidAPI FlashScore4 (`flashscore4.p.rapidapi.com`). `FLASHSCORE_API_KEY` required. `LEAGUE_MAP` const maps football-data league codes (e.g. `'PL'`) to FlashScore `template_id`/`season_id`. Key methods: `getNextMatchdayFixtures()` — detects matchday boundary by first repeated team; `getRecentResultsMap()` — fetches up to 2 pages of results keyed by `match_id`; `mapMatchToFixture()` — prefixes `external_id` with `fs_`. `isConfigured()` gates all calls.
- **`RoundSyncService`** — primary sync orchestrator. `syncFixtures()`: calls FootballDataService first; if empty AND FlashScore is configured, deletes existing football-data fixtures for the round and uses FlashScore instead. `syncResults()`: uses FlashScore path when any fixture has `fs_` external_id. Activates pending round and sets `locks_at` from earliest kickoff. `maybeAutoResolve()` triggers when all non-postponed/cancelled fixtures are finished.
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

Deploys via **Dockerfile** (takes precedence over nixpacks.toml). The Dockerfile CMD runs `config:clear`, `package:discover`, `migrate --force`, then starts `schedule:work` (background) and `artisan serve`.

Required environment variables:

```
APP_ENV=production
APP_KEY=base64:...
APP_URL=https://bettingapp-production-745e.up.railway.app
DB_CONNECTION=pgsql
DATABASE_URL=postgresql://...       # auto-injected by Railway PostgreSQL service
SESSION_DRIVER=cookie
QUEUE_CONNECTION=sync
FOOTBALL_DATA_API_KEY=...
FLASHSCORE_API_KEY=...          # RapidAPI key for FlashScore4 fallback (flashscore4.p.rapidapi.com)
R2_ACCESS_KEY_ID=...
R2_SECRET_ACCESS_KEY=...
R2_ENDPOINT=https://<account-id>.r2.cloudflarestorage.com
R2_BUCKET=bettingapp-backups
```

- PostgreSQL is a **separate** Railway service — data persists independently of app deployments. The filesystem is ephemeral; never store state on disk.
- `DATABASE_URL` must be copied from the PostgreSQL service into the **bettingApp service variables** manually — Railway does not share env vars between services automatically.
- To run a one-off artisan command: use Railway CLI `railway run php artisan <command>`
- Daily database backup to Cloudflare R2 still runs via scheduler (`backup:database` command) — update it if PostgreSQL backup strategy changes

### football-data.org API
- Free tier: **100 requests/day**. The scheduler (fixtures daily 08:00 UTC, results every 3h) consumes these — manual sync during heavy debug sessions can exhaust the quota.
- `ConnectionException` (timeout) is caught in `FootballDataService::getMatches()` and `getFinishedMatches()` — returns `[]` with a warning log instead of crashing. `getCurrentMatchday()` (called at season creation) is **not** wrapped and will 500 if the API times out.
- `RoundSyncService::syncFixtures()` explicitly calls `$round->load('season')` to guarantee the relation is hydrated regardless of the caller's eager-loading.

### FlashScore fallback
- Kicks in when football-data.org returns empty for `syncFixtures()` or `syncResults()`.
- Fixtures get `external_id` prefixed with `fs_` (e.g. `fs_abc123`). This prefix is how the system identifies FlashScore-sourced fixtures throughout.
- **Duplication risk**: if a round was previously synced via football-data.org and then re-synced via FlashScore, both sets coexist (different `external_id` → different rows). Fix: `php artisan fixtures:cleanup-duplicates` removes the football-data.org rows when both exist. The Admin UI also has **Reset & Re-sync** (Admin → round card) and **Delete** buttons for manual recovery.

---

## Android / Play Store

The Android app is a **TWA (Trusted Web Activity)** — generated by PWABuilder from the Railway URL. It is not a NativePHP build.

- PWA manifest: `public/manifest.json`
- Service worker: `public/sw.js`
- Domain verification: `public/.well-known/assetlinks.json`
- Play Store package: `app.railway.up.bettingapp_production_745e.twa`
- Signing keystore: `../playstore/signing.keystore` — required for every update

To publish an update to Play Store: go to PWABuilder, use existing keystore, increment version code, upload new `.aab` to Play Console.
