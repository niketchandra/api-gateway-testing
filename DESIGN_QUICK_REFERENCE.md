# AtGlance Modern Design System - Quick Reference

## Color Palette at a Glance

| Purpose | Color | Hex | Usage |
|---------|-------|-----|-------|
| Primary Background | Light Gray | #F7FAFC | Page background |
| Sidebar | Dark Gray | #1F2937 | Left navigation |
| Primary Accent | Sky Blue | #38BDF8 | Buttons, links, focus |
| Secondary Accent | Cyan | #67E8F9 | Hover states, badges |
| Primary Text | Dark Gray | #111827 | Headlines, body text |
| Secondary Text | Medium Gray | #6B7280 | Labels, descriptions |
| Tertiary Text | Light Gray | #9CA3AF | Helper text, placeholders |
| Borders | Light Gray | #E5E7EB | Borders, dividers |
| Success | Green | #10B981 | Success states, badges |
| Warning | Amber | #F59E0B | Warnings, pending states |
| Error | Red | #EF4444 | Errors, delete actions |
| Info | Blue | #3B82F6 | Info alerts, badges |

---

## Typography

| Element | Font | Size | Weight | Use |
|---------|------|------|--------|-----|
| Page Title | Inter | 48px | 800 | Main headings |
| Section Title | Inter | 36px | 800 | Section headers |
| Card Title | Inter | 18px | 700 | Card headers |
| Body Text | Inter | 14px | 400 | Regular content |
| Labels | Inter | 13px | 600 | Form labels, captions |
| Small Text | Inter | 11px | 600 | Helper text, badges |

---

## Component Quick Reference

### 1. Cards
```blade
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Title</h3>
    </div>
    <div class="card-content">
        Content here
    </div>
    <div class="card-footer">
        <button class="btn btn-primary">Action</button>
    </div>
</div>
```

### 2. Buttons
```blade
<!-- Primary (CTA) -->
<button class="btn btn-primary">Click Me</button>

<!-- Secondary -->
<button class="btn btn-secondary">Secondary</button>

<!-- Danger -->
<button class="btn btn-danger">Delete</button>

<!-- Icon Only -->
<button class="btn-icon"><i class="fas fa-edit"></i></button>
```

### 3. Alerts
```blade
<!-- Success -->
<div class="alert alert-success">
    <i class="fas fa-check-circle"></i>
    <span>Success message</span>
</div>

<!-- Error -->
<div class="alert alert-error">
    <i class="fas fa-exclamation-circle"></i>
    <span>Error message</span>
</div>

<!-- Warning -->
<div class="alert alert-warning">
    <i class="fas fa-warning"></i>
    <span>Warning message</span>
</div>
```

### 4. Badges
```blade
<span class="badge badge-primary">Primary</span>
<span class="badge badge-success">Active</span>
<span class="badge badge-warning">Pending</span>
<span class="badge badge-error">Error</span>
```

### 5. Forms
```blade
<div class="form-section">
    <h4 class="form-section-title">Form Title</h4>
    
    <div class="form-row">
        <div class="form-group">
            <label for="input1">Label</label>
            <input type="text" id="input1" placeholder="Placeholder">
            <p class="form-help">Helper text</p>
        </div>
        
        <div class="form-group">
            <label for="select1">Select</label>
            <select id="select1">
                <option>Option 1</option>
                <option>Option 2</option>
            </select>
        </div>
    </div>
</div>
```

### 6. Tables
```blade
<div class="table-container">
    <table>
        <thead>
            <tr>
                <th>Column 1</th>
                <th>Column 2</th>
                <th>Column 3</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Data 1</td>
                <td>
                    <span class="table-status status-active">
                        <i class="fas fa-circle"></i> Active
                    </span>
                </td>
                <td>
                    <button class="btn-icon"><i class="fas fa-edit"></i></button>
                </td>
            </tr>
        </tbody>
    </table>
</div>
```

