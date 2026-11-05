---
name: mayhen-payments
description: VietQR, SePay, bank transfer order lifecycle, webhook signature verification, idempotency.
---

# mayhen-payments

## Applicable Paths
`app/Services/Payments/, app/Http/Controllers/Client/CheckoutController.php`

## Purpose
Handle subscription and invitation purchase transactions reliably.

## Mandatory Rules
- Decouple Order Status from Payment Status.
- Strictly verify webhook signatures; never activate entitlements without verified payment.
- Enforce idempotency: prevent double processing of webhooks.
- Never confuse wedding gift QR (bride/groom account) with platform payment QR.

## Definition of Done
- Verified against Master Prompt requirements.
- Code is clean, well-documented, tested, and formatted.
