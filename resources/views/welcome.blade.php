<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="#f0e6d2">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Tipping Pool</title>
    <link rel="manifest" href="/manifest.json">
    <link rel="apple-touch-icon" href="/icons/icon-192.png">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="Tipping Pool">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Mono:wght@400;500&family=Fraunces:opsz,wght@9..144,400;9..144,600;9..144,700;9..144,800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
<div id="app">

    {{-- Loading --}}
    <div id="loading">
        <div style="width:64px;height:64px;border-radius:50%;border:2px solid var(--rule);background:var(--surface);display:flex;align-items:center;justify-content:center;">
            <svg width="32" height="32" viewBox="0 0 24 24" fill="none"><path d="M7 4h10v3a5 5 0 01-10 0V4z" stroke="var(--accent)" stroke-width="1.6" stroke-linejoin="round"/><path d="M7 6H4.5a1 1 0 00-1 1v1a3 3 0 003 3M17 6h2.5a1 1 0 011 1v1a3 3 0 01-3 3" stroke="var(--accent)" stroke-width="1.6"/><path d="M9 13.5l-.5 3.5h7l-.5-3.5M8 20h8" stroke="var(--accent)" stroke-width="1.6" stroke-linecap="round"/></svg>
        </div>
        <div style="font-family:'DM Mono',monospace;font-size:11px;letter-spacing:1.4px;text-transform:uppercase;color:var(--ink-soft);">Loading…</div>
        <div class="spinner" style="border-top-color:var(--accent);"></div>
    </div>

    {{-- Toast --}}
    <div id="toast"></div>

    {{-- =========================================================
         SCREEN: Login
         ========================================================= --}}
    <div id="screen-login" class="screen">
        <div class="screen-content" style="display:flex;flex-direction:column;background:var(--bg);padding-bottom:env(safe-area-inset-bottom,0);">
            {{-- Logo --}}
            <div style="padding:40px 24px 20px;text-align:center;">
                <div style="width:76px;height:76px;margin:0 auto 18px;border-radius:50%;border:2px solid var(--rule);background:var(--surface);display:flex;align-items:center;justify-content:center;position:relative;">
                    <svg width="36" height="36" viewBox="0 0 24 24" fill="none"><path d="M7 4h10v3a5 5 0 01-10 0V4z" stroke="var(--accent)" stroke-width="1.6" stroke-linejoin="round"/><path d="M7 6H4.5a1 1 0 00-1 1v1a3 3 0 003 3M17 6h2.5a1 1 0 011 1v1a3 3 0 01-3 3" stroke="var(--accent)" stroke-width="1.6"/><path d="M9 13.5l-.5 3.5h7l-.5-3.5M8 20h8" stroke="var(--accent)" stroke-width="1.6" stroke-linecap="round"/></svg>
                    <div style="position:absolute;inset:-6px;border-radius:50%;border:1px dashed var(--rule);opacity:.35;"></div>
                </div>
                <div style="font-family:'DM Mono',monospace;font-size:11px;letter-spacing:2px;text-transform:uppercase;color:var(--ink-soft);margin-bottom:6px;">Tipping Pool · Est. 2024</div>
                <h1 style="font-family:'Fraunces','Times New Roman',serif;font-weight:800;font-size:38px;line-height:1;margin:4px 0;color:var(--ink);letter-spacing:-1px;">Kafana<br>Pool</h1>
                <p style="font-family:'Inter',system-ui;font-size:13px;color:var(--ink-soft);margin-top:10px;">Pick the matches. Take the pot.</p>
            </div>

            {{-- Name --}}
            <div style="padding:0 20px 14px;">
                <label class="field-label" for="login-name">Who's in?</label>
                <input id="login-name" class="input" type="text" placeholder="Enter your name" autocomplete="off" autocapitalize="words">
            </div>

            {{-- PIN display --}}
            <div style="padding:0 20px 4px;">
                <label class="field-label">4-digit PIN</label>
                <div class="pin-display">
                    <div class="pin-dot active" id="pin-d0"></div>
                    <div class="pin-dot" id="pin-d1"></div>
                    <div class="pin-dot" id="pin-d2"></div>
                    <div class="pin-dot" id="pin-d3"></div>
                </div>
            </div>

            {{-- Hidden actual input --}}
            <input id="login-pin" type="hidden" value="">

            {{-- Numpad --}}
            <div style="padding:0 20px 16px;">
                <div class="numpad">
                    <button class="numpad-key" onclick="pinTap('1')">1</button>
                    <button class="numpad-key" onclick="pinTap('2')">2</button>
                    <button class="numpad-key" onclick="pinTap('3')">3</button>
                    <button class="numpad-key" onclick="pinTap('4')">4</button>
                    <button class="numpad-key" onclick="pinTap('5')">5</button>
                    <button class="numpad-key" onclick="pinTap('6')">6</button>
                    <button class="numpad-key" onclick="pinTap('7')">7</button>
                    <button class="numpad-key" onclick="pinTap('8')">8</button>
                    <button class="numpad-key" onclick="pinTap('9')">9</button>
                    <button class="numpad-key empty" disabled></button>
                    <button class="numpad-key" onclick="pinTap('0')">0</button>
                    <button class="numpad-key del" onclick="pinDel()">← del</button>
                </div>
            </div>

            {{-- Sign in button --}}
            <div style="padding:0 20px 20px;">
                <button id="login-btn" class="btn btn-primary btn-full btn-lg">Sign In</button>
                <div id="login-error" style="display:none;text-align:center;color:var(--lose);font-size:14px;margin-top:10px;"></div>
            </div>
        </div>
    </div>

    {{-- =========================================================
         SCREEN: Home
         ========================================================= --}}
    <div id="screen-home" class="screen">
        <div class="screen-content" style="padding:0 0 80px;background:var(--bg);">

            {{-- Header --}}
            <div class="safe-top" style="padding:16px 16px 14px;border-bottom:1.5px solid var(--rule);">
                <div style="display:flex;justify-content:space-between;align-items:center;">
                    <div style="font-family:'DM Mono',monospace;font-size:11px;letter-spacing:1.4px;text-transform:uppercase;color:var(--ink-soft);" id="home-round-eyebrow">Round · Season</div>
                    <div class="balance-pill" id="home-balance-pill">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="9" stroke="var(--gold)" stroke-width="1.6"/><path d="M12 8v8M9.5 9.5h4a1.5 1.5 0 010 3h-3a1.5 1.5 0 000 3h4" stroke="var(--gold)" stroke-width="1.4" stroke-linecap="round"/></svg>
                        <span id="home-token-balance">0</span> <span style="opacity:.6;font-size:11px;">KM</span>
                    </div>
                </div>
                <div style="font-family:'Fraunces','Times New Roman',serif;font-size:26px;font-weight:700;margin-top:6px;line-height:1.1;letter-spacing:-0.5px;">
                    Dobro došao,<br><span style="color:var(--accent);" id="home-player-name"></span>
                </div>
            </div>

            <div style="padding:14px 16px 0;">

                {{-- No season --}}
                <div id="home-no-season" style="display:none;text-align:center;padding:32px 0;">
                    <div style="font-family:'DM Mono',monospace;font-size:11px;letter-spacing:1.4px;text-transform:uppercase;color:var(--ink-soft);margin-bottom:8px;">No Active Season</div>
                    <div style="color:var(--ink-soft);font-size:14px;">Check back soon!</div>
                </div>

                {{-- No round --}}
                <div id="home-no-round" style="display:none;text-align:center;padding:32px 0;">
                    <div style="font-family:'DM Mono',monospace;font-size:11px;letter-spacing:1.4px;text-transform:uppercase;color:var(--ink-soft);margin-bottom:8px;">No Active Round</div>
                    <div style="color:var(--ink-soft);font-size:14px;">Admin will open one soon!</div>
                </div>

                {{-- Round info --}}
                <div id="home-round-info" style="display:none;">

                    {{-- Jackpot card --}}
                    <div class="card" style="border-width:2px;text-align:center;margin-bottom:12px;position:relative;overflow:hidden;">
                        <div style="font-family:'DM Mono',monospace;font-size:10px;letter-spacing:2px;text-transform:uppercase;color:var(--ink-soft);margin-bottom:4px;">The Pot</div>
                        <div id="home-jackpot" style="font-family:'Fraunces','Times New Roman',serif;font-size:54px;font-weight:800;line-height:1;color:var(--accent);letter-spacing:-1.5px;">0</div>
                        <div style="font-family:'Fraunces','Times New Roman',serif;font-size:13px;font-weight:600;color:var(--ink);margin-top:2px;">Konvertibilnih maraka</div>
                        <div id="home-league" style="font-family:'Inter',system-ui;font-size:12px;color:var(--ink-soft);margin-top:4px;"></div>

                        {{-- Countdown --}}
                        <div id="home-countdown-card" style="margin-top:12px;padding:10px 12px;border-radius:8px;background:var(--bg-deep);border:1px dashed var(--rule);display:flex;align-items:center;gap:10px;">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="9" stroke="var(--ink)" stroke-width="1.6"/><path d="M12 7v5l3 2" stroke="var(--ink)" stroke-width="1.6" stroke-linecap="round"/></svg>
                            <div style="flex:1;text-align:left;">
                                <div style="font-family:'DM Mono',monospace;font-size:10px;letter-spacing:1.2px;text-transform:uppercase;color:var(--ink-soft);">Picks lock in</div>
                                <div id="home-countdown" style="display:none;font-family:'Fraunces','Times New Roman',serif;font-size:18px;font-weight:700;color:var(--ink);margin-top:1px;"></div>
                                <div id="home-locks-at" style="font-size:12px;color:var(--ink-soft);margin-top:1px;"></div>
                            </div>
                        </div>
                    </div>

                    {{-- Prediction CTA --}}
                    <div id="home-prediction-status" style="margin-bottom:12px;"></div>
                    <button id="home-predict-btn" class="btn btn-primary btn-full" style="margin-bottom:12px;display:none;justify-content:space-between;padding:16px 18px;">
                        <div style="text-align:left;">
                            <div style="font-family:'DM Mono',monospace;font-size:10px;letter-spacing:1.4px;opacity:.8;text-transform:uppercase;">Your turn</div>
                            <div style="font-family:'Fraunces','Times New Roman',serif;font-size:19px;font-weight:700;margin-top:2px;">Submit your picks</div>
                        </div>
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none"><path d="M9 5l7 7-7 7" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </button>
                    <div id="home-no-fixtures-help" style="display:none;font-family:'Inter',system-ui;font-size:12px;color:var(--ink-soft);text-align:center;margin-bottom:12px;">No fixtures synced yet — Admin → Sync Fixtures</div>

                    {{-- Submission tracker --}}
                    <div style="margin-bottom:12px;">
                        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:6px;">
                            <div style="font-family:'DM Mono',monospace;font-size:10px;letter-spacing:1.4px;text-transform:uppercase;color:var(--ink-soft);">Submissions</div>
                            <div id="home-completion-counter" style="display:none;font-family:'DM Mono',monospace;font-size:11px;color:var(--ink-soft);"></div>
                        </div>
                        <div id="home-round-status-badge"></div>
                    </div>

                </div>{{-- /home-round-info --}}

                {{-- Streak highlight --}}
                <div id="home-streak-highlight" style="display:none;margin-bottom:12px;"></div>

                {{-- Quick nav --}}
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:16px;">
                    <button class="quick-card" onclick="loadBalances()">
                        <div class="quick-card-eyebrow">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="9" stroke="var(--gold)" stroke-width="1.6"/><path d="M12 8v8M9.5 9.5h4a1.5 1.5 0 010 3h-3a1.5 1.5 0 000 3h4" stroke="var(--gold)" stroke-width="1.4" stroke-linecap="round"/></svg>
                            Balances
                        </div>
                        <div class="quick-card-big" id="home-balance-quick">—</div>
                        <div class="quick-card-sub">The tab</div>
                    </button>
                    <button class="quick-card" onclick="loadHallOfFame()">
                        <div class="quick-card-eyebrow">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none"><path d="M7 4h10v3a5 5 0 01-10 0V4z" stroke="var(--gold)" stroke-width="1.6" stroke-linejoin="round"/><path d="M7 6H4.5a1 1 0 00-1 1v1a3 3 0 003 3M17 6h2.5a1 1 0 011 1v1a3 3 0 01-3 3" stroke="var(--gold)" stroke-width="1.6"/><path d="M9 13.5l-.5 3.5h7l-.5-3.5M8 20h8" stroke="var(--gold)" stroke-width="1.6" stroke-linecap="round"/></svg>
                            Hall of Fame
                        </div>
                        <div class="quick-card-big">HoF</div>
                        <div class="quick-card-sub">Past champions</div>
                    </button>
                </div>

                {{-- Ticket (collapsible) --}}
                <div id="home-ticket" style="display:none;margin-bottom:16px;">
                    <div class="card" style="padding:0;overflow:hidden;">
                        <div style="display:flex;align-items:center;justify-content:space-between;padding:14px 16px;cursor:pointer;" onclick="toggleTicket()">
                            <div style="display:flex;align-items:center;gap:8px;">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none"><rect x="3" y="5" width="18" height="14" rx="2" stroke="var(--ink)" stroke-width="1.6"/><path d="M7 10h3M7 14h3M14 10h3M14 14h3" stroke="var(--ink)" stroke-width="1.4" stroke-linecap="round"/></svg>
                                <span style="font-size:15px;font-weight:700;color:var(--ink);font-family:'Fraunces','Times New Roman',serif;">My Ticket</span>
                                <span id="home-ticket-count" class="badge-kf badge-green" style="font-size:11px;"></span>
                            </div>
                            <svg id="home-ticket-chevron" viewBox="0 0 24 24" fill="none" stroke="var(--ink-faint)" stroke-width="2" width="18" height="18" style="transition:transform 0.2s ease;"><path d="M6 9l6 6 6-6"/></svg>
                        </div>
                        <div id="home-ticket-body" style="display:none;border-top:1px solid var(--rule);">
                            <div id="home-ticket-rows" style="padding:8px 0;"></div>
                            <div style="padding:12px 16px;border-top:1px solid rgba(58,40,24,.15);">
                                <button class="btn btn-secondary btn-full btn-sm" onclick="showScreen('predict')">Edit Predictions →</button>
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
            <div class="screen-header-row">
                <button class="back-btn" onclick="showScreen('home')">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none"><path d="M15 5l-7 7 7 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </button>
                <div style="flex:1;">
                    <div class="screen-header-eyebrow" id="predict-eyebrow">Your picks</div>
                    <h2 id="predict-title">Predictions</h2>
                </div>
                <div id="predict-header-balance" class="balance-pill" style="display:none;"></div>
            </div>
        </div>
        <div id="predict-lock-bar" style="display:none;" class="lock-ribbon">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none"><rect x="4" y="10" width="16" height="11" rx="2" stroke="currentColor" stroke-width="1.6"/><path d="M8 10V7a4 4 0 018 0v3" stroke="currentColor" stroke-width="1.6"/></svg>
            <span id="predict-lock-info"></span>
            <div style="flex:1;"></div>
            <span id="predict-pick-count" style="color:var(--ink);font-weight:600;"></span>
        </div>
        <div class="screen-content" style="padding:0 0 calc(72px + env(safe-area-inset-bottom,0px));">
            <div id="predict-locked-msg" style="display:none;padding:40px 24px;text-align:center;">
                <svg width="44" height="44" viewBox="0 0 24 24" fill="none" style="margin:0 auto 14px;display:block;"><rect x="4" y="10" width="16" height="11" rx="2" stroke="var(--ink-soft)" stroke-width="1.6"/><path d="M8 10V7a4 4 0 018 0v3" stroke="var(--ink-soft)" stroke-width="1.6"/><circle cx="12" cy="15.5" r="1.5" fill="var(--ink-soft)"/></svg>
                <div style="font-family:'Fraunces','Times New Roman',serif;font-size:20px;font-weight:700;color:var(--ink);margin-bottom:6px;">Round Locked</div>
                <div style="color:var(--ink-soft);font-size:14px;">Predictions are closed for this round.</div>
            </div>
            <div id="predict-list" style="padding:10px 12px 0;"></div>
            <div id="predict-footer" style="padding:0 14px 14px;display:none;">
                <div id="predict-debt-banner" style="display:none;background:rgba(168,52,26,.12);border:1px solid var(--lose);border-radius:10px;padding:12px 16px;margin-bottom:12px;text-align:center;color:var(--lose);font-size:14px;font-weight:500;">Token balance too low — pay your debt before submitting</div>
                <div id="predict-progress" style="font-family:'DM Mono',monospace;font-size:11px;color:var(--ink-soft);margin-bottom:10px;text-align:center;letter-spacing:.5px;"></div>
                <button id="predict-submit" class="btn btn-primary btn-full btn-lg">Lock in my picks</button>
            </div>
        </div>
    </div>

    {{-- =========================================================
         SCREEN: Round Results
         ========================================================= --}}
    <div id="screen-results" class="screen">
        <div class="screen-header">
            <div class="screen-header-row">
                <button class="back-btn" onclick="showScreen('home')">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none"><path d="M15 5l-7 7 7 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </button>
                <div style="flex:1;">
                    <div class="screen-header-eyebrow" id="results-eyebrow">Round results</div>
                    <h2 id="results-title">Results</h2>
                </div>
            </div>
        </div>
        <div id="results-nav" style="display:none;align-items:center;justify-content:space-between;padding:10px 16px;border-bottom:1.5px solid var(--rule);background:var(--bg-deep);">
            <button id="results-prev" onclick="navigateResults(-1)" style="background:none;border:1.5px solid var(--rule);border-radius:8px;padding:6px 14px;color:var(--ink-soft);font-size:13px;cursor:pointer;min-width:44px;min-height:36px;">← Prev</button>
            <span id="results-nav-pos" style="font-family:'DM Mono',monospace;font-size:11px;color:var(--ink-faint);"></span>
            <button id="results-next" onclick="navigateResults(1)" style="background:none;border:1.5px solid var(--rule);border-radius:8px;padding:6px 14px;color:var(--ink-soft);font-size:13px;cursor:pointer;min-width:44px;min-height:36px;">Next →</button>
        </div>
        <div class="screen-content" style="padding:0 0 80px;">
            <div id="results-fixtures" style="padding:16px 16px 0;"></div>
            <div id="results-entries"></div>
        </div>
    </div>

    {{-- =========================================================
         SCREEN: Leaderboard
         ========================================================= --}}
    <div id="screen-leaderboard" class="screen">
        <div class="screen-content" style="padding:0 0 80px;background:var(--bg);">
            <div class="safe-top" style="padding:16px 16px 14px;border-bottom:1.5px solid var(--rule);">
                <div style="font-family:'DM Mono',monospace;font-size:11px;letter-spacing:1.4px;text-transform:uppercase;color:var(--ink-soft);margin-bottom:4px;" id="lb-season-info"></div>
                <h2 style="font-family:'Fraunces','Times New Roman',serif;font-size:30px;font-weight:700;color:var(--ink);letter-spacing:-0.5px;margin:0;">Standings</h2>
            </div>
            <div id="lb-jackpot-card" style="padding:12px 16px 0;display:none;">
                <div class="card" style="border-width:2px;display:flex;align-items:center;gap:12px;">
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none"><path d="M7 4h10v3a5 5 0 01-10 0V4z" stroke="var(--gold)" stroke-width="1.6" stroke-linejoin="round"/><path d="M7 6H4.5a1 1 0 00-1 1v1a3 3 0 003 3M17 6h2.5a1 1 0 011 1v1a3 3 0 01-3 3" stroke="var(--gold)" stroke-width="1.6"/><path d="M9 13.5l-.5 3.5h7l-.5-3.5M8 20h8" stroke="var(--gold)" stroke-width="1.6" stroke-linecap="round"/></svg>
                    <div style="flex:1;">
                        <div style="font-family:'DM Mono',monospace;font-size:10px;letter-spacing:1.4px;text-transform:uppercase;color:var(--ink-soft);">Current pot</div>
                        <div id="lb-jackpot-amount" style="font-family:'Fraunces','Times New Roman',serif;font-size:22px;font-weight:800;color:var(--ink);line-height:1;"></div>
                    </div>
                    <div class="stamp" style="color:var(--accent);">Live</div>
                </div>
            </div>
            <div id="lb-podium" style="padding:16px 16px 0;display:none;"></div>
            <div id="lb-list"></div>
            <div id="lb-empty" style="display:none;padding:48px 24px;text-align:center;">
                <div style="font-family:'DM Mono',monospace;font-size:11px;letter-spacing:1.4px;text-transform:uppercase;color:var(--ink-soft);">No scores yet</div>
                <div style="color:var(--ink-soft);margin-top:8px;font-size:14px;">Play some rounds first!</div>
            </div>
        </div>
    </div>

    {{-- =========================================================
         SCREEN: History
         ========================================================= --}}
    <div id="screen-history" class="screen">
        <div class="screen-content" style="padding:0 0 80px;background:var(--bg);">
            <div class="safe-top" style="padding:16px 16px 14px;border-bottom:1.5px solid var(--rule);">
                <div style="font-family:'DM Mono',monospace;font-size:11px;letter-spacing:1.4px;text-transform:uppercase;color:var(--ink-soft);margin-bottom:4px;">Season archive</div>
                <h2 style="font-family:'Fraunces','Times New Roman',serif;font-size:30px;font-weight:700;color:var(--ink);letter-spacing:-0.5px;margin:0;">History</h2>
            </div>
            <div id="history-list" style="padding:14px 16px;"></div>
            <div id="history-empty" style="display:none;padding:48px 24px;text-align:center;color:var(--ink-soft);">No completed rounds yet.</div>
        </div>
    </div>

    {{-- =========================================================
         SCREEN: History Detail
         ========================================================= --}}
    <div id="screen-history-detail" class="screen">
        <div class="screen-header">
            <div class="screen-header-row">
                <button class="back-btn" onclick="showScreen('history')">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none"><path d="M15 5l-7 7 7 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </button>
                <h2 id="history-detail-title">Round Detail</h2>
            </div>
        </div>
        <div class="screen-content" style="padding:14px 0 24px;">
            <div id="history-detail-fixtures" style="padding:0 16px 16px;"></div>
            <div id="history-detail-entries"></div>
        </div>
    </div>

    {{-- =========================================================
         SCREEN: Admin Panel
         ========================================================= --}}
    <div id="screen-admin" class="screen">
        <div class="screen-content" style="padding:0 0 80px;">
            <div class="safe-top" style="padding:16px 16px 14px;border-bottom:1.5px solid var(--rule);display:flex;justify-content:space-between;align-items:flex-end;">
                <div>
                    <div style="font-family:'DM Mono',monospace;font-size:11px;letter-spacing:1.4px;text-transform:uppercase;color:var(--ink-soft);margin-bottom:4px;">Admin panel</div>
                    <h2 style="font-family:'Fraunces','Times New Roman',serif;font-size:28px;font-weight:700;color:var(--ink);margin:0;">Admin</h2>
                </div>
                <div style="text-align:right;">
                    <div style="font-family:'DM Mono',monospace;font-size:10px;letter-spacing:1.2px;text-transform:uppercase;color:var(--ink-soft);">Jackpot</div>
                    <div id="admin-jackpot" style="font-family:'Fraunces','Times New Roman',serif;font-size:20px;font-weight:700;color:var(--accent);"></div>
                </div>
            </div>
            <div style="padding:14px 16px;display:flex;flex-direction:column;gap:12px;">

                <div class="card">
                    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px;">
                        <div style="font-family:'Fraunces','Times New Roman',serif;font-size:16px;font-weight:700;color:var(--ink);">Season</div>
                        <div id="admin-season-badge"></div>
                    </div>
                    <div id="admin-season-info" style="font-size:14px;color:var(--ink-soft);margin-bottom:12px;"></div>
                    <div style="display:flex;gap:8px;flex-wrap:wrap;">
                        <button class="btn btn-secondary btn-sm" onclick="showScreen('admin-season')">New Season</button>
                        <button id="admin-pending-settlement-btn" class="btn btn-secondary btn-sm" style="display:none;" onclick="adminStartSettlement()">Start Settlement</button>
                    </div>
                </div>

                <div class="card">
                    <div style="font-family:'Fraunces','Times New Roman',serif;font-size:16px;font-weight:700;color:var(--ink);margin-bottom:8px;">Active Round</div>
                    <div id="admin-round-info" style="font-size:14px;color:var(--ink-soft);margin-bottom:12px;"></div>
                    <div style="display:flex;gap:8px;flex-wrap:wrap;">
                        <button class="btn btn-secondary btn-sm" onclick="showScreen('admin-rounds');renderAdminRounds()">Manage Rounds</button>
                        <button id="admin-sync-fixtures-btn" class="btn btn-secondary btn-sm" style="display:none;">Sync Fixtures</button>
                        <button id="admin-sync-results-btn" class="btn btn-secondary btn-sm" style="display:none;">Sync Results</button>
                        <button id="admin-check-api-btn" class="btn btn-secondary btn-sm" onclick="adminCheckApi()">Check API</button>
                    </div>
                </div>

                <div class="card">
                    <div style="font-family:'Fraunces','Times New Roman',serif;font-size:16px;font-weight:700;color:var(--ink);margin-bottom:8px;">Players</div>
                    <div id="admin-players-summary" style="font-size:14px;color:var(--ink-soft);margin-bottom:12px;">Manage player accounts and token balances.</div>
                    <div style="display:flex;gap:8px;flex-wrap:wrap;">
                        <button class="btn btn-secondary btn-sm" onclick="loadAdminPlayers()">Manage Players</button>
                        <button class="btn btn-secondary btn-sm" onclick="showScreen('admin-credit')">Credit Tokens</button>
                    </div>
                </div>

                <div id="admin-settlement-card" class="card" style="display:none;border-color:var(--accent);">
                    <div style="font-family:'Fraunces','Times New Roman',serif;font-size:16px;font-weight:700;color:var(--ink);margin-bottom:8px;">Season Settlement</div>
                    <div style="font-size:14px;color:var(--ink-soft);margin-bottom:12px;">Season is pending settlement. Process player balances.</div>
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
            <div class="screen-header-row">
                <button class="back-btn" onclick="showScreen('admin')">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none"><path d="M15 5l-7 7 7 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </button>
                <h2>Players</h2>
                <button class="back-btn" onclick="openPlayerForm(null)" style="margin:0;">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none"><path d="M12 5v14M5 12h14" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"/></svg>
                </button>
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
            <div class="screen-header-row">
                <button class="back-btn" onclick="showScreen('admin-players')">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none"><path d="M15 5l-7 7 7 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </button>
                <h2 id="player-form-title">Add Player</h2>
            </div>
        </div>
        <div class="screen-content" style="padding:20px;">
            <input type="hidden" id="player-form-id">
            <div style="display:flex;flex-direction:column;gap:16px;">
                <div><label class="field-label">Name</label><input id="player-form-name" class="input" type="text" placeholder="Player name" autocomplete="off"></div>
                <div>
                    <label class="field-label">Nickname (optional)</label>
                    <input id="player-form-nickname" class="input" type="text" placeholder="Display name" autocomplete="off" maxlength="30">
                    <div style="font-size:12px;color:var(--ink-faint);margin-top:6px;">Shown instead of real name if set</div>
                </div>
                <div>
                    <label class="field-label">PIN</label>
                    <input id="player-form-pin" class="input" type="password" placeholder="4–8 digits" maxlength="8" inputmode="numeric">
                    <div id="player-form-pin-hint" style="font-size:12px;color:var(--ink-faint);margin-top:6px;display:none;">Leave blank to keep existing PIN</div>
                </div>
                <div><label class="field-label">Token Balance</label><input id="player-form-tokens" class="input" type="number" placeholder="0" inputmode="numeric"></div>
                <div style="display:flex;align-items:center;gap:12px;padding:14px 16px;background:var(--surface-alt);border-radius:10px;border:1.5px solid var(--rule);">
                    <input id="player-form-admin" type="checkbox" style="width:20px;height:20px;accent-color:var(--accent);cursor:pointer;">
                    <label for="player-form-admin" style="font-size:15px;font-weight:500;color:var(--ink);cursor:pointer;">Admin access</label>
                </div>
                <button id="player-form-save" class="btn btn-primary btn-full btn-lg" style="margin-top:4px;">Save Player</button>
                <button id="player-form-delete" class="btn btn-danger btn-full" style="display:none;">Delete Player</button>
            </div>
        </div>
    </div>

    {{-- =========================================================
         SCREEN: Admin Rounds
         ========================================================= --}}
    <div id="screen-admin-rounds" class="screen">
        <div class="screen-header">
            <div class="screen-header-row">
                <button class="back-btn" onclick="showScreen('admin')">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none"><path d="M15 5l-7 7 7 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </button>
                <h2>Rounds</h2>
                <button id="admin-rounds-new-btn" class="back-btn" onclick="editRound(null)" style="display:none;margin:0;">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none"><path d="M12 5v14M5 12h14" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"/></svg>
                </button>
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
            <div class="screen-header-row">
                <button class="back-btn" onclick="showScreen('admin-rounds')">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none"><path d="M15 5l-7 7 7 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </button>
                <h2 id="round-form-title">New Round</h2>
            </div>
        </div>
        <div class="screen-content" style="padding:20px;">
            <input type="hidden" id="round-form-id">
            <div style="display:flex;flex-direction:column;gap:16px;">
                <div><label class="field-label">Matchweek Number</label><input id="round-form-number" class="input" type="number" placeholder="e.g. 28" min="1" inputmode="numeric"></div>
                <div id="round-form-locks-at-group" style="display:none;">
                    <label class="field-label">Lock Time Override</label>
                    <input id="round-form-locks-at" class="input" type="datetime-local">
                    <div style="font-size:12px;color:var(--ink-faint);margin-top:6px;">Override the auto-detected lock time if needed.</div>
                </div>
                <div id="round-form-locks-at-note" style="display:none;" class="card">
                    <div style="font-size:13px;color:var(--ink-soft);">Lock time will be set automatically when fixtures are synced.</div>
                </div>
                <div id="round-form-status-group" style="display:none;">
                    <label class="field-label">Status</label>
                    <select id="round-form-status" class="input">
                        <option value="pending">Pending</option>
                        <option value="active">Active</option>
                        <option value="locked">Locked</option>
                    </select>
                </div>
                <button id="round-form-save" class="btn btn-primary btn-full btn-lg" style="margin-top:4px;">Save Round</button>
                <div id="round-form-resolve-wrap" style="display:none;margin-top:4px;">
                    <button id="round-form-resolve" class="btn btn-full btn-lg" style="background:rgba(168,52,26,.12);border:1.5px solid var(--lose);color:var(--lose);" onclick="adminResolveRound(document.getElementById('round-form-id').value)">Resolve Round</button>
                </div>
            </div>
        </div>
    </div>

    {{-- =========================================================
         SCREEN: Admin New Season
         ========================================================= --}}
    <div id="screen-admin-season" class="screen">
        <div class="screen-header">
            <div class="screen-header-row">
                <button class="back-btn" onclick="showScreen('admin')">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none"><path d="M15 5l-7 7 7 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </button>
                <h2>New Season</h2>
            </div>
        </div>
        <div class="screen-content" style="padding:20px;">
            <div style="background:rgba(168,52,26,.1);border:1.5px solid rgba(168,52,26,.4);border-radius:10px;padding:14px;margin-bottom:20px;">
                <div style="font-size:13px;color:var(--lose);">Starting a new season will end the current season and reset the leaderboard.</div>
            </div>
            <div style="display:flex;flex-direction:column;gap:16px;">
                <div>
                    <label class="field-label">League Code</label>
                    <input id="season-form-league-id" class="input" type="text" placeholder="e.g. PL, PD, BL1, SA" autocomplete="off" style="text-transform:uppercase;">
                    <div style="font-size:12px;color:var(--ink-faint);margin-top:6px;">football-data.org competition code</div>
                </div>
                <div><label class="field-label">League Name</label><input id="season-form-league-name" class="input" type="text" placeholder="e.g. Premier League" autocomplete="off"></div>
                <div><label class="field-label">Entry Cost (tokens per round)</label><input id="season-form-entry-tokens" class="input" type="number" placeholder="5" min="1" inputmode="numeric"></div>
                <button id="season-form-save" class="btn btn-primary btn-full btn-lg" style="margin-top:4px;">Start Season</button>
            </div>
        </div>
    </div>

    {{-- =========================================================
         SCREEN: Balances
         ========================================================= --}}
    <div id="screen-balances" class="screen">
        <div class="screen-header">
            <div class="screen-header-row">
                <button class="back-btn" onclick="showScreen('home')">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none"><path d="M15 5l-7 7 7 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </button>
                <div style="flex:1;">
                    <div class="screen-header-eyebrow">All balances · public</div>
                    <h2>The Tab</h2>
                </div>
            </div>
        </div>
        <div class="screen-content" style="padding-bottom:24px;">
            <div id="balances-summary" style="display:none;padding:14px 16px 0;display:grid;grid-template-columns:1fr 1fr;gap:10px;"></div>
            <div id="balances-list" style="padding:14px 16px 0;"></div>
            <div style="margin:12px 16px 0;padding:10px 12px;border-radius:8px;background:var(--bg-deep);border:1px dashed var(--rule);">
                <p style="font-family:'Inter',system-ui;font-size:11px;color:var(--ink-soft);line-height:1.5;margin:0;">1 token = <strong style="color:var(--ink);">1 KM</strong>. Settle with admin in cash. Everything is on the board — no secrets in this kafana.</p>
            </div>
        </div>
    </div>

    {{-- =========================================================
         SCREEN: Ledger
         ========================================================= --}}
    <div id="screen-ledger" class="screen">
        <div class="screen-header">
            <div class="screen-header-row">
                <button class="back-btn" onclick="showScreen('balances')">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none"><path d="M15 5l-7 7 7 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </button>
                <div style="flex:1;">
                    <div class="screen-header-eyebrow">Transaction history</div>
                    <h2 id="ledger-player-name" style="font-size:22px;">Ledger</h2>
                </div>
                <div id="ledger-player-balance" class="balance-pill"></div>
            </div>
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
            <div class="screen-header-row">
                <button class="back-btn" onclick="showScreen('leaderboard')">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none"><path d="M15 5l-7 7 7 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </button>
                <div style="flex:1;">
                    <div class="screen-header-eyebrow">Badge shelf</div>
                    <h2 id="badges-player-name" style="font-size:22px;">Badges</h2>
                </div>
            </div>
        </div>
        <div class="screen-content" style="padding:14px 0 24px;">
            <div id="badges-shelf" style="padding:0 16px;"></div>
        </div>
    </div>

    {{-- =========================================================
         SCREEN: Hall of Fame
         ========================================================= --}}
    <div id="screen-hall-of-fame" class="screen">
        <div class="screen-header">
            <div class="screen-header-row">
                <button class="back-btn" onclick="showScreen('home')">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none"><path d="M15 5l-7 7 7 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </button>
                <div style="flex:1;">
                    <div class="screen-header-eyebrow">Past seasons</div>
                    <h2>Hall of Fame</h2>
                </div>
            </div>
        </div>
        <div class="screen-content" style="padding:14px 0 24px;">
            <div id="hof-list"></div>
        </div>
    </div>

    {{-- =========================================================
         SCREEN: Admin Settlement
         ========================================================= --}}
    <div id="screen-admin-settlement" class="screen">
        <div class="screen-header">
            <div class="screen-header-row">
                <button class="back-btn" onclick="showScreen('admin')">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none"><path d="M15 5l-7 7 7 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </button>
                <h2>Season Settlement</h2>
            </div>
        </div>
        <div class="screen-content" style="padding:0 0 24px;">
            <div style="padding:14px 16px;border-bottom:1.5px solid var(--rule);display:flex;justify-content:space-between;align-items:center;">
                <div id="settlement-progress" style="font-family:'Fraunces','Times New Roman',serif;font-size:16px;font-weight:700;color:var(--ink);"></div>
                <button id="settlement-close-btn" class="btn btn-primary btn-sm" onclick="closeSeasonFinal()">Close Season</button>
            </div>
            <div id="settlement-content"></div>
        </div>
    </div>

    {{-- =========================================================
         SCREEN: Admin Credit Tokens
         ========================================================= --}}
    <div id="screen-admin-credit" class="screen">
        <div class="screen-header">
            <div class="screen-header-row">
                <button class="back-btn" onclick="showScreen('admin')">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none"><path d="M15 5l-7 7 7 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </button>
                <h2>Credit Tokens</h2>
            </div>
        </div>
        <div class="screen-content" style="padding:20px;">
            <div style="display:flex;flex-direction:column;gap:16px;">
                <div><label class="field-label">Player</label><select id="credit-player-id" class="input"><option value="">— Select player —</option></select></div>
                <div><label class="field-label">Amount (tokens)</label><input id="credit-amount" class="input" type="number" placeholder="e.g. 10" min="1" inputmode="numeric"></div>
                <div><label class="field-label">Description (optional)</label><input id="credit-description" class="input" type="text" placeholder="e.g. Top-up" autocomplete="off"></div>
                <button id="credit-submit-btn" class="btn btn-primary btn-full btn-lg" onclick="submitCreditTokens()">Credit Tokens</button>
                <div id="credit-result"></div>
            </div>
        </div>
    </div>

    {{-- =========================================================
         Bottom Navigation
         ========================================================= --}}
    <nav id="bottom-nav">
        <button class="nav-item" id="nav-home" onclick="showScreen('home')">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M3 11l9-7 9 7v9a1 1 0 01-1 1h-5v-7H9v7H4a1 1 0 01-1-1v-9z" stroke-linejoin="round"/></svg>
            <span class="nav-label">Home</span>
        </button>
        <button class="nav-item" id="nav-predict" onclick="showScreen('predict')">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="M7 10h3M7 14h3M14 10h3M14 14h3" stroke-linecap="round"/></svg>
            <span class="nav-label">Predict</span>
        </button>
        <button class="nav-item" id="nav-results" onclick="loadAndShowResults()">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2M9 12l2 2 4-4"/></svg>
            <span class="nav-label">Results</span>
        </button>
        <button class="nav-item" id="nav-leaderboard" onclick="showScreen('leaderboard')">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 21V8M19 21V12M12 21V4" stroke-linecap="round"/><circle cx="5" cy="6" r="2" stroke-width="1.6"/><circle cx="12" cy="3" r="1.5" stroke-width="1.6"/><circle cx="19" cy="10" r="2" stroke-width="1.6"/></svg>
            <span class="nav-label">Table</span>
        </button>
        <button class="nav-item" id="nav-history" onclick="showScreen('history')">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2" stroke-linecap="round"/></svg>
            <span class="nav-label">History</span>
        </button>
        <button class="nav-item" id="nav-admin" onclick="showScreen('admin')" style="display:none;">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4"><path d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><circle cx="12" cy="12" r="3"/></svg>
            <span class="nav-label">Admin</span>
        </button>
    </nav>

