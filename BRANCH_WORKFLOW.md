# Branch Integration Workflow

## Overview
This document outlines the stable workflow for integrating changes between `main` and `pawan3` branches to prevent regressions and ensure consistency.

---

## Branch Strategy

### **Main Branch** (`main`)
- **Purpose**: Production-ready code
- **Status**: Always contains tested, working features
- **Protection**: Default merge target
- **Rule**: All features must be tested and working before merging to main

### **Development Branch** (`pawan3`)
- **Purpose**: Active development and feature testing
- **Status**: May contain experimental features
- **Rule**: Always keep in sync with main after major updates

---

## Daily Workflow

### **Starting Work**
```bash
# 1. Update main from remote
git checkout main
git pull origin main

# 2. Ensure build passes
npm run build

# 3. Switch to development branch
git checkout pawan3

# 4. Rebase on latest main (recommended)
git rebase origin/main
# OR merge if rebase causes issues
git merge origin/main
```

### **Making Changes**
```bash
# 1. Create feature branch (optional but recommended)
git checkout -b feature/your-feature

# 2. Make changes and test locally
npm run dev  # or php artisan serve

# 3. Validate build
npm run build

# 4. Commit changes
git add .
git commit -m "feat: Description of changes"

# 5. Push to feature branch
git push origin feature/your-feature
```

### **Merging to Development (pawan3)**
```bash
# 1. Checkout pawan3
git checkout pawan3

# 2. Merge feature branch
git merge feature/your-feature

# 3. Test on pawan3
npm run dev
npm run build

# 4. Push to remote
git push origin pawan3
```

### **Merging to Main**
```bash
# 1. Ensure pawan3 is tested and working
git checkout pawan3
npm run dev  # Test locally

# 2. Verify build passes
npm run build

# 3. Switch to main
git checkout main
git pull origin main

# 4. Merge pawan3 into main
git merge pawan3

# 5. Verify build on main
npm run build

# 6. Push to main
git push origin main

# 7. Sync pawan3 back with main (important!)
git checkout pawan3
git rebase origin/main
git push origin pawan3
```

---

## Handling Merge Conflicts

### **If Conflicts Occur During Rebase/Merge**

```bash
# 1. Check what's conflicting
git status

# 2. For Vue/JS files:
#    - Check both versions
#    - Keep the fetch-based approach (not Inertia .post())
#    - Validate logic is complete

# 3. For PHP files:
#    - Ensure no duplicate methods
#    - Check database migrations are idempotent

# 4. After resolving conflicts
git add <resolved-files>
git rebase --continue  # if rebasing
# OR
git merge --continue  # if merging

# 5. Test the resolution
npm run build
npm run dev

# 6. Push with force if rebasing
git push origin <branch> --force
```

### **Critical Files to Watch**
- `resources/js/Components/custom/RentalItemCreateModel.vue` - Use **fetch API** (not Inertia `.post()`)
- `resources/js/Pages/RentalItems/Index.vue` - Ensure `handleSuccess` has `setTimeout` before reload
- `resources/js/Components/custom/ProductCreateModel.vue` - Keep inline supplier dialog (not separate component)
- `app/Providers/FortifyServiceProvider.php` - Ensure `orWhere('email')` is present in auth logic
- Any migration files - Always add idempotent checks (`Schema::hasColumn()`)

---

## Preventing Regressions

### **Before Pushing Any Branch**
1. Run `npm run build` - verify no TypeScript/Vue errors
2. Test critical workflows locally:
   - Admin login (email or username)
   - Add rental item and verify it appears in list
   - Add product and verify supplier can be added
   - Check page redirects don't break modal interactions
3. Review the diff: `git diff main` (on pawan3)

### **Git Hooks (Automatic Validation)**
Hooks are installed in `.git/hooks/` and run automatically before each push.

**Pre-push validation:**
- Runs `npm run build`
- Prevents pushing if build fails
- Ensures code quality before reaching remote

To install/update hooks manually:
```bash
npm run setup-hooks  # (if available)
# Or manually copy files from .githooks/ to .git/hooks/ and make executable
```

---

## Synchronization Pattern

### **After Major Merge to Main**
Always sync development branches back:

```bash
# After merging to main
git checkout main
git pull origin main

git checkout pawan3
git reset --hard origin/main
git push origin pawan3 --force
```

This ensures both branches are in sync and prevents regressions when switching between them.

---

## Quick Reference

| Action | Command |
|--------|---------|
| Update main locally | `git checkout main && git pull origin main` |
| Sync pawan3 with main | `git checkout pawan3 && git reset --hard origin/main && git push origin pawan3 --force` |
| Rebase on main | `git rebase origin/main` |
| Check for conflicts | `git status` |
| Validate build | `npm run build` |
| Test locally | `npm run dev` |

---

## Key Principles

✅ **Always test before pushing**
✅ **Always rebase/merge main into pawan3 after updating main**
✅ **Use fetch API for form submissions (not Inertia `.post()` in modals)**
✅ **Add timeout delays before page reloads in success callbacks**
✅ **Keep migrations idempotent with schema checks**
✅ **Review diffs before merging to catch regressions**

---

## Contact & Issues

If you encounter persistent merge conflicts or regressions:
1. Check `.git/config` for correct remote URLs
2. Run `git fetch --all --prune` to sync remotes
3. Consult the "Handling Merge Conflicts" section above
4. Create a detailed commit message explaining the fix
