import OrderRowButton from './OrderRowButton'

/**
 * Brick entry point. This module has no dedicated route/page — it only exposes a
 * reusable component for OTHER bricks to consume conditionally (see melis-commerce's
 * `shared/externalBricks.ts`, which polls for `window.MelisCommerceOrderInvoiceBrick`
 * and renders `OrderRowButton` in the Orders list IF this module is active/loaded).
 */
declare global {
  interface Window {
    MelisCommerceOrderInvoiceBrick?: { OrderRowButton: unknown }
  }
}

window.MelisCommerceOrderInvoiceBrick = { OrderRowButton }
