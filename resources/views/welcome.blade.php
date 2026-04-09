<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="#0f172a">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Tipping Pool</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
<div id="app">

    {{-- Loading overlay --}}
    <div id="loading">
        <div style="font-size:28px;font-weight:700;color:#22c55e;letter-spacing:-1px;">⚽ Tipping Pool</div>
        <div class="spinner" style="margin-top:16px;"></div>
    </div>

    {{-- Toast --}}
    <div id="toast"></div>

    {{-- =========================================================
         SCREEN: Login
         ========================================================= --}}
    <div id="screen-login" class="screen">
        <div class="screen-content" style="display:flex;flex-direction:column;justify-content:center;min-height:100%;padding:32px 24px;">
            <div style="text-align:center;margin-bottom:48px;">
                <div style="font-size:48px;margin-bottom:12px;">⚽</div>
                <h1 style="font-size:28px;font-weight:700;color:#f1f5f9;letter-spacing:-0.5px;">Tipping Pool</h1>
                <p style="color:#94a3b8;margin-top:6px;font-size:15px;">Predict. Win. Repeat.</p>
            </div>
            <div style="display:flex;flex-direction:column;gap:16px;max-width:360px;margin:0 auto;width:100%;">
                <div>
                    <label style="display:block;font-size:13px;font-weight:600;color:#94a3b8;margin-bottom:8px;text-transform:uppercase;letter-spacing:0.5px;">Player Name</label>
                    <input id="login-name" class="input" type="text" placeholder="Enter your name" autocomplete="off" autocapitalize="words">
                </div>
                <div>
                    <label style="display:block;font-size:13px;font-weight:600;color:#94a3b8;margin-bottom:8px;text-transform:uppercase;letter-spacing:0.5px;">PIN</label>
                    <input id="login-pin" class="input" type="password" placeholder="Enter your PIN" maxlength="8" inputmode="numeric">
                </div>
                <button id="login-btn" class="btn btn-primary btn-full btn-lg" style="margin-top:8px;">Sign In</button>
                <div id="login-error" style="display:none;text-align:center;color:#ef4444;font-size:14px;"></div>
            </div>
        </div>
    </div>

    {{-- =========================================================
         SCREEN: Home
         ========================================================= --}}
    <div id="screen-home" class="screen">
        <div class="screen-content" style="padding:0 0 80px;">
            <div class="safe-top" style="padding:20px 20px 16px;display:flex;align-items:center;justify-content:space-between;border-bottom:1px solid #334155;">
                <div>
                    <div style="font-size:13px;color:#94a3b8;font-weight:500;">Welcome back</div>
                    <div id="home-player-name" style="font-size:20px;font-weight:700;color:#f1f5f9;"></div>
                </div>
                <div style="text-align:right;cursor:pointer;" onclick="logout()">
                    <div style="font-size:12px;color:#94a3b8;">Tokens</div>
                    <div id="home-token-balance" style="font-size:22px;font-weight:700;color:#22c55e;"></div>
                </div>
            </div>

            <div style="padding:16px 16px 0;">
                <div class="card" style="text-align:center;margin-bottom:16px;background:linear-gradient(135deg,#1e293b,#263347);border-color:#22c55e44;">
                    <div style="font-size:12px;font-weight:600;color:#94a3b8;text-transform:uppercase;letter-spacing:1px;margin-bottom:8px;">Current Jackpot</div>
                    <div id="home-jackpot" style="font-size:48px;font-weight:700;color:#22c55e;line-height:1;">0</div>
                    <div style="font-size:13px;color:#94a3b8;margin-top:6px;">tokens</div>
                    <div id="home-league" style="font-size:13px;color:#64748b;margin-top:8px;"></div>
                </div>

                <div id="home-round-card" class="card" style="margin-bottom:16px;">
                    <div id="home-no-season" style="display:none;text-align:center;padding:16px 0;">
                        <div style="font-size:32px;margin-bottom:8px;">🏆</div>
                        <div style="color:#94a3b8;">No active season. Check back soon!</div>
                    </div>
                    <div id="home-no-round" style="display:none;text-align:center;padding:16px 0;">
                        <div style="font-size:32px;margin-bottom:8px;">⏳</div>
                        <div style="color:#94a3b8;">No active round. Admin will open one soon!</div>
                    </div>
                    <div id="home-round-info" style="display:none;">
                        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;">
                            <div>
                                <div style="font-size:13px;color:#94a3b8;">Matchweek</div>
                                <div id="home-round-number" style="font-size:24px;font-weight:700;color:#f1f5f9;"></div>
                            </div>
                            <div id="home-round-status-badge"></div>
                        </div>
                        <div id="home-locks-at" style="font-size:13px;color:#94a3b8;margin-bottom:6px;"></div>
                        <div id="home-countdown" style="font-size:22px;font-weight:700;color:#f59e0b;margin-bottom:8px;display:none;font-variant-numeric:tabular-nums;"></div>
                        <div id="home-completion-counter" style="font-size:13px;color:#64748b;margin-bottom:12px;display:none;"></div>
                        <div id="home-prediction-status"></div>
                        <button id="home-predict-btn" class="btn btn-primary btn-full" style="margin-top:12px;display:none;">Make Predictions →</button>
                    </div>
                </div>

                {{-- Streak Highlight --}}
                <div id="home-streak-highlight" style="display:none;padding:0 0 0;"></div>

                {{-- Quick Nav --}}
                <div style="display:flex;gap:10px;margin-bottom:16px;">
                    <button class="btn btn-secondary" style="flex:1;" onclick="loadBalances()">💰 Balances</button>
                    <button class="btn btn-secondary" style="flex:1;" onclick="loadHallOfFame()">🏛️ Hall of Fame</button>
                </div>

                {{-- My Ticket --}}
                <div id="home-ticket" style="display:none;margin-bottom:16px;">
                    <div class="card" style="padding:0;overflow:hidden;">
                        <div style="display:flex;align-items:center;justify-content:space-between;padding:14px 16px;cursor:pointer;" onclick="toggleTicket()">
                            <div style="display:flex;align-items:center;gap:8px;">
                                <span style="font-size:16px;">🎫</span>
                                <span style="font-size:15px;font-weight:700;color:#f1f5f9;">My Ticket</span>
                                <span id="home-ticket-count" class="badge badge-green" style="font-size:12px;"></span>
                            </div>
                            <svg id="home-ticket-chevron" viewBox="0 0 24 24" fill="none" stroke="#64748b" stroke-width="2" width="18" height="18" style="transition:transform 0.2s ease;"><path d="M6 9l6 6 6-6"/></svg>
                        </div>
                        <div id="home-ticket-body" style="display:none;border-top:1px solid #334155;">
                            <div id="home-ticket-rows" style="padding:8px 0;"></div>
                            <div style="padding:12px 16px;border-top:1px solid #263347;">
                                <button class="btn btn-secondary btn-full" onclick="showScreen('predict')" style="min-height:40px;font-size:14px;">Edit Predictions →</button>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    {{-- =========================================================
         SCREEN: Predict
         ========================================================= --}}
    <div id="screen-predict" class="screen">
        <div class="screen-header">
            <div class="back-btn" onclick="showScreen('home')">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="24" height="24"><path d="M15 18l-6-6 6-6"/></svg>
            </div>
            <h2 id="predict-title">Predictions</h2>
            <div style="width:44px;"></div>
        </div>
        <div class="screen-content" style="padding:0 0 calc(72px + env(safe-area-inset-bottom, 0px));">
            <div id="predict-lock-bar" style="display:none;background:#f59e0b22;border-bottom:1px solid #f59e0b44;padding:10px 16px;text-align:center;">
                <span id="predict-lock-info" style="font-size:13px;color:#f59e0b;font-weight:500;"></span>
            </div>
            <div id="predict-locked-msg" style="display:none;padding:32px 24px;text-align:center;">
                <div style="font-size:40px;margin-bottom:12px;">🔒</div>
                <div style="font-size:17px;font-weight:600;color:#f1f5f9;margin-bottom:6px;">Round Locked</div>
                <div style="color:#94a3b8;font-size:14px;">Predictions are closed for this round.</div>
            </div>
            <div id="predict-list" style="padding:16px;"></div>
            <div id="predict-footer" style="padding:0 16px 16px;display:none;">
                <div id="predict-progress" style="font-size:13px;color:#94a3b8;margin-bottom:12px;text-align:center;"></div>
                <button id="predict-submit" class="btn btn-primary btn-full btn-lg">Save Predictions</button>
            </div>
        </div>
    </div>

    {{-- =========================================================
         SCREEN: Round Results
         ========================================================= --}}
    <div id="screen-results" class="screen">
        <div class="screen-header">
            <div class="back-btn" onclick="showScreen('home')">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="24" height="24"><path d="M15 18l-6-6 6-6"/></svg>
            </div>
            <h2 id="results-title">Round Results</h2>
            <div style="width:44px;"></div>
        </div>
        <div class="screen-content" style="padding:16px 0 80px;">
            <div id="results-fixtures" style="padding:0 16px 16px;"></div>
            <div id="results-entries"></div>
        </div>
    </div>

    {{-- =========================================================
         SCREEN: Leaderboard
         ========================================================= --}}
    <div id="screen-leaderboard" class="screen">
        <div class="screen-content" style="padding:0 0 80px;">
            <div class="safe-top" style="padding:20px 16px 16px;border-bottom:1px solid #334155;">
                <h2 style="font-size:22px;font-weight:700;color:#f1f5f9;">Leaderboard</h2>
                <div id="lb-season-info" style="font-size:13px;color:#94a3b8;margin-top:4px;"></div>
            </div>
            <div id="lb-list"></div>
            <div id="lb-empty" style="display:none;padding:48px 24px;text-align:center;">
                <div style="font-size:40px;margin-bottom:12px;">🏆</div>
                <div style="color:#94a3b8;">No scores yet. Play some rounds first!</div>
            </div>
        </div>
    </div>

    {{-- =========================================================
         SCREEN: History
         ========================================================= --}}
    <div id="screen-history" class="screen">
        <div class="screen-content" style="padding:0 0 80px;">
            <div class="safe-top" style="padding:20px 16px 16px;border-bottom:1px solid #334155;">
                <h2 style="font-size:22px;font-weight:700;color:#f1f5f9;">History</h2>
            </div>
            <div id="history-list" style="padding:16px;"></div>
            <div id="history-empty" style="display:none;padding:48px 24px;text-align:center;">
                <div style="font-size:40px;margin-bottom:12px;">📋</div>
                <div style="color:#94a3b8;">No completed rounds yet.</div>
            </div>
        </div>
    </div>

    {{-- =========================================================
         SCREEN: History Detail
         ========================================================= --}}
    <div id="screen-history-detail" class="screen">
        <div class="screen-header">
            <div class="back-btn" onclick="showScreen('history')">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="24" height="24"><path d="M15 18l-6-6 6-6"/></svg>
            </div>
            <h2 id="history-detail-title">Round Detail</h2>
            <div style="width:44px;"></div>
        </div>
        <div class="screen-content" style="padding:16px 0 24px;">
            <div id="history-detail-fixtures" style="padding:0 16px 16px;"></div>
            <div id="history-detail-entries"></div>
        </div>
    </div>

    {{-- =========================================================
         SCREEN: Admin Panel
         ========================================================= --}}
    <div id="screen-admin" class="screen">
        <div class="screen-content" style="padding:0 0 80px;">
            <div class="safe-top" style="padding:20px 16px 16px;border-bottom:1px solid #334155;display:flex;justify-content:space-between;align-items:center;">
                <h2 style="font-size:22px;font-weight:700;color:#f1f5f9;">Admin</h2>
                <div style="text-align:right;">
                    <div style="font-size:12px;color:#94a3b8;">Jackpot</div>
                    <div id="admin-jackpot" style="font-size:20px;font-weight:700;color:#22c55e;"></div>
                </div>
            </div>
            <div style="padding:16px;display:flex;flex-direction:column;gap:12px;">

                <div class="card">
                    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px;">
                        <div style="font-size:16px;font-weight:700;color:#f1f5f9;">Season</div>
                        <div id="admin-season-badge"></div>
                    </div>
                    <div id="admin-season-info" style="font-size:14px;color:#94a3b8;margin-bottom:12px;"></div>
                    <div style="display:flex;gap:8px;flex-wrap:wrap;">
                        <button class="btn btn-secondary" onclick="showScreen('admin-season')">New Season</button>
                        <button id="admin-pending-settlement-btn" class="btn btn-secondary" style="display:none;" onclick="adminStartSettlement()">Start Settlement</button>
                    </div>
                </div>

                <div class="card">
                    <div style="font-size:16px;font-weight:700;color:#f1f5f9;margin-bottom:8px;">Active Round</div>
                    <div id="admin-round-info" style="font-size:14px;color:#94a3b8;margin-bottom:12px;"></div>
                    <div style="display:flex;gap:8px;flex-wrap:wrap;">
                        <button class="btn btn-secondary" onclick="showScreen('admin-rounds');renderAdminRounds()">Manage Rounds</button>
                        <button id="admin-sync-fixtures-btn" class="btn btn-secondary" style="display:none;">Sync Fixtures</button>
                        <button id="admin-sync-results-btn" class="btn btn-secondary" style="display:none;">Sync Results</button>

                        <button id="admin-resolve-btn" class="btn btn-primary" style="display:none;">Resolve Round</button>
                    </div>
                </div>

                <div class="card">
                    <div style="font-size:16px;font-weight:700;color:#f1f5f9;margin-bottom:8px;">Players</div>
                    <div id="admin-players-summary" style="font-size:14px;color:#94a3b8;margin-bottom:12px;">Manage player accounts and token balances.</div>
                    <div style="display:flex;gap:8px;flex-wrap:wrap;">
                        <button class="btn btn-secondary" onclick="loadAdminPlayers()">Manage Players</button>
                        <button class="btn btn-secondary" onclick="showScreen('admin-credit')">Credit Tokens</button>
                    </div>
                </div>

                <div id="admin-settlement-card" class="card" style="display:none;">
                    <div style="font-size:16px;font-weight:700;color:#f1f5f9;margin-bottom:8px;">Season Settlement</div>
                    <div style="font-size:14px;color:#94a3b8;margin-bottom:12px;">Season is pending settlement. Process player balances.</div>
                    <button class="btn btn-primary" onclick="loadSettlements()">Open Settlement</button>
                </div>

            </div>
        </div>
    </div>

    {{-- =========================================================
         SCREEN: Admin Players
         ========================================================= --}}
    <div id="screen-admin-players" class="screen">
        <div class="screen-header">
            <div class="back-btn" onclick="showScreen('admin')">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="24" height="24"><path d="M15 18l-6-6 6-6"/></svg>
            </div>
            <h2>Players</h2>
            <div style="width:44px;display:flex;align-items:center;justify-content:flex-end;">
                <div class="back-btn" onclick="openPlayerForm(null)">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="24" height="24"><path d="M12 5v14M5 12h14"/></svg>
                </div>
            </div>
        </div>
        <div class="screen-content" style="padding-bottom:24px;">
            <div id="admin-players-list"></div>
        </div>
    </div>

    {{-- =========================================================
         SCREEN: Admin Player Form
         ========================================================= --}}
    <div id="screen-admin-player-form" class="screen">
        <div class="screen-header">
            <div class="back-btn" onclick="showScreen('admin-players')">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="24" height="24"><path d="M15 18l-6-6 6-6"/></svg>
            </div>
            <h2 id="player-form-title">Add Player</h2>
            <div style="width:44px;"></div>
        </div>
        <div class="screen-content" style="padding:24px 20px;">
            <input type="hidden" id="player-form-id">
            <div style="display:flex;flex-direction:column;gap:16px;">
                <div>
                    <label style="display:block;font-size:13px;font-weight:600;color:#94a3b8;margin-bottom:8px;text-transform:uppercase;letter-spacing:0.5px;">Name</label>
                    <input id="player-form-name" class="input" type="text" placeholder="Player name" autocomplete="off">
                </div>
                <div>
                    <label style="display:block;font-size:13px;font-weight:600;color:#94a3b8;margin-bottom:8px;text-transform:uppercase;letter-spacing:0.5px;">Nickname (optional)</label>
                    <input id="player-form-nickname" class="input" type="text" placeholder="Display name" autocomplete="off" maxlength="30">
                    <div style="font-size:12px;color:#64748b;margin-top:6px;">Shown instead of real name if set</div>
                </div>
                <div>
                    <label style="display:block;font-size:13px;font-weight:600;color:#94a3b8;margin-bottom:8px;text-transform:uppercase;letter-spacing:0.5px;">PIN</label>
                    <input id="player-form-pin" class="input" type="password" placeholder="4–8 digits" maxlength="8" inputmode="numeric">
                    <div id="player-form-pin-hint" style="font-size:12px;color:#64748b;margin-top:6px;display:none;">Leave blank to keep existing PIN</div>
                </div>
                <div>
                    <label style="display:block;font-size:13px;font-weight:600;color:#94a3b8;margin-bottom:8px;text-transform:uppercase;letter-spacing:0.5px;">Token Balance</label>
                    <input id="player-form-tokens" class="input" type="number" placeholder="0" min="0" inputmode="numeric">
                </div>
                <div style="display:flex;align-items:center;gap:12px;padding:14px 16px;background:#263347;border-radius:10px;border:1px solid #334155;">
                    <input id="player-form-admin" type="checkbox" style="width:20px;height:20px;accent-color:#22c55e;cursor:pointer;">
                    <label for="player-form-admin" style="font-size:15px;font-weight:500;color:#f1f5f9;cursor:pointer;">Admin access</label>
                </div>
                <button id="player-form-save" class="btn btn-primary btn-full btn-lg" style="margin-top:8px;">Save Player</button>
                <button id="player-form-delete" class="btn btn-danger btn-full" style="display:none;">Delete Player</button>
            </div>
        </div>
    </div>

    {{-- =========================================================
         SCREEN: Admin Rounds
         ========================================================= --}}
    <div id="screen-admin-rounds" class="screen">
        <div class="screen-header">
            <div class="back-btn" onclick="showScreen('admin')">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="24" height="24"><path d="M15 18l-6-6 6-6"/></svg>
            </div>
            <h2>Rounds</h2>
            <div style="width:44px;display:flex;align-items:center;justify-content:flex-end;">
                <div class="back-btn" onclick="editRound(null)">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="24" height="24"><path d="M12 5v14M5 12h14"/></svg>
                </div>
            </div>
        </div>
        <div class="screen-content" style="padding-bottom:24px;">
            <div id="admin-rounds-list"></div>
        </div>
    </div>

    {{-- =========================================================
         SCREEN: Admin Round Form
         ========================================================= --}}
    <div id="screen-admin-round-form" class="screen">
        <div class="screen-header">
            <div class="back-btn" onclick="showScreen('admin-rounds')">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="24" height="24"><path d="M15 18l-6-6 6-6"/></svg>
            </div>
            <h2 id="round-form-title">New Round</h2>
            <div style="width:44px;"></div>
        </div>
        <div class="screen-content" style="padding:24px 20px;">
            <input type="hidden" id="round-form-id">
            <div style="display:flex;flex-direction:column;gap:16px;">
                <div>
                    <label style="display:block;font-size:13px;font-weight:600;color:#94a3b8;margin-bottom:8px;text-transform:uppercase;letter-spacing:0.5px;">Matchweek Number</label>
                    <input id="round-form-number" class="input" type="number" placeholder="e.g. 28" min="1" inputmode="numeric">
                </div>
                <div>
                    <label style="display:block;font-size:13px;font-weight:600;color:#94a3b8;margin-bottom:8px;text-transform:uppercase;letter-spacing:0.5px;">Locks At (first kick-off)</label>
                    <input id="round-form-locks-at" class="input" type="datetime-local">
                </div>
                <div id="round-form-status-group" style="display:none;">
                    <label style="display:block;font-size:13px;font-weight:600;color:#94a3b8;margin-bottom:8px;text-transform:uppercase;letter-spacing:0.5px;">Status</label>
                    <select id="round-form-status" class="input" style="cursor:pointer;">
                        <option value="pending">Pending</option>
                        <option value="active">Active</option>
                        <option value="locked">Locked</option>
                    </select>
                </div>
                <button id="round-form-save" class="btn btn-primary btn-full btn-lg" style="margin-top:8px;">Save Round</button>
            </div>
        </div>
    </div>

    {{-- =========================================================
         SCREEN: Admin New Season
         ========================================================= --}}
    <div id="screen-admin-season" class="screen">
        <div class="screen-header">
            <div class="back-btn" onclick="showScreen('admin')">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="24" height="24"><path d="M15 18l-6-6 6-6"/></svg>
            </div>
            <h2>New Season</h2>
            <div style="width:44px;"></div>
        </div>
        <div class="screen-content" style="padding:24px 20px;">
            <div style="background:#ef444422;border:1px solid #ef444444;border-radius:10px;padding:14px;margin-bottom:24px;">
                <div style="font-size:13px;color:#ef4444;">⚠️ Starting a new season will end the current season and reset the leaderboard.</div>
            </div>
            <div style="display:flex;flex-direction:column;gap:16px;">
                <div>
                    <label style="display:block;font-size:13px;font-weight:600;color:#94a3b8;margin-bottom:8px;text-transform:uppercase;letter-spacing:0.5px;">League Code</label>
                    <input id="season-form-league-id" class="input" type="text" placeholder="e.g. PL, PD, BL1, SA" autocomplete="off" style="text-transform:uppercase;">
                    <div style="font-size:12px;color:#64748b;margin-top:6px;">football-data.org competition code</div>
                </div>
                <div>
                    <label style="display:block;font-size:13px;font-weight:600;color:#94a3b8;margin-bottom:8px;text-transform:uppercase;letter-spacing:0.5px;">League Name</label>
                    <input id="season-form-league-name" class="input" type="text" placeholder="e.g. Premier League" autocomplete="off">
                </div>
                <div>
                    <label style="display:block;font-size:13px;font-weight:600;color:#94a3b8;margin-bottom:8px;text-transform:uppercase;letter-spacing:0.5px;">Entry Cost (tokens per round)</label>
                    <input id="season-form-entry-tokens" class="input" type="number" placeholder="5" min="1" inputmode="numeric">
                </div>
                <button id="season-form-save" class="btn btn-primary btn-full btn-lg" style="margin-top:8px;">Start Season</button>
            </div>
        </div>
    </div>

    {{-- =========================================================
         SCREEN: Balances
         ========================================================= --}}
    <div id="screen-balances" class="screen">
        <div class="screen-header">
            <div class="back-btn" onclick="showScreen('home')">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="24" height="24"><path d="M15 18l-6-6 6-6"/></svg>
            </div>
            <h2>Balances</h2>
            <div style="width:44px;"></div>
        </div>
        <div class="screen-content" style="padding-bottom:24px;">
            <div id="balances-list"></div>
        </div>
    </div>

    {{-- =========================================================
         SCREEN: Ledger
         ========================================================= --}}
    <div id="screen-ledger" class="screen">
        <div class="screen-header">
            <div class="back-btn" onclick="showScreen('balances')">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="24" height="24"><path d="M15 18l-6-6 6-6"/></svg>
            </div>
            <h2 id="ledger-player-name" style="font-size:17px;">Ledger</h2>
            <div id="ledger-player-balance" style="font-size:17px;font-weight:700;color:#22c55e;padding-right:16px;"></div>
        </div>
        <div class="screen-content" style="padding-bottom:24px;">
            <div id="ledger-list"></div>
        </div>
    </div>

    {{-- =========================================================
         SCREEN: Badges
         ========================================================= --}}
    <div id="screen-badges" class="screen">
        <div class="screen-header">
            <div class="back-btn" onclick="showScreen('leaderboard')">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="24" height="24"><path d="M15 18l-6-6 6-6"/></svg>
            </div>
            <h2 id="badges-player-name" style="font-size:17px;">Badges</h2>
            <div style="width:44px;"></div>
        </div>
        <div class="screen-content" style="padding:16px 0 24px;">
            <div id="badges-shelf" style="padding:0 16px;"></div>
        </div>
    </div>

    {{-- =========================================================
         SCREEN: Hall of Fame
         ========================================================= --}}
    <div id="screen-hall-of-fame" class="screen">
        <div class="screen-header">
            <div class="back-btn" onclick="showScreen('home')">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="24" height="24"><path d="M15 18l-6-6 6-6"/></svg>
            </div>
            <h2>Hall of Fame</h2>
            <div style="width:44px;"></div>
        </div>
        <div class="screen-content" style="padding:16px 0 24px;">
            <div id="hof-list"></div>
        </div>
    </div>

    {{-- =========================================================
         SCREEN: Admin Settlement
         ========================================================= --}}
    <div id="screen-admin-settlement" class="screen">
        <div class="screen-header">
            <div class="back-btn" onclick="showScreen('admin')">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="24" height="24"><path d="M15 18l-6-6 6-6"/></svg>
            </div>
            <h2>Season Settlement</h2>
            <div style="width:44px;"></div>
        </div>
        <div class="screen-content" style="padding:0 0 24px;">
            <div style="padding:14px 16px;border-bottom:1px solid #334155;display:flex;justify-content:space-between;align-items:center;">
                <div id="settlement-progress" style="font-size:15px;font-weight:600;color:#f1f5f9;"></div>
                <button id="settlement-close-btn" class="btn btn-primary" style="min-height:36px;font-size:13px;padding:0 14px;" onclick="closeSeasonFinal()">Close Season</button>
            </div>
            <div id="settlement-content"></div>
        </div>
    </div>

    {{-- =========================================================
         SCREEN: Admin Credit Tokens
         ========================================================= --}}
    <div id="screen-admin-credit" class="screen">
        <div class="screen-header">
            <div class="back-btn" onclick="showScreen('admin')">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="24" height="24"><path d="M15 18l-6-6 6-6"/></svg>
            </div>
            <h2>Credit Tokens</h2>
            <div style="width:44px;"></div>
        </div>
        <div class="screen-content" style="padding:24px 20px;">
            <div style="display:flex;flex-direction:column;gap:16px;">
                <div>
                    <label style="display:block;font-size:13px;font-weight:600;color:#94a3b8;margin-bottom:8px;text-transform:uppercase;letter-spacing:0.5px;">Player</label>
                    <select id="credit-player-id" class="input" style="cursor:pointer;">
                        <option value="">— Select player —</option>
                    </select>
                </div>
                <div>
                    <label style="display:block;font-size:13px;font-weight:600;color:#94a3b8;margin-bottom:8px;text-transform:uppercase;letter-spacing:0.5px;">Amount (tokens)</label>
                    <input id="credit-amount" class="input" type="number" placeholder="e.g. 10" min="1" inputmode="numeric">
                </div>
                <div>
                    <label style="display:block;font-size:13px;font-weight:600;color:#94a3b8;margin-bottom:8px;text-transform:uppercase;letter-spacing:0.5px;">Description (optional)</label>
                    <input id="credit-description" class="input" type="text" placeholder="e.g. Top-up" autocomplete="off">
                </div>
                <button id="credit-submit-btn" class="btn btn-primary btn-full btn-lg" onclick="submitCreditTokens()">Credit Tokens</button>
                <div id="credit-result"></div>
            </div>
        </div>
    </div>

    {{-- =========================================================
         Bottom Navigation
         ========================================================= --}}
    <nav id="bottom-nav">
        <div class="nav-item" id="nav-home" onclick="showScreen('home')">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="22" height="22"><path d="M3 12L12 3l9 9M5 10v9a1 1 0 001 1h4v-5h4v5h4a1 1 0 001-1v-9"/></svg>
            <span class="nav-label">Home</span>
        </div>
        <div class="nav-item" id="nav-predict" onclick="showScreen('predict')">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="22" height="22"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span class="nav-label">Predict</span>
        </div>
        <div class="nav-item" id="nav-results" onclick="loadAndShowResults()">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="22" height="22"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
            <span class="nav-label">Results</span>
        </div>
        <div class="nav-item" id="nav-leaderboard" onclick="showScreen('leaderboard')">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="22" height="22"><path d="M16 8v8m-4-5v5m-4-2v2M6 20h12"/></svg>
            <span class="nav-label">Table</span>
        </div>
        <div class="nav-item" id="nav-history" onclick="showScreen('history')">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="22" height="22"><path d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span class="nav-label">History</span>
        </div>
        <div class="nav-item" id="nav-admin" onclick="showScreen('admin')" style="display:none;">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="22" height="22"><path d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><circle cx="12" cy="12" r="3"/></svg>
            <span class="nav-label">Admin</span>
        </div>
    </nav>