</div>

<script>
// ================================================================
//  STATE
// ================================================================
const state = {
    token: null, player: null, season: null, round: null,
    predictions: {}, leaderboard: [], history: [],
    adminPlayers: [], adminRounds: [], roundResults: null, historyDetail: null,
    currentScreen: 'login', balances: [], ledger: {}, pots: {}, badges: {},
    hallOfFame: [], settlements: null, currentLedgerPlayerId: null, currentBadgePlayerId: null,
};

async function refreshState() {
    const s = await api('GET', '/api/state');
    applyStateData(s);
    saveLocal();
}

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
        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF() },
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
function showLoading(v) { document.getElementById('loading').classList.toggle('hidden', !v); }

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
            token: state.token, player: state.player, season: state.season, round: state.round,
            predictions: state.predictions, leaderboard: state.leaderboard, history: state.history,
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
        if (Array.isArray(state.predictions)) state.predictions = {};
        return true;
    } catch(e) { return false; }
}

function clearLocal() { localStorage.removeItem('tp_state'); }
function sleep(ms) { return new Promise(r => setTimeout(r, ms)); }

// ================================================================
//  INIT
// ================================================================
async function init() {
    showLoading(true);
    const hasCache = loadLocal();

    if (!state.token) { showLoading(false); showScreen('login'); return; }

    if (hasCache) { showLoading(false); toggleAdminNav(); showScreen('home'); }

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
            if (status === 401) { showLoading(false); logout(); return; }
            tries++;
            if (tries < 6) await sleep(1200 * tries);
        }
    }

    if (!hasCache) { showLoading(false); toggleAdminNav(); showScreen('home'); }
}

