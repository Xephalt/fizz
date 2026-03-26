import { AnnouncementDismissalRepository } from '../domain/AnnouncementDismissalRepository'
import { AnnouncementPopup } from '../domain/AnnouncementPopup'

const DISMISSED_KEY_PREFIX = 'announcement_dismissed_'

interface DismissalRecord {
  dismissedAt: number      // timestamp ms (Date.now())
  forcedResetAtSnapshot: string | null  // valeur de forcedResetAt au moment du dismiss
}

function readRecord(id: string): DismissalRecord | null {
  const stored = localStorage.getItem(`${DISMISSED_KEY_PREFIX}${id}`)
  if (stored === null) return null
  try {
    const parsed = JSON.parse(stored)
    // Vérification stricte du format attendu :
    // JSON.parse('true')   → true   (boolean, pas d'objet)
    // JSON.parse('123456') → number (ancien format timestamp)
    // Ces deux cas NE lèvent PAS d'exception mais ne sont pas des DismissalRecord valides.
    // Sans cette vérification, record.forcedResetAtSnapshot vaut `undefined`,
    // et `null !== undefined` déclenche un force-reset spurieux à chaque chargement.
    if (typeof parsed !== 'object' || parsed === null || typeof parsed.dismissedAt !== 'number') {
      return null
    }
    return parsed as DismissalRecord
  } catch {
    return null
  }
}

function buildRecord(popup: AnnouncementPopup): string {
  const record: DismissalRecord = {
    dismissedAt: Date.now(),
    forcedResetAtSnapshot: popup.forcedResetAt ?? null,  // undefined → null pour garantir la sérialisation JSON
  }
  return JSON.stringify(record)
}

function isDismissedPopup(popup: AnnouncementPopup): boolean {
  const record = readRecord(popup.id)
  if (record === null) return false

  // Force reset : si forcedResetAt a changé depuis le dernier dismiss → réafficher.
  // On normalise undefined → null des deux côtés pour gérer les anciens records sans la clé.
  const currentForcedReset = popup.forcedResetAt ?? null
  const snapshotForcedReset = record.forcedResetAtSnapshot ?? null
  if (currentForcedReset !== snapshotForcedReset) return false

  // Récurrence : si le TTL est expiré → réafficher
  if (popup.recurrenceSeconds !== null) {
    const ttlMs = popup.recurrenceSeconds * 1000
    if (Date.now() - record.dismissedAt > ttlMs) return false
  }

  return true
}

export const localStorageAnnouncementDismissalRepository: AnnouncementDismissalRepository = {
  isDismissed(popup: AnnouncementPopup): boolean {
    return isDismissedPopup(popup)
  },

  dismiss(popup: AnnouncementPopup): void {
    localStorage.setItem(`${DISMISSED_KEY_PREFIX}${popup.id}`, buildRecord(popup))
  },

  dismissAll(popups: AnnouncementPopup[]): void {
    popups.forEach((popup) =>
      localStorage.setItem(`${DISMISSED_KEY_PREFIX}${popup.id}`, buildRecord(popup)),
    )
  },
}