</div>

<script>
// ================================================================
//  STATE
// ================================================================
const state = {
    token: null,
    player: null,
    season: null,
    round: null,
    predictions: {},
    leaderboard: [],
    history: [],
    adminPlayers: [],
    adminRounds: [],
    roundResults: null,
    historyDetail: null,
    currentScreen: 'login',
    balances: [],
    ledger: {},
    pots: {},
    badges: {},
    hallOfFame: [],
    settlements: null,
    currentLedgerPlayerId: null,
    currentBadgePlayerId: null,
};

// Apply /api/state response into state object
function applyStateData(data) {
    Object.assign(state, data);
    if (Array.isArray(state.predictions)) state.predictions = {};
    if (data.balances) state.balances = data.balances;
}

const NAV_SCREENS = ['home', 'predict', 'results', 'leaderboard', 'history', 'admin'];
const CSRF = () => document.querySelector('meta[name="csrf-token"]').content;

// ================================================================
//  API
// ================================================================
async function api(method, url, data) {
    const opts = {
        method,
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': CSRF(),
        },
    };
    if (state.token) opts.headers['Authorization'] = 'Bearer ' + state.token;
    if (data !== undefined) opts.body = JSON.stringify(data);
    const res = await fetch(url, opts);
    const json = await res.json().catch(() => ({}));
    if (!res.ok) throw json;
    return json;
}