// ================================================================
//  PIN NUMPAD
// ================================================================
let _pinValue = '';
function pinTap(d) {
    if (_pinValue.length >= 8) return;
    _pinValue += d;
    syncPin();
}
function pinDel() { _pinValue = _pinValue.slice(0, -1); syncPin(); }
function syncPin() {
    document.getElementById('login-pin').value = _pinValue;
    for (let i = 0; i < 4; i++) {
        const dot = document.getElementById('pin-d' + i);
        if (!dot) continue;
        dot.textContent = _pinValue[i] ? '•' : '';
        dot.classList.toggle('active', i === _pinValue.length);
    }
}
function resetPin() { _pinValue = ''; syncPin(); }

// ================================================================
//  AUTH
// ================================================================
document.getElementById('login-btn').addEventListener('click', doLogin);

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
        resetPin();
        document.getElementById('login-name').value = '';

        try { const s = await api('GET', '/api/state'); applyStateData(s); saveLocal(); } catch(e) {}

        showScreen('home');
    } catch(e) {
        errEl.textContent = e.message || 'Invalid name or PIN';
        errEl.style.display = 'block';
        resetPin();
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
    resetPin();
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
    const pname = state.player.displayName || state.player.name;
    document.getElementById('home-player-name').textContent = pname;
    const bal = state.player.tokenBalance;
    document.getElementById('home-token-balance').textContent = bal;
    const pill = document.getElementById('home-balance-pill');
    if (pill) pill.classList.toggle('negative', bal < 0);
    toggleAdminNav();

    if (!state.season) {
        document.getElementById('home-jackpot').textContent = '0';
        const lg = document.getElementById('home-league'); if(lg) lg.textContent = '';
        document.getElementById('home-no-season').style.display = 'block';
        document.getElementById('home-no-round').style.display = 'none';
        document.getElementById('home-round-info').style.display = 'none';
        return;
    }

    document.getElementById('home-jackpot').textContent = state.season.jackpot;
    const lg = document.getElementById('home-league'); if(lg) lg.textContent = state.season.leagueName;
    document.getElementById('home-no-season').style.display = 'none';

    if (!state.round) {
        document.getElementById('home-no-round').style.display = 'block';
        document.getElementById('home-round-info').style.display = 'none';
        return;
    }

    document.getElementById('home-no-round').style.display = 'none';
    document.getElementById('home-round-info').style.display = 'block';

    const eyebrow = document.getElementById('home-round-eyebrow');
    if (eyebrow) eyebrow.textContent = 'Round ' + state.round.number + ' · ' + (state.season.leagueName || 'Season');

    if (state.round.locksAt) {
        const locksAt = new Date(state.round.locksAt);
        const locksAtStr = locksAt.toLocaleDateString() + ' ' + locksAt.toLocaleTimeString([], {hour:'2-digit',minute:'2-digit'});
        const diff = locksAt - Date.now();
        if (diff > 0) {
            document.getElementById('home-locks-at').textContent = 'Locks · ' + locksAtStr;
            startCountdown(locksAt);
        } else {
            document.getElementById('home-locks-at').textContent = 'Locked · ' + locksAtStr;
            document.getElementById('home-countdown').style.display = 'none';
        }
    }

    const completedCount = state.round.completedCount ?? null;
    const totalPlayers = state.round.totalPlayers ?? null;
    const counterEl = document.getElementById('home-completion-counter');
    if (completedCount !== null && totalPlayers !== null) {
        counterEl.textContent = completedCount + '/' + totalPlayers + ' submitted';
        counterEl.style.display = 'block';
    } else { counterEl.style.display = 'none'; }

    const fixtures = (state.round.fixtures || []).filter(f => f.status !== 'postponed' && f.status !== 'cancelled' && f.status !== 'finished');
    const predicted = fixtures.filter(f => state.predictions[f.id]).length;
    const total = fixtures.length;
    const complete = predicted === total && total > 0;
    const locked = state.round.isLocked;

    const statusEl = document.getElementById('home-prediction-status');
    if (total === 0) {
        statusEl.innerHTML = '<span class="badge-kf badge-slate">No fixtures yet</span>';
    } else if (locked) {
        statusEl.innerHTML = complete
            ? '<div style="display:flex;align-items:center;gap:12px;padding:14px 16px;border:2px solid var(--green);border-radius:10px;background:var(--surface);cursor:pointer;" onclick="showScreen(\'predict\')">' +
              '<div style="width:32px;height:32px;border-radius:50%;background:var(--green);display:flex;align-items:center;justify-content:center;"><svg width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="M4 12l5 5 11-12" stroke="#fff" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"/></svg></div>' +
              '<div style="flex:1;"><div style="font-family:\'DM Mono\',monospace;font-size:10px;letter-spacing:1.4px;text-transform:uppercase;color:var(--green);font-weight:600;">Locked in</div>' +
              '<div style="font-family:\'Fraunces\',serif;font-size:16px;font-weight:700;color:var(--ink);">You picked all ' + total + ' matches</div></div>' +
              '<svg width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="M9 5l7 7-7 7" stroke="var(--ink-soft)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg></div>'
            : '<span class="badge-kf badge-amber">Incomplete · ' + predicted + '/' + total + '</span>';
    } else if (complete) {
        statusEl.innerHTML = '<div style="display:flex;align-items:center;gap:12px;padding:14px 16px;border:2px solid var(--green);border-radius:10px;background:var(--surface);cursor:pointer;" onclick="showScreen(\'predict\')">' +
          '<div style="width:32px;height:32px;border-radius:50%;background:var(--green);display:flex;align-items:center;justify-content:center;"><svg width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="M4 12l5 5 11-12" stroke="#fff" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"/></svg></div>' +
          '<div style="flex:1;"><div style="font-family:\'DM Mono\',monospace;font-size:10px;letter-spacing:1.4px;text-transform:uppercase;color:var(--green);font-weight:600;">Locked in</div>' +
          '<div style="font-family:\'Fraunces\',serif;font-size:16px;font-weight:700;color:var(--ink);">You picked all ' + total + ' matches</div></div>' +
          '<svg width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="M9 5l7 7-7 7" stroke="var(--ink-soft)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg></div>';
    } else {
        statusEl.innerHTML = '<span class="badge-kf badge-amber">' + predicted + '/' + total + ' predicted</span>';
    }

    const predictBtn = document.getElementById('home-predict-btn');
    predictBtn.style.display = (locked || total === 0) ? 'none' : 'flex';
    predictBtn.onclick = () => showScreen('predict');

    const noFixturesHelp = document.getElementById('home-no-fixtures-help');
    if (noFixturesHelp) noFixturesHelp.style.display = (total === 0 && state.player?.isAdmin) ? 'block' : 'none';

    renderTicket();

    const streakEl = document.getElementById('home-streak-highlight');
    if (streakEl) {
        const hotEntry = (state.leaderboard || []).reduce((best, e) => {
            const s = e.streaks || {};
            return (s.onFire >= 2 && s.onFire > (best?.streaks?.onFire || 0)) ? e : best;
        }, null);
        if (hotEntry && hotEntry.streaks && hotEntry.streaks.onFire >= 2) {
            const name = hotEntry.displayName || hotEntry.playerName;
            streakEl.innerHTML = '<div class="card" style="background:var(--bg-deep);border-color:var(--lose);display:flex;align-items:center;gap:12px;">' +
              '<div style="width:38px;height:38px;border-radius:50%;border:2px solid var(--lose);background:var(--surface);display:flex;align-items:center;justify-content:center;flex-shrink:0;">' +
              '<svg width="20" height="20" viewBox="0 0 24 24" fill="none"><path d="M12 3c0 3 3 4 3 8a3 3 0 11-6 0c0-1.5 1-2 1-3.5C9 9 12 6 12 3z" fill="var(--lose)"/></svg></div>' +
              '<div><div style="font-family:\'DM Mono\',monospace;font-size:10px;letter-spacing:1.2px;text-transform:uppercase;color:var(--ink-soft);">Word on the street</div>' +
              '<div style="font-family:\'Fraunces\',serif;font-size:15px;font-weight:600;color:var(--ink);margin-top:1px;"><strong>' + name + '</strong> on a <strong style="color:var(--lose);">' + hotEntry.streaks.onFire + '</strong>-round hot streak!</div></div>' +
              '</div>';
            streakEl.style.display = 'block';
        } else { streakEl.style.display = 'none'; }
    }
}

