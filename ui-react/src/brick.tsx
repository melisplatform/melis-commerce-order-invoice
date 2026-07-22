import OrderRowButton from './OrderRowButton'
import InvoicesTab from './InvoicesTab'

/**
 * Brick entry point. This module has no dedicated route/page — it only exposes
 * reusable components for OTHER bricks to consume conditionally (see melis-commerce's
 * `shared/externalBricks.ts`, which polls for `window.MelisCommerceOrderInvoiceBrick`):
 * `OrderRowButton` in the Orders list, `InvoicesTab` in the Orders edit screen — both
 * render IF this module is active/loaded, nothing otherwise.
 */
declare global {
  interface Window {
    MelisCommerceOrderInvoiceBrick?: { OrderRowButton: unknown; InvoicesTab: unknown }
  }
}

window.MelisCommerceOrderInvoiceBrick = { OrderRowButton, InvoicesTab }
