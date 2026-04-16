"""
Tipping Pool — Play Store Asset Generator
Generates: icon-512.png, icon-192.png, featured-graphic.png,
           ss-home.png, ss-predict.png, ss-results.png, ss-leaderboard.png,
           tab-home.png, tab-predict.png, tab-results.png, tab-lb.png
Requires: Pillow only
"""

import math
import os
from PIL import Image, ImageDraw, ImageFilter, ImageFont

# ---------------------------------------------------------------------------
# OUTPUT DIRECTORY
# ---------------------------------------------------------------------------
OUT = "D:/ClaudeProjects/bettingApp/my-app/public/icons"
os.makedirs(OUT, exist_ok=True)

# ---------------------------------------------------------------------------
# PALETTE
# ---------------------------------------------------------------------------
BG       = "#0f172a"   # dark navy
CARD     = "#1e293b"   # dark slate
ACCENT   = "#22c55e"   # green
GOLD     = "#f59e0b"   # amber/gold
GOLD2    = "#eab308"   # yellow-gold
TEXT     = "#f1f5f9"   # near-white
MUTED    = "#94a3b8"   # muted blue-grey
RED      = "#ef4444"   # red
SILVER   = "#94a3b8"
BRONZE   = "#d97706"
BORDER   = "#334155"   # subtle card border

def hex2rgb(h, a=255):
    h = h.lstrip("#")
    r, g, b = int(h[0:2],16), int(h[2:4],16), int(h[4:6],16)
    return (r, g, b, a)

def hex3(h):
    r,g,b,_ = hex2rgb(h)
    return (r,g,b)

# ---------------------------------------------------------------------------
# FONT HELPERS — fall back to default if no system font is found
# ---------------------------------------------------------------------------
def get_font(size, bold=False):
    candidates_bold = [
        "C:/Windows/Fonts/arialbd.ttf",
        "C:/Windows/Fonts/calibrib.ttf",
        "C:/Windows/Fonts/segoeui.ttf",
        "/usr/share/fonts/truetype/dejavu/DejaVuSans-Bold.ttf",
        "/usr/share/fonts/truetype/liberation/LiberationSans-Bold.ttf",
    ]
    candidates_regular = [
        "C:/Windows/Fonts/arial.ttf",
        "C:/Windows/Fonts/calibri.ttf",
        "C:/Windows/Fonts/segoeui.ttf",
        "/usr/share/fonts/truetype/dejavu/DejaVuSans.ttf",
        "/usr/share/fonts/truetype/liberation/LiberationSans-Regular.ttf",
    ]
    candidates = candidates_bold if bold else candidates_regular
    for path in candidates:
        if os.path.exists(path):
            try:
                return ImageFont.truetype(path, size)
            except Exception:
                pass
    return ImageFont.load_default()

# ---------------------------------------------------------------------------
# DRAWING HELPERS
# ---------------------------------------------------------------------------

def draw_radial_gradient(img, cx, cy, r_inner, r_outer, col_inner, col_outer):
    """Additive radial glow onto an RGBA image."""
    data = img.load()
    w, h = img.size
    ri2, ro2 = r_inner**2, r_outer**2
    for y in range(h):
        for x in range(w):
            dx, dy = x - cx, y - cy
            d2 = dx*dx + dy*dy
            if d2 < ro2:
                t = max(0.0, 1.0 - (math.sqrt(d2) - r_inner) / max(1, r_outer - r_inner))
                t = t * t  # ease
                r = int(col_inner[0]*t + col_outer[0]*(1-t))
                g = int(col_inner[1]*t + col_outer[1]*(1-t))
                b = int(col_inner[2]*t + col_outer[2]*(1-t))
                a = int(col_inner[3]*t + col_outer[3]*(1-t))
                pr, pg, pb, pa = data[x, y]
                # alpha composite
                fa = a / 255.0
                data[x, y] = (
                    int(r * fa + pr * (1-fa)),
                    int(g * fa + pg * (1-fa)),
                    int(b * fa + pb * (1-fa)),
                    min(255, pa + a),
                )

def make_glow_ring(size, center, radius, color_hex, thickness=18, blur=22):
    """Return an RGBA image with a blurred ring (glow effect)."""
    layer = Image.new("RGBA", (size, size), (0,0,0,0))
    d = ImageDraw.Draw(layer)
    cx, cy = center
    r = radius
    t = thickness // 2
    col = hex2rgb(color_hex, 200)
    d.ellipse([cx-r-t, cy-r-t, cx+r+t, cy+r+t], outline=col, width=thickness)
    return layer.filter(ImageFilter.GaussianBlur(blur))