let _countdownTimer = null;
function startCountdown(locksAt) {
    if (_countdownTimer) clearInterval(_countdownTimer);
    const el = document.getElementById('home-countdown');
    if (!el) return;
    function tick() {
        const diff = locksAt - Date.now();
        if (diff <= 0) { el.style.display = 'none'; clearInterval(_countdownTimer); return; }
        const h = Math.floor(diff / 3600000);
        const m = Math.floor((diff % 3600000) / 60000);
        const s = Math.floor((diff % 60000) / 1000);
        el.textContent = (h > 0 ? h + 'h ' : '') + (h > 0 || m > 0 ? String(m).padStart(2,'0') + 'm ' : '') + String(s).padStart(2,'0') + 's';
        el.style.display = 'block';
    }
    tick();
    _countdownTimer = setInterval(tick, 1000);
}

function renderTicket() {
    const ticketEl = document.getElementById('home-ticket');
    if (!state.round || !state.round.fixtures) { ticketEl.style.display = 'none'; return; }
    const fixtures = state.round.fixtures.filter(f => f.status !== 'postponed' && f.status !== 'cancelled' && f.status !== 'finished');
    const predicted = fixtures.filter(f => state.predictions[f.id]);
    if (predicted.length === 0) { ticketEl.style.display = 'none'; return; }
    ticketEl.style.display = 'block';
    document.getElementById('home-ticket-count').textContent = predicted.length + '/' + fixtures.length;
    document.getElementById('home-ticket-rows').innerHTML = fixtures.map(f => {
        const pick = state.predictions[f.id];
        return '<div style="display:flex;align-items:center;padding:9px 16px;border-bottom:1px solid rgba(58,40,24,.12);gap:10px;">' +
            '<div style="flex:1;font-size:13px;color:var(--ink);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">' + f.homeTeam + ' <span style="color:var(--ink-faint);">v</span> ' + f.awayTeam + '</div>' +
            (pick ? '<div style="min-width:32px;text-align:center;font-family:\'Fraunces\',serif;font-size:15px;font-weight:700;color:var(--green);background:rgba(46,93,58,.12);border-radius:6px;padding:3px 8px;">' + pick + '</div>'
                  : '<div style="min-width:32px;text-align:center;font-size:13px;color:var(--ink-faint);">–</div>') +
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
    if (isLocked || status === 'locked') return '<span class="badge-kf badge-amber">Locked</span>';
    if (status === 'active')   return '<span class="badge-kf badge-green">Active</span>';
    if (status === 'resolved') return '<span class="badge-kf badge-slate">Resolved</span>';
    return '<span class="badge-kf badge-slate">Pending</span>';
}

// ================================================================
//  PREDICT SCREEN
// ================================================================
function renderPredict() {
    if (!state.round) {
        document.getElementById('predict-list').innerHTML = '<div style="text-align:center;padding:32px;color:var(--ink-soft);">No active round.</div>';
        document.getElementById('predict-footer').style.display = 'none';
        document.getElementById('predict-locked-msg').style.display = 'none';
        document.getElementById('predict-lock-bar').style.display = 'none';
        return;
    }

    document.getElementById('predict-title').textContent = 'Round ' + state.round.number;
    const headerBal = document.getElementById('predict-header-balance');
    if (headerBal && state.player) {
        headerBal.style.display = 'inline-flex';
        headerBal.innerHTML = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="9" stroke="var(--gold)" stroke-width="1.6"/><path d="M12 8v8M9.5 9.5h4a1.5 1.5 0 010 3h-3a1.5 1.5 0 000 3h4" stroke="var(--gold)" stroke-width="1.4" stroke-linecap="round"/></svg>' +
            '<span>' + state.player.tokenBalance + '</span><span style="opacity:.6;font-size:11px;">KM</span>';
        headerBal.classList.toggle('negative', state.player.tokenBalance < 0);
    }

    const locked = state.round.isLocked;
    const fixtures = (state.round.fixtures || []).filter(f => f.status !== 'postponed' && f.status !== 'cancelled' && f.status !== 'finished');

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
            document.getElementById('predict-lock-bar').style.display = 'flex';
            document.getElementById('predict-lock-info').innerHTML = 'LOCKS IN <strong style="color:var(--lose);">' + h + 'h ' + m + 'm</strong>';
        } else {
            document.getElementById('predict-lock-bar').style.display = 'none';
        }
    }

    renderFixtureList(fixtures, false);
    updatePredictProgress(fixtures);
    document.getElementById('predict-footer').style.display = fixtures.length ? 'block' : 'none';
    document.getElementById('predict-debt-banner').style.display = 'none';
    document.getElementById('predict-submit').disabled = false;
    document.getElementById('predict-submit').onclick = submitPredictions;
}

function renderFixtureList(fixtures, readonly) {
    const list = document.getElementById('predict-list');
    if (!fixtures.length) {
        list.innerHTML = '<div style="text-align:center;padding:32px;color:var(--ink-soft);">No fixtures for this round yet.</div>';
        return;
    }
    list.innerHTML = fixtures.map((f, idx) => {
        const pick = state.predictions[f.id] || null;
        const resolved = f.result != null;
        const kickoff = f.kickoffAt ? new Date(f.kickoffAt).toLocaleString([], {weekday:'short',month:'short',day:'numeric',hour:'2-digit',minute:'2-digit'}) : '';

        const scoreHtml = f.status === 'finished'
            ? '<div class="vs-score finished">' + (f.homeScore ?? 0) + '–' + (f.awayScore ?? 0) + '</div>'
            : f.status === 'live'
            ? '<div class="vs-score live">' + (f.homeScore ?? 0) + '–' + (f.awayScore ?? 0) + ' ●</div>'
            : '';

        const picksHtml = ['1','X','2'].map(p => {
            let cls = 'pick-btn';
            const label = p === '1' ? 'HOME' : p === 'X' ? 'DRAW' : 'AWAY';
            if (pick === p) cls += resolved ? (p === f.result ? ' correct' : ' wrong') : ' selected';
            const handler = readonly ? '' : 'onclick="setPick(' + f.id + ',\'' + p + '\',this)"';
            const checkDot = (!readonly && pick === p) ? '<div style="position:absolute;top:-7px;right:-7px;width:18px;height:18px;border-radius:50%;background:var(--accent);border:2px solid var(--bg);display:flex;align-items:center;justify-content:center;"><svg width="9" height="9" viewBox="0 0 24 24" fill="none"><path d="M4 12l5 5 11-12" stroke="#fff" stroke-width="2.4" stroke-linecap="round"/></svg></div>' : '';
            return '<button class="' + cls + '" ' + handler + '><span class="pick-val">' + p + '</span><span class="pick-sub">' + label + '</span>' + checkDot + '</button>';
        }).join('');

        return '<div class="fixture-card" id="fixture-' + f.id + '">' +
            '<div class="fixture-meta">' +
              '<span class="fix-num">#' + String(idx+1).padStart(2,'0') + '</span>' +
              (kickoff ? '<span>' + kickoff + '</span>' : '') +
              (scoreHtml ? '<div style="margin-left:auto;">' + scoreHtml + '</div>' : '') +
            '</div>' +
            '<div class="fixture-teams">' +
              '<div class="fixture-team">' + f.homeTeam + '</div>' +
              '<div class="fixture-vs">v</div>' +
              '<div class="fixture-team">' + f.awayTeam + '</div>' +
            '</div>' +
            (!readonly
                ? '<div class="pick-row">' + picksHtml + '</div>'
                : '<div style="text-align:center;padding-top:4px;font-family:\'DM Mono\',monospace;font-size:11px;color:var(--ink-soft);">' +
                  (pick ? 'Your pick: <strong style="color:var(--ink);">' + pick + '</strong>' : 'No prediction') + '</div>'
            ) +
        '</div>';
    }).join('');
}