### 7. Dashboard Cards
```blade
<div class="dashboard-grid">
    <div class="dashboard-card">
        <div class="stat-label">Total Users</div>
        <div class="stat-value">256</div>
        <div class="stat-trend positive">
            <i class="fas fa-arrow-up"></i> 12% increase
        </div>
    </div>
    
    <div class="dashboard-card">
        <div class="stat-label">Active Systems</div>
        <div class="stat-value">42</div>
        <div class="stat-trend positive">
            <i class="fas fa-arrow-up"></i> 5% increase
        </div>
    </div>
</div>
```

### 8. Empty States
```blade
<div class="empty-state">
    <div class="empty-state-icon">
        <i class="fas fa-inbox"></i>
    </div>
    <h4 class="empty-state-title">No items found</h4>
    <p class="empty-state-text">Try adjusting your filters</p>
    <button class="btn btn-primary">Create New Item</button>
</div>
```

### 9. Modals
```blade
<div class="modal-overlay">
    <div class="modal">
        <div class="modal-header">
            <h3 class="modal-title">Modal Title</h3>
            <button class="modal-close">&times;</button>
        </div>
        <div class="modal-body">
            Modal content goes here
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary">Cancel</button>
            <button class="btn btn-primary">Confirm</button>
        </div>
    </div>
</div>
```

---

## Spacing Scale

| Size | Value | CSS |
|------|-------|-----|
| Extra Small | 4px | margin/padding: 0.25rem |
| Small | 8px | margin/padding: 0.5rem |
| Base | 12px | margin/padding: 0.75rem |
| Medium | 16px | margin/padding: 1rem |
| Large | 20px | margin/padding: 1.25rem |
| XL | 24px | margin/padding: 1.5rem |
| 2XL | 32px | margin/padding: 2rem |
| 3XL | 48px | margin/padding: 3rem |
| 4XL | 60px | margin/padding: 3.75rem |

---

## Border Radius

| Element | Radius | Usage |
|---------|--------|-------|
| Inputs | 10px | Form inputs, selects |
| Buttons | 24px | Pill-shaped buttons |
| Cards | 14px | Card containers |
| Badges | 20px | Pill badges |
| Modals | 16px | Modal dialogs |
| Small Elements | 8px | Chip elements |

---

## Shadows

| Level | CSS | Usage |
|-------|-----|-------|
| Small | 0 1px 2px rgba(0,0,0,0.05) | Subtle depth |
| Medium | 0 4px 6px rgba(0,0,0,0.1) | Card hover |
| Large | 0 10px 15px rgba(0,0,0,0.1) | Elevated cards |
| Extra Large | 0 20px 25px rgba(0,0,0,0.1) | Modals |
| Glow | 0 0 20px rgba(56,189,248,0.3) | Blue accent |

---

## Animation Timings

| Speed | Duration | Easing |
|-------|----------|--------|
| Fast | 0.2s | cubic-bezier(0.4, 0, 0.2, 1) |
| Normal | 0.3s | cubic-bezier(0.4, 0, 0.2, 1) |
| Slow | 0.5s | cubic-bezier(0.4, 0, 0.2, 1) |

---

## CSS Variables (Use in Styles)

```css
/* Colors */
var(--color-bg)               /* #F7FAFC */
var(--color-sidebar)          /* #1F2937 */
var(--color-accent-blue)      /* #38BDF8 */
var(--color-accent-cyan)      /* #67E8F9 */
var(--color-text-dark)        /* #111827 */
var(--color-text-light)       /* #6B7280 */
var(--color-text-lighter)     /* #9CA3AF */
var(--color-border)           /* #E5E7EB */
var(--color-border-light)     /* #F3F4F6 */
var(--color-white)            /* #FFFFFF */
var(--color-success)          /* #10B981 */
var(--color-warning)          /* #F59E0B */
var(--color-error)            /* #EF4444 */
var(--color-info)             /* #3B82F6 */

/* Shadows */
var(--shadow-sm)              /* 0 1px 2px ... */
var(--shadow-md)              /* 0 4px 6px ... */
var(--shadow-lg)              /* 0 10px 15px ... */
var(--shadow-xl)              /* 0 20px 25px ... */
var(--shadow-glow)            /* 0 0 20px ... */

/* Transitions */
var(--transition-fast)        /* 0.2s cubic-bezier(...) */
var(--transition-base)        /* 0.3s cubic-bezier(...) */
var(--transition-slow)        /* 0.5s cubic-bezier(...) */
```