def draw_soccer_ball(draw, img, cx, cy, radius, shadow=True):
    """
    Draw a 3-D looking soccer ball centred at (cx, cy) with given radius.
    Uses Pillow only — no external images.
    """
    r = radius

    # --- drop shadow ---
    if shadow:
        shadow_layer = Image.new("RGBA", img.size, (0,0,0,0))
        sd = ImageDraw.Draw(shadow_layer)
        sd.ellipse([cx-r+8, cy-r+14, cx+r+8, cy+r+14], fill=(0,0,0,120))
        shadow_layer = shadow_layer.filter(ImageFilter.GaussianBlur(r//4))
        img.alpha_composite(shadow_layer)
        draw = ImageDraw.Draw(img)

    # --- base circle with gradient simulation ---
    # Draw concentric circles from dark (bottom-right) to light (top-left)
    steps = 30
    for i in range(steps, -1, -1):
        t = i / steps
        # light at top-left, dark at bottom-right
        light = (255, 255, 255)
        dark  = (190, 190, 195)
        rc = int(dark[0] + (light[0]-dark[0]) * t)
        gc = int(dark[1] + (light[1]-dark[1]) * t)
        bc = int(dark[2] + (light[2]-dark[2]) * t)
        offset = int((steps - i) * r * 0.012)
        ri = max(1, r - i)
        draw.ellipse([cx - ri + offset, cy - ri + offset,
                      cx + ri + offset, cy + ri + offset],
                     fill=(rc, gc, bc))

    # --- specular highlight (top-left) ---
    spec = Image.new("RGBA", img.size, (0,0,0,0))
    sd2 = ImageDraw.Draw(spec)
    hr = int(r * 0.45)
    sd2.ellipse([cx - r + int(r*0.1), cy - r + int(r*0.05),
                 cx - r + int(r*0.1) + hr, cy - r + int(r*0.05) + hr],
                fill=(255, 255, 255, 90))
    spec = spec.filter(ImageFilter.GaussianBlur(r // 5))
    img.alpha_composite(spec)
    draw = ImageDraw.Draw(img)

    # --- pentagon patches ---
    # Classic soccer ball: 1 center pentagon + 5 surrounding + top cap
    patch_color = (30, 35, 45, 245)

    def pentagon_points(cx_, cy_, r_, angle_offset=0):
        pts = []
        for i in range(5):
            angle = math.radians(angle_offset + i * 72 - 90)
            pts.append((cx_ + r_ * math.cos(angle), cy_ + r_ * math.sin(angle)))
        return pts

    pr = int(r * 0.28)   # pentagon "radius" (circumradius)

    # Center pentagon (slightly top-left shifted for 3-D feel)
    cx_shift = cx - int(r * 0.05)
    cy_shift = cy - int(r * 0.05)
    pts0 = pentagon_points(cx_shift, cy_shift, pr, 0)
    patch_layer = Image.new("RGBA", img.size, (0,0,0,0))
    pd = ImageDraw.Draw(patch_layer)
    pd.polygon(pts0, fill=patch_color)

    # 5 surrounding pentagons
    outer_dist = int(r * 0.58)
    for i in range(5):
        angle = math.radians(i * 72 - 90)
        ocx = cx_shift + int(outer_dist * math.cos(angle))
        ocy = cy_shift + int(outer_dist * math.sin(angle))
        # Rotate each patch so it "faces" center
        rot = i * 72 + 36
        pts = pentagon_points(ocx, ocy, pr, rot)
        # clip to ball circle
        pd.polygon(pts, fill=patch_color)

    # Composite patches
    img.alpha_composite(patch_layer)
    draw = ImageDraw.Draw(img)

    # --- thin white seam outline on each patch (optional, adds realism) ---
    seam_layer = Image.new("RGBA", img.size, (0,0,0,0))
    semd = ImageDraw.Draw(seam_layer)
    seam_col = (255, 255, 255, 60)
    semd.polygon(pts0, outline=seam_col, width=max(1, r//22))
    for i in range(5):
        angle = math.radians(i * 72 - 90)
        ocx = cx_shift + int(outer_dist * math.cos(angle))
        ocy = cy_shift + int(outer_dist * math.sin(angle))
        rot = i * 72 + 36
        pts = pentagon_points(ocx, ocy, pr, rot)
        semd.polygon(pts, outline=seam_col, width=max(1, r//22))
    img.alpha_composite(seam_layer)
    draw = ImageDraw.Draw(img)

    return draw


def rounded_rect_bg(draw, x1, y1, x2, y2, radius, fill, border=None, border_width=2):
    draw.rounded_rectangle([x1, y1, x2, y2], radius=radius, fill=fill,
                           outline=border, width=border_width if border else 0)


def text_shadow(draw, pos, text, font, fill, shadow_color=(0,0,0,100), offset=(2,3)):
    draw.text((pos[0]+offset[0], pos[1]+offset[1]), text, font=font, fill=shadow_color, anchor=pos[2] if len(pos)>2 else None)
    draw.text((pos[0], pos[1]), text, font=font, fill=fill)


# ===========================================================================
# 1. APP ICON  1024 × 1024
# ===========================================================================
def make_icon():
    SIZE = 1024
    img = Image.new("RGBA", (SIZE, SIZE), (0, 0, 0, 0))
    draw = ImageDraw.Draw(img)

    # --- rounded square mask ---
    corner_r = int(SIZE * 0.22)
    # Background radial gradient: slightly lighter in center
    bg_layer = Image.new("RGBA", (SIZE, SIZE), (0,0,0,0))
    bg_d = ImageDraw.Draw(bg_layer)
    # base fill
    bg_d.rounded_rectangle([0,0,SIZE-1,SIZE-1], radius=corner_r,
                            fill=hex2rgb(BG))
    # centre glow
    center_glow = Image.new("RGBA", (SIZE, SIZE), (0,0,0,0))
    draw_radial_gradient(center_glow, SIZE//2, SIZE//2,
                         0, SIZE//2,
                         (40, 55, 90, 120), (0,0,0,0))
    bg_layer.alpha_composite(center_glow)

    # Mask bg to rounded square
    mask = Image.new("L", (SIZE, SIZE), 0)
    md = ImageDraw.Draw(mask)
    md.rounded_rectangle([0,0,SIZE-1,SIZE-1], radius=corner_r, fill=255)
    bg_layer.putalpha(mask)
    img.alpha_composite(bg_layer)
    draw = ImageDraw.Draw(img)

    # --- green glow ring ---
    ball_cx, ball_cy = SIZE//2, SIZE//2 + 20
    ball_r = int(SIZE * 0.30)
    ring_layer = make_glow_ring(SIZE, (ball_cx, ball_cy),
                                ball_r + 28, ACCENT, thickness=24, blur=28)
    img.alpha_composite(ring_layer)
    draw = ImageDraw.Draw(img)

    # A second, softer outer glow
    ring_layer2 = make_glow_ring(SIZE, (ball_cx, ball_cy),
                                 ball_r + 48, ACCENT, thickness=14, blur=40)
    img.alpha_composite(ring_layer2)
    draw = ImageDraw.Draw(img)

    # --- soccer ball ---
    draw = draw_soccer_ball(draw, img, ball_cx, ball_cy, ball_r, shadow=True)

    # --- re-apply rounded square mask (keep corners clean) ---
    final_mask = Image.new("L", (SIZE, SIZE), 0)
    fd = ImageDraw.Draw(final_mask)
    fd.rounded_rectangle([0,0,SIZE-1,SIZE-1], radius=corner_r, fill=255)
    img.putalpha(final_mask)

    # Save 512 and 192
    img512 = img.resize((512, 512), Image.LANCZOS)
    img512.save(os.path.join(OUT, "icon-512.png"))
    img192 = img.resize((192, 192), Image.LANCZOS)
    img192.save(os.path.join(OUT, "icon-192.png"))
    print("  icon-512.png + icon-192.png saved")


# ===========================================================================
# 2. FEATURED GRAPHIC  1024 × 500
# ===========================================================================
def make_featured_graphic():
    W, H = 1024, 500
    img = Image.new("RGBA", (W, H), hex2rgb(BG))
    draw = ImageDraw.Draw(img)

    # Background gradient (left darker, right slightly lighter)
    grad = Image.new("RGBA", (W, H), (0,0,0,0))
    for x in range(W):
        t = x / W
        r = int(15 + 10*t)
        g = int(23 + 8*t)
        b = int(42 + 15*t)
        for y in range(H):
            grad.putpixel((x, y), (r, g, b, 255))
    img.alpha_composite(grad)
    draw = ImageDraw.Draw(img)

    # Subtle grid dots background
    dot_col = (255, 255, 255, 12)
    for gx in range(0, W, 40):
        for gy in range(0, H, 40):
            draw.ellipse([gx-1, gy-1, gx+1, gy+1], fill=dot_col)

    # --- Right side: soccer ball ---
    ball_cx, ball_cy = W - 200, H // 2 - 10
    ball_r = 148

    ring_layer = make_glow_ring(W, (ball_cx, ball_cy),
                                ball_r + 22, ACCENT, thickness=20, blur=24)
    img_temp = Image.new("RGBA", (W, H), (0,0,0,0))
    img_temp.alpha_composite(ring_layer)
    img.alpha_composite(img_temp)
    draw = ImageDraw.Draw(img)

    draw = draw_soccer_ball(draw, img, ball_cx, ball_cy, ball_r, shadow=True)
    draw = ImageDraw.Draw(img)

    # --- Left side: text block ---
    left_margin = 60

    # App name
    font_title = get_font(88, bold=True)
    font_tag   = get_font(38, bold=False)
    font_pill  = get_font(30, bold=True)

    # Draw "Tipping" on line 1, "Pool" on line 2 for better visual weight
    draw.text((left_margin, 90), "Tipping", font=font_title, fill=hex3(TEXT))
    draw.text((left_margin, 185), "Pool", font=font_title, fill=hex3(ACCENT))

    # Tagline
    draw.text((left_margin + 4, 302), "Predict. Win. Repeat.",
              font=font_tag, fill=hex3(ACCENT))

    # Decorative underline under "Pool"
    draw.rectangle([left_margin, 278, left_margin + 160, 282],
                   fill=hex3(GOLD))

    # --- Feature pills at bottom ---
    pills = [("⚽  Predictions", ACCENT), ("🏆  Jackpot", GOLD), ("📊  Leaderboard", "#3b82f6")]
    pill_y1 = H - 90
    pill_y2 = H - 44
    px = left_margin
    for label, color in pills:
        col = hex2rgb(color, 30)
        border_col = hex2rgb(color, 180)
        # measure text
        try:
            bbox = draw.textbbox((0,0), label, font=font_pill)
            tw = bbox[2] - bbox[0]
        except Exception:
            tw = len(label) * 18
        pw = tw + 44
        draw.rounded_rectangle([px, pill_y1, px+pw, pill_y2],
                               radius=20,
                               fill=col,
                               outline=border_col, width=2)
        draw.text((px + pw//2, (pill_y1+pill_y2)//2), label,
                  font=font_pill, fill=hex3(color), anchor="mm")
        px += pw + 20

    # Left accent bar
    draw.rectangle([0, 0, 5, H], fill=hex3(ACCENT))

    img.save(os.path.join(OUT, "featured-graphic.png"))
    print("  featured-graphic.png saved")


# ===========================================================================
# SHARED SCREENSHOT COMPONENTS
# ===========================================================================
SW, SH = 1080, 1920   # phone

def new_phone_canvas():
    img = Image.new("RGBA", (SW, SH), hex2rgb(BG))
    draw = ImageDraw.Draw(img)
    return img, draw

def draw_status_bar(draw, img):
    """Draw a dark status bar at top with app name."""
    draw.rectangle([0, 0, SW, 80], fill=hex2rgb(CARD))
    draw.rectangle([0, 78, SW, 80], fill=hex2rgb(BORDER))
    f = get_font(32, bold=True)
    draw.text((SW//2, 40), "Tipping Pool", font=f, fill=hex3(TEXT), anchor="mm")
    # Time on left
    ft = get_font(26)
    draw.text((44, 40), "20:14", font=ft, fill=hex3(MUTED), anchor="lm")
    # Battery/signal on right (simple shapes)
    bx = SW - 44
    draw.rounded_rectangle([bx-42, 28, bx-4, 52], radius=4,
                           fill=hex2rgb(ACCENT), outline=hex2rgb(TEXT,120), width=1)
    draw.text((bx + 2, 40), "●●●", font=ft, fill=hex3(MUTED), anchor="rm")

def draw_section_header(draw, y, title, subtitle=None):
    f = get_font(42, bold=True)
    fs = get_font(30)
    draw.text((54, y), title, font=f, fill=hex3(TEXT))
    if subtitle:
        draw.text((54, y+52), subtitle, font=fs, fill=hex3(MUTED))

def card(draw, x1, y1, x2, y2, radius=20, fill=CARD, border=BORDER):
    draw.rounded_rectangle([x1, y1, x2, y2], radius=radius,
                           fill=hex2rgb(fill),
                           outline=hex2rgb(border), width=2)

def pill(draw, cx, cy, label, font, bg_hex, text_hex, border_hex=None, pw_extra=32):
    try:
        bbox = draw.textbbox((0,0), label, font=font)
        tw = bbox[2] - bbox[0]
        th = bbox[3] - bbox[1]
    except Exception:
        tw = len(label)*14
        th = 20
    ph = th + 18
    pw = tw + pw_extra
    x1, y1 = cx - pw//2, cy - ph//2
    draw.rounded_rectangle([x1, y1, x1+pw, y1+ph], radius=ph//2,
                           fill=hex2rgb(bg_hex, 50),
                           outline=hex2rgb(border_hex or bg_hex, 200), width=2)
    draw.text((cx, cy), label, font=font, fill=hex3(text_hex), anchor="mm")


# ===========================================================================
# 3a. SCREENSHOT — HOME
# ===========================================================================
def make_ss_home():
    img, draw = new_phone_canvas()

    # Status bar
    draw_status_bar(draw, img)
    y = 100

    # APP HEADER
    fh = get_font(52, bold=True)
    fm = get_font(34)
    fs = get_font(28)
    draw.text((54, y + 40), "Tipping Pool", font=fh, fill=hex3(TEXT))
    draw.text((SW - 54, y + 44), "GW 14", font=fm, fill=hex3(MUTED), anchor="rm")
    y += 110

    # JACKPOT BANNER (gold)
    card(draw, 30, y, SW-30, y+110, radius=22, fill=GOLD, border=GOLD2)
    # radial glow on banner
    glow = Image.new("RGBA", (SW, SH), (0,0,0,0))
    gd = ImageDraw.Draw(glow)
    gd.rounded_rectangle([30, y, SW-30, y+110], radius=22, fill=(0,0,0,0))
    glow = glow.filter(ImageFilter.GaussianBlur(0))
    # just draw the text
    fj = get_font(48, bold=True)
    fj2 = get_font(30)
    draw.text((SW//2, y+38), "🏆  JACKPOT", font=fj, fill=(20,20,20), anchor="mm")
    draw.text((SW//2, y+82), "47 tokens · Rolls over if no perfect prediction",
              font=fj2, fill=(40,40,40), anchor="mm")
    y += 130

    # ROUND CARD
    card(draw, 30, y, SW-30, y+160, radius=20)
    fw = get_font(40, bold=True)
    fd = get_font(30)
    draw.text((54, y+30), "Gameweek 14", font=fw, fill=hex3(TEXT))
    draw.text((54, y+80), "5 matches  ·  Locks in 2h 30m", font=fd, fill=hex3(MUTED))
    # Lock icon
    draw.text((54, y+122), "🔒  Predictions lock when first match kicks off",
              font=get_font(26), fill=hex3(ACCENT))
    # Status pill
    pill(draw, SW-140, y+60, "● OPEN", get_font(28, bold=True), ACCENT, ACCENT)
    y += 184

    # MY PREDICTION STATUS
    card(draw, 30, y, SW-30, y+100, radius=20, fill="#1a2942", border=ACCENT)
    draw.text((54, y+28), "Your Predictions", font=get_font(34, bold=True), fill=hex3(TEXT))
    draw.text((54, y+70), "✅  Submitted — 5/5 matches predicted", font=get_font(28), fill=hex3(ACCENT))
    y += 124

    # LEADERBOARD SECTION
    draw.text((54, y+10), "Season Leaderboard", font=get_font(40, bold=True), fill=hex3(TEXT))
    draw.text((SW-54, y+18), "View all →", font=get_font(28), fill=hex3(ACCENT), anchor="rm")
    y += 66

    # Player rows — 5 players
    players = [
        ("1", "Mirsad",   "🔥 On Fire",  "94 pts", GOLD,   "🥇"),
        ("2", "Damir",    "⚡ 4 streak", "87 pts", SILVER, "🥈"),
        ("3", "Emir",     "🔥 On Fire",  "81 pts", BRONZE, "🥉"),
        ("4", "Sanel",    "🧊 Cold",     "74 pts", MUTED,  " 4"),
        ("5", "Adnan",    "",            "68 pts", MUTED,  " 5"),
    ]
    for rank, name, streak, pts, rank_col, rank_icon in players:
        card(draw, 30, y, SW-30, y+90, radius=16)
        # Rank circle
        draw.ellipse([52, y+18, 96, y+72], fill=hex2rgb(rank_col, 40),
                     outline=hex2rgb(rank_col, 200), width=2)
        draw.text((74, y+45), rank, font=get_font(28, bold=True),
                  fill=hex3(rank_col), anchor="mm")
        # Name
        draw.text((118, y+28), name, font=get_font(36, bold=True), fill=hex3(TEXT))
        # Streak
        if streak:
            draw.text((118, y+66), streak, font=get_font(26), fill=hex3(MUTED))
        # Points
        draw.text((SW-54, y+45), pts, font=get_font(36, bold=True),
                  fill=hex3(TEXT), anchor="rm")
        y += 106

    img.save(os.path.join(OUT, "ss-home.png"))
    print("  ss-home.png saved")
    return img


# ===========================================================================
# 3b. SCREENSHOT — PREDICTIONS
# ===========================================================================
def make_ss_predict():
    img, draw = new_phone_canvas()
    draw_status_bar(draw, img)
    y = 100

    # Header
    fh = get_font(52, bold=True)
    draw.text((SW//2, y+44), "Gameweek 14", font=fh, fill=hex3(TEXT), anchor="mm")
    draw.text((SW//2, y+96), "Pick the result for each match",
              font=get_font(30), fill=hex3(MUTED), anchor="mm")
    y += 136

    # Deadline bar
    card(draw, 30, y, SW-30, y+64, radius=14, fill="#1a2030", border=GOLD)
    draw.text((SW//2, y+32), "🔒  Locks in 2h 30m · 5/5 predicted",
              font=get_font(30, bold=True), fill=hex3(GOLD), anchor="mm")
    y += 84

    # Match cards
    matches = [
        ("Manchester City", "Arsenal",        "1",  ["1","X","2"]),
        ("Liverpool",       "Chelsea",         "X",  ["1","X","2"]),
        ("Real Madrid",     "Barcelona",       "2",  ["1","X","2"]),
        ("PSG",             "Bayern Munich",   "1",  ["1","X","2"]),
    ]
    fn = get_font(32, bold=True)
    fb = get_font(36, bold=True)
    fl = get_font(26)

    for home, away, selected, opts in matches:
        card(draw, 30, y, SW-30, y+182, radius=20)

        # Time / league label
        draw.text((SW//2, y+26), "Premier League · 21:00",
                  font=fl, fill=hex3(MUTED), anchor="mm")

        # Teams
        draw.text((54, y+74), home, font=fn, fill=hex3(TEXT))
        draw.text((SW//2, y+74), "vs", font=get_font(28), fill=hex3(MUTED), anchor="mm")
        draw.text((SW-54, y+74), away, font=fn, fill=hex3(TEXT), anchor="rm")

        # Buttons: 1, X, 2
        btn_w = (SW - 60 - 30 - 30) // 3  # total width minus margins and gaps
        bx = 30
        gap = 15
        total_btns = 3
        btn_total = (SW - 60) - gap*(total_btns-1)
        bw = btn_total // total_btns
        for i, opt in enumerate(opts):
            bx1 = 30 + i*(bw + gap)
            bx2 = bx1 + bw
            by1 = y + 108
            by2 = y + 162
            if opt == selected:
                draw.rounded_rectangle([bx1, by1, bx2, by2], radius=12,
                                      fill=hex2rgb(ACCENT),
                                      outline=hex2rgb(ACCENT), width=2)
                draw.text(((bx1+bx2)//2, (by1+by2)//2), opt,
                          font=fb, fill=(10,20,10), anchor="mm")
            else:
                draw.rounded_rectangle([bx1, by1, bx2, by2], radius=12,
                                      fill=hex2rgb(CARD),
                                      outline=hex2rgb(BORDER), width=2)
                draw.text(((bx1+bx2)//2, (by1+by2)//2), opt,
                          font=fb, fill=hex3(MUTED), anchor="mm")
        y += 202

    # Submit button
    y += 20
    draw.rounded_rectangle([30, y, SW-30, y+100], radius=22,
                           fill=hex2rgb(ACCENT))
    draw.text((SW//2, y+50), "Submit Predictions →",
              font=get_font(40, bold=True), fill=(10,20,10), anchor="mm")

    img.save(os.path.join(OUT, "ss-predict.png"))
    print("  ss-predict.png saved")
    return img


# ===========================================================================
# 3c. SCREENSHOT — ROUND RESULTS
# ===========================================================================
def make_ss_results():
    img, draw = new_phone_canvas()
    draw_status_bar(draw, img)
    y = 100

    # Header
    draw.text((54, y+40), "Round Results", font=get_font(52, bold=True), fill=hex3(TEXT))
    draw.text((54, y+96), "Gameweek 13  ·  Completed", font=get_font(30), fill=hex3(MUTED))
    y += 140

    # Result banner — jackpot rolls over
    card(draw, 30, y, SW-30, y+120, radius=20, fill="#1a1a2e", border="#3b3b6e")
    draw.text((SW//2, y+34), "🎲  No Perfect Prediction", font=get_font(38, bold=True),
              fill=hex3(TEXT), anchor="mm")
    draw.text((SW//2, y+80), "Jackpot rolls over → now 47 tokens",
              font=get_font(30), fill=hex3(GOLD), anchor="mm")
    y += 144

    # Score summary pills
    draw.text((54, y+10), "Player Scores", font=get_font(38, bold=True), fill=hex3(TEXT))
    y += 60

    # Player result rows
    players_res = [
        ("Mirsad",   8, "8/8 — Best of the round!",    ACCENT),
        ("Damir",    7, "7/8 — Great round",            ACCENT),
        ("Emir",     6, "6/8 — Solid",                  ACCENT),
        ("Kemal",    5, "5/8 — Not bad",                GOLD),
        ("Sanel",    4, "4/8 — Average",                GOLD),
        ("Adnan",    3, "3/8 — Tough round",            RED),
        ("Belma",    2, "2/8 — Better luck next time",  RED),
        ("Fikret",   6, "6/8 — Solid",                  ACCENT),
    ]
    fn = get_font(34, bold=True)
    fd = get_font(26)
    for name, score, desc, col in players_res:
        card(draw, 30, y, SW-30, y+96, radius=16)
        # Score bar (left accent strip)
        bar_h = 96 - 4
        draw.rounded_rectangle([30, y+2, 38, y+bar_h], radius=4, fill=hex2rgb(col))
        # Name + desc
        draw.text((62, y+24), name, font=fn, fill=hex3(TEXT))
        draw.text((62, y+62), desc, font=fd, fill=hex3(MUTED))
        # Score badge
        badge_col = col
        draw.rounded_rectangle([SW-120, y+22, SW-46, y+74], radius=14,
                               fill=hex2rgb(badge_col, 40),
                               outline=hex2rgb(badge_col, 200), width=2)
        draw.text((SW-83, y+48), f"{score}", font=get_font(36, bold=True),
                  fill=hex3(badge_col), anchor="mm")
        draw.text((SW-54, y+48), "pts", font=get_font(24), fill=hex3(MUTED), anchor="lm")
        y += 112

    img.save(os.path.join(OUT, "ss-results.png"))
    print("  ss-results.png saved")
    return img


# ===========================================================================
# 3d. SCREENSHOT — LEADERBOARD
# ===========================================================================
def make_ss_leaderboard():
    img, draw = new_phone_canvas()
    draw_status_bar(draw, img)
    y = 100

    # Header
    draw.text((54, y+40), "Leaderboard", font=get_font(52, bold=True), fill=hex3(TEXT))
    draw.text((54, y+96), "Season 2025/26  ·  Gameweek 14",
              font=get_font(30), fill=hex3(MUTED))
    y += 140

    # Leader card — highlighted gold
    card(draw, 30, y, SW-30, y+150, radius=22, fill="#2a1e00", border=GOLD)
    # Gold crown
    draw.text((62, y+50), "👑", font=get_font(52), fill=hex3(GOLD))
    draw.text((136, y+32), "Mirsad", font=get_font(48, bold=True), fill=hex3(GOLD))
    draw.text((136, y+86), "🔥 On Fire  ·  4-round streak",
              font=get_font(28), fill=hex3(MUTED))
    draw.text((SW-54, y+50), "94", font=get_font(64, bold=True),
              fill=hex3(GOLD), anchor="rm")
    draw.text((SW-54, y+114), "pts", font=get_font(28), fill=hex3(MUTED), anchor="rm")
    y += 174

    # Rest of the board
    lb_players = [
        ("2", "Damir",   "⚡ 3 streak",  "87", SILVER),
        ("3", "Emir",    "🔥 On Fire",   "81", BRONZE),
        ("4", "Fikret",  "📈 Improving", "76", MUTED),
        ("5", "Sanel",   "🧊 Cold Run",  "74", MUTED),
        ("6", "Kemal",   "⚡ 2 streak",  "69", MUTED),
        ("7", "Adnan",   "",             "68", MUTED),
        ("8", "Belma",   "🔥 On Fire",   "65", MUTED),
        ("9", "Tarik",   "",             "60", MUTED),
    ]
    fn = get_font(36, bold=True)
    fs = get_font(26)
    fp = get_font(38, bold=True)

    rank_colors = {
        "2": SILVER,
        "3": BRONZE,
    }

    for rank, name, streak, pts, col in lb_players:
        card(draw, 30, y, SW-30, y+96, radius=16)
        rc = rank_colors.get(rank, MUTED)
        # Rank circle
        draw.ellipse([50, y+16, 100, y+80], fill=hex2rgb(rc, 35),
                     outline=hex2rgb(rc, 200), width=2)
        draw.text((75, y+48), rank, font=get_font(30, bold=True),
                  fill=hex3(rc), anchor="mm")
        # Name
        draw.text((122, y+24), name, font=fn, fill=hex3(TEXT))
        # Streak
        if streak:
            draw.text((122, y+64), streak, font=fs, fill=hex3(MUTED))
        # Points
        draw.text((SW-54, y+48), f"{pts} pts", font=fp,
                  fill=hex3(TEXT), anchor="rm")
        y += 112

    img.save(os.path.join(OUT, "ss-leaderboard.png"))
    print("  ss-leaderboard.png saved")
    return img


# ===========================================================================
# 4. TABLET VERSIONS  1920 × 1080  (phone screenshot centred on dark bg)
# ===========================================================================
def make_tablet(phone_img, filename):
    TW, TH = 1920, 1080
    tab = Image.new("RGBA", (TW, TH), hex2rgb(BG))
    draw = ImageDraw.Draw(tab)

    # subtle background pattern
    for gx in range(0, TW, 60):
        for gy in range(0, TH, 60):
            draw.ellipse([gx-1, gy-1, gx+1, gy+1], fill=(255,255,255,8))

    # Scale phone screenshot to fit height
    scale = TH / phone_img.height
    new_w = int(phone_img.width * scale)
    phone_scaled = phone_img.resize((new_w, TH), Image.LANCZOS)

    # Paste centred
    ox = (TW - new_w) // 2
    tab.alpha_composite(phone_scaled, (ox, 0))

    # Side panels — decorative gradient overlay
    for x in range(ox):
        t = x / ox if ox > 0 else 1
        a = int(180 * (1-t))
        for y in range(TH):
            tab.putpixel((x, y), (*hex3(BG), 255))
        # Already set to BG above; skip per-pixel for speed — just blank

    tab.save(os.path.join(OUT, filename))
    print(f"  {filename} saved")


# ===========================================================================
# MAIN
# ===========================================================================
if __name__ == "__main__":
    print("Generating Tipping Pool Play Store assets...")
    print()

    print("[1/9] App icon...")
    make_icon()

    print("[2/9] Featured graphic...")
    make_featured_graphic()

    print("[3/9] Screenshot: Home...")
    ss_home = make_ss_home()

    print("[4/9] Screenshot: Predictions...")
    ss_predict = make_ss_predict()

    print("[5/9] Screenshot: Results...")
    ss_results = make_ss_results()

    print("[6/9] Screenshot: Leaderboard...")
    ss_lb = make_ss_leaderboard()

    print("[7/9] Tablet: Home...")
    make_tablet(ss_home, "tab-home.png")

    print("[8/9] Tablet: Predict...")
    make_tablet(ss_predict, "tab-predict.png")

    print("[9/9] Tablet: Results + Leaderboard...")
    make_tablet(ss_results, "tab-results.png")
    make_tablet(ss_lb, "tab-lb.png")

    print()
    print("All assets saved to:", OUT)
