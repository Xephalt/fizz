import React from 'react'
import BasicModal from './BasicModal'
import { AnnouncementPopup } from '../../core/announcement'
import { useTranslation } from 'react-i18next'

interface Props {
  popup: AnnouncementPopup
  currentIndex: number
  total: number
  onNext: () => void
  onPrev: () => void
  onFinish: () => void
}

export function AnnouncementPopupModal({ popup, currentIndex, total, onNext, onPrev, onFinish }: Props) {
  const { i18n } = useTranslation()
  const isFr = i18n.language?.startsWith('fr')

  const isFirst = currentIndex === 0
  const isLast = currentIndex === total - 1

  const displayTitle = isFr && popup.titleFr ? popup.titleFr : popup.title
  const displayContent = isFr && popup.contentFr ? popup.contentFr : popup.content
  const displayImageUrl = isFr && popup.imageUrlFr ? popup.imageUrlFr : popup.imageUrl

  return (
    <BasicModal open={true} onClose={onFinish} removeButtons={true} setOpen={onFinish}>
      <div className="flex flex-col h-full">

        {/* Header */}
        <div className="flex items-center justify-center mb-6">
          <h2 className="text-xl font-bold text-bnpp-color-green text-center">
            Astuces et nouveautés CommsGPT !
          </h2>
        </div>

        {/* Titre astuce */}
        <h3 className="text-lg font-semibold mb-4">
          {currentIndex + 1} - {displayTitle}
        </h3>

        {/* Body 60/40 */}
        <div className="flex flex-row flex-1 gap-4 min-h-0">

          {/* Image 60% */}
          <div className="w-3/5 bg-gray-50 rounded-lg overflow-hidden border border-gray-200">
            {displayImageUrl ? (
              <img
                src={displayImageUrl}
                alt=""
                className="w-full h-full object-contain"
              />
            ) : (
              <div className="w-full h-full flex items-center justify-center text-gray-300">
                <i className="fa fa-image fa-3x" />
              </div>
            )}
          </div>

          {/* Description 40% */}
          <div className="w-2/5 flex flex-col">
            <p className="font-bold text-base mb-2">Description</p>
            <div
              className="text-sm text-gray-700 leading-relaxed"
              dangerouslySetInnerHTML={{ __html: displayContent }}
            />
          </div>

        </div>

        {/* Footer */}
        <div className="flex justify-center items-center gap-6 mt-6">
          <button
            onClick={onPrev}
            disabled={isFirst}
            className={`text-gray-500 hover:text-gray-800 transition ${isFirst ? 'invisible' : ''}`}
          >
            <i className="fa fa-chevron-left" />
          </button>

          <span className="text-sm font-medium text-gray-600">
            {currentIndex + 1} / {total}
          </span>

          {isLast ? (
            <button
              onClick={onFinish}
              className="text-sm font-medium text-bnpp-color-green hover:underline"
            >
              Terminer
            </button>
          ) : (
            <button
              onClick={onNext}
              className="text-gray-500 hover:text-gray-800 transition"
            >
              <i className="fa fa-chevron-right" />
            </button>
          )}
        </div>

      </div>
    </BasicModal>
  )
}
