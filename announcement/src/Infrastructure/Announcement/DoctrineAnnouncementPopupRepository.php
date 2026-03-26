<?php

namespace App\Infrastructure\Announcement;

use App\Domain\Announcement\AnnouncementPopup;
use App\Domain\Announcement\AnnouncementPopupRepositoryInterface;
use Doctrine\DBAL\Connection;

final class DoctrineAnnouncementPopupRepository implements AnnouncementPopupRepositoryInterface
{
    public function __construct(
        private readonly Connection $connection,
    ) {
    }

    public function save(AnnouncementPopup $popup): void
    {
        $exists = $this->connection->fetchOne(
            'SELECT id FROM announcement_popup WHERE id = ?',
            [$popup->getId()]
        );

        $data = [
            'title'              => $popup->getTitle(),
            'title_fr'           => $popup->getTitleFr(),
            'content'            => $popup->getContent(),
            'content_fr'         => $popup->getContentFr(),
            'image_url'          => $popup->getImageUrl(),
            'image_url_fr'       => $popup->getImageUrlFr(),
            'is_active'          => (int) $popup->isActive(),
            'priority'           => $popup->getPriority(),
            'recurrence_seconds' => $popup->getRecurrenceSeconds(),
            'forced_reset_at'    => $popup->getForcedResetAt()?->format('Y-m-d H:i:s'),
        ];

        if ($exists) {
            $this->connection->update('announcement_popup', $data, ['id' => $popup->getId()]);
            return;
        }

        $this->connection->insert('announcement_popup', array_merge($data, [
            'id'         => $popup->getId(),
            'created_at' => (new \DateTimeImmutable())->format('Y-m-d H:i:s'),
        ]));
    }

    public function findById(string $id): ?AnnouncementPopup
    {
        $row = $this->connection->fetchAssociative(
            'SELECT * FROM announcement_popup WHERE id = ?',
            [$id]
        );

        return $row ? $this->hydrate($row) : null;
    }

    public function findActiveOrderedByPriority(): array
    {
        $rows = $this->connection->fetchAllAssociative(
            'SELECT * FROM announcement_popup WHERE is_active = 1 ORDER BY priority ASC'
        );

        return array_map($this->hydrate(...), $rows);
    }

    public function findAllOrderedByPriority(): array
    {
        $rows = $this->connection->fetchAllAssociative(
            'SELECT * FROM announcement_popup ORDER BY priority ASC'
        );

        return array_map($this->hydrate(...), $rows);
    }

    public function delete(string $id): void
    {
        $this->connection->delete('announcement_popup', ['id' => $id]);
    }

    private function hydrate(array $row): AnnouncementPopup
    {
        $forcedResetAt = !empty($row['forced_reset_at'])
            ? new \DateTimeImmutable($row['forced_reset_at'])
            : null;

        return AnnouncementPopup::reconstitute(
            id: $row['id'],
            title: $row['title'],
            titleFr: $row['title_fr'] ?? null,
            content: $row['content'],
            contentFr: $row['content_fr'] ?? null,
            imageUrl: $row['image_url'],
            imageUrlFr: $row['image_url_fr'] ?? null,
            isActive: (bool) $row['is_active'],
            priority: (int) $row['priority'],
            recurrenceSeconds: !empty($row['recurrence_seconds']) ? (int) $row['recurrence_seconds'] : null,
            forcedResetAt: $forcedResetAt,
        );
    }
}