// ================================================================
//  TOAST
// ================================================================
let _toastTimer;
function toast(msg, type) {
    const el = document.getElementById('toast');
    el.textContent = msg;
    el.className = 'show ' + (type || 'success');
    clearTimeout(_toastTimer);
    _toastTimer = setTimeout(() => { el.className = ''; }, 3000);
}

// ================================================================
//  LOADING
// ================================================================
function showLoading(v) {
    document.getElementById('loading').classList.toggle('hidden', !v);
}

// ================================================================
//  SCREEN NAVIGATION
// ================================================================
function showScreen(id) {
    document.querySelectorAll('.screen').forEach(s => s.classList.remove('active'));
    const screen = document.getElementById('screen-' + id);
    if (screen) screen.classList.add('active');
    state.currentScreen = id;

    const nav = document.getElementById('bottom-nav');
    nav.classList.toggle('visible', NAV_SCREENS.includes(id));

    document.querySelectorAll('.nav-item').forEach(n => n.classList.remove('active'));
    const navEl = document.getElementById('nav-' + id);
    if (navEl) navEl.classList.add('active');

    if (id === 'home')        renderHome();
    else if (_countdownTimer) { clearInterval(_countdownTimer); _countdownTimer = null; }
    if (id === 'predict')     renderPredict();
    if (id === 'leaderboard') renderLeaderboard();
    if (id === 'history')     renderHistory();
    if (id === 'admin')       renderAdmin();
    if (id === 'admin-players')   renderAdminPlayers();
    if (id === 'admin-rounds')    renderAdminRounds();
    if (id === 'balances')    renderBalancesScreen();
    if (id === 'admin-credit') renderCreditForm();
}

// ================================================================
//  LOCAL STORAGE
// ================================================================
function saveLocal() {
    try {
        localStorage.setItem('tp_state', JSON.stringify({
            token: state.token,
            player: state.player,
            season: state.season,
            round: state.round,
            predictions: state.predictions,
            leaderboard: state.leaderboard,
            history: state.history,
        }));
    } catch(e) {}
}

function loadLocal() {
    try {
        const raw = localStorage.getItem('tp_state');
        if (!raw) return false;
        const s = JSON.parse(raw);
        if (!s.token) return false;
        Object.assign(state, s);
        // Ensure predictions is always a plain object, never an array
        if (Array.isArray(state.predictions)) state.predictions = {};
        return true;
    } catch(e) { return false; }
}

function clearLocal() { localStorage.removeItem('tp_state'); }

function sleep(ms) { return new Promise(r => setTimeout(r, ms)); }

// ================================================================
//  INIT (offline-first, retry on cold start)
// ================================================================
async function init() {
    showLoading(true);
    const hasCache = loadLocal();

    if (!state.token) {
        showLoading(false);
        showScreen('login');
        return;
    }

    if (hasCache) {
        showLoading(false);
        toggleAdminNav();
        showScreen('home');
    }

    // Background fetch with retry for cold start
    let tries = 0;
    while (tries < 6) {
        try {
            const data = await api('GET', '/api/state');
            applyStateData(data);
            saveLocal();
            if (state.currentScreen === 'home')        renderHome();
            if (state.currentScreen === 'leaderboard') renderLeaderboard();
            if (state.currentScreen === 'history')     renderHistory();
            break;
        } catch(e) {
            const status = e.status || (e.message === 'Unauthenticated' ? 401 : 0);
            if (status === 401) { logout(); return; }
            tries++;
            if (tries < 6) await sleep(1200 * tries);
        }
    }

    if (!hasCache) {
        showLoading(false);
        toggleAdminNav();
        showScreen('home');
    }
}

// ================================================================
//  AUTH
// ================================================================
document.getElementById('login-btn').addEventListener('click', doLogin);
document.getElementById('login-pin').addEventListener('keydown', e => { if (e.key === 'Enter') doLogin(); });

async function doLogin() {
    const name = document.getElementById('login-name').value.trim();
    const pin  = document.getElementById('login-pin').value.trim();
    const errEl = document.getElementById('login-error');
    errEl.style.display = 'none';

    if (!name || !pin) {
        errEl.textContent = 'Enter your name and PIN';
        errEl.style.display = 'block';
        return;
    }

    const btn = document.getElementById('login-btn');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner"></span>';

    try {
        const data = await api('POST', '/api/auth/login', { name, pin });
        state.token  = data.token;
        state.player = data.player;
        saveLocal();
        toggleAdminNav();

        try {
            const s = await api('GET', '/api/state');
            applyStateData(s);
            saveLocal();
        } catch(e) {}

        showScreen('home');
    } catch(e) {
        errEl.textContent = e.message || 'Invalid name or PIN';
        errEl.style.display = 'block';
    } finally {
        btn.disabled = false;
        btn.textContent = 'Sign In';
    }
}

