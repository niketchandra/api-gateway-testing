# AtGlance Modern Design System

## Overview

AtGlance now features a **ultra-clean, minimal, enterprise-grade modern design** with:
- Dark vertical sidebar navigation (#1F2937)
- Large white/light content area (#F7FAFC)
- Soft blue (#38BDF8) and cyan (#67E8F9) accent colors
- Rounded buttons with pill shape (border-radius: 24px)
- Soft shadows and spacious padding
- Elegant Inter typography
- Smooth animations and transitions
- Mobile-responsive desktop-first design

---

## Color Palette

### Primary Colors
- **Background**: `#F7FAFC` - Light, clean background
- **Sidebar**: `#1F2937` - Dark, professional sidebar
- **White**: `#FFFFFF` - Clean white for cards and content

### Accent Colors
- **Accent Blue**: `#38BDF8` - Primary action color
- **Accent Cyan**: `#67E8F9` - Secondary accent, hover states

### Text Colors
- **Text Dark**: `#111827` - Primary text
- **Text Light**: `#6B7280` - Secondary text
- **Text Lighter**: `#9CA3AF` - Tertiary text

### Semantic Colors
- **Success**: `#10B981` - Green for success states
- **Warning**: `#F59E0B` - Amber for warnings
- **Error**: `#EF4444` - Red for errors
- **Info**: `#3B82F6` - Blue for information

### Borders
- **Border**: `#E5E7EB` - Standard border color
- **Border Light**: `#F3F4F6` - Light background borders

---

## CSS Variables Reference

All colors are defined as CSS variables in `:root`:

```css
:root {
    --color-bg: #F7FAFC;
    --color-sidebar: #1F2937;
    --color-accent-blue: #38BDF8;
    --color-accent-cyan: #67E8F9;
    --color-text-dark: #111827;
    --color-text-light: #6B7280;
    --color-border: #E5E7EB;
    --color-white: #FFFFFF;
    --color-success: #10B981;
    --color-warning: #F59E0B;
    --color-error: #EF4444;
    --color-info: #3B82F6;
}
```

Use these variables in your styles:
```css
.my-element {
    background: var(--color-white);
    color: var(--color-text-dark);
    border: 1px solid var(--color-border);
}
```

---

## Typography

### Font Family
- **Font**: Inter (San-serif)
- **Weights**: 300, 400, 500, 600, 700, 800
- **Fallback**: -apple-system, BlinkMacSystemFont, 'Segoe UI', system-ui, sans-serif

### Font Sizes

| Component | Size | Weight | Usage |
|-----------|------|--------|-------|
| **H1 (Welcome Title)** | 48px | 800 | Page titles |
| **H2 (Section Title)** | 36px | 800 | Section headers |
| **H3 (Card Title)** | 18px | 700 | Card headers |
| **Body** | 14px | 400 | Regular text |
| **Small** | 13px | 500 | Labels, secondary text |
| **Tiny** | 11px | 600 | Helper text, badges |

### Line Height
- **Headings**: 1.2
- **Body**: 1.6

---

## Components

### Cards
Cards are the primary container for content sections.

```html
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Card Title</h3>
    </div>
    <div class="card-content">
        <!-- Content here -->
    </div>
    <div class="card-footer">
        <button class="btn btn-primary">Action</button>
    </div>
</div>
```

**Features**:
- 14px border-radius
- Subtle shadow on hover
- Border elevation on hover
- Smooth transitions

### Buttons

#### Primary Button (CTA)
```html
<button class="btn btn-primary">
    <i class="fas fa-icon"></i> Click Me
</button>
```

**Features**:
- Pill-shaped (24px border-radius)
- Gradient background (Blue to Cyan)
- Glow shadow effect
- Smooth hover animation

#### Secondary Button
```html
<button class="btn btn-secondary">
    <i class="fas fa-icon"></i> Secondary
</button>
```

**Features**:
- Transparent with border
- White text on sidebar
- Subtle hover effect

#### Danger Button
```html
<button class="btn btn-danger">
    <i class="fas fa-icon"></i> Delete
</button>
```

**Features**:
- Red background
- Elevated shadow on hover
- Clear warning color

#### Icon Button
```html
<button class="btn-icon">
    <i class="fas fa-icon"></i>
</button>
```

**Features**:
- 40x40px square
- Border style
- Hover color change

### Forms

#### Form Group
```html
<div class="form-group">
    <label for="input">Label Text</label>
    <input type="text" id="input" placeholder="Placeholder">
    <p class="form-help">Helper text (optional)</p>
</div>
```

**Features**:
- 10px border-radius
- Soft border color
- Blue focus state with glow
- Disabled state styling

#### Form Section (Multiple Groups)
```html
<div class="form-section">
    <h4 class="form-section-title">Section Title</h4>
    <div class="form-row">
        <div class="form-group">...</div>
        <div class="form-group">...</div>
    </div>
</div>
```

### Tables

```html
<div class="table-container">
    <table>
        <thead>
            <tr>
                <th>Header 1</th>
                <th>Header 2</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Data 1</td>
                <td>Data 2</td>
            </tr>
        </tbody>
    </table>
</div>
```

**Features**:
- Clean header styling
- Row hover highlighting
- Responsive scrolling container

### Alerts

```html
<!-- Success Alert -->
<div class="alert alert-success">
    <i class="fas fa-check-circle"></i>
    <span>Success message here</span>
</div>

<!-- Error Alert -->
<div class="alert alert-error">
    <i class="fas fa-exclamation-circle"></i>
    <span>Error message here</span>
</div>

<!-- Warning Alert -->
<div class="alert alert-warning">
    <i class="fas fa-warning"></i>
    <span>Warning message here</span>
</div>

<!-- Info Alert -->
<div class="alert alert-info">
    <i class="fas fa-info-circle"></i>
    <span>Info message here</span>
</div>
```

**Features**:
- Color-coded backgrounds
- Icon support
- Slide-down animation
- Subtle borders

### Badges

```html
<!-- Primary Badge -->
<span class="badge badge-primary">Primary</span>

<!-- Success Badge -->
<span class="badge badge-success">Active</span>

<!-- Warning Badge -->
<span class="badge badge-warning">Pending</span>

<!-- Error Badge -->
<span class="badge badge-error">Error</span>

<!-- Secondary Badge -->
<span class="badge badge-secondary">Secondary</span>
```

**Features**:
- Pill-shaped (20px border-radius)
- Color-coded backgrounds
- 12px font size
- Icon support

### Dashboard Cards

For displaying statistics and key metrics:

```html
<div class="dashboard-card">
    <div class="stat-label">Total Systems</div>
    <div class="stat-value">42</div>
    <div class="stat-trend positive">
        <i class="fas fa-arrow-up"></i> 12% increase
    </div>
</div>
```

**Features**:
- Gradient background
- Top border accent (animated on hover)
- Lift animation on hover
- Large value display

### Status Indicators

```html
<!-- In Table -->
<span class="table-status status-active">
    <i class="fas fa-circle"></i> Active
</span>

<span class="table-status status-inactive">
    <i class="fas fa-circle"></i> Inactive
</span>

<span class="table-status status-pending">
    <i class="fas fa-clock"></i> Pending
</span>
```

### Empty States

```html
<div class="empty-state">
    <div class="empty-state-icon">
        <i class="fas fa-inbox"></i>
    </div>
    <h4 class="empty-state-title">No items found</h4>
    <p class="empty-state-text">Try adjusting your filters</p>
    <button class="btn btn-primary">Create New</button>
</div>
```

### Modals

```html
<div class="modal-overlay">
    <div class="modal">
        <div class="modal-header">
            <h3 class="modal-title">Modal Title</h3>
            <button class="modal-close">&times;</button>
        </div>
        <div class="modal-body">
            <!-- Content here -->
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary">Cancel</button>
            <button class="btn btn-primary">Confirm</button>
        </div>
    </div>
</div>
```

---

## Layout Structure

### Main Container (Dashboard)
```
┌──────────────────────────────────────────────┐
│              Header (top)                     │
├──────────┬──────────────────────────────────┤
│          │                                    │
│ Sidebar  │    Main Content Area             │
│ (Fixed)  │    (Scrollable)                  │
│          │                                    │
│          │                                    │
├──────────┴──────────────────────────────────┤
│              Footer                          │
└──────────────────────────────────────────────┘
```

### Sidebar (20% width, fixed)
- Dark background (#1F2937)
- Logo/branding at top
- Navigation menu items
- Profile section
- Logout button
- Version info

### Content Area (80% width, light background)
- Header with title and user info
- Main dashboard/page content
- Footer with links

### Mobile Layout
- Sidebar becomes fixed drawer (slide from left)
- Hamburger menu toggle
- Full-width content
- Touch-optimized spacing

---

## Spacing & Padding

| Size | Value | Usage |
|------|-------|-------|
| **xs** | 4px | Micro-interactions |
| **sm** | 8px | Small gaps |
| **md** | 12px | Standard gaps |
| **lg** | 16px | Component padding |
| **xl** | 20px | Container padding |
| **2xl** | 24px | Section padding |
| **3xl** | 32px | Large section padding |
| **4xl** | 48px | Page sections |
| **5xl** | 60px | Hero sections |

---

## Shadows

| Shadow | Value | Usage |
|--------|-------|-------|
| **sm** | 0 1px 2px rgba(0,0,0,0.05) | Subtle depth |
| **md** | 0 4px 6px -1px rgba(0,0,0,0.1) | Card hover |
| **lg** | 0 10px 15px -3px rgba(0,0,0,0.1) | Elevated cards |
| **xl** | 0 20px 25px -5px rgba(0,0,0,0.1) | Modals |
| **glow** | 0 0 20px rgba(56,189,248,0.3) | Blue accent glow |

---

## Transitions

| Speed | Duration | Usage |
|-------|----------|-------|
| **fast** | 0.2s | Hover effects |
| **base** | 0.3s | Standard animations |
| **slow** | 0.5s | Complex animations |

All transitions use: `cubic-bezier(0.4, 0, 0.2, 1)` for smooth, natural feel.

---

## Animation Examples

### Button Hover
```css
.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 24px rgba(56, 189, 248, 0.4);
}
```

### Card Hover
```css
.card:hover {
    border-color: var(--color-accent-blue);
    box-shadow: var(--shadow-md);
}
```

### Navigation Link Hover
```css
.nav-link:hover {
    background: rgba(255, 255, 255, 0.12);
    color: rgba(255, 255, 255, 1);
    transform: translateX(4px);
}
```

---

## Responsive Breakpoints

| Breakpoint | Width | Device |
|-----------|-------|--------|
| **xs** | 0px | Mobile |
| **sm** | 640px | Small tablet |
| **md** | 768px | Tablet |
| **lg** | 1024px | Desktop |
| **xl** | 1280px | Large desktop |

---

## Usage Guide

### In Blade Templates

```blade
<!-- Card with content -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">My Card</h3>
    </div>
    <div class="card-content">
        <p>Card content goes here</p>
    </div>
</div>

<!-- Button with icon -->
<button class="btn btn-primary">
    <i class="fas fa-plus"></i> Create New
</button>

<!-- Alert message -->
<div class="alert alert-success">
    <i class="fas fa-check-circle"></i>
    <span>Operation completed successfully!</span>
</div>

<!-- Dashboard grid -->
<div class="dashboard-grid">
    <div class="dashboard-card">
        <div class="stat-label">Total Users</div>
        <div class="stat-value">256</div>
    </div>
    <!-- More cards -->
</div>
```

### In Inline Styles

```blade
<!-- Using CSS variables -->
<div style="background: var(--color-white); border: 1px solid var(--color-border); border-radius: 14px; padding: 24px;">
    Content with modern styling
</div>
```

---

## Best Practices

1. **Use CSS Classes** - Prefer predefined classes over inline styles
2. **Maintain Consistency** - Use the design system colors throughout
3. **Accessibility** - Ensure sufficient color contrast (WCAG AA minimum)
4. **Spacing** - Use consistent spacing units from the scale
5. **Icons** - Always use FontAwesome icons (`fas fa-*`)
6. **Focus States** - All interactive elements must have clear focus states
7. **Animations** - Keep animations subtle and purposeful
8. **Loading States** - Always provide feedback for long operations
9. **Error Handling** - Use semantic colors for errors and warnings
10. **Mobile First** - Design for mobile, enhance for desktop

---

## File Structure

```
resources/
├── css/
│   ├── app.css                    # Main Tailwind + modern styles
│   └── modern-design-system.css   # Component library
├── views/
│   ├── app.blade.php              # Main layout
│   ├── dashboard.blade.php        # Dashboard view
│   ├── settings.blade.php         # Settings view
│   ├── admin/
│   │   ├── users.blade.php
│   │   ├── settings.blade.php
│   │   └── ...
│   └── ...
└── js/
    └── app.js                     # JavaScript interactions
```

---

## Customization

To customize colors globally, update the CSS variables in `resources/css/modern-design-system.css`:

```css
:root {
    --color-accent-blue: #3B82F6;  /* Change primary blue */
    --color-accent-cyan: #06B6D4;  /* Change accent cyan */
    --color-sidebar: #1E293B;      /* Change sidebar color */
    /* ... other variables */
}
```

---

## Support & Documentation

- **Design Tool**: Figma (if design files exist)
- **Icons**: [FontAwesome v6.4](https://fontawesome.com)
- **Colors**: See color palette section above
- **Typography**: Inter Font from Google Fonts

---

## Version
- **AtGlance Design System v2.0**
- **Last Updated**: May 2026
- **Status**: Production Ready