function setPick(fixtureId, pick, btnEl) {
    state.predictions[fixtureId] = pick;
    const card = document.getElementById('fixture-' + fixtureId);
    if (card) {
        card.querySelectorAll('.pick-btn').forEach(b => { b.classList.remove('selected'); b.querySelector('div[style*="position:absolute"]')?.remove(); });
        btnEl.classList.add('selected');
        const dot = document.createElement('div');
        dot.style.cssText = 'position:absolute;top:-7px;right:-7px;width:18px;height:18px;border-radius:50%;background:var(--accent);border:2px solid var(--bg);display:flex;align-items:center;justify-content:center;';
        dot.innerHTML = '<svg width="9" height="9" viewBox="0 0 24 24" fill="none"><path d="M4 12l5 5 11-12" stroke="#fff" stroke-width="2.4" stroke-linecap="round"/></svg>';
        btnEl.appendChild(dot);
    }
    const fixtures = (state.round?.fixtures || []).filter(f => f.status !== 'postponed' && f.status !== 'cancelled' && f.status !== 'finished');
    updatePredictProgress(fixtures);
}

function updatePredictProgress(fixtures) {
    const predicted = fixtures.filter(f => state.predictions[f.id]).length;
    const el = document.getElementById('predict-progress');
    el.textContent = predicted + ' / ' + fixtures.length + ' matches predicted';
    const btn = document.getElementById('predict-submit');
    if (btn) {
        const allPicked = predicted === fixtures.length && fixtures.length > 0;
        btn.textContent = allPicked ? 'Lock in my picks' : 'Pick ' + (fixtures.length - predicted) + ' more';
    }
    const pickCount = document.getElementById('predict-pick-count');
    if (pickCount) pickCount.textContent = predicted + '/' + fixtures.length + ' picked';
}

async function submitPredictions() {
    const btn = document.getElementById('predict-submit');
    const fixtures = (state.round?.fixtures || []).filter(f => f.status !== 'postponed' && f.status !== 'cancelled' && f.status !== 'finished');
    const predicted = fixtures.filter(f => state.predictions[f.id]).length;
    if (predicted === 0) { toast('Make at least one prediction', 'error'); return; }
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner"></span> Saving…';
    saveLocal();
    let debtBlocked = false;
    try {
        await api('POST', '/api/predictions', { predictions: state.predictions });
        await refreshState();
        toast('Predictions saved ✓');
        setTimeout(() => showScreen('home'), 600);
    } catch(e) {
        if (e.debtCapExceeded) {
            debtBlocked = true;
            document.getElementById('predict-debt-banner').style.display = 'block';
            document.getElementById('predict-submit').disabled = true;
            document.getElementById('predict-submit').textContent = 'Lock in my picks';
        } else {
            toast(e.message || 'Failed to save', 'error');
        }
    } finally {
        if (!debtBlocked) { btn.disabled = false; btn.textContent = 'Lock in my picks'; }
    }
}

// ================================================================
//  RESULTS SCREEN
// ================================================================
let resultsViewRoundId = null;

function buildResultsRoundList() {
    const rounds = [];
    (state.history || []).forEach(r => rounds.push({ id: r.id, number: r.number }));
    if (state.round && !rounds.find(r => r.id === state.round.id)) rounds.push({ id: state.round.id, number: state.round.number });
    rounds.sort((a, b) => a.number - b.number);
    return rounds;
}

function renderResultsNav(rounds) {
    const idx = rounds.findIndex(r => r.id === resultsViewRoundId);
    const hasPrev = idx > 0, hasNext = idx < rounds.length - 1;
    const prevBtn = document.getElementById('results-prev');
    const nextBtn = document.getElementById('results-next');
    const pos = document.getElementById('results-nav-pos');
    if (prevBtn) { prevBtn.disabled = !hasPrev; prevBtn.style.opacity = hasPrev ? '1' : '0.3'; }
    if (nextBtn) { nextBtn.disabled = !hasNext; nextBtn.style.opacity = hasNext ? '1' : '0.3'; }
    if (pos) pos.textContent = rounds.length > 1 ? (idx + 1) + ' / ' + rounds.length : '';
    const nav = document.getElementById('results-nav');
    if (nav) nav.style.display = rounds.length > 1 ? 'flex' : 'none';
}

function navigateResults(direction) {
    const rounds = buildResultsRoundList();
    const idx = rounds.findIndex(r => r.id === resultsViewRoundId);
    const nextIdx = idx + direction;
    if (nextIdx >= 0 && nextIdx < rounds.length) loadAndShowResults(rounds[nextIdx].id);
}

async function loadAndShowResults(roundId = null) {
    const rounds = buildResultsRoundList();
    if (!roundId) roundId = state.round ? state.round.id : (rounds.length ? rounds[rounds.length - 1].id : null);
    if (!roundId) { toast('No rounds to view', 'error'); return; }
    resultsViewRoundId = roundId;
    showScreen('results');
    renderResultsNav(rounds);
    document.getElementById('results-title').textContent = 'Results';
    document.getElementById('results-fixtures').innerHTML = '<div style="color:var(--ink-soft);text-align:center;padding:24px;"><span class="spinner" style="border-top-color:var(--accent);"></span></div>';
    document.getElementById('results-entries').innerHTML = '';
    try {
        const data = await api('GET', '/api/round/' + roundId + '/results');
        state.roundResults = data;
        document.getElementById('results-title').textContent = 'Round ' + data.round.number;
        const eyebrow = document.getElementById('results-eyebrow');
        if (eyebrow) eyebrow.textContent = data.round.status === 'resolved' ? 'Final results' : 'Round results';
        renderRoundResults(data);
    } catch(e) {
        document.getElementById('results-fixtures').innerHTML = '<div style="color:var(--lose);text-align:center;padding:16px;">Failed to load</div>';
    }
}

