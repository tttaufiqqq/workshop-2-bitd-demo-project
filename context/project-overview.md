# Project Overview — Heterogeneous Distributed DB Demo

## Purpose

A teaching demo for undergraduate students building a **heterogeneous distributed database
system** — one that spans **three different database engines** (MariaDB, MySQL, PostgreSQL)
on **separate physical machines** connected via **Tailscale** mesh VPN.

*Heterogeneous* means each node runs a different DBMS. This is the opposite of a homogeneous
distributed system (e.g. three MySQL servers). The engine differences are intentional teaching
material, not incidental complexity.

The goal is not a polished product. The goal is to make distributed
database architecture *visible and debuggable* at every layer.

## What Students Learn From This Project

| Concept | Where it shows up |
|---|---|
| Different DB engines, same PDO interface | `includes/db_*.php` |
| DSN differences between MySQL family and PostgreSQL | `db_mariadb.php` vs `db_postgres.php` |
| Cross-node data without foreign key constraints | `pages/reports.php` join logic in PHP |
| Remote DB connections over a VPN | Tailscale IPs in `config.php` |
| Graceful degradation when a node is offline | Per-node try/catch in `pages/index.php` |
| Parameterised queries as the only safe option | Every INSERT/SELECT in `pages/` |
| Remote DB user permissions (`'%'` wildcard) | SQL setup files in `db/` |

## System Name

**Heterogeneous Distributed DB Demo** — Student Order System

## Domain

Students register on one node. They place orders on another. A third node
stores aggregated analytics. This mirrors a real microservice-adjacent
split: user identity store → transactional store → analytics store.

## Actors

| Actor | Actions |
|---|---|
| Student | Register, view profile, place orders |
| Instructor | View dashboard, check node health, view reports |

*(No authentication is implemented in the demo — the focus is DB architecture.)*

## Scope (Demo Only)

In scope:
- User registration (write to Node A)
- Order placement (write to Node B, reads user from Node A)
- Order summary sync (write to Node C after order confirmed)
- Dashboard showing live data from all 3 nodes simultaneously
- Connection health checker page

Out of scope for this demo:
- Authentication / sessions
- Payment processing
- File uploads
- Email notifications
- Role-based access control
