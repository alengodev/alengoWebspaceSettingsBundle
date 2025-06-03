<?php

declare(strict_types=1);

namespace Alengo\Bundle\AlengoWebspaceSettingsBundle\Twig;

use Alengo\Bundle\AlengoWebspaceSettingsBundle\Entity\WebspaceSettings;
use Alengo\Bundle\AlengoWebspaceSettingsBundle\Repository\WebspaceSettingsRepository;
use Symfony\Component\HttpFoundation\RequestStack;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class WebspaceSettingsExtension extends AbstractExtension
{
    public function __construct(
        private readonly RequestStack $requestStack,
        private readonly WebspaceSettingsRepository $repository,
    ) {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('webspaceSettings', $this->webspaceSettings(...)),
        ];
    }

    public function webspaceSettings($typeKey, $locale = false, $webspaceKey = false)
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
