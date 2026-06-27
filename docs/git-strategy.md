# Git Strategy — Group Project Guide

This guide covers how a group should organise their Git workflow when building
a database-driven project from scratch. It follows the natural order of a
university project: **proposal → modules → ERD → implementation**.

---

## The Workflow at a Glance

```
1. Modules declared in proposal (already done)
        ↓
2. Assign one module per team member
        ↓
3. Each module contributes entities → combine into ERD
        ↓
4. Create branches per module → implement → PR to main
```

---

## Step 1 — Design the ERD From Your Modules

Once modules are declared, **each person designs the entities for their module**.
Then the group combines them into one ERD.

### How to go from module → entity → ERD

**For each module, ask:**
1. What data does this module store? → these become **attributes**
2. What is the main thing being stored? → this becomes the **entity (table)**
3. What uniquely identifies each record? → this becomes the **primary key**
4. Does this module reference data from another module? → this becomes a **foreign key / relationship**

**Example walkthrough:**

```
Module A — Students
  Entity:     STUDENTS
  Attributes: student_id (PK), name, email, created_at
  References: nothing — this is the root entity

Module B — Orders
  Entity:     ORDERS
  Attributes: order_id (PK), item_name, quantity, price, status, ordered_at
  References: STUDENTS (a student places the order) → student_id (FK)

Module C — Reports
  Entity:     ORDER_SUMMARY
  Attributes: summary_id (PK), user_name, total_orders, total_spent, last_updated
  References: STUDENTS (summarises a student's activity) → student_id (FK)
```

### How to determine cardinality (the relationship symbols)

Once you know which entities reference each other, ask these two questions:

**Question 1 — from the parent side (e.g. STUDENTS):**
"Can one student have zero or many [orders / summaries]?"
→ If yes → `o{` (zero or many) or `o|` (zero or one)

**Question 2 — from the child side (e.g. ORDERS):**
"Must every order belong to exactly one student?"
→ If yes → `||` (exactly one, mandatory)

**Resulting notation:**

```
STUDENTS  ||--o{  ORDERS         (one student → zero or many orders)
STUDENTS  ||--o|  ORDER_SUMMARY  (one student → zero or one summary)
```

### ERD design order for group projects

```
1. Each person draws their module's entity box (table + columns)
2. Each person marks their foreign keys (which entity they point to)
3. Group meets and draws the relationship lines between modules
4. Group agrees on cardinality for each relationship
5. One person draws the final combined ERD (hand-drawn or using a tool)
6. ERD is reviewed and saved to docs/ before any code is written
```

> **Do not write any code until the ERD is agreed on.** Changing a primary key
> type after implementation (e.g. from INT to VARCHAR) breaks every foreign key
> that references it across all modules.

---

## Step 2 — Branch Strategy

### Branch naming

Each module gets its own branch. Name branches after the module they implement:

```
main                        ← stable, always working
feat/module-students        ← Person 1 works here
feat/module-orders          ← Person 2 works here
feat/module-reports         ← Person 3 works here
feat/module-dashboard       ← Person 4 works here
```

For fixes and documentation:
```
fix/orders-student-verify   ← bug fix scoped to a module
docs/erd-update             ← documentation changes
```

### Rules

- **Never push directly to `main`.** All work goes through a branch and a Pull Request.
- **One branch per module.** Do not mix two modules' code in one branch.
- **Branch from `main`** at the start of your module, not from a teammate's branch.
- **Keep your branch up to date** — regularly pull from main to avoid large merge conflicts:

```bash
git checkout feat/module-orders
git pull origin main
```

---

## Step 3 — Implementation Order

Because modules depend on each other's database schemas (foreign keys reference
other modules' tables), the implementation must follow a specific order:

```
Phase 1 — Database schemas (all at once, before any PHP)
  → All members create their SQL schema files
  → Group reviews the combined schema against the ERD
  → Schema files are merged to main before any pages are written

Phase 2 — Root module first (the entity with no foreign keys)
  → Module A (STUDENTS) is implemented first
  → Its table and PHP page are merged to main
  → Other modules can now reference student_id

Phase 3 — Dependent modules (in any order, once Phase 2 is in main)
  → Module B (ORDERS) and Module C (REPORTS) can be built in parallel
  → Each is on its own branch

Phase 4 — Aggregating module last
  → Module D (DASHBOARD) reads from all other modules
  → Build this after the other modules are merged and tested
```

---

## Step 4 — Pull Request Checklist

Before merging your branch to `main`, go through this checklist:

```
[ ] My branch is up to date with main (git pull origin main done)
[ ] The feature works end-to-end on my local setup
[ ] I have not broken any other module's page
[ ] SQL schema file is updated if I changed the table structure
[ ] docs/database.md is updated to reflect any schema changes
[ ] docs/erd.md is updated if I added or changed an entity/relationship
[ ] No config.php with real IPs or passwords committed
[ ] Commit messages follow the format in Step 5 of this guide
```

Ask a teammate to review your PR before merging. They should test it on their
own machine, not just read the code.

---

## Step 5 — Commit Message Format

```
type(scope): what changed and why  (≤72 chars)

What was the problem?
What did you try?
What is the solution?
Files changed: list them.
```

**Scope = your module name:**

```
feat(module-students): add registration form and Node A write
feat(module-orders): implement 3-node write flow
fix(module-reports): handle Node C down without crashing page
docs(erd): update ORDER_SUMMARY entity with student_id FK
```

---

## Quick Reference — Who Does What

| Phase | What | Who |
|---|---|---|
| Modules | Modules already declared in proposal — assign one per person | Whole group |
| ERD | Each person draws their module's entity | Each person individually |
| ERD | Combine entities, draw relationships, agree on cardinality | Whole group |
| Schema | Each person writes their `db/node_*.sql` file | Each person |
| Schema review | Check combined schema against ERD | Whole group |
| Implementation | Each person implements their module on their branch | Each person |
| PR review | Teammate reviews and tests before merging | Cross-review |
| Integration | Dashboard module ties everything together | Person 4 (or last to merge) |
