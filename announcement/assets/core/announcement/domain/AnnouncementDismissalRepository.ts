import { AnnouncementPopup } from './AnnouncementPopup'

export interface AnnouncementDismissalRepository {
  isDismissed(popup: AnnouncementPopup): boolean
  dismiss(popup: AnnouncementPopup): void
  dismissAll(popups: AnnouncementPopup[]): void
}
