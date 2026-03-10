import { useEffect, useState, useRef } from 'react'
import { AnnouncementPopup, dismiss, getVisibleAnnouncementPopups } from '../../core/announcement'

interface UseAnnouncementPopupsReturn {
  current: AnnouncementPopup | null
  queue: AnnouncementPopup[]
  currentIndex: number
  next: () => void
  prev: () => void
  finish: () => void
}

export function useAnnouncementPopups(): UseAnnouncementPopupsReturn {
  const [queue, setQueue] = useState<AnnouncementPopup[]>([])
  const [currentIndex, setCurrentIndex] = useState(0)
  const initialized = useRef(false)

  useEffect(() => {
    if (initialized.current) return
    initialized.current = true

    getVisibleAnnouncementPopups()
      .then((popups) => {
        setQueue(popups)
        // La première popup est visible immédiatement → on la dismiss
        if (popups.length > 0) {
          dismiss(popups[0].id)
        }
      })
      .catch(() => setQueue([]))
  }, [])

  const current = queue[currentIndex] ?? null

  const next = () => {
    if (currentIndex < queue.length - 1) {
      const nextIndex = currentIndex + 1
      // La popup suivante devient visible → on la dismiss
      dismiss(queue[nextIndex].id)
      setCurrentIndex(nextIndex)
    }
  }

  const prev = () => {
    if (currentIndex > 0) {
      setCurrentIndex((i) => i - 1)
    }
  }

  const finish = () => setQueue([])

  return { current, queue, currentIndex, next, prev, finish }
}
