import { AnnouncementPopup } from '../domain/AnnouncementPopup'

export async function fetchActiveAnnouncementPopups(): Promise<AnnouncementPopup[]> {
  const response = await fetch('/api/announcement-popups/active')

  if (!response.ok) {
    throw new Error('Failed to fetch announcement popups')
  }

  return response.json()
}
