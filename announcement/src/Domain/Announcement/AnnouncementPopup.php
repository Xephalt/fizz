<?php

namespace App\Domain\Announcement;

use InvalidArgumentException;

final class AnnouncementPopup
{
    private function __construct(
        private readonly string $id,
        private string $title,
        private ?string $titleFr,
        private string $content,
        private ?string $contentFr,
        private ?string $imageUrl,
        private ?string $imageUrlFr,
        private bool $isActive,
        private int $priority,
        private ?int $recurrenceSeconds,
        private ?\DateTimeImmutable $forcedResetAt,
    ) {}

    public static function create(
        string $id,
        string $title,
        string $content,
        ?string $imageUrl,
        int $priority,
        ?string $titleFr = null,
        ?string $contentFr = null,
        ?string $imageUrlFr = null,
        ?int $recurrenceSeconds = null,
    ): self {
        if (trim($title) === '') {
            throw new InvalidArgumentException('Title cannot be empty');
        }

        if (trim($content) === '') {
            throw new InvalidArgumentException('Content cannot be empty');
        }

        if ($priority < 0) {
            throw new InvalidArgumentException('Priority must be a positive integer');
        }

        return new self(
            id: $id,
            title: $title,
            titleFr: $titleFr,
            content: $content,
            contentFr: $contentFr,
            imageUrl: $imageUrl,
            imageUrlFr: $imageUrlFr,
            isActive: false,
            priority: $priority,
            recurrenceSeconds: $recurrenceSeconds,
            forcedResetAt: null,
        );
    }

    public static function reconstitute(
        string $id,
        string $title,
        ?string $titleFr,
        string $content,
        ?string $contentFr,
        ?string $imageUrl,
        ?string $imageUrlFr,
        bool $isActive,
        int $priority,
        ?int $recurrenceSeconds = null,
        ?\DateTimeImmutable $forcedResetAt = null,
    ): self {
        return new self(
            id: $id,
            title: $title,
            titleFr: $titleFr,
            content: $content,
            contentFr: $contentFr,
            imageUrl: $imageUrl,
            imageUrlFr: $imageUrlFr,
            isActive: $isActive,
            priority: $priority,
            recurrenceSeconds: $recurrenceSeconds,
            forcedResetAt: $forcedResetAt,
        );
    }

    public function activate(): void
    {
        $this->isActive = true;
    }

    public function deactivate(): void
    {
        $this->isActive = false;
    }

    public function update(
        string $title,
        string $content,
        ?string $imageUrl,
        int $priority,
        ?string $titleFr = null,
        ?string $contentFr = null,
        ?string $imageUrlFr = null,
        ?int $recurrenceSeconds = null,
    ): void {
        if (trim($title) === '') {
            throw new InvalidArgumentException('Title cannot be empty');
        }

        if (trim($content) === '') {
            throw new InvalidArgumentException('Content cannot be empty');
        }

        if ($priority < 0) {
            throw new InvalidArgumentException('Priority must be a positive integer');
        }

        $this->title = $title;
        $this->titleFr = $titleFr;
        $this->content = $content;
        $this->contentFr = $contentFr;
        $this->imageUrl = $imageUrl;
        $this->imageUrlFr = $imageUrlFr;
        $this->priority = $priority;
        $this->recurrenceSeconds = $recurrenceSeconds;
    }

    public function forceReset(): void
    {
        $this->forcedResetAt = new \DateTimeImmutable();
    }

    public function setImageUrl(?string $imageUrl): void
    {
        $this->imageUrl = $imageUrl;
    }

    public function setImageUrlFr(?string $imageUrlFr): void
    {
        $this->imageUrlFr = $imageUrlFr;
    }

    public function getId(): string { return $this->id; }
    public function getTitle(): string { return $this->title; }
    public function getTitleFr(): ?string { return $this->titleFr; }
    public function getContent(): string { return $this->content; }
    public function getContentFr(): ?string { return $this->contentFr; }
    public function getImageUrl(): ?string { return $this->imageUrl; }
    public function getImageUrlFr(): ?string { return $this->imageUrlFr; }
    public function isActive(): bool { return $this->isActive; }
    public function getPriority(): int { return $this->priority; }
    public function getRecurrenceSeconds(): ?int { return $this->recurrenceSeconds; }
    public function getForcedResetAt(): ?\DateTimeImmutable { return $this->forcedResetAt; }

    public function setPriority(int $priority): void
    {
        if ($priority < 0) {
            throw new InvalidArgumentException('Priority must be a positive integer');
        }

        $this->priority = $priority;
    }
}