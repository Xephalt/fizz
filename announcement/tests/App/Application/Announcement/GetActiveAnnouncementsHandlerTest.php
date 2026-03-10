<?php

namespace Tests\App\Application\Announcement;

use App\Application\Announcement\Query\GetActiveAnnouncementsQuery;
use App\Application\Announcement\UseCase\GetActiveAnnouncementsHandler;
use App\Domain\Announcement\AnnouncementPopup;
use PHPUnit\Framework\TestCase;

final class GetActiveAnnouncementsHandlerTest extends TestCase
{
    private InMemoryAnnouncementPopupRepository $repository;
    private GetActiveAnnouncementsHandler $handler;

    protected function setUp(): void
    {
        $this->repository = new InMemoryAnnouncementPopupRepository();
        $this->handler = new GetActiveAnnouncementsHandler($this->repository);
    }

    public function test_returns_empty_when_no_active_popups(): void
    {
        $result = ($this->handler)(new GetActiveAnnouncementsQuery());
        $this->assertSame([], $result);
    }

    public function test_returns_only_active_popups(): void
    {
        $active = AnnouncementPopup::create('uuid-1', 'Actif', 'Contenu', null, 0);
        $active->activate();
        $inactive = AnnouncementPopup::create('uuid-2', 'Inactif', 'Contenu', null, 1);

        $this->repository->save($active);
        $this->repository->save($inactive);

        $result = ($this->handler)(new GetActiveAnnouncementsQuery());

        $this->assertCount(1, $result);
        $this->assertSame('uuid-1', $result[0]->getId());
    }

    public function test_returns_active_popups_ordered_by_priority(): void
    {
        $second = AnnouncementPopup::create('uuid-2', 'Second', 'Contenu', null, 2);
        $second->activate();
        $first = AnnouncementPopup::create('uuid-1', 'Premier', 'Contenu', null, 1);
        $first->activate();
        $third = AnnouncementPopup::create('uuid-3', 'Troisième', 'Contenu', null, 3);
        $third->activate();

        $this->repository->save($second);
        $this->repository->save($first);
        $this->repository->save($third);

        $result = ($this->handler)(new GetActiveAnnouncementsQuery());

        $this->assertSame('uuid-1', $result[0]->getId());
        $this->assertSame('uuid-2', $result[1]->getId());
        $this->assertSame('uuid-3', $result[2]->getId());
    }
}
