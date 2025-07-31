<?php

declare(strict_types=1);

namespace Alengo\Bundle\AlengoWebspaceSettingsBundle\Twig;

use Alengo\Bundle\AlengoWebspaceSettingsBundle\Service\WebspaceSettingsService;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class WebspaceSettingsExtension extends AbstractExtension
{
    public function __construct(
        private readonly WebspaceSettingsService $webspaceSettingsService,
    ) {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('webspaceSettings', $this->webspaceSettings(...)),
        ];
    }

    public function webspaceSettings(string $typeKey, ?string $locale = null, ?string $webspaceKey = null): mixed
    {
        return $this->webspaceSettingsService->getSettings($typeKey, $locale, $webspaceKey);
    }
}
