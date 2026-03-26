import React, { useEffect } from 'react';
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
  onClose: () => void;
}

export function AnnouncementPopupModal({
  popup,
  currentIndex,
  total,
  onNext,
  onPrev,
  onFinish,
  onFinishAll,
  onClose,
}: Props) {
  const { t, i18n } = useTranslation();
  const isFr = i18n.language?.startsWith('fr');

  const isFirst = currentIndex === 0;
  const isLast = currentIndex === total - 1;

  const displayTitle = isFr && popup.titleFr ? popup.titleFr : popup.title;
  const displayContent = isFr && popup.contentFr ? popup.contentFr : popup.content;
  const displayImageUrl = isFr && popup.imageUrlFr ? popup.imageUrlFr : popup.imageUrl;

  const IMAGE_MAX_HEIGHT_PX = 250;
  const IMAGE_MAX_WIDTH_PX = Math.round(IMAGE_MAX_HEIGHT_PX * 4 / 3);
  const DESC_WIDTH_PX = Math.round(IMAGE_MAX_WIDTH_PX * 0.7);
  const MODAL_TOTAL_WIDTH_PX =
    IMAGE_MAX_WIDTH_PX +
    DESC_WIDTH_PX +
    16 +
    32 +
    32 +
    2 +
    30;

  useEffect(() => {
    document.body.classList.add('announcement-modal-open');
    return () => {
      document.body.classList.remove('announcement-modal-open');
    };
  }, []);

  return (
    <>
      <BasicModal
        open={true}
        onClose={onClose}
        removeButtons={true}
        setOpen={onClose}
      >
        <div className="flex flex-col h-full overflow-y-auto p-4">

          {/* Header */}
          <div className="flex items-center justify-between mb-4 rounded-md -mx-4">
            <LanguageDropdown />
            <h2 className="flex-1 mx-4 text-2xl font-bold bnpp-color-green text-center">
              {t('CommsGptTips')}
            </h2>
            <ModalCloseButton />
          </div>

          {/* Title */}
          <h3 className="text-lg font-semibold mb-4 px-4">
            {displayTitle}
          </h3>

          {/* Body */}
          <div className="flex flex-col md:flex-row gap-4 px-4">

            {/* Image */}
            <div
              className="flex-none relative bg-gray-50 rounded-lg overflow-hidden border border-gray-200"
              style={{
                height: `${IMAGE_MAX_HEIGHT_PX}px`,
                maxHeight: `${IMAGE_MAX_HEIGHT_PX}px`,
                width: `${IMAGE_MAX_WIDTH_PX}px`,
                maxWidth: `${IMAGE_MAX_WIDTH_PX}px`,
                flexShrink: 0,
                boxSizing: 'border-box',
              }}
            >
              {displayImageUrl ? (
                <img
                  src={displayImageUrl}
                  alt=""
                  className="absolute inset-0 w-full h-full object-fill"
                />
              ) : (
                <div className="absolute inset-0 flex items-center justify-center">
                  <i className="fa fa-image fa-3x text-gray-300" />
                </div>
              )}
            </div>

            {/* Description */}
            <div
              className="flex-none flex flex-col bg-gray-100 p-4 rounded-md overflow-y-auto"
              style={{
                width: `${DESC_WIDTH_PX}px`,
                maxWidth: `${DESC_WIDTH_PX}px`,
                minWidth: `${DESC_WIDTH_PX}px`,
                flexShrink: 0,
                boxSizing: 'border-box',
              }}
            >
              <p className="font-bold text-base mb-2">Description</p>
              <div
                className="text-sm text-gray-700 leading-relaxed"
                dangerouslySetInnerHTML={{ __html: displayContent }}
              />
            </div>
          </div>

          {/* Footer */}
          <div className="relative flex justify-center items-center gap-6 mt-6">

            <button
              onClick={onPrev}
              disabled={isFirst}
              className={`
                rounded-full px-3.5 py-1 transition
                bnpp-bg-green text-white
                hover:bnpp-bg-green-dark
                ${isFirst ? 'invisible' : ''}
              `}
            >
              <i className="fa fa-chevron-left" />
            </button>

            <span className="text-sm font-medium text-gray-600 bnpp-color-green">
              {currentIndex + 1} / {total}
            </span>

            {isLast ? (
              <button
                onClick={onFinish}
                className="rounded-full px-3.5 py-1 transition bnpp-bg-green text-white hover:bnpp-bg-green-dark"
                aria-label="Terminer"
              >
                <i className="fa-solid fa-check" />
              </button>
            ) : (
              <button
                onClick={onNext}
                className="rounded-full px-3.5 py-1 transition bnpp-bg-green text-white hover:bnpp-bg-green-dark"
              >
                <i className="fa fa-chevron-right" />
              </button>
            )}

            {/* Do not display anymore — dismissAll */}
            <button
              onClick={onFinishAll}
              className="
                absolute right-0 top-1/2 -translate-y-1/2
                underline text-sm
                bnpp-color-green hover:bnpp-color-green-dark
              "
            >
              {t('DoNotDisplayAnymore')}
            </button>

          </div>
        </div>
      </BasicModal>

      <style>
        {`
          .announcement-modal-open .MuiBox-root {
            width: auto !important;
            max-width: ${MODAL_TOTAL_WIDTH_PX}px !important;
            min-width: ${MODAL_TOTAL_WIDTH_PX}px !important;
            max-height: 80vh !important;
            min-height: 400px !important;
            overflow-y: auto !important;
            overflow-x: hidden !important;
            border: 6px solid #d1d5db !important;
            border-radius: 24px !important;
          }
        `}
      </style>
    </>
  );
}