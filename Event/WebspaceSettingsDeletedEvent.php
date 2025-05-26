<?php

declare(strict_types=1);

namespace Alengo\Bundle\AlengoWebspaceSettingsBundle\Event;

use Alengo\Bundle\AlengoWebspaceSettingsBundle\Entity\WebspaceSettings;
use Symfony\Contracts\EventDispatcher\Event;

class WebspaceSettingsDeletedEvent extends Event
{
    public function __construct(
        private readonly WebspaceSettings $webspaceSettings,
    ) {
    }

    public function getWebspaceSettings(): WebspaceSettings
    {
        return $this->webspaceSettings;
    }
}