async function logout() {
    try { await api('POST', '/api/auth/logout'); } catch(e) {}
    Object.assign(state, {
        token:null, player:null, season:null, round:null,
        predictions:{}, leaderboard:[], history:[],
        adminPlayers:[], adminRounds:[],
        balances:[], ledger:{}, pots:{}, badges:{},
        hallOfFame:[], settlements:null,
        currentLedgerPlayerId:null, currentBadgePlayerId:null,
    });
    clearLocal();
    showScreen('login');
    document.getElementById('nav-admin').style.display = 'none';
    document.getElementById('bottom-nav').classList.remove('visible');
}

function toggleAdminNav() {
    document.getElementById('nav-admin').style.display = state.player?.isAdmin ? 'flex' : 'none';
}

// ================================================================
//  HOME SCREEN
// ================================================================
function renderHome() {
    if (!state.player) return;
    document.getElementById('home-player-name').textContent = state.player.displayName || state.player.name;
    document.getElementById('home-token-balance').textContent = state.player.tokenBalance;
    toggleAdminNav();

    if (!state.season) {
        document.getElementById('home-jackpot').textContent = '0';
        document.getElementById('home-league').textContent = '';
        document.getElementById('home-no-season').style.display = 'block';
        document.getElementById('home-no-round').style.display = 'none';
        document.getElementById('home-round-info').style.display = 'none';
        return;
    }

    document.getElementById('home-jackpot').textContent = state.season.jackpot;
    document.getElementById('home-league').textContent = state.season.leagueName;
    document.getElementById('home-no-season').style.display = 'none';

    if (!state.round) {
        document.getElementById('home-no-round').style.display = 'block';
        document.getElementById('home-round-info').style.display = 'none';
        return;
    }

    document.getElementById('home-no-round').style.display = 'none';
    document.getElementById('home-round-info').style.display = 'block';
    document.getElementById('home-round-number').textContent = state.round.number;
    document.getElementById('home-round-status-badge').innerHTML = roundStatusBadge(state.round.status, state.round.isLocked);

    if (state.round.locksAt) {
        const locksAt = new Date(state.round.locksAt);
        const locksAtStr = locksAt.toLocaleDateString() + ' ' +
            locksAt.toLocaleTimeString([], {hour:'2-digit',minute:'2-digit'});
        const diff = locksAt - Date.now();
        if (diff > 0) {
            document.getElementById('home-locks-at').textContent = 'Locks · ' + locksAtStr;
            startCountdown(locksAt);
        } else {
            document.getElementById('home-locks-at').textContent = 'Locked · ' + locksAtStr;
            document.getElementById('home-countdown').style.display = 'none';
        }
    }

    // Completion counter
    const completedCount = state.round.completedCount ?? null;
    const totalPlayers = state.round.totalPlayers ?? null;
    const counterEl = document.getElementById('home-completion-counter');
    if (completedCount !== null && totalPlayers !== null) {
        counterEl.textContent = completedCount + '/' + totalPlayers + ' players submitted predictions';
        counterEl.style.display = 'block';
        counterEl.style.color = completedCount === totalPlayers ? '#22c55e' : '#64748b';
    } else {
        counterEl.style.display = 'none';
    }

    const fixtures = (state.round.fixtures || []).filter(f => f.status !== 'postponed' && f.status !== 'cancelled');
    const predicted = fixtures.filter(f => state.predictions[f.id]).length;
    const total = fixtures.length;
    const complete = predicted === total && total > 0;
    const locked = state.round.isLocked;

    const statusEl = document.getElementById('home-prediction-status');
    if (total === 0) {
        statusEl.innerHTML = '<span class="badge badge-slate">No fixtures yet</span>';
    } else if (locked) {
        statusEl.innerHTML = complete
            ? '<span class="badge badge-green">✓ Submitted · ' + predicted + '/' + total + '</span>'
            : '<span class="badge badge-amber">⚠ Incomplete · ' + predicted + '/' + total + '</span>';
    } else if (complete) {
        statusEl.innerHTML = '<span class="badge badge-green">✓ All predicted · ' + predicted + '/' + total + '</span>';
    } else {
        statusEl.innerHTML = '<span class="badge badge-amber">' + predicted + '/' + total + ' predicted</span>';
    }

    const predictBtn = document.getElementById('home-predict-btn');
    predictBtn.style.display = (locked || total === 0) ? 'none' : 'block';
    predictBtn.onclick = () => showScreen('predict');

    renderTicket();

    // Streak highlight — find player with highest onFire streak >= 2
    const streakEl = document.getElementById('home-streak-highlight');
    if (streakEl) {
        const hotEntry = (state.leaderboard || []).reduce((best, e) => {
            const s = e.streaks || {};
            return (s.onFire >= 2 && s.onFire > (best?.streaks?.onFire || 0)) ? e : best;
        }, null);
        if (hotEntry && hotEntry.streaks && hotEntry.streaks.onFire >= 2) {
            const name = hotEntry.displayName || hotEntry.playerName;
            streakEl.innerHTML = '<div class="card" style="background:linear-gradient(135deg,#1e293b,#431407);border-color:#f97316;margin-bottom:16px;text-align:center;">🔥 <strong style="color:#f97316;">' + name + '</strong> is on a <strong style="color:#f97316;">' + hotEntry.streaks.onFire + '</strong>-round hot streak!</div>';
            streakEl.style.display = 'block';
        } else {
            streakEl.style.display = 'none';
        }
    }
}

let _countdownTimer = null;
function startCountdown(locksAt) {
    if (_countdownTimer) clearInterval(_countdownTimer);
    const el = document.getElementById('home-countdown');
    if (!el) return;

    function tick() {
        const diff = locksAt - Date.now();
        if (diff <= 0) {
            el.style.display = 'none';
            clearInterval(_countdownTimer);
            return;
        }
        const h  = Math.floor(diff / 3600000);
        const m  = Math.floor((diff % 3600000) / 60000);
        const s  = Math.floor((diff % 60000) / 1000);
        el.textContent = (h > 0 ? h + 'h ' : '') +
            (h > 0 || m > 0 ? String(m).padStart(2,'0') + 'm ' : '') +
            String(s).padStart(2,'0') + 's';
        el.style.display = 'block';
    }
    tick();
    _countdownTimer = setInterval(tick, 1000);
}

function renderTicket() {
    const ticketEl = document.getElementById('home-ticket');
    if (!state.round || !state.round.fixtures) { ticketEl.style.display = 'none'; return; }

    const fixtures = state.round.fixtures.filter(f => f.status !== 'postponed' && f.status !== 'cancelled');
    const predicted = fixtures.filter(f => state.predictions[f.id]);

    if (predicted.length === 0) { ticketEl.style.display = 'none'; return; }

    ticketEl.style.display = 'block';
    document.getElementById('home-ticket-count').textContent = predicted.length + '/' + fixtures.length;

    document.getElementById('home-ticket-rows').innerHTML = fixtures.map(f => {
        const pick = state.predictions[f.id];
        return '<div style="display:flex;align-items:center;padding:9px 16px;border-bottom:1px solid #263347;gap:10px;">' +
            '<div style="flex:1;font-size:13px;color:#f1f5f9;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">' +
                f.homeTeam + ' <span style="color:#64748b;">v</span> ' + f.awayTeam +
            '</div>' +
            (pick
                ? '<div style="min-width:32px;text-align:center;font-size:15px;font-weight:700;color:#22c55e;background:rgba(34,197,94,0.12);border-radius:6px;padding:3px 8px;">' + pick + '</div>'
                : '<div style="min-width:32px;text-align:center;font-size:13px;color:#64748b;">–</div>'
            ) +
        '</div>';
    }).join('');
}

function toggleTicket() {
    const body = document.getElementById('home-ticket-body');
    const chevron = document.getElementById('home-ticket-chevron');
    const open = body.style.display === 'none';
    body.style.display = open ? 'block' : 'none';
    chevron.style.transform = open ? 'rotate(180deg)' : 'rotate(0deg)';
}

function roundStatusBadge(status, isLocked) {
    if (isLocked || status === 'locked') return '<span class="badge badge-amber">🔒 Locked</span>';
    if (status === 'active')   return '<span class="badge badge-green">● Active</span>';
    if (status === 'resolved') return '<span class="badge badge-slate">✓ Resolved</span>';
    return '<span class="badge badge-slate">Pending</span>';
}

// ================================================================
//  PREDICT SCREEN
// ================================================================
function renderPredict() {
    if (!state.round) {
        document.getElementById('predict-list').innerHTML =
            '<div style="text-align:center;padding:32px;color:#94a3b8;">No active round.</div>';
        document.getElementById('predict-footer').style.display = 'none';
        document.getElementById('predict-locked-msg').style.display = 'none';
        document.getElementById('predict-lock-bar').style.display = 'none';
        return;
    }

    document.getElementById('predict-title').textContent = 'Matchweek ' + state.round.number;
    const locked = state.round.isLocked;
    const fixtures = (state.round.fixtures || []).filter(f => f.status !== 'postponed' && f.status !== 'cancelled');

    if (locked) {
        document.getElementById('predict-lock-bar').style.display = 'none';
        document.getElementById('predict-locked-msg').style.display = 'block';
        document.getElementById('predict-footer').style.display = 'none';
        renderFixtureList(fixtures, true);
        return;
    }

    document.getElementById('predict-locked-msg').style.display = 'none';

    if (state.round.locksAt) {
        const diff = new Date(state.round.locksAt) - Date.now();
        if (diff > 0) {
            const h = Math.floor(diff / 3600000);
            const m = Math.floor((diff % 3600000) / 60000);
            document.getElementById('predict-lock-bar').style.display = 'block';
            document.getElementById('predict-lock-info').textContent = '⏰ Locks in ' + h + 'h ' + m + 'm';
        } else {
            document.getElementById('predict-lock-bar').style.display = 'none';
        }
    }

    renderFixtureList(fixtures, false);
    updatePredictProgress(fixtures);
    document.getElementById('predict-footer').style.display = fixtures.length ? 'block' : 'none';
    document.getElementById('predict-submit').onclick = submitPredictions;
}

