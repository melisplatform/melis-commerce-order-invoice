import { useEffect, useState } from 'react'
import { fetchOrderInvoiceList, regenerateOrderInvoice, downloadInvoiceFile, triggerDownload, type InvoiceRow } from './invoiceApi'

function currentLang(): 'fr' | 'en' {
  const l = (document.documentElement.lang || 'fr').toLowerCase()
  return l.startsWith('fr') ? 'fr' : 'en'
}

const STR = {
  fr: {
    regenerate: 'Régénérer la facture', col_id: 'ID', col_date: 'Date', col_action: 'Action',
    empty: 'Aucune facture pour cette commande.', loading: 'Chargement…', download: 'Télécharger',
    fail_generate: 'La génération de la facture a échoué.', fail_download: 'Le téléchargement a échoué.',
  },
  en: {
    regenerate: 'Regenerate Invoice', col_id: 'ID', col_date: 'Date', col_action: 'Action',
    empty: 'No invoice for this order.', loading: 'Loading…', download: 'Download',
    fail_generate: 'Invoice generation failed.', fail_download: 'Invoice download failed.',
  },
}

/** Styles inline reprenant les valeurs de melis-commerce/ui-react/src/shared/styles.ts —
 *  cette brique est un bundle Vite séparé, elle ne peut pas importer les sources de l'hôte. */
const card = { border: '1px solid var(--color-border)', background: 'var(--color-card)', borderRadius: 12, boxShadow: '0 1px 2px rgba(0,0,0,.04)', padding: 28 } as const
const th = { textAlign: 'left' as const, padding: '10px 16px', fontSize: 11, fontWeight: 600, textTransform: 'uppercase' as const, letterSpacing: '.04em', color: 'var(--color-muted-foreground)', whiteSpace: 'nowrap' as const }
const td = { padding: '10px 16px', fontSize: 14, color: 'var(--color-foreground)', borderTop: '1px solid var(--color-border)' } as const
const btnPrimary = { display: 'inline-flex', alignItems: 'center', gap: 6, height: 36, padding: '0 14px', borderRadius: 8, border: 0, background: 'var(--color-primary)', color: 'var(--color-primary-foreground,#fff)', fontSize: 14, fontWeight: 500, cursor: 'pointer' } as const

function fmtDate(v: string): string {
  const d = new Date(v.replace(' ', 'T'))
  if (isNaN(d.getTime())) return v
  return d.toLocaleString(currentLang() === 'fr' ? 'fr-FR' : 'en-GB')
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

/** Onglet "Invoices" de la commande (melis-commerce) — exposé sur
 *  `window.MelisCommerceOrderInvoiceBrick.InvoicesTab`, monté par melis-commerce
 *  uniquement si ce module est actif (voir shared/externalBricks.ts côté hôte). */
export default function InvoicesTab({ orderId }: { orderId: number }) {
  const t = STR[currentLang()]
  const [rows, setRows] = useState<InvoiceRow[]>([])
  const [loading, setLoading] = useState(true)
  const [regenerating, setRegenerating] = useState(false)
  const [downloadingId, setDownloadingId] = useState<number | null>(null)

  function refresh() {
    setLoading(true)
    fetchOrderInvoiceList(orderId).then(setRows).catch(() => setRows([])).finally(() => setLoading(false))
  }
  useEffect(refresh, [orderId])

  async function regenerate() {
    if (regenerating) return
    setRegenerating(true)
    try {
      await regenerateOrderInvoice(orderId)
      refresh()
    } catch {
      window.alert(t.fail_generate)
    } finally {
      setRegenerating(false)
    }
  }

  async function download(invoiceId: number) {
    if (downloadingId) return
    setDownloadingId(invoiceId)
    try {
      const { blob, fileName } = await downloadInvoiceFile(invoiceId)
      triggerDownload(blob, fileName)
    } catch {
      window.alert(t.fail_download)
    } finally {
      setDownloadingId(null)
    }
  }

  return (
    <div style={card}>
      <div style={{ display: 'flex', justifyContent: 'flex-end', marginBottom: 16 }}>
        <button style={{ ...btnPrimary, opacity: regenerating ? 0.6 : 1 }} onClick={regenerate} disabled={regenerating}>
          {regenerating ? '…' : t.regenerate}
        </button>
      </div>
      <table style={{ width: '100%', borderCollapse: 'collapse' }}>
        <thead>
          <tr>
            <th style={th}>{t.col_id}</th>
            <th style={th}>{t.col_date}</th>
            <th style={{ ...th, textAlign: 'right' }}>{t.col_action}</th>
          </tr>
        </thead>
        <tbody>
          {loading ? (
            <tr><td colSpan={3} style={{ ...td, textAlign: 'center', padding: '28px 16px', color: 'var(--color-muted-foreground)' }}>{t.loading}</td></tr>
          ) : rows.length === 0 ? (
            <tr><td colSpan={3} style={{ ...td, textAlign: 'center', padding: '28px 16px', color: 'var(--color-muted-foreground)' }}>{t.empty}</td></tr>
          ) : rows.map((r) => (
            <tr key={r.id}>
              <td style={td}>{r.id}</td>
              <td style={td}>{fmtDate(r.dateGenerated)}</td>
              <td style={{ ...td, textAlign: 'right' }}>
                <button
                  onClick={() => download(r.id)}
                  disabled={downloadingId === r.id}
                  title={t.download}
                  style={{
                    display: 'inline-flex', alignItems: 'center', justifyContent: 'center', width: 28, height: 28, borderRadius: 6,
                    border: '1px solid var(--color-primary, #3b82f6)', background: 'transparent', color: 'var(--color-primary, #3b82f6)',
                    cursor: downloadingId === r.id ? 'default' : 'pointer', padding: 0, opacity: downloadingId === r.id ? 0.5 : 1,
                  }}>
                  <DownloadIcon />
                </button>
              </td>
            </tr>
          ))}
        </tbody>
      </table>
    </div>
  )
}
