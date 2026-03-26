import { AnnouncementPopup } from '../domain/AnnouncementPopup'
import { AnnouncementDismissalRepository } from '../domain/AnnouncementDismissalRepository'
import { fetchActiveAnnouncementPopups } from '../infrastructure/AnnouncementPopupApi'
import { localStorageAnnouncementDismissalRepository } from '../infrastructure/LocalStorageAnnouncementDismissalRepository'

// Composition root léger : on câble l'adapter par défaut ici.
// Si tu veux l'injecter (tests), tu passes un autre repo.
const dismissalRepo: AnnouncementDismissalRepository = localStorageAnnouncementDismissalRepository

export function isDismissed(popup: AnnouncementPopup): boolean {
  return dismissalRepo.isDismissed(popup)
}

export function dismiss(popup: AnnouncementPopup): void {
  dismissalRepo.dismiss(popup)
}

export async function getVisibleAnnouncementPopups(): Promise<AnnouncementPopup[]> {
  const all = await fetchActiveAnnouncementPopups()
  return all.filter((popup) => !dismissalRepo.isDismissed(popup))
}

export async function dismissAll(): Promise<void> {
  const all = await fetchActiveAnnouncementPopups()
  dismissalRepo.dismissAll(all)
}
