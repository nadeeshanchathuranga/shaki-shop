# Quick Integration Reference

## Problem: "Changes disappeared after pull from main"

**Cause**: One branch has working code, other branch has older code. Merge brought back the old version.

**Solution**:
```bash
# 1. Check which version is correct
git log --oneline -5

# 2. Reset to main (main is the source of truth)
git checkout pawan3
git reset --hard origin/main
git push origin pawan3 --force

# 3. Verify the code has fetch API and setTimeout patterns
grep "fetch('/rental-items'" resources/js/Components/custom/RentalItemCreateModel.vue
grep "setTimeout" resources/js/Pages/RentalItems/Index.vue
```

---

## Problem: "Build fails when pushing"

**Cause**: Pre-push hook detected compilation errors.

**Solution**:
```bash
# 1. Check what's wrong
npm run build

# 2. Fix any TypeScript/Vue errors shown in output

# 3. Verify it builds
npm run build

# 4. Try push again
git push origin <branch>
```

---

## Problem: "Rental items don't appear after creation"

**Cause**: Form is using Inertia `.post()` instead of fetch API, or missing setTimeout in reload.

**Solution**:
```bash
# 1. Verify the fetch pattern is present
grep -A 20 "const submit = () => {" resources/js/Components/custom/RentalItemCreateModel.vue

# If you see "fetch('/rental-items'" -> ✅ Correct
# If you see "form.post('/rental-items'" -> ❌ Wrong (need to fix)

# 2. Verify setTimeout is in handleSuccess
grep -B 2 "setTimeout" resources/js/Pages/RentalItems/Index.vue

# If you see "setTimeout(() => {" with "router.reload" inside -> ✅ Correct
# If missing -> ❌ Need to add it

# 3. If patterns are wrong, reset to main
git reset --hard origin/main
npm run build
```

---

## Problem: "Merge conflict - don't know which version to keep"

**Solution**:
```bash
# 1. See what's conflicting
git status

# 2. For these files, ALWAYS keep main's version:
#    - resources/js/Components/custom/RentalItemCreateModel.vue
#    - resources/js/Pages/RentalItems/Index.vue
#    - resources/js/Components/custom/ProductCreateModel.vue

git checkout --theirs <conflicted-file>

# OR if pulling, use:
git checkout --ours <conflicted-file>

# 3. Complete the merge
git add <resolved-files>
git rebase --continue

# 4. Verify build passes
npm run build

# 5. Push with force (only for rebase)
git push origin <branch> --force
```

---

## Problem: "Can't login with email, only username works"

**Cause**: Auth logic missing `.orWhere('email')` clause.

**Solution**:
```bash
# Check if the pattern is present
grep -n "orWhere('email'" app/Providers/FortifyServiceProvider.php

# If NOT found, reset to main
git reset --hard origin/main

# Verify
grep "orWhere('email'" app/Providers/FortifyServiceProvider.php
```

---

## Problem: "Migration fails - column already exists"

**Cause**: Migration missing idempotent schema check.

**Solution**:
```bash
# Verify the pattern in the migration
grep -B 2 "hasColumn" database/migrations/*.php

# If NOT found in recent migration, reset and fix
git reset --hard origin/main

# Verify
grep "hasColumn" database/migrations/2026_03_31_add_barcode_to_rental_items.php
```

---

## Standard Daily Workflow

```bash
# Morning: Update main
git checkout main
git pull origin main
npm run build

# Switch to dev branch
git checkout pawan3

# If main had updates, rebase
git rebase origin/main

# Make changes...
# ... edit files ...
git add .
git commit -m "feat: your description"

# Before pushing, always test
npm run build
npm run dev  # test locally

# Push (pre-push hook will validate)
git push origin pawan3

# When ready to merge to main:
git checkout main
git pull origin main
git merge pawan3
npm run build
npm run dev
git push origin main

# Sync pawan3 back with main
git checkout pawan3
git reset --hard origin/main
git push origin pawan3 --force
```

---

## One-Liner Diagnostics

```bash
# Check if code is in sync between branches
git diff main pawan3 | grep -E "fetch|setTimeout|orWhere|hasColumn"

# List recent commits to see what changed
git log --oneline -10

# See all branches and their relationship
git log --all --oneline --graph --decorate -n 15

# Find which commit introduced a file
git log --all --full-history -- "<file-path>"

# See who changed a line (git blame)
git blame -L <start>,<end> <file>
```

---

## When All Else Fails

```bash
# Nuclear option - reset everything to main's latest
git fetch origin
git checkout main
git reset --hard origin/main
git checkout pawan3
git reset --hard origin/main
git push origin pawan3 --force

# Verify you're in the right state
npm run build
npm run dev

# Check critical patterns are present
grep "fetch" resources/js/Components/custom/RentalItemCreateModel.vue
```

---

## Verify Integration Health

Run this checklist weekly to catch issues early:

```bash
# 1. Both branches exist and are reachable
git branch -a

# 2. Main builds successfully
git checkout main
npm run build

# 3. Pawan3 builds successfully
git checkout pawan3
npm run build

# 4. No divergence
git log main..pawan3 | wc -l  # Should be 0

# 5. Critical patterns are present
grep -q "fetch('/rental-items'" resources/js/Components/custom/RentalItemCreateModel.vue && echo "✅ Rental fetch OK" || echo "❌ Rental fetch MISSING"
grep -q "setTimeout" resources/js/Pages/RentalItems/Index.vue && echo "✅ Reload timeout OK" || echo "❌ Reload timeout MISSING"
grep -q "orWhere('email'" app/Providers/FortifyServiceProvider.php && echo "✅ Email auth OK" || echo "❌ Email auth MISSING"

# If any ❌, run: git reset --hard origin/main
```

---

## Contact & Support

If you're stuck:
1. Check `BRANCH_WORKFLOW.md` for detailed procedures
2. Check `CRITICAL_PATTERNS.md` for code examples
3. Run the "One-Liner Diagnostics" above to identify the issue
4. Use "When All Else Fails" to reset to a known good state
