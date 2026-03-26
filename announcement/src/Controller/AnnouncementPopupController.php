<?php

namespace App\Controller;

use App\Application\Announcement\Query\GetActiveAnnouncementsQuery;
use App\Application\Announcement\UseCase\GetActiveAnnouncementsHandler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/announcement-popups')]
final class AnnouncementPopupController extends AbstractController
{
    public function __construct(
        private readonly GetActiveAnnouncementsHandler $handler,
    ) {
    }

    #[Route('/active', name: 'api_announcement_popups_active', methods: ['GET'])]
    public function active(): JsonResponse
    {
        $popups = ($this->handler)(new GetActiveAnnouncementsQuery());

        return $this->json(array_map(fn($popup) => [
            'id'                 => $popup->getId(),
            'title'              => $popup->getTitle(),
            'titleFr'            => $popup->getTitleFr(),
            'content'            => $popup->getContent(),
            'contentFr'          => $popup->getContentFr(),
            'imageUrl'           => $popup->getImageUrl(),
            'imageUrlFr'         => $popup->getImageUrlFr(),
            'priority'           => $popup->getPriority(),
            'recurrenceSeconds'  => $popup->getRecurrenceSeconds(),
            'forcedResetAt'      => $popup->getForcedResetAt()?->format(\DateTimeInterface::ATOM),
        ], $popups));
    }
}
