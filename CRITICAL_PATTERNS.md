# Critical Code Patterns - Integration Stability Checklist

This file documents the correct patterns used in the codebase. When merging branches or pulling updates, verify these patterns are preserved.

---

## 1. Rental Item Form Submission (Vue)

**File**: `resources/js/Components/custom/RentalItemCreateModel.vue`

### ✅ Correct Pattern (Fetch API)
```javascript
const submit = () => {
  if (isSubmitting.value) return; // Prevent double submission
  isSubmitting.value = true;
  
  // Use fetch to prevent full page redirect issues with modal
  const formData = new FormData();
  
  // Append all form fields
  Object.keys(form.data()).forEach(key => {
    const value = form[key];
    if (value !== null && value !== undefined) {
      if (key === 'image' && value instanceof File) {
        formData.append(key, value);
      } else if (!(value instanceof File)) {
        formData.append(key, value);
      }
    }
  });
  
  const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
  
  fetch('/rental-items', {
    method: 'POST',
    body: formData,
    headers: {
      'X-CSRF-TOKEN': token,
      'X-Requested-With': 'XMLHttpRequest',
    },
  })
  .then(response => {
    if (response.ok) {
      form.reset();
      emit("success", "Rental item created successfully!");
      emit("update:open", false);
      isSubmitting.value = false;
    } else {
      isSubmitting.value = false;
      return response.json().then(data => {
        console.error("Validation errors:", data.errors);
        if (data.errors) {
          Object.keys(data.errors).forEach(field => {
            form.errors[field] = data.errors[field];
          });
        }
      });
    }
  })
  .catch(error => {
    console.error("Form submission failed:", error);
    isSubmitting.value = false;
  });
};
```

### ❌ Incorrect Pattern (Inertia .post - CAUSES REGRESSIONS)
```javascript
// DON'T DO THIS - causes modal to close before success event fires
form.post("/rental-items", {
  preserveState: false,
  onSuccess: () => {
    form.reset();
    emit("success", "...");
  }
});
```

**Why?** Inertia's redirect fires immediately, closing the modal and preventing the success event from reaching the parent component.

---

## 2. Success Callback with Page Reload (Vue)

**File**: `resources/js/Pages/RentalItems/Index.vue`

### ✅ Correct Pattern (Timeout before reload)
```javascript
const handleSuccess = (message) => {
  successMessage.value = message;
  isSuccessModalOpen.value = true;
  
  // Use a small delay to ensure form submission completes before reloading
  setTimeout(() => {
    router.reload({
      only: ["rentalItems"],
      preserveScroll: true,
      preserveState: false,
    });
  }, 500);
};
```

### ❌ Incorrect Pattern (No delay - CAUSES MISSING DATA)
```javascript
// DON'T DO THIS - reload fires too quickly
const handleSuccess = (message) => {
  successMessage.value = message;
  isSuccessModalOpen.value = true;
  router.reload({...}); // Fires immediately, data not yet persisted
};
```

**Why?** The 500ms delay ensures the fetch request completes and data is persisted to database before the page reloads.

---

## 3. Product Form with Inline Supplier Dialog

**File**: `resources/js/Components/custom/ProductCreateModel.vue`

### ✅ Correct Pattern (Inline dialog, fetch supplier submit)
```javascript
// Dialog inside the same component
<Teleport to="body">
  <Dialog :open="isSupplierModalOpen" @close="isSupplierModalOpen = false">
    <!-- Supplier form fields -->
    <form @submit.prevent="submitSupplierForm">
      <!-- inputs -->
    </form>
  </Dialog>
</Teleport>

// Fetch-based supplier submission
const submitSupplierForm = async () => {
  try {
    const response = await fetch('/suppliers', {
      method: 'POST',
      body: JSON.stringify(supplierForm),
      headers: {
        'X-CSRF-TOKEN': token,
        'X-Requested-With': 'XMLHttpRequest',
        'Content-Type': 'application/json',
      },
    });
    
    if (response.ok) {
      const newSupplier = await response.json();
      suppliers.push(newSupplier);
      form.supplier_id = newSupplier.id;
      isSupplierModalOpen.value = false;
    }
  } catch (error) {
    console.error("Failed to create supplier:", error);
  }
};
```

### ❌ Incorrect Pattern (Separate component with redirect)
```javascript
// DON'T DO THIS - Inertia redirect causes navigation away from form
form.post('/suppliers', {
  onSuccess: () => {
    // This won't fire - page already redirecting
  }
});
```

---

## 4. Authentication Logic

**File**: `app/Providers/FortifyServiceProvider.php`

### ✅ Correct Pattern (Email or Name login)
```php
use Illuminate\Support\Facades\Validator;

public function boot()
{
  Validator::make($request->all(), [
    'identity' => 'required|string',
    'password' => 'required|string',
  ])->validate();

  $user = User::where('name', $request->identity)
    ->orWhere('email', $request->identity)
    ->first();

  if ($user && Hash::check($request->password, $user->password)) {
    return $user;
  }
}
```

### ❌ Incorrect Pattern (Name-only login)
```php
// DON'T DO THIS - admin can't login with email
$user = User::where('name', $request->identity)->first();
```

---

## 5. Database Migrations

**File**: `database/migrations/` (all files)

### ✅ Correct Pattern (Idempotent with schema check)
```php
Schema::table('rental_items', function (Blueprint $table) {
  if (!Schema::hasColumn('rental_items', 'barcode')) {
    $table->string('barcode')->nullable()->unique();
  }
});
```

### ❌ Incorrect Pattern (No idempotent check - CAUSES MIGRATION ERRORS)
```php
// DON'T DO THIS - fails if migration runs twice
Schema::table('rental_items', function (Blueprint $table) {
  $table->string('barcode')->nullable()->unique();
});
```

---

## 6. Form Button Types

**File**: `resources/js/Components/custom/*.vue`

### ✅ Correct Pattern (Prevent default on action buttons)
```vue
<!-- Add Size/Color/Supplier buttons prevent form submission -->
<button
  type="button"
  @click="openSizeModal"
  class="text-blue-500 hover:underline"
>
  + Add Size
</button>
```

### ❌ Incorrect Pattern (Missing type="button" - CAUSES UNINTENDED SUBMISSIONS)
```vue
<!-- DON'T DO THIS - clicking triggers form submission
<button @click="openSizeModal" class="text-blue-500">
  + Add Size
</button>
```

---

## Integration Testing Checklist

Before merging to main, verify:

- [ ] `npm run build` completes without errors
- [ ] Rental item creation shows new item immediately (no manual refresh)
- [ ] Login works with both email and username
- [ ] Add Supplier button in product form doesn't redirect away
- [ ] Add Size/Color/Category doesn't trigger form submission
- [ ] All migrations run without "column already exists" errors
- [ ] Page refreshes include timeout delay for successful form submissions
- [ ] No console errors in browser dev tools

---

## Regression Detection

Run these commands to detect if patterns have diverged:

```bash
# Check if fetch pattern is present in rental form
grep -n "fetch('/rental-items'" resources/js/Components/custom/RentalItemCreateModel.vue

# Check if setTimeout is in handleSuccess
grep -n "setTimeout" resources/js/Pages/RentalItems/Index.vue

# Check if orWhere('email') is in auth
grep -n "orWhere('email'" app/Providers/FortifyServiceProvider.php

# Check if hasColumn check exists in migrations
grep -r "hasColumn" database/migrations/
```

If any pattern is missing, run: `git reset --hard origin/main` and restart.

---

## Questions or Issues?

Refer to `BRANCH_WORKFLOW.md` for detailed integration procedures.