---

## Responsive Breakpoints

| Breakpoint | Width | Device |
|-----------|-------|--------|
| Mobile | 0-639px | Phones |
| Tablet | 640-1023px | Tablets |
| Desktop | 1024px+ | Desktops |
| Large | 1280px+ | Large screens |

---

## Icon Library

Using FontAwesome 6.4:
```blade
<!-- Navigation Icons -->
<i class="fas fa-chart-line"></i>      <!-- Dashboard -->
<i class="fas fa-cog"></i>             <!-- Settings -->
<i class="fas fa-users"></i>           <!-- Users -->
<i class="fas fa-server"></i>          <!-- Systems -->
<i class="fas fa-file-code"></i>       <!-- Config Files -->

<!-- Action Icons -->
<i class="fas fa-plus"></i>            <!-- Add/Create -->
<i class="fas fa-edit"></i>            <!-- Edit -->
<i class="fas fa-trash"></i>           <!-- Delete -->
<i class="fas fa-search"></i>          <!-- Search -->
<i class="fas fa-download"></i>        <!-- Download -->

<!-- Status Icons -->
<i class="fas fa-check-circle"></i>    <!-- Success -->
<i class="fas fa-exclamation-circle"></i> <!-- Error -->
<i class="fas fa-warning"></i>         <!-- Warning -->
<i class="fas fa-info-circle"></i>     <!-- Info -->

<!-- Arrows & Navigation -->
<i class="fas fa-arrow-up"></i>        <!-- Trend Up -->
<i class="fas fa-arrow-down"></i>      <!-- Trend Down -->
<i class="fas fa-chevron-right"></i>   <!-- Next -->
<i class="fas fa-chevron-left"></i>    <!-- Previous -->
```

---

## Common Usage Patterns

### Success Message
```blade
<div class="alert alert-success">
    <i class="fas fa-check-circle"></i>
    <span>Operation completed successfully!</span>
</div>
```

### Error Message
```blade
<div class="alert alert-error">
    <i class="fas fa-exclamation-circle"></i>
    <span>An error occurred. Please try again.</span>
</div>
```

### Loading State
```blade
<div class="loading"></div>
<p>Loading...</p>
```

### Skeleton Loader
```blade
<div class="skeleton" style="height: 100px; border-radius: 12px;"></div>
```

### Button Group
```blade
<div class="btn-group">
    <button class="btn btn-primary">Save</button>
    <button class="btn btn-secondary">Cancel</button>
</div>
```

### Filter Controls
```blade
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Filters</h3>
    </div>
    <div class="card-content">
        <div class="form-row">
            <div class="form-group">
                <label>Status</label>
                <select>
                    <option>All</option>
                    <option>Active</option>
                    <option>Inactive</option>
                </select>
            </div>
            <div class="form-group">
                <label>Date</label>
                <input type="date">
            </div>
        </div>
    </div>
    <div class="card-footer">
        <button class="btn btn-primary">Apply Filters</button>
        <button class="btn btn-secondary">Reset</button>
    </div>
</div>
```

---

## Files to Reference

1. **DESIGN_SYSTEM.md** - Complete design documentation
2. **composer/resources/css/modern-design-system.css** - Component CSS library
3. **composer/resources/views/app.blade.php** - Main layout with modern design
4. **DESIGN_IMPLEMENTATION_GUIDE.sh** - Implementation instructions

---

## Key Features

✅ **Dark Sidebar** - Professional, modern navigation
✅ **Light Content** - Clean, readable main content area
✅ **Color Accents** - Blue & cyan for highlights
✅ **Rounded UI** - Modern, friendly appearance
✅ **Smooth Animations** - Natural, polished feel
✅ **Spacious Layout** - Premium, uncluttered design
✅ **Responsive** - Works perfectly on all devices
✅ **Accessible** - WCAG compliant colors & contrast
✅ **Enterprise-Grade** - Professional, trustworthy look
✅ **Modern Typography** - Inter font family

---

**Last Updated:** May 2026
**Version:** 2.0
**Status:** Production Ready ✅

