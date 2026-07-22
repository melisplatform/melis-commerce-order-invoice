/** Rejoue les endpoints legacy de public/js/meliscommerceorderinvoice.js (même session/cookie),
 *  sans toucher au legacy. Partagé par OrderRowButton (liste des commandes) et InvoicesTab
 *  (onglet Invoices de la commande). */
export const INVOICE_BASE = '/melis/MelisCommerceOrderInvoice/MelisCommerceOrderInvoice'

export interface InvoiceRow { id: number; dateGenerated: string }

export async function fetchLatestInvoiceId(orderId: number): Promise<number | null> {
  const res = await fetch(`${INVOICE_BASE}/getOrderLatestInvoiceId`, {
    method: 'POST', credentials: 'same-origin',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: `orderId=${encodeURIComponent(String(orderId))}`,
  })
  if (!res.ok) return null
  const json = await res.json().catch(() => null)
  return json?.latestInvoiceId || null
}

export async function downloadInvoiceFile(invoiceId: number): Promise<{ blob: Blob; fileName: string }> {
  const res = await fetch(`${INVOICE_BASE}/getOrderInvoice`, {
    method: 'POST', credentials: 'same-origin',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: `invoiceId=${encodeURIComponent(String(invoiceId))}`,
  })
  if (!res.ok) throw new Error('download failed')
  const fileName = res.headers.get('fileName') || `invoice-${invoiceId}.pdf`
  return { blob: await res.blob(), fileName }
}

export function triggerDownload(blob: Blob, fileName: string) {
  const url = URL.createObjectURL(blob)
  const a = document.createElement('a')
  a.href = url
  a.download = fileName
  document.body.appendChild(a)
  a.click()
  a.remove()
  URL.revokeObjectURL(url)
}

export async function fetchOrderInvoiceList(orderId: number): Promise<InvoiceRow[]> {
  const res = await fetch(`${INVOICE_BASE}/getOrderInvoiceList`, {
    method: 'POST', credentials: 'same-origin',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: `draw=1&start=0&length=100&orderId=${encodeURIComponent(String(orderId))}`,
  })
  if (!res.ok) throw new Error('list failed')
  const json = await res.json().catch(() => null)
  const rows = (json?.data ?? []) as Array<{ ordin_id: number; ordin_date_generated: string }>
  return rows.map((r) => ({ id: r.ordin_id, dateGenerated: r.ordin_date_generated }))
}

export async function regenerateOrderInvoice(orderId: number): Promise<number | null> {
  const res = await fetch(`${INVOICE_BASE}/generateOrderInvoice`, {
    method: 'POST', credentials: 'same-origin',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: `orderId=${encodeURIComponent(String(orderId))}`,
  })
  if (!res.ok) throw new Error('generate failed')
  const json = await res.json().catch(() => null)
  return json?.id || null
}
