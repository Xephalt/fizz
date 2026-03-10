<?php

namespace Tests\App\Domain\Announcement;

use App\Domain\Announcement\AnnouncementPopup;
use PHPUnit\Framework\TestCase;

final class AnnouncementPopupTest extends TestCase
{
    public function test_create_popup_with_valid_data(): void
    {
        $popup = AnnouncementPopup::create(
            id: 'uuid-1',
            title: 'Nouvelle feature',
            content: '<p>Découvrez la nouvelle feature</p>',
            imageUrl: null,
            priority: 0,
        );

        $this->assertSame('Nouvelle feature', $popup->getTitle());
        $this->assertFalse($popup->isActive());
        $this->assertSame(0, $popup->getPriority());
        $this->assertNull($popup->getTitleFr());
        $this->assertNull($popup->getContentFr());
        $this->assertNull($popup->getImageUrlFr());
    }

    public function test_create_popup_with_i18n(): void
    {
        $popup = AnnouncementPopup::create(
            id: 'uuid-1',
            title: 'New feature',
            content: '<p>Content</p>',
            imageUrl: 'https://img.png',
            priority: 0,
            titleFr: 'Nouvelle feature',
            contentFr: '<p>Contenu</p>',
            imageUrlFr: 'https://img-fr.png',
        );

        $this->assertSame('Nouvelle feature', $popup->getTitleFr());
        $this->assertSame('<p>Contenu</p>', $popup->getContentFr());
        $this->assertSame('https://img-fr.png', $popup->getImageUrlFr());
    }

    public function test_popup_is_inactive_by_default(): void
    {
        $popup = AnnouncementPopup::create('uuid-1', 'Titre', 'Contenu', null, 0);
        $this->assertFalse($popup->isActive());
    }

    public function test_activate_popup(): void
    {
        $popup = AnnouncementPopup::create('uuid-1', 'Titre', 'Contenu', null, 0);
        $popup->activate();
        $this->assertTrue($popup->isActive());
    }

    public function test_deactivate_popup(): void
    {
        $popup = AnnouncementPopup::create('uuid-1', 'Titre', 'Contenu', null, 0);
        $popup->activate();
        $popup->deactivate();
        $this->assertFalse($popup->isActive());
    }

    public function test_create_throws_on_empty_title(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        AnnouncementPopup::create('uuid-1', '   ', 'Contenu', null, 0);
    }

    public function test_create_throws_on_empty_content(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        AnnouncementPopup::create('uuid-1', 'Titre', '', null, 0);
    }

    public function test_create_throws_on_negative_priority(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        AnnouncementPopup::create('uuid-1', 'Titre', 'Contenu', null, -1);
    }

    public function test_update_popup(): void
    {
        $popup = AnnouncementPopup::create('uuid-1', 'Titre', 'Contenu', null, 0);
        $popup->update(
            title: 'Nouveau titre',
            content: '<p>Nouveau contenu</p>',
            imageUrl: 'https://img.png',
            priority: 2,
            titleFr: 'Nouveau titre FR',
            contentFr: '<p>Nouveau contenu FR</p>',
            imageUrlFr: 'https://img-fr.png',
        );

        $this->assertSame('Nouveau titre', $popup->getTitle());
        $this->assertSame('Nouveau titre FR', $popup->getTitleFr());
        $this->assertSame(2, $popup->getPriority());
        $this->assertSame('https://img-fr.png', $popup->getImageUrlFr());
    }
}
