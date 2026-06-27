<?php
// includes/styles.php
// Shared CSS and navigation helper for all pages.
//
// Usage:
//   In <head>: <?php renderStyles(); ?>
//   In <body>: <?php renderNav('dashboard'); ?>
//   Active key: 'dashboard' | 'register' | 'orders' | 'reports'

function renderStyles(): void {
    echo '<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
/* ── Reset & base ───────────────────────────────────────── */
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
body {
    font-family: "Outfit", "Inter", ui-sans-serif, system-ui, sans-serif;
    background: #f1f5f9; color: #1e293b; font-size: 15.5px; line-height: 1.65; min-height: 100vh;
}
a { color: #2563eb; text-decoration: none; transition: color .15s; }
a:hover { color: #1d4ed8; text-decoration: underline; }
small { font-size: .8em; font-weight: 400; color: #94a3b8; }
code  { font-family: ui-monospace, monospace; font-size: .85em; background: #f1f5f9;
        padding: 2px 6px; border-radius: 4px; color: #475569; }

/* ── Navigation ─────────────────────────────────────────── */
.nav { background: #0f172a; display: flex; align-items: stretch; padding: 0 32px;
       box-shadow: 0 1px 0 rgba(255,255,255,.06), 0 4px 12px rgba(0,0,0,.25); min-height: 52px; }
.nav-brand { color: #f8fafc; font-weight: 800; font-size: .95em; letter-spacing: -.01em;
             display: flex; align-items: center; padding-right: 24px; margin-right: 4px;
             border-right: 1px solid #1e293b; white-space: nowrap; flex-shrink: 0; }
.nav-links { display: flex; }
.nav-links a { color: #64748b; text-decoration: none; padding: 0 16px; font-size: .86em;
               font-weight: 500; display: flex; align-items: center; letter-spacing: .01em;
               border-bottom: 2px solid transparent; transition: color .15s, border-color .15s; }
.nav-links a:hover { color: #cbd5e1; text-decoration: none; }
.nav-links a.active { color: #60a5fa; border-bottom-color: #3b82f6; font-weight: 600; }

/* ── Layout ─────────────────────────────────────────────── */
.page { max-width: 1100px; margin: 0 auto; padding: 40px 32px; }
.page.narrow { max-width: 680px; }

/* ── Page header ────────────────────────────────────────── */
.page-header { margin-bottom: 32px; }
.page-header h1 { font-size: 1.6rem; font-weight: 800; color: #0f172a;
                  letter-spacing: -.02em; margin: 10px 0 5px; }
.page-header p  { color: #64748b; font-size: .875em; font-weight: 400; }

/* ── Node badges ────────────────────────────────────────── */
.badges { display: flex; gap: 6px; flex-wrap: wrap; margin-bottom: 2px; }
.badge  { display: inline-flex; align-items: center; gap: 5px; padding: 3px 11px;
          border-radius: 99px; font-size: .72em; font-weight: 700; letter-spacing: .04em; }
.badge::before { content: ""; width: 6px; height: 6px; border-radius: 50%; flex-shrink: 0; }
.badge-a { background: #dbeafe; color: #1d4ed8; } .badge-a::before { background: #3b82f6; }
.badge-b { background: #dcfce7; color: #15803d; } .badge-b::before { background: #22c55e; }
.badge-c { background: #fef3c7; color: #b45309; } .badge-c::before { background: #f59e0b; }

/* ── Cards ──────────────────────────────────────────────── */
.card { background: #fff; border: 1px solid #e2e8f0; border-radius: 14px;
        padding: 26px; box-shadow: 0 1px 2px rgba(0,0,0,.04), 0 4px 16px rgba(0,0,0,.04); }
.card-table { padding: 0; overflow: hidden; }
.grid-3 { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; }
.card-label { font-size: .68em; font-weight: 700; letter-spacing: .12em; text-transform: uppercase;
              color: #94a3b8; margin-bottom: 6px; }
.card-title { font-size: 1em; font-weight: 700; color: #0f172a;
              display: flex; align-items: center; gap: 8px;
              margin-bottom: 18px; padding-bottom: 14px; border-bottom: 1px solid #f1f5f9; }
.status-dot { width: 8px; height: 8px; border-radius: 50%; flex-shrink: 0; }
.dot-ok  { background: #22c55e; box-shadow: 0 0 0 3px rgba(34,197,94,.15); }
.dot-err { background: #ef4444; box-shadow: 0 0 0 3px rgba(239,68,68,.15); }

/* ── Alerts ─────────────────────────────────────────────── */
.alert { padding: 12px 16px; border-radius: 10px; margin-bottom: 18px;
         font-size: .875em; font-weight: 500; border: 1px solid transparent; }
.alert-ok   { background: #f0fdf4; border-color: #bbf7d0; color: #15803d; }
.alert-warn { background: #fffbeb; border-color: #fde68a; color: #92400e; }
.alert-err  { background: #fef2f2; border-color: #fecaca; color: #b91c1c; }

/* ── Tables ─────────────────────────────────────────────── */
table { width: 100%; border-collapse: collapse; font-size: .875em; }
th { padding: 10px 14px; text-align: left; font-size: .7em; font-weight: 700; color: #64748b;
     text-transform: uppercase; letter-spacing: .08em; background: #f8fafc;
     border-bottom: 1px solid #e2e8f0; }
td { padding: 11px 14px; color: #334155; border-bottom: 1px solid #f8fafc; }
tbody tr:last-child td { border-bottom: none; }
tbody tr:hover td { background: #f8fafc; transition: background .1s; }

/* ── Forms ──────────────────────────────────────────────── */
.form-group { margin-bottom: 20px; }
.form-label { display: block; font-size: .84em; font-weight: 600; color: #374151; margin-bottom: 6px; letter-spacing: .01em; }
.form-input { width: 100%; padding: 10px 14px; border: 1.5px solid #e2e8f0; border-radius: 9px;
              font-family: "Outfit", "Inter", sans-serif; font-size: .95em; color: #1e293b;
              background: #fff; outline: none; transition: border-color .15s, box-shadow .15s;
              -webkit-appearance: none; }
.form-input:focus { border-color: #3b82f6; box-shadow: 0 0 0 3px rgba(59,130,246,.1); }
.form-input::placeholder { color: #cbd5e1; }
.btn { display: inline-flex; align-items: center; gap: 6px; padding: 10px 24px; border: none;
       border-radius: 9px; font-family: "Outfit", "Inter", sans-serif;
       font-size: .9em; font-weight: 700; letter-spacing: .01em; cursor: pointer;
       transition: background .15s, box-shadow .15s, transform .1s; margin-top: 4px; }
.btn-primary { background: #1d4ed8; color: #fff; }
.btn-primary:hover { background: #1e40af; box-shadow: 0 4px 12px rgba(29,78,216,.3); transform: translateY(-1px); }
.btn-primary:active { transform: translateY(0); }

/* ── Section title ──────────────────────────────────────── */
.section-title { font-size: 1rem; font-weight: 700; color: #0f172a; letter-spacing: -.01em;
                 margin: 36px 0 14px; padding-bottom: 10px; border-bottom: 1px solid #e2e8f0; }

/* ── Callout / teaching note ────────────────────────────── */
.callout { margin-top: 36px; padding: 18px 22px; background: #f8fafc;
           border: 1px solid #e2e8f0; border-left: 3px solid #3b82f6;
           border-radius: 0 10px 10px 0; font-size: .83em; color: #475569; line-height: 1.75; }
.callout strong { color: #1e293b; font-weight: 700; }

/* ── Empty state ────────────────────────────────────────── */
.empty { color: #94a3b8; font-size: .875em; padding: 14px 0; }
</style>
';
}

function renderNav(string $active = ''): void {
    $links = [
        'dashboard' => ['index.php',                     'Dashboard'],
        'register'  => ['register.php',                  'Register'],
        'orders'    => ['orders.php',                    'Place Order'],
        'reports'   => ['reports.php',                   'Reports'],
        'setup'     => ['../setup/test_connections.php', 'Test Connections'],
    ];
    $html = '<nav class="nav"><span class="nav-brand">' . APP_NAME . '</span><div class="nav-links">';
    foreach ($links as $key => [$href, $label]) {
        $cls   = ($key === $active) ? ' class="active"' : '';
        $html .= "<a href=\"{$href}\"{$cls}>{$label}</a>";
    }
    echo $html . '</div></nav>';
}
