import { useEffect, useState, useRef } from 'react'
import { AnnouncementPopup, dismiss, dismissAll, getVisibleAnnouncementPopups } from '../../core/announcement'

interface UseAnnouncementPopupsReturn {
  current: AnnouncementPopup | null
  queue: AnnouncementPopup[]
  currentIndex: number
  next: () => void
  prev: () => void
  finish: () => void
  finishAll: () => Promise<void>
  close: () => void
}

export function useAnnouncementPopups(): UseAnnouncementPopupsReturn {
  const [queue, setQueue] = useState<AnnouncementPopup[]>([])
  const [currentIndex, setCurrentIndex] = useState(0)
  const initialized = useRef(false)

  useEffect(() => {
    if (initialized.current) return
    initialized.current = true

    getVisibleAnnouncementPopups()
      .then((popups) => setQueue(popups))
      .catch(() => setQueue([]))
  }, [])

  const current = queue[currentIndex] ?? null

  // Next : dismiss la popup courante (vue), puis avance
  const next = () => {
    if (currentIndex < queue.length - 1) {
      dismiss(queue[currentIndex])
      setCurrentIndex((i) => i + 1)
    }
  }

  const prev = () => {
    if (currentIndex > 0) {
      setCurrentIndex((i) => i - 1)
    }
  }

  // Terminer (dernière popup) : dismiss la courante + ferme
  const finish = () => {
    if (current) {
      dismiss(current)
    }
    setQueue([])
  }

  // Do not display anymore : dismissAll + ferme
  const finishAll = async () => {
    await dismissAll()
    setQueue([])
  }

  // Croix : ferme sans rien dismiss — au reload tout réapparaît
  const close = () => setQueue([])

  return { current, queue, currentIndex, next, prev, finish, finishAll, close }
}