function renderFixtureList(fixtures, readonly) {
    const list = document.getElementById('predict-list');
    if (!fixtures.length) {
        list.innerHTML = '<div style="text-align:center;padding:32px;color:#94a3b8;">No fixtures for this round yet.</div>';
        return;
    }

    list.innerHTML = fixtures.map(f => {
        const pick = state.predictions[f.id] || null;
        const resolved = f.result != null;

        const picks = ['1','X','2'].map(p => {
            let cls = 'pick-btn';
            if (pick === p) cls += resolved ? (p === f.result ? ' correct' : ' wrong') : ' selected';
            const handler = readonly ? '' : 'onclick="setPick(' + f.id + ',\'' + p + '\',this)"';
            return '<button class="' + cls + '" ' + handler + '>' + p + '</button>';
        }).join('');

        const scoreHtml = f.status === 'finished'
            ? '<div class="vs-score finished">' + (f.homeScore ?? 0) + '–' + (f.awayScore ?? 0) + '</div>'
            : f.status === 'live'
            ? '<div class="vs-score live">' + (f.homeScore ?? 0) + '–' + (f.awayScore ?? 0) + ' ●</div>'
            : '<div class="vs-score">vs</div>';

        const kickoff = f.kickoffAt
            ? '<div style="font-size:11px;color:#64748b;text-align:center;margin-bottom:6px;">' +
              new Date(f.kickoffAt).toLocaleString([], {weekday:'short',month:'short',day:'numeric',hour:'2-digit',minute:'2-digit'}) + '</div>'
            : '';

        return '<div class="fixture-card" id="fixture-' + f.id + '">' +
            kickoff +
            '<div class="fixture-teams">' +
                '<div class="team-name home">' + f.homeTeam + '</div>' +
                scoreHtml +
                '<div class="team-name away">' + f.awayTeam + '</div>' +
            '</div>' +
            (!readonly
                ? '<div class="pick-row">' + picks + '</div>'
                : '<div style="text-align:center;padding-top:6px;font-size:13px;color:#94a3b8;">' +
                  (pick ? 'Your pick: <strong style="color:#f1f5f9;">' + pick + '</strong>' : 'No prediction') + '</div>'
            ) +
        '</div>';
    }).join('');
}

function setPick(fixtureId, pick, btnEl) {
    state.predictions[fixtureId] = pick;
    const card = document.getElementById('fixture-' + fixtureId);
    if (card) {
        card.querySelectorAll('.pick-btn').forEach(b => b.classList.remove('selected'));
        btnEl.classList.add('selected');
    }
    const fixtures = (state.round?.fixtures || []).filter(f => f.status !== 'postponed' && f.status !== 'cancelled');
    updatePredictProgress(fixtures);
}

function updatePredictProgress(fixtures) {
    const predicted = fixtures.filter(f => state.predictions[f.id]).length;
    document.getElementById('predict-progress').textContent = predicted + ' / ' + fixtures.length + ' matches predicted';
}

async function submitPredictions() {
    const btn = document.getElementById('predict-submit');
    const fixtures = (state.round?.fixtures || []).filter(f => f.status !== 'postponed' && f.status !== 'cancelled');
    const predicted = fixtures.filter(f => state.predictions[f.id]).length;

    if (predicted === 0) { toast('Make at least one prediction', 'error'); return; }

    btn.disabled = true;
    btn.innerHTML = '<span class="spinner"></span> Saving…';
    saveLocal(); // optimistic

    try {
        const res = await api('POST', '/api/predictions', { predictions: state.predictions });
        // Apply updated token balance (entry fee may have been auto-charged)
        if (res && res.tokenBalance !== undefined) {
            state.player.tokenBalance = res.tokenBalance;
            document.getElementById('home-token-balance').textContent = res.tokenBalance;
        }
        saveLocal();
        toast('Predictions saved ✓');
    } catch(e) {
        toast(e.message || 'Failed to save', 'error');
    } finally {
        btn.disabled = false;
        btn.textContent = 'Save Predictions';
    }
}

// ================================================================
//  RESULTS SCREEN
// ================================================================
async function loadAndShowResults() {
    if (!state.round) { toast('No active round', 'error'); return; }
    showScreen('results');
    document.getElementById('results-title').textContent = 'Matchweek ' + state.round.number;
    document.getElementById('results-fixtures').innerHTML =
        '<div style="color:#94a3b8;text-align:center;padding:24px;"><span class="spinner"></span></div>';
    document.getElementById('results-entries').innerHTML = '';

    try {
        const data = await api('GET', '/api/round/' + state.round.id + '/results');
        state.roundResults = data;
        renderRoundResults(data);
    } catch(e) {
        document.getElementById('results-fixtures').innerHTML =
            '<div style="color:#ef4444;text-align:center;padding:16px;">Failed to load</div>';
    }
}

function renderRoundResults(data) {
    const { round, entries } = data;
    const fixtures = round.fixtures || [];

    const fixturesHtml = fixtures.map(f =>
        '<div style="display:flex;align-items:center;justify-content:space-between;padding:10px 0;border-bottom:1px solid #263347;">' +
            '<div style="flex:1;font-size:13px;color:#94a3b8;text-align:right;">' + f.homeTeam + '</div>' +
            '<div style="min-width:60px;text-align:center;font-size:14px;font-weight:700;color:' +
                (f.status === 'finished' ? '#f1f5f9' : f.status === 'live' ? '#22c55e' : '#64748b') + ';">' +
                (f.status === 'finished' || f.status === 'live'
                    ? (f.homeScore ?? 0) + '–' + (f.awayScore ?? 0) + (f.status === 'live' ? ' ●' : '')
                    : '–') +
            '</div>' +
            '<div style="flex:1;font-size:13px;color:#94a3b8;">' + f.awayTeam + '</div>' +
        '</div>'
    ).join('');

    document.getElementById('results-fixtures').innerHTML =
        '<div class="card"><div style="font-size:13px;font-weight:600;color:#94a3b8;margin-bottom:8px;text-transform:uppercase;letter-spacing:0.5px;">Fixtures</div>' +
        fixturesHtml + '</div>';

    const entriesHtml = entries.map(e => {
        const isMe = state.player && e.playerId === state.player.id;
        const displayName = e.displayName || e.playerName;
        const picksHtml = fixtures.map(f => {
            const pick = e.predictions && e.predictions[f.id] ? e.predictions[f.id] : null;
            return '<div style="display:flex;align-items:center;justify-content:space-between;padding:8px 0;border-bottom:1px solid #263347;">' +
                '<div style="font-size:13px;color:#94a3b8;">' + f.homeTeam + ' v ' + f.awayTeam + '</div>' +
                '<div style="font-weight:700;font-size:15px;color:' + (pick ? '#f1f5f9' : '#64748b') + ';">' + (pick || '–') + '</div>' +
            '</div>';
        }).join('');

        return '<div style="padding:0 16px;border-bottom:1px solid #334155;">' +
            '<div style="display:flex;align-items:center;padding:14px 0;cursor:pointer;" onclick="toggleDrawer(this)">' +
                '<div style="flex:1;font-size:16px;font-weight:600;color:' + (isMe ? '#22c55e' : '#f1f5f9') + ';">' +
                    displayName + (isMe ? ' (you)' : '') + (e.isPerfect ? ' 🏆' : '') + '</div>' +
                '<div style="display:flex;align-items:center;gap:8px;">' +
                    (round.status === 'resolved'
                        ? '<span style="font-size:18px;font-weight:700;color:#22c55e;">' + e.points + '</span><span style="font-size:12px;color:#64748b;">pts</span>'
                        : '') +
                    (e.isComplete ? '<span class="badge badge-green">✓</span>' : '<span class="badge badge-amber">–</span>') +
                '</div>' +
            '</div>' +
            '<div class="picks-drawer" style="display:none;padding-bottom:12px;">' + picksHtml + '</div>' +
        '</div>';
    }).join('');

    document.getElementById('results-entries').innerHTML = entriesHtml;
}

function toggleDrawer(headerEl) {
    const drawer = headerEl.nextElementSibling;
    drawer.style.display = drawer.style.display === 'none' ? 'block' : 'none';
}

// ================================================================
//  LEADERBOARD
// ================================================================
function renderLeaderboard() {
    const lb = state.leaderboard || [];
    if (state.season) {
        document.getElementById('lb-season-info').textContent =
            state.season.leagueName + ' · Jackpot: ' + state.season.jackpot + ' tokens';
    }

    if (!lb.length) {
        document.getElementById('lb-list').innerHTML = '';
        document.getElementById('lb-empty').style.display = 'block';
        return;
    }

    document.getElementById('lb-empty').style.display = 'none';
    document.getElementById('lb-list').innerHTML = lb.map((p, i) => {
        const rank = i + 1;
        const rankIcon = rank === 1 ? '🥇' : rank === 2 ? '🥈' : rank === 3 ? '🥉' : rank;
        const isMe = state.player && p.playerId === state.player.id;
        const displayName = p.displayName || p.playerName;
        const streaks = p.streaks || {};
        const badgeCount = p.badgeCount || 0;

        let streakHtml = '';
        if (streaks.onFire > 0) streakHtml += '<span style="color:#f97316;font-size:13px;">🔥' + streaks.onFire + '</span>';
        if (streaks.cold > 1) streakHtml += '<span style="color:#93c5fd;font-size:13px;margin-left:4px;">❄' + streaks.cold + '</span>';
        if (badgeCount > 0) streakHtml += '<span style="color:#fbbf24;font-size:13px;margin-left:4px;">⭐' + badgeCount + '</span>';

        return '<div class="lb-row" style="' + (isMe ? 'background:#22c55e11;' : '') + '">' +
            '<div class="lb-rank ' + (rank <= 3 ? 'top3' : '') + '" style="font-size:' + (rank <= 3 ? '20' : '16') + 'px;">' + rankIcon + '</div>' +
            '<div class="lb-name" style="' + (isMe ? 'color:#22c55e;' : '') + 'cursor:pointer;" onclick="openBadges(' + p.playerId + ')">' +
                displayName + (isMe ? ' (you)' : '') +
                (streakHtml ? '<div style="margin-top:2px;display:flex;gap:4px;">' + streakHtml + '</div>' : '') +
            '</div>' +
            '<div style="display:flex;align-items:baseline;gap:4px;">' +
                '<span class="lb-points">' + p.points + '</span>' +
                '<span style="font-size:12px;color:#64748b;">pts</span>' +
            '</div>' +
        '</div>';
    }).join('');
}

// ================================================================
//  HISTORY
// ================================================================
function renderHistory() {
    const history = state.history || [];
    if (!history.length) {
        document.getElementById('history-list').innerHTML = '';
        document.getElementById('history-empty').style.display = 'block';
        return;
    }

    document.getElementById('history-empty').style.display = 'none';
    document.getElementById('history-list').innerHTML = history.map(r =>
        '<div class="card" style="margin-bottom:10px;cursor:pointer;" onclick="loadHistoryDetail(' + r.id + ')">' +
            '<div style="display:flex;align-items:center;justify-content:space-between;">' +
                '<div>' +
                    '<div style="font-size:17px;font-weight:700;color:#f1f5f9;">Matchweek ' + r.number + '</div>' +
                    '<div style="font-size:13px;color:#94a3b8;margin-top:2px;">' +
                        (r.locksAt ? new Date(r.locksAt).toLocaleDateString() : '') + '</div>' +
                '</div>' +
                '<div style="display:flex;align-items:center;gap:8px;">' +
                    '<span class="badge badge-slate">Resolved</span>' +
                    '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#64748b" stroke-width="2"><path d="M9 18l6-6-6-6"/></svg>' +
                '</div>' +
            '</div>' +
        '</div>'
    ).join('');
}

async function loadHistoryDetail(roundId) {
    showScreen('history-detail');
    document.getElementById('history-detail-title').textContent = 'Loading…';
    document.getElementById('history-detail-fixtures').innerHTML =
        '<div style="color:#94a3b8;text-align:center;padding:24px;"><span class="spinner"></span></div>';
    document.getElementById('history-detail-entries').innerHTML = '';

    try {
        const data = await api('GET', '/api/history/' + roundId);
        state.historyDetail = data;
        renderHistoryDetail(data);
    } catch(e) {
        document.getElementById('history-detail-fixtures').innerHTML =
            '<div style="color:#ef4444;text-align:center;padding:16px;">Failed to load</div>';
    }
}

