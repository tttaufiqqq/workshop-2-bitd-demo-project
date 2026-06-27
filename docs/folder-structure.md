# Folder Structure — Distributed DB Demo

```
distributed-db-demo/
│
├── CLAUDE.md                        Agent rules for Claude Code
├── README.md                        Main student-facing setup guide
├── CHANGELOG.md                     Version history
├── CONTRIBUTING.md                  How to extend the demo
├── .gitignore
│
├── config.php                       All DB credentials and app settings
│                                    (one file, no .env, no Composer)
│
├── pages/                           Browseable PHP pages
│   ├── index.php                    Dashboard — all 3 nodes side-by-side
│   ├── register.php                 Register a user → Node A (MariaDB)
│   ├── orders.php                   Place an order → Node B (MySQL)
│   │                                  also verifies user on Node A
│   │                                  also syncs summary to Node C
│   └── reports.php                  View order summary → Node C (PostgreSQL)
│
├── includes/                        Reusable PHP helpers
│   ├── db_mariadb.php               getMariaDBConnection(): PDO
│   ├── db_mysql.php                 getMySQLConnection(): PDO
│   └── db_postgres.php              getPostgresConnection(): PDO
│
├── db/                              SQL setup scripts (run once per node)
│   ├── node_a_mariadb.sql           Schema + user + seed data for Node A
│   ├── node_b_mysql.sql             Schema + user + seed data for Node B
│   └── node_c_postgres.sql          Schema + user + seed data for Node C
│
├── setup/                           Setup and diagnostic tools
│   ├── test_connections.php         Health checker — run this first
│   └── tailscale_guide.md           Step-by-step Tailscale setup
│
├── docs/                            Technical documentation
│   ├── database.md                  Schema reference for all 3 nodes
│   ├── requirements.md              FR / NFR / Domain requirements
│   ├── flow-of-events.md            Use case flows
│   ├── domain-rules.md              Business rules and constraints
│   ├── environment.md               config.php reference + PHP extensions
│   ├── folder-structure.md          This file
│   └── troubleshooting.md           Common errors and fixes
│
└── context/                         Agent context (not for students)
    ├── project-overview.md
    ├── architecture.md
    ├── ui-context.md
    ├── code-standards.md
    ├── progress-tracker.md
    └── specs/
        ├── 01-config-and-helpers.md
        ├── 02-dashboard.md
        ├── 03-register-page.md
        ├── 04-orders-page.md
        └── 05-reports-page.md
```

---

## Rules

**`pages/`** — One file per user-facing page. No file exceeds 150 lines.
Each file is self-contained: it `require_once`s only what it needs.

**`includes/`** — One file per DB engine. Each exposes exactly one function.
No page-rendering logic here.

**`db/`** — Pure SQL. No PHP. Each file runs top-to-bottom on a fresh install.

**`setup/`** — Tools for getting the demo running. Students run these first,
not last.

**`docs/`** — The technical reference students and instructors use during
development. These are not marketing docs — they are decision records.

**`context/`** — Written for Claude Code, not for students. Contains the
agent rules, architecture decisions, and per-spec implementation guides.
Students don't need to read these.

**`config.php`** — Never committed with real IPs or passwords.
The committed version always contains `100.x.x.x` placeholders only.
Listed in `.gitignore`.
