import { AnnouncementDismissalRepository } from '../domain/AnnouncementDismissalRepository'

const DISMISSED_KEY_PREFIX = 'announcement_dismissed_'

export const localStorageAnnouncementDismissalRepository: AnnouncementDismissalRepository = {
  isDismissed(id: string): boolean {
    return localStorage.getItem(`${DISMISSED_KEY_PREFIX}${id}`) === 'true'
  },

  dismiss(id: string): void {
    localStorage.setItem(`${DISMISSED_KEY_PREFIX}${id}`, 'true')
  },

  dismissAll(ids: string[]): void {
    ids.forEach((id) => localStorage.setItem(`${DISMISSED_KEY_PREFIX}${id}`, 'true'))
  },
}