function renderHistoryDetail(data) {
    const { round, entries } = data;
    document.getElementById('history-detail-title').textContent = 'Matchweek ' + round.number;
    const fixtures = round.fixtures || [];

    const fixturesHtml = fixtures.map(f =>
        '<div style="display:flex;align-items:center;justify-content:space-between;padding:10px 0;border-bottom:1px solid #263347;">' +
            '<div style="flex:1;font-size:13px;color:#94a3b8;text-align:right;">' + f.homeTeam + '</div>' +
            '<div style="min-width:60px;text-align:center;font-size:15px;font-weight:700;color:#f1f5f9;">' +
                (f.homeScore ?? 0) + '–' + (f.awayScore ?? 0) + '</div>' +
            '<div style="flex:1;font-size:13px;color:#94a3b8;">' + f.awayTeam + '</div>' +
        '</div>'
    ).join('');

    document.getElementById('history-detail-fixtures').innerHTML =
        '<div class="card"><div style="font-size:13px;font-weight:600;color:#94a3b8;margin-bottom:8px;text-transform:uppercase;letter-spacing:0.5px;">Final Results</div>' +
        fixturesHtml + '</div>';

    const sorted = [...entries].sort((a, b) => b.points - a.points);
    const entriesHtml = sorted.map(e => {
        const isMe = state.player && e.playerId === state.player.id;
        const displayName = e.displayName || e.playerName;
        const picksHtml = fixtures.map(f => {
            const pick = e.predictions && e.predictions[f.id] ? e.predictions[f.id] : null;
            const result = f.result;
            const isCorrect = pick && result && pick === result;
            const isWrong = pick && result && pick !== result;
            return '<div style="display:flex;align-items:center;justify-content:space-between;padding:8px 0;border-bottom:1px solid #263347;">' +
                '<div style="font-size:13px;color:#94a3b8;">' + f.homeTeam + ' v ' + f.awayTeam + '</div>' +
                '<div style="font-weight:700;color:' + (isCorrect ? '#22c55e' : isWrong ? '#ef4444' : '#64748b') + '">' + (pick || '–') + '</div>' +
            '</div>';
        }).join('');

        return '<div style="padding:0 16px;border-bottom:1px solid #334155;">' +
            '<div style="display:flex;align-items:center;padding:14px 0;cursor:pointer;" onclick="toggleDrawer(this)">' +
                '<div style="flex:1;font-size:16px;font-weight:600;color:' + (isMe ? '#22c55e' : '#f1f5f9') + ';">' +
                    displayName + (isMe ? ' (you)' : '') + (e.isPerfect ? ' 🏆' : '') + '</div>' +
                '<div style="display:flex;align-items:baseline;gap:4px;">' +
                    '<span style="font-size:18px;font-weight:700;color:#22c55e;">' + e.points + '</span>' +
                    '<span style="font-size:12px;color:#64748b;">pts</span>' +
                '</div>' +
            '</div>' +
            '<div class="picks-drawer" style="display:none;padding-bottom:12px;">' + picksHtml + '</div>' +
        '</div>';
    }).join('');

    document.getElementById('history-detail-entries').innerHTML = entriesHtml;
}

// ================================================================
//  ADMIN
// ================================================================
function renderAdmin() {
    if (!state.player?.isAdmin) { showScreen('home'); return; }

    document.getElementById('admin-jackpot').textContent = state.season?.jackpot ?? 0;

    if (state.season) {
        const isPending = state.season.isPendingSettlement;
        document.getElementById('admin-season-badge').innerHTML = isPending
            ? '<span class="badge badge-amber">Pending Settlement</span>'
            : '<span class="badge badge-green">Active</span>';
        document.getElementById('admin-season-info').textContent =
            state.season.leagueName + ' · ' + state.season.entryTokens + ' tokens/round';
        document.getElementById('admin-settlement-card').style.display = isPending ? 'block' : 'none';
        document.getElementById('admin-pending-settlement-btn').style.display = isPending ? 'none' : 'inline-flex';
    } else {
        document.getElementById('admin-season-badge').innerHTML = '<span class="badge badge-slate">None</span>';
        document.getElementById('admin-season-info').textContent = 'No active season.';
        document.getElementById('admin-settlement-card').style.display = 'none';
        document.getElementById('admin-pending-settlement-btn').style.display = 'none';
    }

    if (state.round) {
        document.getElementById('admin-round-info').textContent =
            'Matchweek ' + state.round.number + ' · ' + state.round.status;
        ['admin-sync-fixtures-btn','admin-sync-results-btn'].forEach(id =>
            document.getElementById(id).style.display = 'inline-flex');
        document.getElementById('admin-resolve-btn').style.display =
            state.round.status !== 'resolved' ? 'inline-flex' : 'none';

        document.getElementById('admin-sync-fixtures-btn').onclick = adminSyncFixtures;
        document.getElementById('admin-sync-results-btn').onclick = adminSyncResults;
        document.getElementById('admin-resolve-btn').onclick = adminResolveRound;
    } else {
        document.getElementById('admin-round-info').textContent = 'No active round.';
        ['admin-sync-fixtures-btn','admin-sync-results-btn','admin-resolve-btn'].forEach(id =>
            document.getElementById(id).style.display = 'none');
    }
}

async function adminAction(btnId, label, fn) {
    const btn = document.getElementById(btnId);
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner"></span>';
    try {
        await fn();
        const s = await api('GET', '/api/state');
        applyStateData(s);
        saveLocal();
        renderAdmin();
    } catch(e) {
        toast(e.message || 'Action failed', 'error');
    } finally {
        btn.disabled = false;
        btn.textContent = label;
    }
}

function adminSyncFixtures() {
    if (!state.round) return;
    adminAction('admin-sync-fixtures-btn', 'Sync Fixtures', async () => {
        const d = await api('POST', '/api/admin/sync/fixtures', { round_id: state.round.id });
        toast(d.message || 'Fixtures synced');
    });
}

function adminSyncResults() {
    if (!state.round) return;
    adminAction('admin-sync-results-btn', 'Sync Results', async () => {
        const d = await api('POST', '/api/admin/sync/results', { round_id: state.round.id });
        toast(d.message || 'Results synced');
    });
}

function adminChargeRound() {
    if (!state.round) return;
    if (!confirm('Deduct ' + (state.season?.entryTokens || '?') + ' tokens from all complete entries?')) return;
    adminAction('admin-charge-btn', 'Charge Entry', async () => {
        const d = await api('POST', '/api/admin/charge-round', { round_id: state.round.id });
        toast(d.message || 'Players charged');
    });
}

function adminResolveRound() {
    if (!state.round) return;
    if (!confirm('Resolve this round? This scores all predictions and updates the leaderboard.')) return;
    adminAction('admin-resolve-btn', 'Resolve Round', async () => {
        const d = await api('POST', '/api/admin/rounds/' + state.round.id + '/resolve');
        toast(d.message || 'Round resolved');
    });
}

async function adminStartSettlement() {
    if (!confirm('Move the season to pending settlement? Players will not be able to make new predictions.')) return;
    adminAction('admin-pending-settlement-btn', 'Start Settlement', async () => {
        await api('POST', '/api/admin/season/pending-settlement');
        toast('Season moved to pending settlement');
    });
}

// ================================================================
//  ADMIN PLAYERS
// ================================================================
async function loadAdminPlayers() {
    showScreen('admin-players');
    document.getElementById('admin-players-list').innerHTML =
        '<div style="color:#94a3b8;text-align:center;padding:32px;"><span class="spinner"></span></div>';
    try {
        const data = await api('GET', '/api/admin/players');
        state.adminPlayers = data.players;
        renderAdminPlayers();
    } catch(e) {
        document.getElementById('admin-players-list').innerHTML =
            '<div style="color:#ef4444;text-align:center;padding:16px;">Failed to load</div>';
    }
}

function renderAdminPlayers() {
    const players = state.adminPlayers || [];
    if (!players.length) {
        document.getElementById('admin-players-list').innerHTML =
            '<div style="color:#94a3b8;text-align:center;padding:32px;">No players yet.</div>';
        return;
    }

    document.getElementById('admin-players-list').innerHTML = players.map(p =>
        '<div class="admin-item" onclick="openPlayerForm(' + p.id + ')">' +
            '<div style="flex:1;">' +
                '<div style="font-size:16px;font-weight:600;color:#f1f5f9;">' + p.name +
                    (p.isAdmin ? ' <span class="badge badge-blue" style="margin-left:6px;">Admin</span>' : '') + '</div>' +
                '<div style="font-size:13px;color:#94a3b8;margin-top:2px;">' + p.tokenBalance + ' tokens</div>' +
            '</div>' +
            '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#64748b" stroke-width="2"><path d="M9 18l6-6-6-6"/></svg>' +
        '</div>'
    ).join('');
}

function openPlayerForm(playerId) {
    showScreen('admin-player-form');
    const p = playerId ? (state.adminPlayers || []).find(x => x.id === playerId) : null;

    document.getElementById('player-form-id').value = playerId || '';
    document.getElementById('player-form-title').textContent = p ? 'Edit Player' : 'Add Player';
    document.getElementById('player-form-name').value = p?.name || '';
    document.getElementById('player-form-nickname').value = p?.nickname || '';
    document.getElementById('player-form-pin').value = '';
    document.getElementById('player-form-tokens').value = p !== null ? (p?.tokenBalance ?? 0) : 0;
    document.getElementById('player-form-admin').checked = p?.isAdmin || false;
    document.getElementById('player-form-pin-hint').style.display = p ? 'block' : 'none';
    document.getElementById('player-form-delete').style.display = p ? 'block' : 'none';
    document.getElementById('player-form-save').onclick = savePlayerForm;
    document.getElementById('player-form-delete').onclick = () => deletePlayer(playerId);
}

async function savePlayerForm() {
    const id = document.getElementById('player-form-id').value;
    const name = document.getElementById('player-form-name').value.trim();
    const nickname = document.getElementById('player-form-nickname').value.trim();
    const pin  = document.getElementById('player-form-pin').value.trim();
    const tokenBalance = parseInt(document.getElementById('player-form-tokens').value) || 0;
    const isAdmin = document.getElementById('player-form-admin').checked;

    if (!name) { toast('Name is required', 'error'); return; }
    if (!id && !pin) { toast('PIN is required for new players', 'error'); return; }
    if (pin && (pin.length < 4 || pin.length > 8)) { toast('PIN must be 4–8 digits', 'error'); return; }

    const btn = document.getElementById('player-form-save');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner"></span>';

    const payload = { name, is_admin: isAdmin, token_balance: tokenBalance };
    if (pin) payload.pin = pin;

    try {
        if (id) {
            await api('PUT', '/api/admin/players/' + id, payload);
            // Also save nickname separately
            await api('PUT', '/api/admin/players/' + id + '/nickname', { nickname: nickname || null });
        } else {
            const res = await api('POST', '/api/admin/players', payload);
            // Save nickname for new player too if provided
            if (nickname && res.player?.id) {
                await api('PUT', '/api/admin/players/' + res.player.id + '/nickname', { nickname });
            }
        }
        toast('Player saved ✓');
        const data = await api('GET', '/api/admin/players');
        state.adminPlayers = data.players;
        showScreen('admin-players');
    } catch(e) {
        toast(e.message || 'Save failed', 'error');
    } finally {
        btn.disabled = false;
        btn.textContent = 'Save Player';
    }
}

async function deletePlayer(playerId) {
    if (!confirm('Delete this player? This cannot be undone.')) return;
    try {
        await api('DELETE', '/api/admin/players/' + playerId);
        toast('Player deleted');
        const data = await api('GET', '/api/admin/players');
        state.adminPlayers = data.players;
        showScreen('admin-players');
    } catch(e) {
        toast(e.message || 'Delete failed', 'error');
    }
}

