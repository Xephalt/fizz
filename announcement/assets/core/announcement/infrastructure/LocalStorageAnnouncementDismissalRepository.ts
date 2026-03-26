import { AnnouncementDismissalRepository } from '../domain/AnnouncementDismissalRepository'
import { AnnouncementPopup } from '../domain/AnnouncementPopup'

const DISMISSED_KEY_PREFIX = 'announcement_dismissed_'

function isDismissedPopup(popup: AnnouncementPopup): boolean {
  const stored = localStorage.getItem(`${DISMISSED_KEY_PREFIX}${popup.id}`)
  if (stored === null) return false

  const dismissedAt = parseInt(stored, 10)
  if (isNaN(dismissedAt)) return false

  // Si un force reset a eu lieu après le dismiss, la popup doit réapparaître
  if (popup.forcedResetAt !== null) {
    const forcedResetAtMs = new Date(popup.forcedResetAt).getTime()
    if (dismissedAt < forcedResetAtMs) return false
  }

  // Si une récurrence est définie, vérifier si le TTL est expiré
  if (popup.recurrenceSeconds !== null) {
    const ttlMs = popup.recurrenceSeconds * 1000
    if (Date.now() - dismissedAt > ttlMs) return false
  }

  return true
}

export const localStorageAnnouncementDismissalRepository: AnnouncementDismissalRepository = {
  isDismissed(popup: AnnouncementPopup): boolean {
    return isDismissedPopup(popup)
  },

  dismiss(popup: AnnouncementPopup): void {
    localStorage.setItem(`${DISMISSED_KEY_PREFIX}${popup.id}`, Date.now().toString())
  },

  dismissAll(popups: AnnouncementPopup[]): void {
    const now = Date.now().toString()
    popups.forEach((popup) =>
      localStorage.setItem(`${DISMISSED_KEY_PREFIX}${popup.id}`, now),
    )
  },
}
