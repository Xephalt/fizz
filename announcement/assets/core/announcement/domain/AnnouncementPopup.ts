export interface AnnouncementPopup {
  id: string
  title: string
  titleFr: string | null
  content: string
  contentFr: string | null
  imageUrl: string | null
  imageUrlFr: string | null
  priority: number
  recurrenceSeconds: number | null
  forcedResetAt: string | null
}
