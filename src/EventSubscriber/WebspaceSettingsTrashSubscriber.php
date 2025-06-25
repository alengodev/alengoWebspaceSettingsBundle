<?php

declare(strict_types=1);

namespace Alengo\Bundle\AlengoWebspaceSettingsBundle\EventSubscriber;

use Alengo\Bundle\AlengoWebspaceSettingsBundle\Entity\WebspaceSettings;
use Alengo\Bundle\AlengoWebspaceSettingsBundle\Event\WebspaceSettingsDeletedEvent;
use Doctrine\ORM\EntityManagerInterface;
use Sulu\Bundle\TrashBundle\Application\TrashManager\TrashManagerInterface;
use Sulu\Component\DocumentManager\Event\ClearEvent;
use Sulu\Component\DocumentManager\Event\FlushEvent;
use Sulu\Component\DocumentManager\Events;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class WebspaceSettingsTrashSubscriber implements EventSubscriberInterface
{
    private bool $hasPendingTrashItem = false;

    public function __construct(
        private readonly TrashManagerInterface $trashManager,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            WebspaceSettingsDeletedEvent::class => ['storeWebspaceSettingsToTrash', 1024],
            Events::FLUSH => 'flushTrashItem',
            Events::CLEAR => 'clearPendingTrashItem',
        ];
    }

    public function storeWebspaceSettingsToTrash(WebspaceSettingsDeletedEvent $event): void
    {
        $document = $event->getWebspaceSettings();
        if (!$document instanceof WebspaceSettings) {
            return;
        }
        $this->trashManager->store(WebspaceSettings::RESOURCE_KEY, $document);
        $this->hasPendingTrashItem = true;
    }

    public function flushTrashItem(FlushEvent $event): void
    {
        if (!$this->hasPendingTrashItem) {
            return;
        }

        $this->entityManager->flush();
        $this->hasPendingTrashItem = false;
    }

    public function clearPendingTrashItem(ClearEvent $event): void
    {
        $this->hasPendingTrashItem = false;
    }
}
