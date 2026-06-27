# Folder Structure — Distributed DB Demo

```
distributed-db-demo/
│
├── README.md                        Main student-facing setup guide (start here)
├── config.example.php               Template — copy to config.php and fill in your IPs
├── config.php                       DB credentials + app settings (gitignored)
├── .gitignore
│
├── pages/                           Browseable PHP pages (open in browser)
│   ├── index.php                    Dashboard — live data from all 3 nodes
│   ├── register.php                 Register a student → writes to Node A (MariaDB)
│   ├── orders.php                   Place an order → verify A → insert B → sync C
│   └── reports.php                  Order summary report → reads from Node C (PostgreSQL)
│
├── includes/                        Shared PHP helpers (required by pages)
│   ├── db_mariadb.php               getMariaDBConnection(): PDO
│   ├── db_mysql.php                 getMySQLConnection(): PDO
│   ├── db_postgres.php              getPostgresConnection(): PDO
│   └── styles.php                   Shared CSS + renderNav()
│
├── db/                              SQL files — run once on each node machine
│   │
│   │   ── Schema files (run first) ──────────────────────────
│   ├── node_a_mariadb.sql           STUDENTS table — Node A (MariaDB)
│   ├── node_b_mysql.sql             ORDERS table — Node B (MySQL)
│   ├── node_c_postgres.sql          ORDER_SUMMARY table — Node C (PostgreSQL)
│   │
│   │   ── Seed / mockup data (run after schema) ─────────────
│   ├── seed_a_mariadb.sql           5 sample students
│   ├── seed_b_mysql.sql             10 sample orders across all students
│   └── seed_c_postgres.sql          Matching order summary rows
│
├── setup/                           Diagnostic tools
│   └── test_connections.php         Connection health checker — run this before anything else
│
└── docs/                            All project documentation
    │
    │   ── Assets ────────────────────────────────────────────
    ├── LogoUTeM.png                 UTeM university logo
    ├── ERD.jpg                      Entity relationship diagram (hand-drawn)
    │
    │   ── Diagrams & Design ─────────────────────────────────
    ├── erd.md                       ERD entities, cardinality, business rules, ASCII diagram
    │
    │   ── Setup Guides ──────────────────────────────────────
    ├── tailscale_guide.md           Step-by-step Tailscale installation and configuration
    ├── environment.md               PHP extensions, config.php reference, Windows setup
    │
    │   ── Technical Reference ──────────────────────────────
    ├── database.md                  Full schema reference for all 3 nodes
    ├── requirements.md              Functional, non-functional, and domain requirements
    ├── domain-rules.md              Business rules and cross-node constraints
    ├── flow-of-events.md            Use case flows (register, order, report)
    │
    │   ── Project Management ───────────────────────────────
    ├── git-strategy.md              Git branching and workflow guide for group projects
    │
    │   ── Meta ─────────────────────────────────────────────
    ├── folder-structure.md          This file
    └── troubleshooting.md           Common errors and how to fix them
```

---

## Rules

**`pages/`** — One file per user-facing page. No file exceeds 150 lines.
Each file is self-contained: it `require_once`s only what it needs.

**`includes/`** — One file per DB engine. Each exposes exactly one function.
No page-rendering logic here.

**`db/`** — Pure SQL. No PHP. Each file runs top-to-bottom on a fresh install.

**`setup/`** — Diagnostic tools only. `test_connections.php` verifies that all
three nodes are reachable before any demo runs. The Tailscale setup guide has
moved to `docs/tailscale_guide.md`.

**`docs/`** — The technical reference students and instructors use during
development. These are not marketing docs — they are decision records.

**`context/`** — Written for Claude Code, not for students. Contains the
agent rules, architecture decisions, and per-spec implementation guides.
Students don't need to read these.

**`config.php`** — Never committed with real IPs or passwords.
The committed version always contains `100.x.x.x` placeholders only.
Listed in `.gitignore`.
