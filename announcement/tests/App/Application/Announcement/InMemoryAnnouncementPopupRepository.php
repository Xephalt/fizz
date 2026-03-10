<?php

namespace Tests\App\Application\Announcement;

use App\Domain\Announcement\AnnouncementPopup;
use App\Domain\Announcement\AnnouncementPopupRepositoryInterface;

final class InMemoryAnnouncementPopupRepository implements AnnouncementPopupRepositoryInterface
{
    /** @var AnnouncementPopup[] */
    private array $popups = [];

    public function save(AnnouncementPopup $popup): void
    {
        $this->popups[$popup->getId()] = $popup;
    }

    public function findById(string $id): ?AnnouncementPopup
    {
        return $this->popups[$id] ?? null;
    }

    public function findActiveOrderedByPriority(): array
    {
        $active = array_filter($this->popups, fn($p) => $p->isActive());
        usort($active, fn($a, $b) => $a->getPriority() <=> $b->getPriority());
        return array_values($active);
    }

    public function findAllOrderedByPriority(): array
    {
        $all = array_values($this->popups);
        usort($all, fn($a, $b) => $a->getPriority() <=> $b->getPriority());
        return $all;
    }

    public function delete(string $id): void
    {
        unset($this->popups[$id]);
    }
}
