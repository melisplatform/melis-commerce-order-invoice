import { useState } from 'react'

/** Rejoue le flux 2 étapes de public/js/meliscommerceorderinvoice.js (même session/cookie),
 *  sans toucher au legacy : on appelle simplement les endpoints existants du contrôleur. */
const INVOICE_BASE = '/melis/MelisCommerceOrderInvoice/MelisCommerceOrderInvoice'

function currentLang(): 'fr' | 'en' {
  const l = (document.documentElement.lang || 'fr').toLowerCase()
  return l.startsWith('fr') ? 'fr' : 'en'
}

const STR = {
  fr: { title: 'Télécharger la facture', none: 'Aucune facture disponible pour cette commande.', fail: 'Le téléchargement de la facture a échoué.' },
  en: { title: 'Download invoice', none: 'No invoice available for this order.', fail: 'Invoice download failed.' },
}

async function fetchLatestInvoiceId(orderId: number): Promise<number | null> {
  const res = await fetch(`${INVOICE_BASE}/getOrderLatestInvoiceId`, {
    method: 'POST', credentials: 'same-origin',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: `orderId=${encodeURIComponent(String(orderId))}`,
  })
  if (!res.ok) return null
  const json = await res.json().catch(() => null)
  return json?.latestInvoiceId || null
}

async function downloadInvoiceFile(invoiceId: number): Promise<{ blob: Blob; fileName: string }> {
  const res = await fetch(`${INVOICE_BASE}/getOrderInvoice`, {
    method: 'POST', credentials: 'same-origin',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: `invoiceId=${encodeURIComponent(String(invoiceId))}`,
  })
  if (!res.ok) throw new Error('download failed')
  const fileName = res.headers.get('fileName') || `invoice-${invoiceId}.pdf`
  return { blob: await res.blob(), fileName }
}

function DownloadIcon() {
  return (
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" style={{ width: 15, height: 15 }}>
      <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
      <polyline points="7 10 12 15 17 10" />
      <line x1="12" y1="15" x2="12" y2="3" />
    </svg>
  )
}

/** Bouton "télécharger la facture" pour une ligne de la liste des commandes (melis-commerce).
 *  Exposé sur `window.MelisCommerceOrderInvoiceBrick.OrderRowButton` — melis-commerce reste
 *  agnostique : il ne connaît que ce nom de composant, pas la logique de facturation. */
export default function OrderRowButton({ orderId }: { orderId: number }) {
  const [busy, setBusy] = useState(false)
  const t = STR[currentLang()]

  async function onClick(e: React.MouseEvent) {
    e.stopPropagation()
    if (busy) return
    setBusy(true)
    try {
      const invoiceId = await fetchLatestInvoiceId(orderId)
      if (!invoiceId) { window.alert(t.none); return }
      const { blob, fileName } = await downloadInvoiceFile(invoiceId)
      const url = URL.createObjectURL(blob)
      const a = document.createElement('a')
      a.href = url
      a.download = fileName
      document.body.appendChild(a)
      a.click()
      a.remove()
      URL.revokeObjectURL(url)
    } catch {
      window.alert(t.fail)
    } finally {
      setBusy(false)
    }
  }

  return (
    <button
      onClick={onClick}
      disabled={busy}
      title={t.title}
      style={{
        display: 'inline-flex', alignItems: 'center', justifyContent: 'center', width: 28, height: 28, borderRadius: 6,
        border: '1px solid var(--color-primary, #3b82f6)', background: 'transparent', color: 'var(--color-primary, #3b82f6)',
        cursor: busy ? 'default' : 'pointer', padding: 0, opacity: busy ? 0.5 : 1, marginLeft: 6,
      }}>
      <DownloadIcon />
    </button>
  )
}
