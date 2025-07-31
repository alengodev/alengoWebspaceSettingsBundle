<?php

declare(strict_types=1);

namespace Alengo\Bundle\AlengoWebspaceSettingsBundle\Service;

use Alengo\Bundle\AlengoWebspaceSettingsBundle\Entity\WebspaceSettings;
use Alengo\Bundle\AlengoWebspaceSettingsBundle\Repository\WebspaceSettingsRepository;
use Symfony\Component\HttpFoundation\RequestStack;

class WebspaceSettingsService
{
    public function __construct(
        private readonly RequestStack $requestStack,
        private readonly WebspaceSettingsRepository $repository,
    ) {
    }

    public function getSettings(string $typeKey, ?string $locale = null, ?string $webspaceKey = null): mixed
    {
        $requestWebspaceKey = $this->requestStack->getCurrentRequest()->attributes->get(key: '_sulu')->getAttributes()['webspace']->getKey();
        if ($webspaceKey) {
            $requestWebspaceKey = $webspaceKey;
        }

        if (!$requestWebspaceKey) {
            return false;
        }

        $queryParameter = [
            'typeKey' => $typeKey,
            'webspaceKey' => $requestWebspaceKey,
            'published' => true,
        ];

        if ($locale) {
            $queryParameter['locale'] = $locale;
        }

        $webspaceSettings = $this->repository->findOneBy($queryParameter);

        if (!$webspaceSettings instanceof WebspaceSettings) {
            return false;
        }

        return $webspaceSettings->getData()['_data'] ?? false;
    }
}
