# DevLoop — UI Design System

This document describes the visual design language, color system, typography, component patterns, and interaction principles that shape DevLoop's user interface.

---

## Design Philosophy

DevLoop's interface is built around three guiding principles:

1. **Dark-first** — The entire application uses a dark color scheme. Dark interfaces reduce eye strain during extended coding sessions and align with the aesthetic preferences of most developer tools.

2. **Information density** — Screens display meaningful data without unnecessary whitespace or decorative filler. Dashboards surface actionable metrics. Lists show relevant metadata inline.

3. **Minimal friction** — Common actions (changing task status, pinning a note, toggling priority) happen inline or via dropdowns rather than requiring navigation to a separate edit page.

---

## Color System

The color palette is defined in `tailwind.config.js` as semantic design tokens rather than raw color values. Every color has a purpose.

### Background & Surface

| Token | Hex | Usage |
|-------|-----|-------|
| `background` | `#0A0A0F` | Page background — near-black with a subtle blue undertone |
| `surface` | `#13131A` | Cards, modals, form containers |
| `surface-hover` | `#1A1A24` | Hover state for interactive surface elements |
| `border` | `#2A2A3C` | Default borders — visible without being harsh |
| `border-hover` | `#3A3A50` | Focus and hover state for borders |

### Text

| Token | Hex | Usage |
|-------|-----|-------|
| `primary-text` | `#F5F5F7` | Headings, primary content |
| `secondary-text` | `#9CA3AF` | Descriptions, metadata, labels |
| `tertiary-text` | `#6B7280` | Placeholders, disabled text |

### Brand Colors

| Token | Hex | Usage |
|-------|-----|-------|
| `primary` | `#6366F1` (Indigo 500) | Buttons, links, active states |
| `primary-hover` | `#818CF8` (Indigo 400) | Hover state for primary elements |
| `primary-muted` | `rgba(99, 102, 241, 0.1)` | Subtle backgrounds, badges |
| `accent` | `#8B5CF6` (Violet 500) | Secondary emphasis, gradients |
| `accent-hover` | `#A78BFA` (Violet 400) | Hover state for accent elements |
| `teal` | `#14B8A6` | Interactive accents, success indicators |
| `teal-hover` | `#2DD4BF` | Hover state for teal elements |

### Status Colors

Task statuses, priorities, and project states use contextual colors:

| Status | Color Approach |
|--------|---------------|
| Todo | Neutral gray |
| In Progress | Primary indigo |
| Review | Amber/yellow |
| Done | Green/teal |
| Low priority | Gray |
| Medium priority | Blue |
| High priority | Orange |
| Urgent priority | Red |

---

## Typography

### Font Families

| Family | Usage | Source |
|--------|-------|--------|
| **Inter** | All UI text — headings, body, labels, buttons | Google Fonts |
| **JetBrains Mono** | Code snippets, monospaced content | Google Fonts |

Both are loaded via Google Fonts CDN and configured as the default sans-serif and monospace families in `tailwind.config.js`.

### Scale

The type scale follows Tailwind's default sizing. Key conventions:

- Page headings: `text-2xl` or `text-3xl` with `font-bold`
- Section headings: `text-lg` with `font-semibold`
- Body text: `text-sm` or `text-base`
- Metadata and labels: `text-xs` or `text-sm` in `secondary-text`

---

## Component Patterns

### Cards

Content is organized into card-like containers with the `surface` background, `border` outline, and rounded corners (`rounded-xl`). Cards use a consistent `p-6` padding.

### Buttons

Three button tiers:

| Type | Appearance | Usage |
|------|-----------|-------|
| Primary | `primary` background, white text | Main actions (Create, Save) |
| Secondary | `surface` background, `border` outline | Cancel, secondary actions |
| Danger | Red background, white text | Destructive actions (Delete) |

### Modals

Modals use a semi-transparent backdrop with a centered `surface` container. They include a title, body content, and a footer with action buttons separated by a `border-t` divider.

### Form Inputs

Text inputs use transparent backgrounds (`bg-background`), `border` outlines, and `primary-text` for input text. Focus states use `primary` colored ring and border via Tailwind's `focus:ring` utilities.

### Stat Cards

Dashboard statistics use the `x-stat-card` component — a compact card displaying a label, value, and optional trend indicator.

### Dropdowns

Inline dropdown menus for quick actions (status changes, priority changes, assignee changes) are built with Alpine.js `x-data`/`x-show` for toggle behavior.

---

## Animations

### Gradient Animation

A custom `gradient-x` animation is defined for decorative header elements. It shifts a linear gradient horizontally over a 3-second cycle.

### Micro-interactions

- **Hover transitions** — Buttons and cards use `transition-colors duration-200` for smooth color changes.
- **Modal transitions** — Modals fade and scale in using Alpine.js transitions.
- **Kanban drag** — SortableJS provides visual drag handles and placeholder positioning during card moves.

---

## Responsive Design

The layout uses Tailwind's responsive breakpoints:

| Breakpoint | Width | Usage |
|------------|-------|-------|
| `sm` | 640px+ | Single-column to two-column transitions |
| `md` | 768px+ | Sidebar visibility, form layouts |
| `lg` | 1024px+ | Full dashboard grid, Kanban columns |
| `xl` | 1280px+ | Maximum content width |

The sidebar navigation collapses on smaller viewports. Kanban columns stack vertically on mobile and display side-by-side on desktop.

---

## Icons

DevLoop uses **Heroicons** (the icon set from the Tailwind CSS team) rendered as inline SVG within Blade templates. Icons use `currentColor` to inherit the text color of their parent element, ensuring they adapt to the dark theme without hardcoded colors.
