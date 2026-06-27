<?php
// includes/styles.php
// Shared CSS and navigation helper for all pages.
//
// Usage:
//   In <head>: <?php renderStyles(); ?>
//   In <body>: <?php renderNav('dashboard'); ?>
//   Active key: 'dashboard' | 'register' | 'orders' | 'reports'

function renderStyles(): void {
    echo '<style>
/* ── Reset & base ───────────────────────────────────────── */
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
body {
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
    background: #f1f5f9; color: #1e293b; font-size: 15px; line-height: 1.6; min-height: 100vh;
}
a { color: #2563eb; text-decoration: none; }
a:hover { text-decoration: underline; }
small { font-size: .82em; font-weight: 400; color: #94a3b8; }
code  { font-family: ui-monospace, monospace; font-size: .88em; background: #f1f5f9; padding: 1px 5px; border-radius: 3px; }

/* ── Navigation ─────────────────────────────────────────── */
.nav { background: #0f172a; display: flex; align-items: stretch; padding: 0 28px; box-shadow: 0 2px 6px rgba(0,0,0,.3); }
.nav-brand { color: #f1f5f9; font-weight: 700; font-size: .9em; letter-spacing: .03em;
             display: flex; align-items: center; padding-right: 20px; margin-right: 4px;
             border-right: 1px solid #1e293b; white-space: nowrap; flex-shrink: 0; }
.nav-links { display: flex; }
.nav-links a { color: #94a3b8; text-decoration: none; padding: 0 15px; font-size: .84em;
               font-weight: 500; display: flex; align-items: center;
               border-bottom: 2px solid transparent; transition: color .15s; }
.nav-links a:hover { color: #e2e8f0; text-decoration: none; }
.nav-links a.active { color: #60a5fa; border-bottom-color: #3b82f6; }

/* ── Layout ─────────────────────────────────────────────── */
.page { max-width: 1100px; margin: 0 auto; padding: 36px 28px; }
.page.narrow { max-width: 680px; }

/* ── Page header ────────────────────────────────────────── */
.page-header { margin-bottom: 28px; }
.page-header h1 { font-size: 1.45rem; font-weight: 700; color: #0f172a; margin: 8px 0 4px; }
.page-header p  { color: #64748b; font-size: .875em; }

/* ── Node badges ────────────────────────────────────────── */
.badges { display: flex; gap: 6px; flex-wrap: wrap; }
.badge  { display: inline-flex; align-items: center; gap: 5px; padding: 3px 10px;
          border-radius: 20px; font-size: .72em; font-weight: 700; letter-spacing: .03em; }
.badge::before { content: ""; width: 6px; height: 6px; border-radius: 50%; flex-shrink: 0; }
.badge-a { background: #dbeafe; color: #1d4ed8; } .badge-a::before { background: #3b82f6; }
.badge-b { background: #dcfce7; color: #15803d; } .badge-b::before { background: #22c55e; }
.badge-c { background: #fef3c7; color: #b45309; } .badge-c::before { background: #f59e0b; }

/* ── Cards ──────────────────────────────────────────────── */
.card { background: #fff; border: 1px solid #e2e8f0; border-radius: 10px;
        padding: 24px; box-shadow: 0 1px 3px rgba(0,0,0,.05); }
.card-table { padding: 0; overflow: hidden; }
.grid-3 { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; }
.card-label { font-size: .7em; font-weight: 700; letter-spacing: .1em; text-transform: uppercase;
              color: #94a3b8; margin-bottom: 4px; }
.card-title { font-size: 1em; font-weight: 600; color: #1e293b;
              display: flex; align-items: center; gap: 8px;
              margin-bottom: 16px; padding-bottom: 12px; border-bottom: 1px solid #f1f5f9; }
.status-dot { width: 8px; height: 8px; border-radius: 50%; flex-shrink: 0; }
.dot-ok { background: #22c55e; } .dot-err { background: #ef4444; }

/* ── Alerts ─────────────────────────────────────────────── */
.alert { padding: 12px 16px; border-radius: 8px; margin-bottom: 16px;
         font-size: .875em; border: 1px solid transparent; }
.alert-ok   { background: #f0fdf4; border-color: #bbf7d0; color: #15803d; }
.alert-warn { background: #fffbeb; border-color: #fde68a; color: #92400e; }
.alert-err  { background: #fef2f2; border-color: #fecaca; color: #b91c1c; }

/* ── Tables ─────────────────────────────────────────────── */
table { width: 100%; border-collapse: collapse; font-size: .875em; }
th { padding: 8px 12px; text-align: left; font-size: .74em; font-weight: 700; color: #64748b;
     text-transform: uppercase; letter-spacing: .06em; border-bottom: 2px solid #e2e8f0; }
td { padding: 10px 12px; color: #334155; border-bottom: 1px solid #f1f5f9; }
tbody tr:last-child td { border-bottom: none; }
tbody tr:hover td { background: #f8fafc; }

/* ── Forms ──────────────────────────────────────────────── */
.form-group { margin-bottom: 18px; }
.form-label { display: block; font-size: .84em; font-weight: 600; color: #374151; margin-bottom: 5px; }
.form-input { width: 100%; padding: 9px 12px; border: 1.5px solid #d1d5db; border-radius: 7px;
              font-size: .95em; color: #1e293b; background: #fff; outline: none;
              transition: border-color .15s, box-shadow .15s; -webkit-appearance: none; }
.form-input:focus { border-color: #3b82f6; box-shadow: 0 0 0 3px rgba(59,130,246,.12); }
.btn { display: inline-flex; align-items: center; padding: 10px 22px; border: none; border-radius: 7px;
       font-size: .9em; font-weight: 600; cursor: pointer; transition: background .15s, box-shadow .15s; margin-top: 4px; }
.btn-primary { background: #1d4ed8; color: #fff; }
.btn-primary:hover { background: #1e40af; box-shadow: 0 2px 8px rgba(29,78,216,.35); }

/* ── Section title ──────────────────────────────────────── */
.section-title { font-size: 1rem; font-weight: 600; color: #1e293b;
                 margin: 32px 0 14px; padding-bottom: 8px; border-bottom: 1px solid #e2e8f0; }

/* ── Callout / teaching note ────────────────────────────── */
.callout { margin-top: 32px; padding: 16px 20px; background: #f8fafc;
           border: 1px solid #e2e8f0; border-left: 3px solid #3b82f6;
           border-radius: 0 8px 8px 0; font-size: .82em; color: #475569; line-height: 1.7; }
.callout strong { color: #1e293b; }

/* ── Empty state ────────────────────────────────────────── */
.empty { color: #94a3b8; font-size: .875em; padding: 12px 0; }
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
