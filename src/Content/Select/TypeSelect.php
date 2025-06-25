<?php

declare(strict_types=1);

namespace Alengo\Bundle\AlengoWebspaceSettingsBundle\Content\Select;

use Symfony\Contracts\Translation\TranslatorInterface;

class TypeSelect
{
    public function __construct(
        private readonly TranslatorInterface $translator,
        private readonly array $typeSelect = [],
    ) {
    }

    public function getValues($locale): array
    {
        if (0 === \count($this->typeSelect)) {
            return [
                [
                    'name' => 'string',
                    'title' => $this->translator->trans('alengo_webspace_settings.string', [], 'admin', $locale),
                ],
            ];
        }

        $types = [];

        foreach ($this->typeSelect as $type) {
            $types[] = [
                'name' => $type,
                'title' => $this->translator->trans('alengo_webspace_settings.' . $type, [], 'admin', $locale),
            ];
        }

        return $types;
    }

    public function getSingleSelectDefaultValue(): string
    {
        return 'string';
    }

    public function getMultiSelectDefaultValue(): array
    {
        return [
            ['name' => 'string'],
        ];
    }
}
