---
name: mayhen-performance
description: Core Web Vitals optimization, responsive images, WebP format, animation FPS budgets.
---

# mayhen-performance

## Applicable Paths
`public/assets/, resources/views/`

## Purpose
Deliver fast page load times and 60fps animations.

## Mandatory Rules
- Target Core Web Vitals (LCP < 2.5s, CLS < 0.1, INP < 200ms).
- Responsive image srcset with WebP fallbacks; never serve multi-megabyte originals directly to phones.
- Debounce scroll and resize listeners; use IntersectionObserver for lazy loading.

## Definition of Done
- Verified against Master Prompt requirements.
- Code is clean, well-documented, tested, and formatted.
