# Changelog

All notable changes to this project are documented here.
Format follows [Keep a Changelog](https://keepachangelog.com/en/1.0.0/).

---

## [Unreleased]

### Added
- `pages/orders.php` — three-node write flow (Node A verify → Node B insert → Node C sync)
- `pages/reports.php` — application-level cross-node join across all three nodes

---

## [0.1.0] — Initial scaffold

### Added
- Project structure: `pages/`, `includes/`, `db/`, `setup/`, `docs/`, `context/`
- `config.php` — all credentials and app settings in one file, no `.env`
- `includes/db_mariadb.php` — MariaDB PDO connection helper
- `includes/db_mysql.php` — MySQL PDO connection helper
- `includes/db_postgres.php` — PostgreSQL PDO connection helper
- `db/node_a_mariadb.sql` — Node A schema, user, seed data
- `db/node_b_mysql.sql` — Node B schema, user, seed data
- `db/node_c_postgres.sql` — Node C schema, user, seed data
- `setup/test_connections.php` — connection health checker with latency display
- `setup/tailscale_guide.md` — step-by-step Tailscale setup for students
- `pages/index.php` — dashboard with per-node error isolation
- `pages/register.php` — user registration (Node A write)
- Full `docs/` suite: database, requirements, flow-of-events, domain-rules,
  environment, folder-structure, troubleshooting
- Full `context/` suite: project-overview, architecture, ui-context,
  code-standards, progress-tracker, specs 01–05
- `CLAUDE.md` — agent rules tailored for plain PHP distributed DB project
- `.gitignore` — excludes config.php and CLAUDE.md
