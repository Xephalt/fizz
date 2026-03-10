import { AnnouncementPopup } from '../domain/AnnouncementPopup'
import { fetchActiveAnnouncementPopups } from '../infrastructure/AnnouncementPopupApi'

const DISMISSED_KEY_PREFIX = 'announcement_dismissed_'

export function isDismissed(id: string): boolean {
  return localStorage.getItem(`${DISMISSED_KEY_PREFIX}${id}`) === 'true'
}

export function dismiss(id: string): void {
  localStorage.setItem(`${DISMISSED_KEY_PREFIX}${id}`, 'true')
}

export async function dismissAll(): Promise<void> {
  const all = await fetchActiveAnnouncementPopups()
  all.forEach((popup) => dismiss(popup.id))
}

export async function getVisibleAnnouncementPopups(): Promise<AnnouncementPopup[]> {
  const all = await fetchActiveAnnouncementPopups()
  // Le back renvoie déjà trié par priority ASC
  // On filtre côté front ceux déjà vus sur ce navigateur
  return all.filter((popup) => !isDismissed(popup.id))
}
