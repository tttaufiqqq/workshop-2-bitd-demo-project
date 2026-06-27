# Progress Tracker — Distributed DB Demo

Last updated: 2026-06-27

## Build Status

| Phase | Status | Notes |
|---|---|---|
| Project structure | ✅ Done | All folders created |
| config.php | ✅ Done | Placeholder IPs, ready to fill |
| DB schemas (all 3 nodes) | ✅ Done | `db/*.sql` |
| Connection helpers | ✅ Done | Fixed: helpers now throw instead of die() |
| Connection test page | ✅ Done | `setup/test_connections.php` |
| Dashboard (index.php) | ✅ Done | All 3 nodes, per-node error isolation |
| Register page (Node A) | ✅ Done | INSERT with prepared statement |
| Orders page (Node B) | ✅ Done | Three-node write: verify A → insert B → sync C |
| Reports page (Node C) | ✅ Done | Cross-node join in PHP |
| Tailscale setup guide | ✅ Done | `docs/tailscale_guide.md` |
| README.md | ✅ Done | Main student-facing guide |
| CLAUDE.md | ✅ Done | Tailored for this project |
| docs/ suite | ✅ Done | All docs written including troubleshooting |

## Docs Checklist

| File | Status |
|---|---|
| docs/architecture.md | ✅ Done (in context/) |
| docs/database.md | ✅ Done |
| docs/requirements.md | ✅ Done |
| docs/flow-of-events.md | ✅ Done |
| docs/domain-rules.md | ✅ Done |
| docs/environment.md | ✅ Done |
| docs/troubleshooting.md | ⬜ Pending |
| docs/folder-structure.md | ✅ Done |

## Spec Files (context/specs/)

| Spec | Status |
|---|---|
| 01-config-and-helpers.md | ✅ Done |
| 02-dashboard.md | ✅ Done |
| 03-register-page.md | ✅ Done |
| 04-orders-page.md | ✅ Done |
| 05-reports-page.md | ✅ Done |

## Next Actions

All planned specs and pages are complete. Project is ready to run.
