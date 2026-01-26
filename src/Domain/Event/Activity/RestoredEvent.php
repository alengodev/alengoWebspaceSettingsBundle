<?php

declare(strict_types=1);

namespace Alengo\Bundle\AlengoWebspaceSettingsBundle\Domain\Event\Activity;

class RestoredEvent extends AbstractEvent
{
    public function getEventType(): string
    {
        return 'restored';
    }
}
