import { AnnouncementPopup } from '../domain/AnnouncementPopup'
import { AnnouncementDismissalRepository } from '../domain/AnnouncementDismissalRepository'
import { fetchActiveAnnouncementPopups } from '../infrastructure/AnnouncementPopupApi'
import { localStorageAnnouncementDismissalRepository } from '../infrastructure/LocalStorageAnnouncementDismissalRepository'

// Composition root léger : on câble l'adapter par défaut ici.
// Si tu veux l'injecter (tests), tu passes un autre repo.
const dismissalRepo: AnnouncementDismissalRepository = localStorageAnnouncementDismissalRepository

export function isDismissed(id: string): boolean {
  return dismissalRepo.isDismissed(id)
}

export function dismiss(id: string): void {
  dismissalRepo.dismiss(id)
}

export async function getVisibleAnnouncementPopups(): Promise<AnnouncementPopup[]> {
  const all = await fetchActiveAnnouncementPopups()
  return all.filter((popup) => !dismissalRepo.isDismissed(popup.id))
}

export async function dismissAll(): Promise<void> {
  const all = await fetchActiveAnnouncementPopups()
  dismissalRepo.dismissAll(all.map((p) => p.id))
}
