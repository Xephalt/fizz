import React from 'react';
import BasicModal, { ModalCloseButton } from './BasicModal';
import { AnnouncementPopup } from '../../core/announcement';
import LanguageDropdown from './LanguageDropdown';
import { useTranslation } from 'react-i18next';

interface Props {
  popup: AnnouncementPopup;
  currentIndex: number;
  total: number;
  onNext: () => void;
  onPrev: () => void;
  onFinish: () => void;
  onFinishAll: () => Promise<void>;
}

export function AnnouncementPopupModal({
  popup,
  currentIndex,
  total,
  onNext,
  onPrev,
  onFinish,
  onFinishAll,
}: Props) {
  // useTranslation abonne ce composant aux changements de langue i18next.
  // Quand LanguageDropdown appelle i18n.changeLanguage(), le modal re-render automatiquement.
  const { t, i18n } = useTranslation();
  const isFr = i18n.language?.startsWith('fr');

  const isFirst = currentIndex === 0;
  const isLast = currentIndex === total - 1;

  const displayTitle    = isFr && popup.titleFr    ? popup.titleFr    : popup.title;
  const displayContent  = isFr && popup.contentFr  ? popup.contentFr  : popup.content;
  const displayImageUrl = isFr && popup.imageUrlFr ? popup.imageUrlFr : popup.imageUrl;

  return (
    <BasicModal
      open={true}
      onClose={onFinishAll}
      removeButtons={true}
      setOpen={onFinish}
    >
      <div className="flex flex-col h-full">
        {/* Header */}
        <div className="flex items-center justify-between mb-6 relative bg-gray-100 p-4 rounded-md">
          <LanguageDropdown />
          <h2 className="flex-1 mx-4 text-xl font-bold bnpp-color-green text-center">
            {t('CommsGptTips')}
          </h2>
          <ModalCloseButton />
        </div>

        {/* Titre astuce */}
        <h3 className="text-lg font-semibold mb-4">
          {displayTitle}
        </h3>

        {/* Body 60/40 */}
        <div className="flex flex-col md:flex-row flex-1 gap-4 min-h-0">
          <div
            className="
              w-full md:w-3/5
              flex items-center justify-center
              bg-gray-50 rounded-lg overflow-hidden border border-gray-200
              aspect-w-16 aspect-h-9
              max-h-[350px]
            "
          >
            {displayImageUrl ? (
              <img
                src={displayImageUrl}
                alt=""
                className="w-full h-full object-cover"
              />
            ) : (
              <div className="w-full h-full flex items-center justify-center">
                <i className="fa fa-image fa-3x text-gray-300" />
              </div>
            )}
          </div>

          <div className="w-full md:w-2/5 flex flex-col bg-gray-100 p-4 rounded-md overflow-y-auto">
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
              className="text-sm font-medium text-bnpp-color-green hover:underline flex items-center"
              aria-label="Terminer"
            >
              <i className="fa-solid fa-check" />
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
  );
}
