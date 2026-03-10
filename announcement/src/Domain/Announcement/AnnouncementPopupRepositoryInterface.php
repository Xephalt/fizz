<?php

namespace App\Domain\Announcement;

interface AnnouncementPopupRepositoryInterface
{
    public function save(AnnouncementPopup $popup): void;

    public function findById(string $id): ?AnnouncementPopup;

    /** @return AnnouncementPopup[] */
    public function findActiveOrderedByPriority(): array;

    /** @return AnnouncementPopup[] */
    public function findAllOrderedByPriority(): array;

    public function delete(string $id): void;
}