// ================================================================
//  ADMIN ROUNDS
// ================================================================
async function renderAdminRounds() {
    document.getElementById('admin-rounds-list').innerHTML =
        '<div style="color:#94a3b8;text-align:center;padding:32px;"><span class="spinner"></span></div>';
    try {
        const data = await api('GET', '/api/admin/rounds');
        state.adminRounds = data.rounds || [];
        if (!state.adminRounds.length) {
            document.getElementById('admin-rounds-list').innerHTML =
                '<div style="color:#94a3b8;text-align:center;padding:32px;">No rounds yet.</div>';
            return;
        }
        document.getElementById('admin-rounds-list').innerHTML = state.adminRounds.map(r =>
            '<div class="admin-item" onclick="editRound(' + r.id + ')">' +
                '<div style="flex:1;">' +
                    '<div style="font-size:16px;font-weight:600;color:#f1f5f9;">Matchweek ' + r.number + '</div>' +
                    '<div style="font-size:13px;color:#94a3b8;margin-top:2px;">' +
                        r.status + ' · ' +
                        (r.locksAt ? new Date(r.locksAt).toLocaleDateString() : 'No lock time') + '</div>' +
                '</div>' +
                roundStatusBadge(r.status, r.isLocked) +
            '</div>'
        ).join('');
    } catch(e) {
        document.getElementById('admin-rounds-list').innerHTML =
            '<div style="color:#ef4444;text-align:center;padding:16px;">Failed to load</div>';
    }
}

function editRound(roundId) {
    showScreen('admin-round-form');
    const r = roundId ? (state.adminRounds || []).find(x => x.id === roundId) : null;

    document.getElementById('round-form-id').value = roundId || '';
    document.getElementById('round-form-title').textContent = r ? 'Edit Round' : 'New Round';
    document.getElementById('round-form-number').value = r?.number || '';
    document.getElementById('round-form-status-group').style.display = r ? 'block' : 'none';

    if (r?.locksAt) {
        const dt = new Date(r.locksAt);
        document.getElementById('round-form-locks-at').value =
            new Date(dt.getTime() - dt.getTimezoneOffset() * 60000).toISOString().slice(0,16);
    } else {
        document.getElementById('round-form-locks-at').value = '';
    }

    if (r?.status) document.getElementById('round-form-status').value = r.status;
    document.getElementById('round-form-save').onclick = saveRoundForm;
}

async function saveRoundForm() {
    const id = document.getElementById('round-form-id').value;
    const number = parseInt(document.getElementById('round-form-number').value);
    const locksAtRaw = document.getElementById('round-form-locks-at').value;
    const status = document.getElementById('round-form-status').value;

    if (!locksAtRaw) { toast('Set a lock time', 'error'); return; }
    const locksAt = new Date(locksAtRaw).toISOString();

    const btn = document.getElementById('round-form-save');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner"></span>';

    try {
        if (id) {
            await api('PUT', '/api/admin/rounds/' + id, { status, locks_at: locksAt });
        } else {
            if (!number || number < 1) { toast('Enter matchweek number', 'error'); btn.disabled=false; btn.textContent='Save Round'; return; }
            await api('POST', '/api/admin/rounds', { number, locks_at: locksAt });
        }
        toast('Round saved ✓');
        const s = await api('GET', '/api/state');
        applyStateData(s);
        saveLocal();
        showScreen('admin-rounds');
        renderAdminRounds();
    } catch(e) {
        toast(e.message || 'Save failed', 'error');
    } finally {
        btn.disabled = false;
        btn.textContent = 'Save Round';
    }
}

// ================================================================
//  ADMIN SEASON FORM
// ================================================================
document.getElementById('season-form-save').addEventListener('click', async () => {
    const leagueId     = document.getElementById('season-form-league-id').value.trim().toUpperCase();
    const leagueName   = document.getElementById('season-form-league-name').value.trim();
    const entryTokens  = parseInt(document.getElementById('season-form-entry-tokens').value) || 0;

    if (!leagueId)     { toast('Enter league code', 'error'); return; }
    if (!leagueName)   { toast('Enter league name', 'error'); return; }
    if (entryTokens < 1) { toast('Entry tokens must be ≥ 1', 'error'); return; }

    const btn = document.getElementById('season-form-save');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner"></span>';

    try {
        await api('POST', '/api/admin/season', { league_id: leagueId, league_name: leagueName, entry_tokens: entryTokens });
        toast('Season started ✓');
        const s = await api('GET', '/api/state');
        applyStateData(s);
        saveLocal();
        showScreen('admin');
    } catch(e) {
        toast(e.message || 'Failed', 'error');
    } finally {
        btn.disabled = false;
        btn.textContent = 'Start Season';
    }
});

// ================================================================
//  BADGE CONSTANTS
// ================================================================
const BADGE_REQUIREMENTS = {
    sniper:         { kafa: '4+ pts in 2 rounds', rakija: '6+ pts in 3 rounds', zlato: '8+ pts in 3 rounds' },
    perfectionist:  { kafa: '1 perfect round', rakija: '2 perfect rounds', zlato: '3 perfect rounds' },
    iron_man:       { kafa: '3 rounds in a row', rakija: '5 rounds in a row', zlato: '8 rounds in a row' },
    comeback_kid:   { kafa: '1 comeback', rakija: '2 comebacks', zlato: '3 comebacks' },
    jackpot:        { kafa: '1 jackpot win', rakija: '2 jackpot wins', zlato: '3 jackpot wins' },
    ledeni:         { kafa: '6 cold rounds', rakija: '9 cold rounds', zlato: '12 cold rounds' },
};
const BADGE_EMOJIS = { sniper:'🎯', perfectionist:'⭐', iron_man:'⛓️', comeback_kid:'🔄', jackpot:'💰', ledeni:'❄️' };
const BADGE_LABELS = { sniper:'Sniper', perfectionist:'Perfectionist', iron_man:'Iron Man', comeback_kid:'Comeback Kid', jackpot:'Jackpot', ledeni:'Ledeni' };
const TIER_COLORS = { kafa:'background:#78350f;color:#fde68a;', rakija:'background:#92400e;color:#fff;', zlato:'background:#fbbf24;color:#1a1a1a;' };
const TIER_LABELS = { kafa:'Kafa', rakija:'Rakija', zlato:'Zlato' };
const TX_TYPE_LABELS = {
    credit: 'Credit', debit_round: 'Entry Fee', payout_jackpot: 'Jackpot Win',
    payout_season_winner: 'Season Win', settlement_refund: 'Settlement Refund',
    settlement_collected: 'Settlement Collected', adjustment: 'Adjustment',
    debit: 'Debit',
};

// ================================================================
//  LAZY LOADERS — NEW SCREENS
// ================================================================
async function loadBalances() {
    try {
        const data = await api('GET', '/api/players/balances');
        state.balances = data.players;
        showScreen('balances');
    } catch(e) {
        toast(e.message || 'Failed to load balances', 'error');
    }
}

function renderBalancesScreen() {
    const players = state.balances || [];
    const list = document.getElementById('balances-list');
    if (!list) return;
    if (!players.length) {
        list.innerHTML = '<div style="color:#94a3b8;text-align:center;padding:32px;">No players.</div>';
        return;
    }
    list.innerHTML = players.map(p => {
        const balColor = p.tokenBalance < 0 ? '#ef4444' : '#22c55e';
        return '<div class="admin-item" onclick="loadLedger(' + p.id + ')">' +
            '<div style="flex:1;">' +
                '<div style="font-size:16px;font-weight:600;color:#f1f5f9;">' + (p.displayName || p.name) + '</div>' +
                '<div style="font-size:13px;color:' + balColor + ';margin-top:2px;">' + p.tokenBalance + ' tokens</div>' +
            '</div>' +
            '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#64748b" stroke-width="2"><path d="M9 18l6-6-6-6"/></svg>' +
        '</div>';
    }).join('');
}

async function loadLedger(playerId) {
    state.currentLedgerPlayerId = playerId;
    showScreen('ledger');
    document.getElementById('ledger-list').innerHTML =
        '<div style="color:#94a3b8;text-align:center;padding:24px;"><span class="spinner"></span></div>';
    try {
        const data = await api('GET', '/api/players/' + playerId + '/ledger');
        state.ledger[playerId] = data;
        renderLedger(playerId);
    } catch(e) {
        document.getElementById('ledger-list').innerHTML =
            '<div style="color:#ef4444;text-align:center;padding:16px;">Failed to load</div>';
    }
}

async function loadMoreLedger(playerId, page) {
    try {
        const data = await api('GET', '/api/players/' + playerId + '/ledger?page=' + page);
        const existing = state.ledger[playerId];
        if (existing) {
            existing.transactions = [...(existing.transactions || []), ...data.transactions];
            existing.meta = data.meta;
        } else {
            state.ledger[playerId] = data;
        }
        renderLedger(playerId);
    } catch(e) {
        toast('Failed to load more', 'error');
    }
}

function renderLedger(playerId) {
    const data = state.ledger[playerId];
    if (!data) return;
    const { player, transactions, meta } = data;

    document.getElementById('ledger-player-name').textContent = player.displayName || player.name;
    document.getElementById('ledger-player-balance').textContent = player.tokenBalance;
    document.getElementById('ledger-player-balance').style.color = player.tokenBalance < 0 ? '#ef4444' : '#22c55e';

    if (!transactions || !transactions.length) {
        document.getElementById('ledger-list').innerHTML =
            '<div style="color:#94a3b8;text-align:center;padding:32px;">No transactions yet.</div>';
        return;
    }

    const rows = transactions.map(t => {
        const isPos = t.amount > 0;
        const amtColor = isPos ? '#22c55e' : '#ef4444';
        const amtPrefix = isPos ? '+' : '';
        const date = t.createdAt ? new Date(t.createdAt).toLocaleDateString() : '';
        const before = t.balanceBefore != null ? t.balanceBefore : '—';
        const after = t.balanceAfter != null ? t.balanceAfter : '—';
        const label = TX_TYPE_LABELS[t.type] || t.type;
        return '<div style="padding:12px 16px;border-bottom:1px solid #263347;">' +
            '<div style="display:flex;justify-content:space-between;align-items:flex-start;">' +
                '<div style="flex:1;">' +
                    '<div style="font-size:13px;font-weight:600;color:#94a3b8;">' + label + '</div>' +
                    (t.description ? '<div style="font-size:13px;color:#f1f5f9;margin-top:2px;">' + t.description + '</div>' : '') +
                    '<div style="font-size:11px;color:#475569;margin-top:4px;">' + date + ' · ' + before + ' → ' + after + '</div>' +
                '</div>' +
                '<div style="font-size:17px;font-weight:700;color:' + amtColor + ';margin-left:12px;">' + amtPrefix + t.amount + '</div>' +
            '</div>' +
        '</div>';
    }).join('');

    const loadMore = (meta && meta.currentPage < meta.lastPage)
        ? '<div style="padding:16px;text-align:center;"><button class="btn btn-secondary" onclick="loadMoreLedger(' + playerId + ',' + (meta.currentPage + 1) + ')">Load more</button></div>'
        : '';

    document.getElementById('ledger-list').innerHTML = rows + loadMore;
}

