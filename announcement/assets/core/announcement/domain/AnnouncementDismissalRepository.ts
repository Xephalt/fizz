export interface AnnouncementDismissalRepository {
  isDismissed(id: string): boolean
  dismiss(id: string): void
  dismissAll(ids: string[]): void
}
