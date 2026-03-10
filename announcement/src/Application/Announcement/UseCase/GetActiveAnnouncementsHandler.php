<?php

namespace App\Application\Announcement\UseCase;

use App\Application\Announcement\Query\GetActiveAnnouncementsQuery;
use App\Domain\Announcement\AnnouncementPopup;
use App\Domain\Announcement\AnnouncementPopupRepositoryInterface;

final class GetActiveAnnouncementsHandler
{
    public function __construct(
        private readonly AnnouncementPopupRepositoryInterface $repository,
    ) {
    }

    /** @return AnnouncementPopup[] */
    public function __invoke(GetActiveAnnouncementsQuery $query): array
    {
        return $this->repository->findActiveOrderedByPriority();
    }
}