async function openBadges(playerId) {
    state.currentBadgePlayerId = playerId;
    showScreen('badges');
    document.getElementById('badges-shelf').innerHTML =
        '<div style="color:#94a3b8;text-align:center;padding:24px;"><span class="spinner"></span></div>';
    try {
        const data = await api('GET', '/api/players/' + playerId + '/badges');
        state.badges[playerId] = data;
        renderBadges(playerId);
    } catch(e) {
        document.getElementById('badges-shelf').innerHTML =
            '<div style="color:#ef4444;text-align:center;padding:16px;">Failed to load</div>';
    }
}

function renderBadges(playerId) {
    const data = state.badges[playerId];
    if (!data) return;
    const { player, shelf } = data;

    document.getElementById('badges-player-name').textContent = player.displayName || player.name;

    if (!shelf || !shelf.length) {
        document.getElementById('badges-shelf').innerHTML =
            '<div style="color:#94a3b8;text-align:center;padding:32px;">No badges data.</div>';
        return;
    }

    const html = shelf.map(cat => {
        const emoji = BADGE_EMOJIS[cat.category] || '🏅';
        const label = BADGE_LABELS[cat.category] || cat.category;
        const slots = cat.slots.map(slot => {
            const tierColor = TIER_COLORS[slot.tier] || '';
            const tierLabel = TIER_LABELS[slot.tier] || slot.tier;
            const req = (BADGE_REQUIREMENTS[cat.category] || {})[slot.tier] || '';
            if (slot.earned) {
                return '<div style="flex:1;border-radius:10px;padding:10px 6px;text-align:center;' + tierColor + '">' +
                    '<div style="font-size:22px;">' + emoji + '</div>' +
                    '<div style="font-size:11px;font-weight:700;margin-top:4px;">' + tierLabel + '</div>' +
                '</div>';
            } else {
                return '<div style="flex:1;border-radius:10px;padding:10px 6px;text-align:center;background:#1e293b;color:#475569;border:1px dashed #334155;">' +
                    '<div style="font-size:22px;filter:grayscale(1);opacity:0.3;">🔒</div>' +
                    '<div style="font-size:10px;margin-top:4px;line-height:1.3;">' + req + '</div>' +
                '</div>';
            }
        }).join('');

        return '<div class="card" style="margin-bottom:12px;">' +
            '<div style="font-size:15px;font-weight:700;color:#f1f5f9;margin-bottom:10px;">' + emoji + ' ' + label + '</div>' +
            '<div style="display:flex;gap:8px;">' + slots + '</div>' +
        '</div>';
    }).join('');

    document.getElementById('badges-shelf').innerHTML = html;
}

async function loadHallOfFame() {
    showScreen('hall-of-fame');
    document.getElementById('hof-list').innerHTML =
        '<div style="color:#94a3b8;text-align:center;padding:24px;"><span class="spinner"></span></div>';
    try {
        const data = await api('GET', '/api/hall-of-fame');
        state.hallOfFame = data.seasons;
        renderHallOfFame();
    } catch(e) {
        document.getElementById('hof-list').innerHTML =
            '<div style="color:#ef4444;text-align:center;padding:16px;">Failed to load</div>';
    }
}

function renderHallOfFame() {
    const seasons = state.hallOfFame || [];
    if (!seasons.length) {
        document.getElementById('hof-list').innerHTML =
            '<div style="padding:48px 24px;text-align:center;color:#94a3b8;">No completed seasons yet.</div>';
        return;
    }

    document.getElementById('hof-list').innerHTML = seasons.map(s => {
        const closedAt = s.closedAt ? new Date(s.closedAt).toLocaleDateString() : '';
        const jackpotWinner = s.jackpotWinner?.displayName || 'No jackpot winner';
        const lbWinner = s.leaderboardWinner?.displayName || '—';
        const posSeason = s.playerOfSeason?.displayName || '—';
        return '<div class="card" style="margin:0 16px 12px;">' +
            '<div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:12px;">' +
                '<div>' +
                    '<div style="font-size:17px;font-weight:700;color:#f1f5f9;">' + (s.leagueName || 'Season') + '</div>' +
                    '<div style="font-size:13px;color:#94a3b8;margin-top:2px;">' + s.totalRounds + ' rounds · ' + closedAt + '</div>' +
                '</div>' +
                '<div style="text-align:right;">' +
                    '<div style="font-size:12px;color:#94a3b8;">Jackpot</div>' +
                    '<div style="font-size:18px;font-weight:700;color:#22c55e;">' + s.totalJackpot + '</div>' +
                '</div>' +
            '</div>' +
            '<div style="display:flex;flex-direction:column;gap:8px;border-top:1px solid #334155;padding-top:12px;">' +
                '<div style="display:flex;justify-content:space-between;">' +
                    '<div style="font-size:13px;color:#94a3b8;">💰 Jackpot Winner</div>' +
                    '<div style="font-size:13px;font-weight:600;color:#f1f5f9;">' + jackpotWinner + '</div>' +
                '</div>' +
                '<div style="display:flex;justify-content:space-between;">' +
                    '<div style="font-size:13px;color:#94a3b8;">🏆 Leaderboard Champion</div>' +
                    '<div style="font-size:13px;font-weight:600;color:#f1f5f9;">' + lbWinner + '</div>' +
                '</div>' +
                '<div style="display:flex;justify-content:space-between;">' +
                    '<div style="font-size:13px;color:#94a3b8;">⭐ Player of the Season</div>' +
                    '<div style="font-size:13px;font-weight:600;color:#f1f5f9;">' + posSeason + '</div>' +
                '</div>' +
            '</div>' +
        '</div>';
    }).join('');
}

async function loadSettlements() {
    showScreen('admin-settlement');
    document.getElementById('settlement-content').innerHTML =
        '<div style="color:#94a3b8;text-align:center;padding:24px;"><span class="spinner"></span></div>';
    try {
        const data = await api('GET', '/api/admin/season/settlements');
        state.settlements = data;
        renderSettlements();
    } catch(e) {
        document.getElementById('settlement-content').innerHTML =
            '<div style="color:#ef4444;text-align:center;padding:16px;">' + (e.message || 'Failed to load') + '</div>';
    }
}

function renderSettlements() {
    const s = state.settlements;
    if (!s) return;
    const total = (s.unsettled || []).length + (s.settled || []).length;
    const settledCount = (s.settled || []).length;

    document.getElementById('settlement-progress').textContent = settledCount + ' of ' + total + ' settled';
    const allDone = s.unsettled.length === 0;
    const closeBtn = document.getElementById('settlement-close-btn');
    if (closeBtn) {
        closeBtn.disabled = !allDone;
        closeBtn.style.opacity = allDone ? '1' : '0.5';
    }

    const unsettledHtml = s.unsettled.length
        ? '<div style="font-size:13px;font-weight:600;color:#94a3b8;text-transform:uppercase;letter-spacing:0.5px;padding:0 16px 8px;">Unsettled</div>' +
          s.unsettled.map(p => {
            const balColor = p.tokenBalance < 0 ? '#ef4444' : '#22c55e';
            return '<div style="display:flex;align-items:center;padding:12px 16px;border-bottom:1px solid #263347;gap:12px;">' +
                '<div style="flex:1;">' +
                    '<div style="font-size:15px;font-weight:600;color:#f1f5f9;">' + p.displayName + '</div>' +
                    '<div style="font-size:13px;color:' + balColor + ';margin-top:2px;">' + p.tokenBalance + ' tokens</div>' +
                '</div>' +
                '<button class="btn btn-secondary" style="min-height:36px;font-size:13px;padding:0 12px;" onclick="settlePlayer(' + p.playerId + ')">Mark Settled</button>' +
            '</div>';
          }).join('')
        : '';

    const settledHtml = s.settled.length
        ? '<div style="font-size:13px;font-weight:600;color:#94a3b8;text-transform:uppercase;letter-spacing:0.5px;padding:16px 16px 8px;">Settled</div>' +
          s.settled.map(p => {
            const amtColor = p.settledAmount >= 0 ? '#22c55e' : '#ef4444';
            const date = p.settledAt ? new Date(p.settledAt).toLocaleDateString() : '';
            return '<div style="display:flex;align-items:center;padding:12px 16px;border-bottom:1px solid #263347;">' +
                '<div style="flex:1;">' +
                    '<div style="font-size:15px;font-weight:600;color:#f1f5f9;">' + p.displayName + '</div>' +
                    '<div style="font-size:13px;color:#94a3b8;margin-top:2px;">' + date + '</div>' +
                '</div>' +
                '<div style="font-size:15px;font-weight:700;color:' + amtColor + ';">' + p.settledAmount + '</div>' +
            '</div>';
          }).join('')
        : '';

    document.getElementById('settlement-content').innerHTML = unsettledHtml + settledHtml;
}

async function settlePlayer(playerId) {
    try {
        await api('POST', '/api/admin/season/settlements/' + playerId);
        toast('Player settled ✓');
        const data = await api('GET', '/api/admin/season/settlements');
        state.settlements = data;
        renderSettlements();
    } catch(e) {
        toast(e.message || 'Settlement failed', 'error');
    }
}

async function closeSeasonFinal() {
    if (!confirm('Close the season? This is final and cannot be undone.')) return;
    const btn = document.getElementById('settlement-close-btn');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner"></span>';
    try {
        await api('POST', '/api/admin/season/close');
        toast('Season closed ✓');
        const s = await api('GET', '/api/state');
        applyStateData(s);
        saveLocal();
        showScreen('admin');
        renderAdmin();
    } catch(e) {
        toast(e.message || 'Failed to close season', 'error');
        btn.disabled = false;
        btn.textContent = 'Close Season';
    }
}

// Credit tokens form
async function submitCreditTokens() {
    const playerId = document.getElementById('credit-player-id').value;
    const amount = parseInt(document.getElementById('credit-amount').value) || 0;
    const description = document.getElementById('credit-description').value.trim();

    if (!playerId) { toast('Select a player', 'error'); return; }
    if (amount < 1) { toast('Amount must be at least 1', 'error'); return; }

    const btn = document.getElementById('credit-submit-btn');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner"></span>';

    try {
        const data = await api('POST', '/api/admin/players/' + playerId + '/credit', {
            amount, description: description || null
        });
        // Optimistic: update balances in state
        const idx = state.balances.findIndex(p => p.id === data.player.id);
        if (idx !== -1) state.balances[idx].tokenBalance = data.player.tokenBalance;

        document.getElementById('credit-result').innerHTML =
            '<div class="card" style="background:#22c55e22;border-color:#22c55e44;margin-top:16px;text-align:center;">' +
            '✓ Credited <strong style="color:#22c55e;">' + amount + '</strong> tokens to <strong>' + (data.player.nickname || data.player.name) + '</strong><br>' +
            '<span style="font-size:13px;color:#94a3b8;">New balance: ' + data.player.tokenBalance + '</span>' +
            '</div>';

        document.getElementById('credit-amount').value = '';
        document.getElementById('credit-description').value = '';
    } catch(e) {
        toast(e.message || 'Credit failed', 'error');
    } finally {
        btn.disabled = false;
        btn.textContent = 'Credit Tokens';
    }
}

function renderCreditForm() {
    const players = state.adminPlayers && state.adminPlayers.length ? state.adminPlayers : state.balances;
    const options = (players || []).map(p =>
        '<option value="' + p.id + '">' + (p.nickname || p.name || p.displayName) + ' (' + p.tokenBalance + ' tokens)</option>'
    ).join('');
    const sel = document.getElementById('credit-player-id');
    if (sel) sel.innerHTML = '<option value="">— Select player —</option>' + options;
    document.getElementById('credit-result').innerHTML = '';
}

// ================================================================
//  BOOT
// ================================================================
window.addEventListener('DOMContentLoaded', init);
</script>
</body>
</html>
