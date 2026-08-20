---
title: MelisCommerceOrderInvoice module — React back-office
package: melisplatform/melis-commerce-order-invoice
doc_type: module-documentation-react
audience: [users, developers, ai]
language: en
module_version: unversioned
last_reviewed: 2026-08-20
maintainer: Melis Technology
keywords: [react, brick, back-office, commerce, order, invoice, pdf, download, extension-point, host-injection, meliscommerce, html2pdf]
related_docs: [../../../melis-commerce/etc/MelisAI/doc/MelisCommerce-react.md, ../../../melis-commerce/etc/MelisAI/doc/MelisCommerce.md]
---

# MelisCommerceOrderInvoice (React back-office) — Functional & Technical Documentation (for AI)

> **What this is.** MelisCommerceOrderInvoice adds **PDF invoices** to commerce orders — it
> has **no tool of its own**. In the new React back-office (`/melis-react`) it plugs **two
> pieces** into the **MelisCommerce → Orders** tool: an **Invoices tab** in the order editor
> (list invoices, regenerate, download) and a small **download-invoice button** on each row
> of the order list. Its brick is an **extension (host-injection) brick**: it registers two
> React components on `window` that the MelisCommerce Orders tool consumes *only if this
> module is active* — it never renders a page, a route or a sidebar entry. This document
> covers it **in the React UI**. For the invoice data model, PDF template, generation
> listeners and services see the [legacy doc](./MelisCommerceOrderInvoice.md) *(not yet
> written — defer to the source and the [MelisCommerce legacy doc](../../../melis-commerce/etc/MelisAI/doc/MelisCommerce.md))*.
>
> **No React screenshots** are available for this module yet — this doc has no image index.
>
> **How this document is organised — two clearly separated parts:**
> - **[Part A — Functional Guide](#part-a--functional-guide)** — for everyday users (and the
>   chat assistant) using the React back-office. Plain language.
> - **[Part B — Technical Reference](#part-b--technical-reference)** — for developers and AI
>   building inside the React UI, with code (brick manifest, extension points, endpoints).
>
> **Audience**: consumed by the **MelisAI** MCP. **Status**: reviewed 2026-08-20.

---

## 0. Where this lives in the React back-office — read this first

**Brick kind: extension / host-injection brick.** Unlike a native tool (its own React page
and `react-api` endpoints) or an iframe brick (a legacy tool shown in the shell), this
module's brick **draws no page and adds no menu entry**. Its `brick.manifest.json` declares
`route`, `label`, `forwardKey` and `melisKey` all as **`null`** — the only real field is
`entry: "brick.js"`. It is discovered and background-loaded like any other brick, and on
load it publishes two React components on a `window` global that the **MelisCommerce Orders**
tool reads back:

1. an **`InvoicesTab`** component — the *Invoices* tab of the order editor, and
2. an **`OrderRowButton`** component — the per-row invoice-download button in the order list.

Because it has no route, there is **nothing to navigate to** and **no sidebar section** —
you meet it *inside the MelisCommerce Orders tool*:

- **Orders list** (MelisCommerce → Orders) → a small **download** button on each order row.
- **Order editor** (open an order) → an **Invoices** tab (only on an existing order).

**Activation-gated by presence of the brick.** The tab and the button appear **iff the
`MelisCommerceOrderInvoice` module is active** — MelisCommerce polls for
`window.MelisCommerceOrderInvoiceBrick` (via its `shared/externalBricks.ts`
`useExternalBrickComponent`); if the module is off, the global is never set and both the tab
and the button simply don't render. MelisCommerce stays fully agnostic: it only knows a
global name (`MelisCommerceOrderInvoiceBrick`) and two component keys (`OrderRowButton`,
`InvoicesTab`) — none of the invoice logic.

**No advanced rights *node* of its own in React.** There is **no `config/react.capabilities.php`**
and no `melisReactToolCapabilities` entry — this brick contributes no capability checkbox.
On the server side, the PDF-download action *does* enforce the commerce order-invoice right
`meliscommerce_orders_content_tab_order_invoice` (see §B4). The React "Invoices" tab button
itself is governed by the host Orders tool's `invoices` capability, which MelisCommerce
declares (its `meliscommerce_order_list_page` caps tree lists a `invoices` tab "brought by
MelisCommerceOrderInvoice if active").

Host (its React doc): [MelisCommerce (Orders tool)](../../../melis-commerce/etc/MelisAI/doc/MelisCommerce-react.md).
Legacy commerce doc (data model, services): [MelisCommerce.md](../../../melis-commerce/etc/MelisAI/doc/MelisCommerce.md).

---
---

# PART A — Functional Guide

## A1. What you can do with MelisCommerceOrderInvoice in the new back-office

Two invoice actions, both surfaced inside the **Orders** tool of MelisCommerce:

- **Download an order's invoice from the list** — a one-click **download** button on each
  order row grabs the order's *latest* generated invoice PDF (or tells you there is none).
- **Manage invoices from the order editor** — an **Invoices** tab lists every invoice for
  the order (id + date), lets you **regenerate** the invoice (produces a fresh PDF), and
  **download** any listed invoice.

> Rule of thumb: the invoice is a **generated PDF** stored against the order. The list-row
> button is a shortcut to the latest one; the **Invoices** tab is the full view where you can
> (re)generate and pick any invoice to download.

## A2. Finding it in /melis-react

There is **no dedicated screen**. Everything lives inside **MelisCommerce → Orders**
(`/melis-commerce/order-list`):

- **From the list:** each order row shows a small **download** icon button — click it to get
  that order's latest invoice PDF.
- **From the editor:** open an existing order → the **Invoices** tab (it appears only when
  the module is active, and only on an already-saved order).

If you don't see the button or the tab, check that **MelisCommerceOrderInvoice** is active
(§0), and that your user/role has the Orders **Invoices** right in Users → Rights.

## A3. Key words explained

- **Invoice** — a PDF document generated from an order (stored as `ordin_invoice_pdf`), with
  an id (`ordin_id`) and a generation date (`ordin_date_generated`).
- **Regenerate** — produce a **new** invoice PDF for the order from the current order data
  (uses the `orderinvoicetemplate/default` PDF template). It adds a new invoice row.
- **Latest invoice** — the most recent invoice for an order; that is what the list-row
  button downloads.

For the PDF template, generation listeners and the invoice data model, defer to the
[MelisCommerce legacy doc](../../../melis-commerce/etc/MelisAI/doc/MelisCommerce.md) and this
module's `src/` services.

## A4. The order-list download button

**Where:** MelisCommerce → **Orders** → each order row (`OrderRowButton`).

Clicking it asks the server for the order's **latest invoice id**; if there is one it
downloads the PDF, otherwise it shows *"No invoice available for this order."* The file name
is `[date]-[orderId]-[invoiceId].pdf`, decided server-side.

## A5. The Invoices tab (order editor)

**Where:** MelisCommerce → **Orders** → open an existing order → **Invoices** tab
(`InvoicesTab`).

It shows a table of the order's invoices — **ID**, **Date**, and a per-row **download**
action — plus a **Regenerate Invoice** button:

- **Regenerate Invoice** — generates a new invoice PDF for the order and refreshes the list.
- **Download** (per row) — downloads that specific invoice's PDF.
- If there are none yet, the table reads *"No invoice for this order."*

> **Tip:** the tab is bilingual (FR/EN) — labels follow `document.documentElement.lang`.

## A6. Common tasks — "How do I…?"

- **…download the latest invoice quickly?** Orders list → click the **download** button on
  the order's row (§A4).
- **…create/refresh an order's invoice?** Orders → open the order → **Invoices** tab →
  **Regenerate Invoice** (§A5).
- **…download a specific (older) invoice?** Orders → open the order → **Invoices** tab →
  **download** on that row (§A5).
- **…nothing shows up?** Ensure **MelisCommerceOrderInvoice** is active (§0) and your role
  has the Orders **Invoices** right.

---
---

# PART B — Technical Reference

## B1. React presence at a glance

| Property | Value |
|---|---|
| Brick id (manifest) | `commerce-order-invoice` |
| Manifest | `public/ui-react/brick.manifest.json` → `{ "id": "commerce-order-invoice", "route": null, "label": null, "forwardKey": null, "melisKey": null, "entry": "brick.js" }` |
| Route / label / forwardKey / melisKey | **all `null`** (no page, no menu entry) |
| Registered with host | **not** via `__melisRegisterBrick` — it sets `window.MelisCommerceOrderInvoiceBrick = { OrderRowButton, InvoicesTab }` |
| Extension components consumed by host | `window.MelisCommerceOrderInvoiceBrick.OrderRowButton`, `window.MelisCommerceOrderInvoiceBrick.InvoicesTab` |
| `config/react-api.php` | **none** — the brick calls **legacy MVC actions** of its own controller (see §B3) |
| `config/react.capabilities.php` | **none** — no capability node in this module; the host's `invoices` tab cap lives in MelisCommerce |
| Brick kind | **extension / host-injection** |
| Activation-gated | yes — host polls for `window.MelisCommerceOrderInvoiceBrick`; renders nothing if module inactive |
| Screenshots | none (no `images/react/`) |

## B2. The brick — anatomy

`ui-react/` is a Vite **IIFE** library (`vite.config.ts`: `formats: ['iife']`, name
`MelisCommerceOrderInvoiceBrickBundle`, `entry: 'src/brick.tsx'`, output
`../public/ui-react/brick.js`, `emptyOutDir: false` so the hand-authored
`brick.manifest.json` survives). `react`, `react-dom`, `react/jsx-runtime` and
`react-router-dom` are **externalised to the host globals** (`MelisReact`, `MelisReactDOM`,
`MelisReactJsxRuntime`, `MelisReactRouterDOM`) — the brick reuses the host's React instance.

Entry `src/brick.tsx` (runs immediately on load) — **no `__melisRegisterBrick`**, it only
publishes the two components:

```ts
import OrderRowButton from './OrderRowButton'
import InvoicesTab from './InvoicesTab'

// Host (melis-commerce) polls window.MelisCommerceOrderInvoiceBrick and renders these
// components IF the module is active — nothing otherwise.
window.MelisCommerceOrderInvoiceBrick = { OrderRowButton, InvoicesTab }
```

Components (`ui-react/src/`):

| File | Role |
|---|---|
| `brick.tsx` | Publishes `{ OrderRowButton, InvoicesTab }` on `window.MelisCommerceOrderInvoiceBrick`. No route, no `__melisRegisterBrick`. |
| `OrderRowButton.tsx` | The order-list row button. On click: `fetchLatestInvoiceId(orderId)` → if none, `alert("No invoice available")`; else `downloadInvoiceFile(latestId)` and trigger a browser download. Bilingual (FR/EN) via `document.documentElement.lang`. |
| `InvoicesTab.tsx` | The order-editor **Invoices** tab. Lists invoices (`fetchOrderInvoiceList`), **Regenerate Invoice** (`regenerateOrderInvoice`) then refresh, and per-row **download** (`downloadInvoiceFile` + `triggerDownload`). Inline styles use the host's CSS variables (`--color-*`) — it can't import MelisCommerce's `shared/styles.ts` (separate bundle). |
| `invoiceApi.ts` | The API client (see §B3) — thin wrappers over the module's own **legacy MVC** endpoints. |

Built bundle: `public/ui-react/brick.js` + `public/ui-react/brick.manifest.json`.

## B3. Endpoints the brick calls (it defines no `react-api`)

This brick has **no `config/react-api.php`**. `invoiceApi.ts` re-plays the module's own
**legacy MVC** endpoints (same session cookie, `credentials: 'same-origin'`), all under one
base:

```ts
export const INVOICE_BASE = '/melis/MelisCommerceOrderInvoice/MelisCommerceOrderInvoice'
```

These URLs resolve through this module's back-office route
`application-MelisCommerceOrderInvoice` → child `default` (`/[:controller[/:action]]`) in
`config/module.config.php`, dispatching to
`MelisCommerceOrderInvoice\Controller\MelisCommerceOrderInvoiceController`. (The module also
declares two extra `Literal` routes — `/CommerceOrderInvoice/getOrderLatestInvoiceId` and
`/CommerceOrderInvoice/getInvoice` — but the React brick does **not** use them; it hits the
segment route above.)

| Call (from `invoiceApi.ts`) | HTTP | Controller action | Returns |
|---|---|---|---|
| `POST {INVOICE_BASE}/getOrderLatestInvoiceId` (body `orderId`) | POST form | `getOrderLatestInvoiceIdAction` | JSON `{ latestInvoiceId }` (0 if none) |
| `POST {INVOICE_BASE}/getOrderInvoice` (body `invoiceId`) | POST form | `getOrderInvoiceAction` | the **PDF bytes** (`Content-Type: application/pdf`) + a `fileName` response header |
| `POST {INVOICE_BASE}/getOrderInvoiceList` (body `draw,start,length,orderId`) | POST form | `getOrderInvoiceListAction` | DataTables JSON `{ draw, recordsTotal, recordsFiltered, data: [{ ordin_id, ordin_date_generated }] }` |
| `POST {INVOICE_BASE}/generateOrderInvoice` (body `orderId`) | POST form | `generateOrderInvoiceAction` | JSON `{ id }` (the new invoice id) |

> ⚠ These are **not** the `{ success, data, error }` react-api envelope — they are the raw
> legacy JSON / binary shapes. The client normalises them:
> `fetchOrderInvoiceList` maps `data[]` → `{ id: ordin_id, dateGenerated: ordin_date_generated }`;
> `downloadInvoiceFile` reads the `fileName` **response header** (fallback `invoice-<id>.pdf`)
> and returns the `Blob`.

Example — the Invoices tab loading and regenerating (as `InvoicesTab` does):

```ts
// List
const res = await fetch(`${INVOICE_BASE}/getOrderInvoiceList`, {
  method: 'POST', credentials: 'same-origin',
  headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
  body: `draw=1&start=0&length=100&orderId=${orderId}`,
})
const { data } = await res.json()   // data: [{ ordin_id, ordin_date_generated }]

// Regenerate
const gen = await fetch(`${INVOICE_BASE}/generateOrderInvoice`, {
  method: 'POST', credentials: 'same-origin',
  headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
  body: `orderId=${orderId}`,
})
const { id } = await gen.json()     // the new invoice id
```

Server side, the actions delegate to the Laminas service alias
**`MelisCommerceOrderInvoiceService`** (`MelisCommerceOrderInvoice\Service\MelisCommerceOrderInvoiceService`):
`getOrderLatestInvoiceId`, `getInvoice`, `getOrderInvoiceList`, `generateFileName`, and
`generateOrderInvoice($orderId, 'orderinvoicetemplate/default')`. PDF rendering uses
**`Spipu\Html2Pdf`** (composer `spipu/html2pdf`) over the module's PDF template. The invoice
data model / listeners are in this module's `src/` and the
[MelisCommerce legacy doc](../../../melis-commerce/etc/MelisAI/doc/MelisCommerce.md).

## B4. Capabilities & gating

There is **no `config/react.capabilities.php`** in this module — it declares no capability
node. Gating is two-layered:

- **UI visibility (frontend):** the host renders both the row button and the Invoices tab
  **iff the brick is loaded**, via MelisCommerce's `useExternalBrickComponent('MelisCommerceOrderInvoiceBrick', …)`
  (which polls `window.MelisCommerceOrderInvoiceBrick`). Additionally, the **Invoices tab
  button** in the Orders editor is filtered by the host's `invoices` capability, declared by
  **MelisCommerce** under its `meliscommerce_order_list_page` caps tree (`can('invoices')` in
  `OrderPage.tsx`). So a role without the Orders *Invoices* right won't see the tab even when
  the module is active.

- **API access (server):** the **PDF-download** action (`getOrderInvoiceAction`) enforces the
  commerce order-invoice right server-side:

  ```php
  const TOOL_KEY = 'meliscommerce_orders_content_tab_order_invoice';
  // …
  if (!$melisCoreRights->canAccess(self::TOOL_KEY)) {
      $response->setStatusCode(403); $response->setContent(''); return $response;
  }
  ```

  > ⚠ **Honest gap:** only `getOrderInvoiceAction` (the PDF download) has this
  > `canAccess()` guard. `getOrderLatestInvoiceIdAction`, `getOrderInvoiceListAction` and
  > `generateOrderInvoiceAction` check only that a BO session exists (or nothing at all for
  > generate/list beyond dispatch). Treat the tab/button visibility as **UI hints**, not a
  > hard authorization boundary, except for the PDF download itself.

## B5. Host integration

- **Discovery / load:** standard brick flow — `GET /melis/react-api/react-modules` lists the
  module, the host background-prefetches `brick.js`, and `brick.tsx` runs on load, setting
  `window.MelisCommerceOrderInvoiceBrick = { OrderRowButton, InvoicesTab }` **before** the
  user reaches the Orders tool.
- **Consumption contract:** MelisCommerce's `ui-react/src/shared/externalBricks.ts` exposes
  `useExternalBrickComponent(globalName, key)` — a generic hook that polls
  `window[globalName][key]` every 300 ms until present and returns the component (or `null`).
  The Orders tool (`melis-commerce/ui-react/src/tools/orders/OrderPage.tsx`) uses it twice:
  - **List:** `const OrderInvoiceButton = useExternalBrickComponent<{ orderId: number }>('MelisCommerceOrderInvoiceBrick', 'OrderRowButton')` — rendered per order row with `orderId`.
  - **Editor:** `const InvoicesTabComp = useExternalBrickComponent<{ orderId: number }>('MelisCommerceOrderInvoiceBrick', 'InvoicesTab')` — when non-null, an extra `{ key: 'invoices', label: t('tab_invoices') }` tab is added and `<InvoicesTabComp orderId={orderId} />` is rendered on that tab.
- **Props contract:** both components take a single `{ orderId: number }` prop; the module
  owns everything downstream (fetching, PDF, download).
- **i18n:** each component reads `document.documentElement.lang` for FR/EN strings; nothing
  is passed from the host.
- **No `__melisRegisterBrick`, no route, no menu mapping** — deliberately: this is an
  extension brick, not a native tool.

> **In short:** React owns only the *button + tab* UI; the module's legacy PHP (controller,
> service, PDF template, generation listeners) is untouched and does the real Melis wiring —
> see this module's `src/` and the
> [MelisCommerce legacy doc](../../../melis-commerce/etc/MelisAI/doc/MelisCommerce.md).

## B6. Quick code map

```
melis-commerce-order-invoice/
├─ ui-react/                                  # brick SOURCE (Vite IIFE, React externalised)
│  ├─ vite.config.ts                          # name MelisCommerceOrderInvoiceBrickBundle → public/ui-react/brick.js
│  └─ src/
│     ├─ brick.tsx                            # window.MelisCommerceOrderInvoiceBrick = { OrderRowButton, InvoicesTab }
│     ├─ OrderRowButton.tsx                   # order-list row download button
│     ├─ InvoicesTab.tsx                      # order-editor Invoices tab (list + regenerate + download)
│     └─ invoiceApi.ts                        # legacy MVC client (getOrderLatestInvoiceId / getOrderInvoice / getOrderInvoiceList / generateOrderInvoice)
├─ public/
│  ├─ ui-react/{brick.js, brick.manifest.json}   # BUILD (id: commerce-order-invoice, all other fields null)
│  └─ js/meliscommerceorderinvoice.js         # legacy tool JS (the endpoints the React client re-plays)
├─ config/
│  ├─ module.config.php                       # MVC routes → MelisCommerceOrderInvoiceController; service/table aliases; PDF template map
│  ├─ app.interface.php / app.tools.php       # legacy tool wiring (Orders invoice tab + list)
└─ src/
   ├─ Module.php
   ├─ Controller/MelisCommerceOrderInvoiceController.php   # getOrderInvoice (canAccess meliscommerce_orders_content_tab_order_invoice), getOrderLatestInvoiceId, getOrderInvoiceList, generateOrderInvoice
   ├─ Service/MelisCommerceOrderInvoiceService.php         # invoice generation (Spipu\Html2Pdf), getInvoice, getOrderInvoiceList, generateFileName
   ├─ Model/Tables/MelisCommerceOrderInvoiceTable.php
   └─ Listener/*                              # order-history / order-details invoice data + generate-on-status listeners (see legacy doc)
```

There is **no** `config/react-api.php` and **no** `config/react.capabilities.php` — by
design (this is an extension brick that re-plays its own legacy MVC endpoints, not a native
react-api tool).

---

*Document for AI consumption (MelisAI MCP) — React back-office of
`melisplatform/melis-commerce-order-invoice`. Part A = functional guide for users; Part B =
technical reference with examples for developers/AI. It is an extension brick injecting an
**Invoices** tab + a list-row invoice button into the **MelisCommerce Orders** tool — host
React doc: [MelisCommerce-react.md](../../../melis-commerce/etc/MelisAI/doc/MelisCommerce-react.md);
commerce data model/services: [MelisCommerce.md](../../../melis-commerce/etc/MelisAI/doc/MelisCommerce.md).
Last reviewed 2026-08-20.*
