import React from 'react'
import { useAnnouncementPopups } from '../../hooks/announcements/useAnnouncementPopups'
import { AnnouncementPopupModal } from './AnnouncementPopupModal'

interface Props {
  children: React.ReactNode
}

export function AnnouncementPopupProvider({ children }: Props) {
  const { current, queue, currentIndex, next, prev, finish, finishAll } = useAnnouncementPopups()

  return (
    <>
      {children}
      {current && queue.length > 0 && (
        <AnnouncementPopupModal
          popup={current}
          currentIndex={currentIndex}
          total={queue.length}
          onNext={next}
          onPrev={prev}
          onFinish={finish}
          onFinishAll={finishAll}
        />
      )}
    </>
  )
}
