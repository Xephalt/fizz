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
    return JSON.parse(stored) as DismissalRecord
  } catch {
    // Ancien format (string brute) → on considère comme non-dismissed
    // pour que l'utilisateur revoie la popup une fois après la mise à jour
    return null
  }
}

function buildRecord(popup: AnnouncementPopup): string {
  const record: DismissalRecord = {
    dismissedAt: Date.now(),
    forcedResetAtSnapshot: popup.forcedResetAt,
  }
  return JSON.stringify(record)
}

function isDismissedPopup(popup: AnnouncementPopup): boolean {
  const record = readRecord(popup.id)
  if (record === null) return false

  // Force reset : si forcedResetAt a changé depuis le dernier dismiss → réafficher.
  // On compare les valeurs (strings ou null), pas des timestamps,
  // donc aucune sensibilité aux décalages d'horloge navigateur/serveur.
  if (popup.forcedResetAt !== record.forcedResetAtSnapshot) return false

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
