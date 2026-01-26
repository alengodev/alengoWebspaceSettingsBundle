<?php

declare(strict_types=1);

namespace Alengo\Bundle\AlengoWebspaceSettingsBundle\Domain\Event\Activity;

use Alengo\Bundle\AlengoWebspaceSettingsBundle\Entity\WebspaceSettings;
use Sulu\Bundle\ActivityBundle\Domain\Event\DomainEvent;

abstract class AbstractEvent extends DomainEvent
{
    public function __construct(
        private readonly WebspaceSettings $webspaceSettings,
    ) {
        parent::__construct();
    }

    public function getResourceKey(): string
    {
        return WebspaceSettings::RESOURCE_KEY;
    }

    public function getResourceId(): string
    {
        return (string) $this->webspaceSettings->getId();
    }

    public function getResourceTitle(): ?string
    {
        return $this->webspaceSettings->getTitle();
    }

    public function getResourceSecurityContext(): ?string
    {
        return WebspaceSettings::SECURITY_CONTEXT . '.' . $this->webspaceSettings->getWebspaceKey();
    }
}