function renderRoundResults(data) {
    const { round, entries } = data;
    const fixtures = round.fixtures || [];

    // Fixtures summary
    const fixturesHtml = fixtures.map(f =>
        '<div style="display:flex;align-items:center;justify-content:space-between;padding:10px 0;border-bottom:1px solid rgba(58,40,24,.15);">' +
            '<div style="flex:1;font-size:13px;color:var(--ink-soft);text-align:right;">' + f.homeTeam + '</div>' +
            '<div style="min-width:60px;text-align:center;font-family:\'Fraunces\',serif;font-size:14px;font-weight:700;color:' +
                (f.status === 'finished' ? 'var(--ink)' : f.status === 'live' ? 'var(--green)' : 'var(--ink-faint)') + ';">' +
                (f.status === 'finished' || f.status === 'live' ? (f.homeScore ?? 0) + '–' + (f.awayScore ?? 0) + (f.status === 'live' ? ' ●' : '') : '–') +
            '</div>' +
            '<div style="flex:1;font-size:13px;color:var(--ink-soft);">' + f.awayTeam + '</div>' +
        '</div>'
    ).join('');

    document.getElementById('results-fixtures').innerHTML =
        '<div class="card" style="margin-bottom:14px;">' +
        '<div style="font-family:\'DM Mono\',monospace;font-size:10px;letter-spacing:1.4px;text-transform:uppercase;color:var(--ink-soft);margin-bottom:8px;">Fixtures</div>' +
        fixturesHtml + '</div>';

    const sorted = [...entries].sort((a, b) => (b.points || 0) - (a.points || 0));
    const entriesHtml = sorted.map(e => {
        const isMe = state.player && e.playerId === state.player.id;
        const displayName = e.displayName || e.playerName;
        const picksHtml = fixtures.map(f => {
            const pick = e.predictions && e.predictions[f.id] ? e.predictions[f.id] : null;
            const result = f.result;
            const isCorrect = pick && result && pick === result;
            const isWrong = pick && result && pick !== result;
            return '<div style="display:flex;align-items:center;justify-content:space-between;padding:8px 0;border-bottom:1px solid rgba(58,40,24,.1);">' +
                '<div style="font-size:13px;color:var(--ink-soft);">' + f.homeTeam + ' v ' + f.awayTeam + '</div>' +
                '<div style="font-family:\'Fraunces\',serif;font-weight:700;font-size:15px;color:' + (isCorrect ? 'var(--win)' : isWrong ? 'var(--lose)' : 'var(--ink-faint)') + '">' + (pick || '–') + '</div>' +
            '</div>';
        }).join('');

        return '<div style="padding:0 16px;border-bottom:1.5px solid rgba(58,40,24,.2);">' +
            '<div style="display:flex;align-items:center;padding:14px 0;cursor:pointer;" onclick="toggleDrawer(this)">' +
                '<div style="flex:1;font-family:\'Fraunces\',serif;font-size:16px;font-weight:600;color:' + (isMe ? 'var(--green)' : 'var(--ink)') + ';">' +
                    displayName + (isMe ? ' (you)' : '') + (e.isPerfect ? ' 🏆' : '') + '</div>' +
                '<div style="display:flex;align-items:center;gap:8px;">' +
                    (round.status === 'resolved' ? '<span style="font-family:\'Fraunces\',serif;font-size:18px;font-weight:700;color:var(--green);">' + e.points + '</span><span style="font-size:12px;color:var(--ink-faint);">pts</span>' : '') +
                    (e.isComplete ? '<span class="badge-kf badge-green">✓</span>' : '<span class="badge-kf badge-amber">–</span>') +
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
        document.getElementById('lb-season-info').textContent = state.season.leagueName + ' · Season';
        const jackCard = document.getElementById('lb-jackpot-card');
        const jackAmt = document.getElementById('lb-jackpot-amount');
        if (jackCard && jackAmt) {
            jackCard.style.display = 'block';
            jackAmt.textContent = state.season.jackpot + ' KM';
        }
    }

    if (!lb.length) {
        document.getElementById('lb-list').innerHTML = '';
        document.getElementById('lb-podium').style.display = 'none';
        document.getElementById('lb-empty').style.display = 'block';
        return;
    }

    document.getElementById('lb-empty').style.display = 'none';

    // Podium (top 3)
    const sorted = [...lb].sort((a,b) => b.points - a.points);
    const podiumEl = document.getElementById('lb-podium');
    if (podiumEl && sorted.length >= 2) {
        podiumEl.style.display = 'block';
        const top3 = [sorted[1], sorted[0], sorted[2]].filter(Boolean);
        const heights = [86, 110, 72];
        podiumEl.innerHTML = '<div style="display:grid;grid-template-columns:1fr 1.2fr 1fr;gap:8px;align-items:end;margin-bottom:16px;">' +
            top3.map((p, i) => {
                const place = i === 1 ? 1 : i === 0 ? 2 : 3;
                const h = heights[i];
                const initials = (p.displayName || p.playerName || '?').slice(0,2).toUpperCase();
                const isGold = place === 1;
                return '<div>' +
                  '<div style="text-align:center;margin-bottom:6px;">' +
                    '<div style="width:44px;height:44px;border-radius:50%;margin:0 auto;background:var(--surface);border:2px solid ' + (isGold ? 'var(--gold)' : 'var(--rule)') + ';display:flex;align-items:center;justify-content:center;font-family:\'Fraunces\',serif;font-size:16px;font-weight:700;color:var(--ink);">' + initials + '</div>' +
                    '<div style="font-size:12px;font-weight:600;margin-top:4px;color:var(--ink);">' + (p.displayName || p.playerName) + '</div>' +
                    '<div style="font-family:\'DM Mono\',monospace;font-size:11px;color:var(--ink-soft);">' + p.points + ' pts</div>' +
                  '</div>' +
                  '<div style="height:' + h + 'px;background:' + (isGold ? 'var(--gold)' : 'var(--surface-alt)') + ';border:1.5px solid var(--ink);border-bottom:0;border-radius:6px 6px 0 0;display:flex;align-items:flex-start;justify-content:center;padding-top:8px;font-family:\'Fraunces\',serif;font-size:28px;font-weight:800;color:' + (isGold ? 'var(--ink)' : 'var(--ink-soft)') + ';">' + place + '</div>' +
                '</div>';
            }).join('') +
        '</div>';
    }

    document.getElementById('lb-list').innerHTML = sorted.map((p, i) => {
        const rank = i + 1;
        const isMe = state.player && p.playerId === state.player.id;
        const displayName = p.displayName || p.playerName;
        const streaks = p.streaks || {};
        const badgeCount = p.badgeCount || 0;

        let streakHtml = '';
        if (streaks.onFire > 0) streakHtml += '<span style="display:inline-flex;align-items:center;gap:2px;color:var(--lose);font-size:12px;"><svg width="13" height="13" viewBox="0 0 24 24" fill="none"><path d="M12 3c0 3 3 4 3 8a3 3 0 11-6 0c0-1.5 1-2 1-3.5C9 9 12 6 12 3z" fill="var(--lose)"/></svg>' + streaks.onFire + '</span>';
        if (streaks.cold > 1) streakHtml += '<span style="display:inline-flex;align-items:center;gap:2px;color:var(--blue);font-size:12px;margin-left:4px;"><svg width="13" height="13" viewBox="0 0 24 24" fill="none"><path d="M12 3v18M3 12h18M5.5 5.5l13 13M5.5 18.5l13-13" stroke="var(--blue)" stroke-width="1.6" stroke-linecap="round"/></svg>' + streaks.cold + '</span>';
        if (badgeCount > 0) streakHtml += '<span style="display:inline-flex;align-items:center;gap:1px;margin-left:4px;">' + Array.from({length:Math.min(badgeCount,5)}).map((_,k) => '<div style="width:6px;height:6px;border-radius:50%;background:var(--gold);opacity:' + (1 - k*0.12) + ';"></div>').join('') + (badgeCount > 5 ? '<span style="font-family:\'DM Mono\',monospace;font-size:9px;color:var(--ink-soft);margin-left:2px;">+' + (badgeCount-5) + '</span>' : '') + '</span>';

        return '<div class="lb-row ' + (isMe ? 'me' : '') + '">' +
            '<div class="lb-rank ' + (rank <= 3 ? 'top3' : '') + '">' + rank + '</div>' +
            '<div class="lb-name" style="cursor:pointer;" onclick="openBadges(' + p.playerId + ')">' +
                displayName +
                (streakHtml ? '<div style="margin-top:2px;display:flex;gap:4px;align-items:center;">' + streakHtml + '</div>' : '') +
            '</div>' +
            '<div class="lb-pts">' + p.points + '</div>' +
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
                    '<div style="font-family:\'Fraunces\',serif;font-size:17px;font-weight:700;color:var(--ink);">Round ' + r.number + '</div>' +
                    '<div style="font-family:\'DM Mono\',monospace;font-size:11px;color:var(--ink-soft);margin-top:2px;">' +
                        (r.locksAt ? new Date(r.locksAt).toLocaleDateString() : '') + '</div>' +
                '</div>' +
                '<div style="display:flex;align-items:center;gap:8px;">' +
                    '<span class="badge-kf badge-slate">Resolved</span>' +
                    '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="var(--ink-faint)" stroke-width="2"><path d="M9 18l6-6-6-6"/></svg>' +
                '</div>' +
            '</div>' +
        '</div>'
    ).join('');
}

async function loadHistoryDetail(roundId) {
    showScreen('history-detail');
    document.getElementById('history-detail-title').textContent = 'Loading…';
    document.getElementById('history-detail-fixtures').innerHTML = '<div style="color:var(--ink-soft);text-align:center;padding:24px;"><span class="spinner" style="border-top-color:var(--accent);"></span></div>';
    document.getElementById('history-detail-entries').innerHTML = '';
    try {
        const data = await api('GET', '/api/history/' + roundId);
        state.historyDetail = data;
        renderHistoryDetail(data);
    } catch(e) {
        document.getElementById('history-detail-fixtures').innerHTML = '<div style="color:var(--lose);text-align:center;padding:16px;">Failed to load</div>';
    }
}

function renderHistoryDetail(data) {
    const { round, entries } = data;
    document.getElementById('history-detail-title').textContent = 'Round ' + round.number;
    const fixtures = round.fixtures || [];
    const fixturesHtml = fixtures.map(f =>
        '<div style="display:flex;align-items:center;justify-content:space-between;padding:10px 0;border-bottom:1px solid rgba(58,40,24,.15);">' +
            '<div style="flex:1;font-size:13px;color:var(--ink-soft);text-align:right;">' + f.homeTeam + '</div>' +
            '<div style="min-width:60px;text-align:center;font-family:\'Fraunces\',serif;font-size:15px;font-weight:700;color:var(--ink);">' + (f.homeScore ?? 0) + '–' + (f.awayScore ?? 0) + '</div>' +
            '<div style="flex:1;font-size:13px;color:var(--ink-soft);">' + f.awayTeam + '</div>' +
        '</div>'
    ).join('');
    document.getElementById('history-detail-fixtures').innerHTML =
        '<div class="card"><div style="font-family:\'DM Mono\',monospace;font-size:10px;letter-spacing:1.4px;text-transform:uppercase;color:var(--ink-soft);margin-bottom:8px;">Final Results</div>' + fixturesHtml + '</div>';
    const sorted = [...entries].sort((a, b) => b.points - a.points);
    const entriesHtml = sorted.map(e => {
        const isMe = state.player && e.playerId === state.player.id;
        const displayName = e.displayName || e.playerName;
        const picksHtml = fixtures.map(f => {
            const pick = e.predictions && e.predictions[f.id] ? e.predictions[f.id] : null;
            const isCorrect = pick && f.result && pick === f.result;
            const isWrong = pick && f.result && pick !== f.result;
            return '<div style="display:flex;align-items:center;justify-content:space-between;padding:8px 0;border-bottom:1px solid rgba(58,40,24,.1);">' +
                '<div style="font-size:13px;color:var(--ink-soft);">' + f.homeTeam + ' v ' + f.awayTeam + '</div>' +
                '<div style="font-family:\'Fraunces\',serif;font-weight:700;color:' + (isCorrect ? 'var(--win)' : isWrong ? 'var(--lose)' : 'var(--ink-faint)') + '">' + (pick || '–') + '</div>' +
            '</div>';
        }).join('');
        return '<div style="padding:0 16px;border-bottom:1.5px solid rgba(58,40,24,.2);">' +
            '<div style="display:flex;align-items:center;padding:14px 0;cursor:pointer;" onclick="toggleDrawer(this)">' +
                '<div style="flex:1;font-family:\'Fraunces\',serif;font-size:16px;font-weight:600;color:' + (isMe ? 'var(--green)' : 'var(--ink)') + ';">' + displayName + (isMe ? ' (you)' : '') + (e.isPerfect ? ' 🏆' : '') + '</div>' +
                '<div style="display:flex;align-items:baseline;gap:4px;"><span style="font-family:\'Fraunces\',serif;font-size:18px;font-weight:700;color:var(--green);">' + e.points + '</span><span style="font-size:12px;color:var(--ink-faint);">pts</span></div>' +
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
    document.getElementById('admin-jackpot').textContent = (state.season?.jackpot ?? 0) + ' KM';
    if (state.season) {
        const isPending = state.season.isPendingSettlement;
        document.getElementById('admin-season-badge').innerHTML = isPending ? '<span class="badge-kf badge-amber">Pending Settlement</span>' : '<span class="badge-kf badge-green">Active</span>';
        document.getElementById('admin-season-info').textContent = state.season.leagueName + ' · ' + state.season.entryTokens + ' KM/round';
        document.getElementById('admin-settlement-card').style.display = isPending ? 'block' : 'none';
        document.getElementById('admin-pending-settlement-btn').style.display = isPending ? 'none' : 'inline-flex';
    } else {
        document.getElementById('admin-season-badge').innerHTML = '<span class="badge-kf badge-slate">None</span>';
        document.getElementById('admin-season-info').textContent = 'No active season.';
        document.getElementById('admin-settlement-card').style.display = 'none';
        document.getElementById('admin-pending-settlement-btn').style.display = 'none';
    }
    if (state.round) {
        const label = state.round.status === 'resolved' ? 'Resolved · Next round created automatically' : state.round.isLocked ? 'Locked · Results syncing automatically' : 'Active · Results syncing automatically';
        document.getElementById('admin-round-info').textContent = 'Round ' + state.round.number + ' · ' + label;
        ['admin-sync-fixtures-btn','admin-sync-results-btn'].forEach(id => document.getElementById(id).style.display = 'inline-flex');
        document.getElementById('admin-sync-fixtures-btn').onclick = adminSyncFixtures;
        document.getElementById('admin-sync-results-btn').onclick = adminSyncResults;
    } else {
        document.getElementById('admin-round-info').textContent = 'No active round. A round will be created automatically when the season starts.';
        ['admin-sync-fixtures-btn','admin-sync-results-btn'].forEach(id => document.getElementById(id).style.display = 'none');
    }
}

async function adminAction(btnId, label, fn) {
    const btn = document.getElementById(btnId);
    btn.disabled = true; btn.innerHTML = '<span class="spinner"></span>';
    try { await fn(); await refreshState(); renderAdmin(); } catch(e) { toast(e.message || 'Action failed', 'error'); }
    finally { btn.disabled = false; btn.textContent = label; }
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

async function adminCheckApi() {
    const btn = document.getElementById('admin-check-api-btn');
    btn.disabled = true; btn.innerHTML = '<span class="spinner"></span>';
    try {
        const d = await api('GET', '/api/admin/api-status');
        toast(d.ok ? '✓ API OK (' + (d.requestsAvailable ?? '?') + ' req remaining)' : '✗ ' + d.message, !d.ok ? 'error' : 'success');
    } catch(e) { toast(e.message || 'API check failed', 'error'); }
    finally { btn.disabled = false; btn.textContent = 'Check API'; }
}

async function adminStartSettlement() {
    if (!confirm('Move the season to pending settlement?')) return;
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
    document.getElementById('admin-players-list').innerHTML = '<div style="color:var(--ink-soft);text-align:center;padding:32px;"><span class="spinner" style="border-top-color:var(--accent);"></span></div>';
    try {
        const data = await api('GET', '/api/admin/players');
        state.adminPlayers = data.players;
        renderAdminPlayers();
    } catch(e) {
        document.getElementById('admin-players-list').innerHTML = '<div style="color:var(--lose);text-align:center;padding:16px;">Failed to load</div>';
    }
}

function renderAdminPlayers() {
    const players = state.adminPlayers || [];
    if (!players.length) { document.getElementById('admin-players-list').innerHTML = '<div style="color:var(--ink-soft);text-align:center;padding:32px;">No players yet.</div>'; return; }
    document.getElementById('admin-players-list').innerHTML = players.map(p =>
        '<div class="admin-item" onclick="openPlayerForm(' + p.id + ')">' +
            '<div style="flex:1;">' +
                '<div style="font-size:16px;font-weight:600;color:var(--ink);">' + p.name + (p.isAdmin ? ' <span class="badge-kf badge-blue" style="margin-left:6px;">Admin</span>' : '') + '</div>' +
                '<div style="font-family:\'DM Mono\',monospace;font-size:12px;color:' + (p.tokenBalance < 0 ? 'var(--lose)' : 'var(--ink-soft)') + ';margin-top:2px;">' + p.tokenBalance + ' KM</div>' +
            '</div>' +
            '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="var(--ink-faint)" stroke-width="2"><path d="M9 18l6-6-6-6"/></svg>' +
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
    const pin = document.getElementById('player-form-pin').value.trim();
    const tokenBalance = parseInt(document.getElementById('player-form-tokens').value) || 0;
    const isAdmin = document.getElementById('player-form-admin').checked;
    if (!name) { toast('Name is required', 'error'); return; }
    if (!id && !pin) { toast('PIN is required for new players', 'error'); return; }
    if (pin && (pin.length < 4 || pin.length > 8)) { toast('PIN must be 4–8 digits', 'error'); return; }
    const btn = document.getElementById('player-form-save');
    btn.disabled = true; btn.innerHTML = '<span class="spinner"></span>';
    const payload = { name, is_admin: isAdmin, token_balance: tokenBalance };
    if (pin) payload.pin = pin;
    try {
        if (id) {
            await api('PUT', '/api/admin/players/' + id, payload);
            await api('PUT', '/api/admin/players/' + id + '/nickname', { nickname: nickname || null });
        } else {
            const res = await api('POST', '/api/admin/players', payload);
            if (nickname && res.player?.id) await api('PUT', '/api/admin/players/' + res.player.id + '/nickname', { nickname });
        }
        toast('Player saved ✓');
        await refreshState();
        const data = await api('GET', '/api/admin/players');
        state.adminPlayers = data.players;
        showScreen('admin-players');
    } catch(e) { toast(e.message || 'Save failed', 'error'); }
    finally { btn.disabled = false; btn.textContent = 'Save Player'; }
}

async function deletePlayer(playerId) {
    if (!confirm('Delete this player? This cannot be undone.')) return;
    try {
        await api('DELETE', '/api/admin/players/' + playerId);
        toast('Player deleted');
        await refreshState();
        const data = await api('GET', '/api/admin/players');
        state.adminPlayers = data.players;
        showScreen('admin-players');
    } catch(e) { toast(e.message || 'Delete failed', 'error'); }
}

// ================================================================
//  ADMIN ROUNDS
// ================================================================
async function renderAdminRounds() {
    document.getElementById('admin-rounds-list').innerHTML = '<div style="color:var(--ink-soft);text-align:center;padding:32px;"><span class="spinner" style="border-top-color:var(--accent);"></span></div>';
    try {
        const data = await api('GET', '/api/admin/rounds');
        state.adminRounds = data.rounds || [];
        if (!state.adminRounds.length) { document.getElementById('admin-rounds-list').innerHTML = '<div style="color:var(--ink-soft);text-align:center;padding:32px;">No rounds yet.</div>'; return; }
        document.getElementById('admin-rounds-list').innerHTML = state.adminRounds.map(r =>
            '<div class="admin-item">' +
                '<div style="flex:1;cursor:pointer;" onclick="editRound(' + r.id + ')">' +
                    '<div style="font-family:\'Fraunces\',serif;font-size:16px;font-weight:600;color:var(--ink);">Round ' + r.number + '</div>' +
                    '<div style="font-family:\'DM Mono\',monospace;font-size:12px;color:var(--ink-soft);margin-top:2px;">' + r.status + ' · ' + (r.locksAt ? new Date(r.locksAt).toLocaleDateString() : 'No lock time') + '</div>' +
                '</div>' +
                roundStatusBadge(r.status, r.isLocked) +
                '<button onclick="event.stopPropagation();deleteRound(' + r.id + ',' + r.number + ')" style="margin-left:10px;background:none;border:1.5px solid var(--lose);border-radius:6px;color:var(--lose);padding:4px 10px;font-size:12px;cursor:pointer;">Delete</button>' +
            '</div>'
        ).join('');
    } catch(e) {
        document.getElementById('admin-rounds-list').innerHTML = '<div style="color:var(--lose);text-align:center;padding:16px;">Failed to load</div>';
    }
}

async function deleteRound(roundId, number) {
    if (!confirm('Delete Round ' + number + '? This removes all fixtures, predictions and entries.')) return;
    try {
        await api('DELETE', '/api/admin/rounds/' + roundId);
        toast('Round ' + number + ' deleted');
        await refreshState();
        renderAdminRounds();
    } catch(e) { toast(e.message || 'Delete failed', 'error'); }
}

function editRound(roundId) {
    showScreen('admin-round-form');
    const r = roundId ? (state.adminRounds || []).find(x => x.id === roundId) : null;
    document.getElementById('round-form-id').value = roundId || '';
    document.getElementById('round-form-title').textContent = r ? 'Edit Round' : 'New Round';
    document.getElementById('round-form-number').value = r?.number || '';
    document.getElementById('round-form-status-group').style.display = r ? 'block' : 'none';
    document.getElementById('round-form-locks-at-group').style.display = r ? 'block' : 'none';
    document.getElementById('round-form-locks-at-note').style.display = r ? 'none' : 'block';
    if (r?.locksAt) {
        const dt = new Date(r.locksAt);
        document.getElementById('round-form-locks-at').value = new Date(dt.getTime() - dt.getTimezoneOffset() * 60000).toISOString().slice(0,16);
    } else { document.getElementById('round-form-locks-at').value = ''; }
    if (r?.status) document.getElementById('round-form-status').value = r.status;
    document.getElementById('round-form-resolve-wrap').style.display = (r && r.isLocked && r.status !== 'resolved' && r.status !== 'pending') ? 'block' : 'none';
    document.getElementById('round-form-save').onclick = saveRoundForm;
}

async function saveRoundForm() {
    const id = document.getElementById('round-form-id').value;
    const number = parseInt(document.getElementById('round-form-number').value);
    const locksAtRaw = document.getElementById('round-form-locks-at').value;
    const status = document.getElementById('round-form-status').value;
    const btn = document.getElementById('round-form-save');
    btn.disabled = true; btn.innerHTML = '<span class="spinner"></span>';
    try {
        if (id) {
            const locksAt = locksAtRaw ? new Date(locksAtRaw).toISOString() : null;
            await api('PUT', '/api/admin/rounds/' + id, { status, locks_at: locksAt });
        } else {
            if (!number || number < 1) { toast('Enter round number', 'error'); btn.disabled=false; btn.textContent='Save Round'; return; }
            await api('POST', '/api/admin/rounds', { number });
        }
        toast('Round saved ✓');
        await refreshState();
        showScreen('admin-rounds');
        renderAdminRounds();
    } catch(e) { toast(e.message || 'Save failed', 'error'); }
    finally { btn.disabled = false; btn.textContent = 'Save Round'; }
}

async function adminResolveRound(roundId) {
    if (!confirm('Resolve this round? This will score all predictions and create the next round.')) return;
    try {
        const d = await api('POST', `/api/admin/rounds/${roundId}/resolve`);
        toast('Round resolved. ' + (d.stats && d.stats.jackpot_winners > 0 ? d.stats.jackpot_winners + ' perfect prediction(s)!' : 'No jackpot winner.'));
        await refreshState();
        showScreen('admin');
    } catch(e) { toast(e.message || 'Failed to resolve round', 'error'); }
}

// ================================================================
//  ADMIN SEASON FORM
// ================================================================
document.getElementById('season-form-save').addEventListener('click', async () => {
    const leagueId    = document.getElementById('season-form-league-id').value.trim().toUpperCase();
    const leagueName  = document.getElementById('season-form-league-name').value.trim();
    const entryTokens = parseInt(document.getElementById('season-form-entry-tokens').value) || 0;
    if (!leagueId)    { toast('Enter league code', 'error'); return; }
    if (!leagueName)  { toast('Enter league name', 'error'); return; }
    if (entryTokens < 1) { toast('Entry tokens must be ≥ 1', 'error'); return; }
    const btn = document.getElementById('season-form-save');
    btn.disabled = true; btn.innerHTML = '<span class="spinner"></span>';
    try {
        await api('POST', '/api/admin/season', { league_id: leagueId, league_name: leagueName, entry_tokens: entryTokens });
        toast('Season started ✓');
        await refreshState();
        showScreen('admin');
    } catch(e) { toast(e.message || 'Failed', 'error'); }
    finally { btn.disabled = false; btn.textContent = 'Start Season'; }
});

// ================================================================
//  BADGE CONSTANTS
// ================================================================
const BADGE_REQUIREMENTS = {
    sniper:        { kafa: '4+ pts in 2 rounds', rakija: '6+ pts in 3 rounds', zlato: '8+ pts in 3 rounds' },
    perfectionist: { kafa: '1 perfect round', rakija: '2 perfect rounds', zlato: '3 perfect rounds' },
    iron_man:      { kafa: '3 rounds in a row', rakija: '5 rounds in a row', zlato: '8 rounds in a row' },
    comeback_kid:  { kafa: '1 comeback', rakija: '2 comebacks', zlato: '3 comebacks' },
    jackpot:       { kafa: '1 jackpot win', rakija: '2 jackpot wins', zlato: '3 jackpot wins' },
    ledeni:        { kafa: '6 cold rounds', rakija: '9 cold rounds', zlato: '12 cold rounds' },
};
const BADGE_ICONS = {
    sniper: '<circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="1.6"/><circle cx="12" cy="12" r="5" stroke="currentColor" stroke-width="1.4"/><circle cx="12" cy="12" r="1.5" fill="currentColor"/>',
    perfectionist: '<path d="M12 2l3 7 7 .8-5.2 4.7 1.6 7.1L12 17.8 5.6 21.6 7.2 14.5 2 9.8 9 9l3-7z" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round" fill="currentColor"/>',
    iron_man: '<path d="M13 2L4 14h6l-1 8 9-12h-6l1-8z" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round" fill="none"/>',
    comeback_kid: '<path d="M12 21c-5 0-7-3-7-6 0-2 1-3 2-3-1-1-1-3 0-5 1 2 3 2 4 1-1-2 0-5 1-7 1 3 3 5 5 5-1 1-1 3 0 4 2-1 4 0 5 2-2 0-3 1-3 3 1 1 1 3 0 4-1-1-3-1-4 0 1 1 0 2-3 2z" fill="currentColor"/>',
    jackpot: '<path d="M7 4h10v3a5 5 0 01-10 0V4z" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/><path d="M9 13.5l-.5 3.5h7l-.5-3.5M8 20h8" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>',
    ledeni: '<path d="M12 3v18M3 12h18M5.5 5.5l13 13M5.5 18.5l13-13" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>',
};
const BADGE_LABELS = { sniper:'Sniper', perfectionist:'Perfectionist', iron_man:'Iron Man', comeback_kid:'Comeback Kid', jackpot:'Jackpot', ledeni:'Ledeni' };
const TIER_COLORS = {
    kafa:   { bg: '#78350f', color: '#fde68a', border: '#a16207' },
    rakija: { bg: '#92400e', color: '#fff',    border: '#b45309' },
    zlato:  { bg: '#fbbf24', color: '#1a1a1a', border: '#d97706' },
};
const TIER_LABELS = { kafa:'Kafa', rakija:'Rakija', zlato:'Zlato' };
const TX_TYPE_LABELS = {
    credit:'Credit', debit_round:'Entry Fee', payout_jackpot:'Jackpot Win',
    payout_season_winner:'Season Win', settlement_refund:'Settlement Refund',
    settlement_collected:'Settlement Collected', adjustment:'Adjustment', debit:'Debit',
};

// ================================================================
//  BALANCES
// ================================================================
async function loadBalances() {
    try {
        const data = await api('GET', '/api/players/balances');
        state.balances = data.players;
        showScreen('balances');
    } catch(e) { toast(e.message || 'Failed to load balances', 'error'); }
}

function renderBalancesScreen() {
    const players = state.balances || [];
    const list = document.getElementById('balances-list');
    if (!list) return;
    if (!players.length) { list.innerHTML = '<div style="color:var(--ink-soft);text-align:center;padding:32px;">No players.</div>'; return; }

    const totalIn = players.filter(p => p.tokenBalance > 0).reduce((s,p) => s + p.tokenBalance, 0);
    const totalOut = players.filter(p => p.tokenBalance < 0).reduce((s,p) => s + p.tokenBalance, 0);
    const summary = document.getElementById('balances-summary');
    if (summary) {
        summary.style.display = 'grid';
        summary.innerHTML =
            '<div class="card"><div class="field-label">In credit</div><div style="font-family:\'Fraunces\',serif;font-size:24px;font-weight:800;color:var(--win);line-height:1;margin-top:4px;">+' + totalIn + ' <span style="font-size:12px;color:var(--ink-soft);font-weight:500;">KM</span></div></div>' +
            '<div class="card"><div class="field-label">In debt</div><div style="font-family:\'Fraunces\',serif;font-size:24px;font-weight:800;color:var(--lose);line-height:1;margin-top:4px;">' + totalOut + ' <span style="font-size:12px;color:var(--ink-soft);font-weight:500;">KM</span></div></div>';
    }

    const sorted = [...players].sort((a,b) => b.tokenBalance - a.tokenBalance);
    list.innerHTML = '<div class="card" style="padding:0;overflow:hidden;">' +
        sorted.map((p, i) => {
            const pos = p.tokenBalance >= 0;
            const pct = Math.min(50, Math.abs(p.tokenBalance) * 2);
            return '<div style="display:flex;align-items:center;gap:10px;padding:12px 14px;border-top:' + (i === 0 ? '0' : '1px solid rgba(58,40,24,.12)') + ';cursor:pointer;" onclick="loadLedger(' + p.id + ')">' +
                '<div style="width:32px;height:32px;border-radius:50%;background:var(--bg);border:1.5px solid var(--rule);display:flex;align-items:center;justify-content:center;font-family:\'Fraunces\',serif;font-size:12px;font-weight:700;color:var(--ink);flex-shrink:0;">' + (p.displayName || p.name).slice(0,2).toUpperCase() + '</div>' +
                '<div style="flex:1;">' +
                    '<div style="font-size:14px;font-weight:600;color:var(--ink);">' + (p.displayName || p.name) + '</div>' +
                    '<div style="font-family:\'DM Mono\',monospace;font-size:10px;letter-spacing:1px;text-transform:uppercase;color:var(--ink-soft);">' + (pos ? 'in credit' : p.tokenBalance < -10 ? 'in the red' : 'in debt') + '</div>' +
                '</div>' +
                '<div style="width:56px;height:6px;border-radius:3px;background:var(--bg-deep);overflow:hidden;position:relative;">' +
                    '<div style="position:absolute;' + (pos ? 'left:50%' : 'left:' + (50 - pct) + '%') + ';width:' + pct + '%;height:100%;background:' + (pos ? 'var(--win)' : 'var(--lose)') + ';"></div>' +
                    '<div style="position:absolute;left:50%;top:0;bottom:0;width:1px;background:var(--ink);opacity:.3;"></div>' +
                '</div>' +
                '<div style="font-family:\'Fraunces\',serif;font-size:16px;font-weight:700;color:' + (pos ? 'var(--win)' : 'var(--lose)') + ';min-width:44px;text-align:right;">' + (pos ? '+' : '') + p.tokenBalance + '</div>' +
            '</div>';
        }).join('') +
    '</div>';
}

// ================================================================
//  LEDGER
// ================================================================
async function loadLedger(playerId) {
    state.currentLedgerPlayerId = playerId;
    showScreen('ledger');
    document.getElementById('ledger-list').innerHTML = '<div style="color:var(--ink-soft);text-align:center;padding:24px;"><span class="spinner" style="border-top-color:var(--accent);"></span></div>';
    try {
        const data = await api('GET', '/api/players/' + playerId + '/ledger');
        state.ledger[playerId] = data;
        renderLedger(playerId);
    } catch(e) {
        document.getElementById('ledger-list').innerHTML = '<div style="color:var(--lose);text-align:center;padding:16px;">Failed to load</div>';
    }
}

async function loadMoreLedger(playerId, page) {
    try {
        const data = await api('GET', '/api/players/' + playerId + '/ledger?page=' + page);
        const existing = state.ledger[playerId];
        if (existing) { existing.transactions = [...(existing.transactions || []), ...data.transactions]; existing.meta = data.meta; }
        else { state.ledger[playerId] = data; }
        renderLedger(playerId);
    } catch(e) { toast('Failed to load more', 'error'); }
}

function renderLedger(playerId) {
    const data = state.ledger[playerId];
    if (!data) return;
    const { player, transactions, meta } = data;
    document.getElementById('ledger-player-name').textContent = player.displayName || player.name;
    const balEl = document.getElementById('ledger-player-balance');
    if (balEl) {
        balEl.innerHTML = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="9" stroke="var(--gold)" stroke-width="1.6"/><path d="M12 8v8M9.5 9.5h4a1.5 1.5 0 010 3h-3a1.5 1.5 0 000 3h4" stroke="var(--gold)" stroke-width="1.4" stroke-linecap="round"/></svg>' +
            '<span>' + player.tokenBalance + '</span><span style="opacity:.6;font-size:11px;">KM</span>';
        balEl.classList.toggle('negative', player.tokenBalance < 0);
    }
    if (!transactions || !transactions.length) {
        document.getElementById('ledger-list').innerHTML = '<div style="color:var(--ink-soft);text-align:center;padding:32px;">No transactions yet.</div>';
        return;
    }
    const rows = transactions.map(t => {
        const isPos = t.amount > 0;
        const date = t.createdAt ? new Date(t.createdAt).toLocaleDateString() : '';
        const before = t.balanceBefore != null ? t.balanceBefore : '—';
        const after  = t.balanceAfter  != null ? t.balanceAfter  : '—';
        const label  = TX_TYPE_LABELS[t.type] || t.type;
        return '<div style="display:flex;align-items:center;gap:10px;padding:11px 14px;border-top:1px solid rgba(58,40,24,.1);">' +
            '<div style="width:28px;height:28px;border-radius:50%;background:' + (isPos ? 'rgba(46,93,58,.15)' : 'rgba(168,52,26,.15)') + ';color:' + (isPos ? 'var(--win)' : 'var(--lose)') + ';display:flex;align-items:center;justify-content:center;font-weight:700;font-family:\'DM Mono\',monospace;font-size:14px;flex-shrink:0;">' + (isPos ? '+' : '−') + '</div>' +
            '<div style="flex:1;min-width:0;">' +
                '<div style="font-size:13px;font-weight:500;color:var(--ink);">' + label + (t.description ? ' — ' + t.description : '') + '</div>' +
                '<div style="font-family:\'DM Mono\',monospace;font-size:10px;color:var(--ink-soft);letter-spacing:1px;">' + (date ? date.toUpperCase() + ' · ' : '') + before + ' → ' + after + '</div>' +
            '</div>' +
            '<div style="font-family:\'Fraunces\',serif;font-size:16px;font-weight:700;color:' + (isPos ? 'var(--win)' : 'var(--lose)') + ';">' + (isPos ? '+' : '') + t.amount + '</div>' +
        '</div>';
    }).join('');
    const loadMore = (meta && meta.currentPage < meta.lastPage)
        ? '<div style="padding:16px;text-align:center;"><button class="btn btn-secondary btn-sm" onclick="loadMoreLedger(' + playerId + ',' + (meta.currentPage + 1) + ')">Load more</button></div>'
        : '';
    document.getElementById('ledger-list').innerHTML = '<div class="card" style="padding:0;overflow:hidden;">' + rows + '</div>' + loadMore;
}

// ================================================================
//  BADGES
// ================================================================
async function openBadges(playerId) {
    state.currentBadgePlayerId = playerId;
    showScreen('badges');
    document.getElementById('badges-shelf').innerHTML = '<div style="color:var(--ink-soft);text-align:center;padding:24px;"><span class="spinner" style="border-top-color:var(--accent);"></span></div>';
    try {
        const data = await api('GET', '/api/players/' + playerId + '/badges');
        state.badges[playerId] = data;
        renderBadges(playerId);
    } catch(e) {
        document.getElementById('badges-shelf').innerHTML = '<div style="color:var(--lose);text-align:center;padding:16px;">Failed to load</div>';
    }
}

function renderBadges(playerId) {
    const data = state.badges[playerId];
    if (!data) return;
    const { player, shelf } = data;
    document.getElementById('badges-player-name').textContent = player.displayName || player.name;
    if (!shelf || !shelf.length) {
        document.getElementById('badges-shelf').innerHTML = '<div style="color:var(--ink-soft);text-align:center;padding:32px;">No badges data.</div>';
        return;
    }

    const html = '<div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">' +
        shelf.map(cat => {
            const iconPath = BADGE_ICONS[cat.category] || '';
            const label = BADGE_LABELS[cat.category] || cat.category;
            const earnedSlot = cat.slots.find(s => s.earned);
            const tier = earnedSlot?.tier;
            const tc = tier ? TIER_COLORS[tier] : null;
            const earnedAny = !!earnedSlot;
            const nextSlot = cat.slots.find(s => !s.earned);
            const req = nextSlot ? (BADGE_REQUIREMENTS[cat.category]?.[nextSlot.tier] || '') : '';

            return '<div style="background:var(--surface);border:1.5px solid ' + (earnedAny ? 'var(--ink)' : 'var(--rule)') + ';border-radius:10px;padding:12px;opacity:' + (earnedAny ? '1' : '0.55') + ';position:relative;">' +
                '<div style="width:44px;height:44px;border-radius:50%;background:' + (earnedAny ? 'var(--bg-deep)' : 'transparent') + ';border:1.5px solid ' + (earnedAny ? 'var(--rule)' : 'rgba(58,40,24,.4)') + ';display:flex;align-items:center;justify-content:center;margin-bottom:8px;color:' + (earnedAny ? 'var(--ink)' : 'var(--ink-faint)') + ';">' +
                    '<svg width="22" height="22" viewBox="0 0 24 24" fill="none">' + iconPath + '</svg>' +
                '</div>' +
                '<div style="font-family:\'Fraunces\',serif;font-size:14px;font-weight:700;color:var(--ink);line-height:1.1;">' + label + '</div>' +
                (earnedAny && tier
                    ? '<div style="position:absolute;top:10px;right:10px;display:flex;align-items:center;gap:3px;padding:2px 7px;border-radius:999px;background:' + tc.bg + ';border:1px solid ' + tc.border + ';font-family:\'DM Mono\',monospace;font-size:9px;letter-spacing:1px;color:' + tc.color + ';text-transform:uppercase;font-weight:600;">' + TIER_LABELS[tier] + '</div>'
                    : '<div style="position:absolute;top:10px;right:10px;font-family:\'DM Mono\',monospace;font-size:9px;letter-spacing:1px;color:var(--ink-faint);text-transform:uppercase;">Locked</div>') +
                (req ? '<div style="font-size:10px;color:var(--ink-soft);margin-top:4px;line-height:1.3;">' + req + '</div>' : '') +
            '</div>';
        }).join('') +
    '</div>';

    document.getElementById('badges-shelf').innerHTML = html;
}

// ================================================================
//  HALL OF FAME
// ================================================================
async function loadHallOfFame() {
    showScreen('hall-of-fame');
    document.getElementById('hof-list').innerHTML = '<div style="color:var(--ink-soft);text-align:center;padding:24px;"><span class="spinner" style="border-top-color:var(--accent);"></span></div>';
    try {
        const data = await api('GET', '/api/hall-of-fame');
        state.hallOfFame = data.seasons;
        renderHallOfFame();
    } catch(e) {
        document.getElementById('hof-list').innerHTML = '<div style="color:var(--lose);text-align:center;padding:16px;">Failed to load</div>';
    }
}

function renderHallOfFame() {
    const seasons = state.hallOfFame || [];
    if (!seasons.length) {
        document.getElementById('hof-list').innerHTML = '<div style="padding:48px 24px;text-align:center;color:var(--ink-soft);">No completed seasons yet.</div>';
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
                    '<div style="font-family:\'Fraunces\',serif;font-size:17px;font-weight:700;color:var(--ink);">' + (s.leagueName || 'Season') + '</div>' +
                    '<div style="font-family:\'DM Mono\',monospace;font-size:11px;color:var(--ink-soft);margin-top:2px;">' + s.totalRounds + ' rounds · ' + closedAt + '</div>' +
                '</div>' +
                '<div style="text-align:right;">' +
                    '<div style="font-family:\'DM Mono\',monospace;font-size:10px;letter-spacing:1.2px;text-transform:uppercase;color:var(--ink-soft);">Jackpot</div>' +
                    '<div style="font-family:\'Fraunces\',serif;font-size:18px;font-weight:700;color:var(--gold);">' + s.totalJackpot + ' KM</div>' +
                '</div>' +
            '</div>' +
            '<div style="display:flex;flex-direction:column;gap:8px;border-top:1px solid rgba(58,40,24,.2);padding-top:12px;">' +
                '<div style="display:flex;justify-content:space-between;"><div style="font-size:13px;color:var(--ink-soft);">Jackpot Winner</div><div style="font-size:13px;font-weight:600;color:var(--ink);">' + jackpotWinner + '</div></div>' +
                '<div style="display:flex;justify-content:space-between;"><div style="font-size:13px;color:var(--ink-soft);">Leaderboard Champion</div><div style="font-size:13px;font-weight:600;color:var(--ink);">' + lbWinner + '</div></div>' +
                '<div style="display:flex;justify-content:space-between;"><div style="font-size:13px;color:var(--ink-soft);">Player of the Season</div><div style="font-size:13px;font-weight:600;color:var(--ink);">' + posSeason + '</div></div>' +
            '</div>' +
        '</div>';
    }).join('');
}

// ================================================================
//  SETTLEMENT
// ================================================================
async function loadSettlements() {
    showScreen('admin-settlement');
    document.getElementById('settlement-content').innerHTML = '<div style="color:var(--ink-soft);text-align:center;padding:24px;"><span class="spinner" style="border-top-color:var(--accent);"></span></div>';
    try {
        const data = await api('GET', '/api/admin/season/settlements');
        state.settlements = data;
        renderSettlements();
    } catch(e) {
        document.getElementById('settlement-content').innerHTML = '<div style="color:var(--lose);text-align:center;padding:16px;">' + (e.message || 'Failed to load') + '</div>';
    }
}

function renderSettlements() {
    const s = state.settlements;
    if (!s) return;
    const total = (s.unsettled || []).length + (s.settled || []).length;
    const settledCount = (s.settled || []).length;
    document.getElementById('settlement-progress').textContent = settledCount + ' of ' + total + ' settled';
    const closeBtn = document.getElementById('settlement-close-btn');
    const allDone = s.unsettled.length === 0;
    if (closeBtn) { closeBtn.disabled = !allDone; closeBtn.style.opacity = allDone ? '1' : '0.5'; }

    const unsettledHtml = s.unsettled.length
        ? '<div style="font-family:\'DM Mono\',monospace;font-size:10px;letter-spacing:1.4px;text-transform:uppercase;color:var(--ink-soft);padding:14px 16px 8px;">Unsettled</div>' +
          s.unsettled.map(p => '<div style="display:flex;align-items:center;padding:12px 16px;border-bottom:1px solid rgba(58,40,24,.15);gap:12px;">' +
              '<div style="flex:1;"><div style="font-size:15px;font-weight:600;color:var(--ink);">' + p.displayName + '</div>' +
              '<div style="font-family:\'DM Mono\',monospace;font-size:12px;color:' + (p.tokenBalance < 0 ? 'var(--lose)' : 'var(--green)') + ';margin-top:2px;">' + p.tokenBalance + ' KM</div></div>' +
              '<button class="btn btn-secondary btn-sm" onclick="settlePlayer(' + p.playerId + ')">Mark Settled</button>' +
          '</div>').join('')
        : '';

    const settledHtml = s.settled.length
        ? '<div style="font-family:\'DM Mono\',monospace;font-size:10px;letter-spacing:1.4px;text-transform:uppercase;color:var(--ink-soft);padding:16px 16px 8px;">Settled</div>' +
          s.settled.map(p => '<div style="display:flex;align-items:center;padding:12px 16px;border-bottom:1px solid rgba(58,40,24,.15);">' +
              '<div style="flex:1;"><div style="font-size:15px;font-weight:600;color:var(--ink);">' + p.displayName + '</div>' +
              '<div style="font-family:\'DM Mono\',monospace;font-size:11px;color:var(--ink-soft);margin-top:2px;">' + (p.settledAt ? new Date(p.settledAt).toLocaleDateString() : '') + '</div></div>' +
              '<div style="font-family:\'Fraunces\',serif;font-size:15px;font-weight:700;color:' + (p.settledAmount >= 0 ? 'var(--win)' : 'var(--lose)') + ';">' + p.settledAmount + '</div>' +
          '</div>').join('')
        : '';

    document.getElementById('settlement-content').innerHTML = unsettledHtml + settledHtml;
}

async function settlePlayer(playerId) {
    try {
        await api('POST', '/api/admin/season/settlements/' + playerId);
        toast('Player settled ✓');
        await refreshState();
        const data = await api('GET', '/api/admin/season/settlements');
        state.settlements = data;
        renderSettlements();
    } catch(e) { toast(e.message || 'Settlement failed', 'error'); }
}

async function closeSeasonFinal() {
    if (!confirm('Close the season? This is final and cannot be undone.')) return;
    const btn = document.getElementById('settlement-close-btn');
    btn.disabled = true; btn.innerHTML = '<span class="spinner"></span>';
    try {
        await api('POST', '/api/admin/season/close');
        toast('Season closed ✓');
        await refreshState();
        showScreen('admin');
        renderAdmin();
    } catch(e) { toast(e.message || 'Failed to close season', 'error'); btn.disabled = false; btn.textContent = 'Close Season'; }
}

// ================================================================
//  CREDIT TOKENS
// ================================================================
async function submitCreditTokens() {
    const playerId = document.getElementById('credit-player-id').value;
    const amount = parseInt(document.getElementById('credit-amount').value) || 0;
    const description = document.getElementById('credit-description').value.trim();
    if (!playerId) { toast('Select a player', 'error'); return; }
    if (amount < 1) { toast('Amount must be at least 1', 'error'); return; }
    const btn = document.getElementById('credit-submit-btn');
    btn.disabled = true; btn.innerHTML = '<span class="spinner"></span>';
    try {
        const data = await api('POST', '/api/admin/players/' + playerId + '/credit', { amount, description: description || null });
        const idx = state.balances.findIndex(p => p.id === data.player.id);
        if (idx !== -1) state.balances[idx].tokenBalance = data.player.tokenBalance;
        document.getElementById('credit-result').innerHTML =
            '<div class="card" style="background:rgba(46,93,58,.12);border-color:rgba(46,93,58,.4);margin-top:14px;text-align:center;">' +
            '<div style="font-family:\'Fraunces\',serif;font-size:16px;font-weight:700;color:var(--green);">+' + amount + ' KM credited</div>' +
            '<div style="font-size:13px;color:var(--ink-soft);margin-top:4px;">to ' + (data.player.nickname || data.player.name) + ' · New balance: ' + data.player.tokenBalance + '</div>' +
            '</div>';
        document.getElementById('credit-amount').value = '';
        document.getElementById('credit-description').value = '';
        await refreshState();
    } catch(e) { toast(e.message || 'Credit failed', 'error'); }
    finally { btn.disabled = false; btn.textContent = 'Credit Tokens'; }
}

function renderCreditForm() {
    const players = state.adminPlayers && state.adminPlayers.length ? state.adminPlayers : state.balances;
    const options = (players || []).map(p => '<option value="' + p.id + '">' + (p.nickname || p.name || p.displayName) + ' (' + p.tokenBalance + ' KM)</option>').join('');
    const sel = document.getElementById('credit-player-id');
    if (sel) sel.innerHTML = '<option value="">— Select player —</option>' + options;
    document.getElementById('credit-result').innerHTML = '';
}

// ================================================================
//  BOOT
// ================================================================
window.addEventListener('DOMContentLoaded', init);

if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => { navigator.serviceWorker.register('/sw.js').catch(() => {}); });
}
</script>
</body>
