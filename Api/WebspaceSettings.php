<?php

declare(strict_types=1);

/*
 * This file is part of Alengo\Bundle\AlengoWebspaceSettingsBundle.
 *
 * (c) alengo
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Alengo\Bundle\AlengoWebspaceSettingsBundle\Api;

use Alengo\Bundle\AlengoWebspaceSettingsBundle\Entity\WebspaceSettings as WebspaceSettingsEntity;
use JMS\Serializer\Annotation\ExclusionPolicy;
use JMS\Serializer\Annotation\Groups;
use JMS\Serializer\Annotation\SerializedName;
use JMS\Serializer\Annotation\VirtualProperty;
use Symfony\Component\Serializer\Encoder\JsonEncoder;

/**
 * The WebspaceSettings class which will be exported to the API.
 */
#[ExclusionPolicy('all')]
class WebspaceSettings
{
    public $entity;

    public function __construct(WebspaceSettingsEntity $entity)
    {
        // @var WebspaceSettingsEntity entity
        $this->entity = $entity;
    }

    #[VirtualProperty()]
    #[Groups(['fullWebspaceSettings'])]
    public function getId(): ?int
    {
        return $this->entity->getId();
    }

    #[SerializedName('webspace')]
    #[VirtualProperty()]
    #[Groups(['fullWebspaceSettings'])]
    public function getWebspace(): string
    {
        return $this->entity->getWebspaceKey();
    }

    #[SerializedName('title')]
    #[VirtualProperty()]
    #[Groups(['fullWebspaceSettings'])]
    public function getTitle(): string
    {
        return $this->entity->getTitle();
    }

    #[SerializedName('description')]
    #[VirtualProperty()]
    #[Groups(['fullWebspaceSettings'])]
    public function getDescription(): string
    {
        return $this->entity->getDescription() ?: '';
    }

    #[SerializedName('type')]
    #[VirtualProperty()]
    #[Groups(['fullWebspaceSettings'])]
    public function getType(): string
    {
        return $this->entity->getType();
    }

    #[SerializedName('typeKey')]
    #[VirtualProperty()]
    #[Groups(['fullWebspaceSettings'])]
    public function getTypeKey(): string
    {
        return $this->entity->getTypeKey();
    }

    #[SerializedName('dataString')]
    #[VirtualProperty()]
    #[Groups(['fullWebspaceSettings'])]
    public function getDataString(): string
    {
        return match ($this->entity->getType()) {
            'string' => $this->entity->getData()[0],
            default => '',
        };
    }

    #[SerializedName('dataEvent')]
    #[VirtualProperty()]
    #[Groups(['fullWebspaceSettings'])]
    public function getDataEvent(): string
    {
        return match ($this->entity->getType()) {
            'event' => $this->entity->getData()[0],
            default => '',
        };
    }

    #[SerializedName('dataTextArea')]
    #[VirtualProperty()]
    #[Groups(['fullWebspaceSettings'])]
    public function getDataTextArea(): string
    {
        return match ($this->entity->getType()) {
            'textArea' => $this->entity->getData()[0],
            default => '',
        };
    }

    #[SerializedName('dataTextEditor')]
    #[VirtualProperty()]
    #[Groups(['fullWebspaceSettings'])]
    public function getDataTextEditor(): string
    {
        return match ($this->entity->getType()) {
            'textEditor' => $this->entity->getData()[0],
            default => '',
        };
    }

    #[SerializedName('dataMedia')]
    #[VirtualProperty()]
    #[Groups(['fullWebspaceSettings'])]
    public function getDataMedia(): array
    {
        return match ($this->entity->getType()) {
            'media' => $this->entity->getData()[0],
            default => [
                'displayOptions' => null,
                'id' => null,
            ],
        };
    }

    #[SerializedName('dataMedias')]
    #[VirtualProperty()]
    #[Groups(['fullWebspaceSettings'])]
    public function getDataMedias(): array
    {
        return match ($this->entity->getType()) {
            'medias' => $this->entity->getData()[0],
            default => [],
        };
    }
    #[SerializedName('dataPage')]
    #[VirtualProperty()]
    #[Groups(['fullWebspaceSettings'])]
    public function getDataPage(): int|string|null
    {
        return match ($this->entity->getType()) {
            'page' => $this->entity->getData()[0],
            default => null,
        };
    }

    #[SerializedName('dataPages')]
    #[VirtualProperty()]
    #[Groups(['fullWebspaceSettings'])]
    public function getDataPages(): array
    {
        return match ($this->entity->getType()) {
            'pages' => $this->entity->getData()[0],
            default => [],
        };
    }

    #[SerializedName('dataContact')]
    #[VirtualProperty()]
    #[Groups(['fullWebspaceSettings'])]
    public function getDataContact(): int|string|null
    {
        return match ($this->entity->getType()) {
            'contact' => $this->entity->getData()[0],
            default => null,
        };
    }

    #[SerializedName('dataContacts')]
    #[VirtualProperty()]
    #[Groups(['fullWebspaceSettings'])]
    public function getDataContacts(): array
    {
        return match ($this->entity->getType()) {
            'contacts' => $this->entity->getData()[0],
            default => [],
        };
    }

    #[SerializedName('dataAccount')]
    #[VirtualProperty()]
    #[Groups(['fullWebspaceSettings'])]
    public function getDataAccount(): int|string|null
    {
        return match ($this->entity->getType()) {
            'organization' => $this->entity->getData()[0],
            default => null,
        };
    }

    #[SerializedName('dataAccounts')]
    #[VirtualProperty()]
    #[Groups(['fullWebspaceSettings'])]
    public function getDataAccounts(): array
    {
        return match ($this->entity->getType()) {
            'organizations' => $this->entity->getData()[0],
            default => [],
        };
    }

    #[SerializedName('dataBlocks')]
    #[VirtualProperty()]
    #[Groups(['fullWebspaceSettings'])]
    public function getDataBlocks(): array
    {
        return match ($this->entity->getType()) {
            'blocks' => $this->entity->getData()[0],
            default => [],
        };
    }

    #[SerializedName('dataListView')]
    #[VirtualProperty()]
    #[Groups(['fullWebspaceSettings'])]
    public function getDataListView(): string
    {
        return $this->getDataAsJsonElement($this->entity->getData());
    }

    #[SerializedName('localeActivated')]
    #[VirtualProperty()]
    #[Groups(['fullWebspaceSettings'])]
    public function isLocaleActivated(): bool
    {
        return (bool)$this->entity->getLocale();
    }

    #[SerializedName('locale')]
    #[VirtualProperty()]
    #[Groups(['fullWebspaceSettings'])]
    public function getLocale(): string
    {
        return $this->entity->getLocale();
    }

    #[SerializedName('execute')]
    #[VirtualProperty()]
    #[Groups(['fullWebspaceSettings'])]
    public function getExecute(): bool
    {
        return $this->entity->getExecute();
    }

    #[SerializedName('executeLog')]
    #[VirtualProperty()]
    #[Groups(['fullWebspaceSettings'])]
    public function getExecuteLog(): string
    {
        return $this->getDataAsJsonElement($this->entity->getExecuteLog());
    }

    #[SerializedName('protected')]
    #[VirtualProperty()]
    #[Groups(['fullWebspaceSettings'])]
    public function isProtected(): bool
    {
        return $this->entity->isProtected();
    }

    #[SerializedName('published')]
    #[VirtualProperty()]
    #[Groups(['fullWebspaceSettings'])]
    public function isPublished(): bool
    {
        return $this->entity->isPublished();
    }

    #[SerializedName('created')]
    #[VirtualProperty()]
    #[Groups(['fullWebspaceSettings'])]
    public function getCreated(): \DateTimeImmutable
    {
        return $this->entity->getCreated();
    }

    #[SerializedName('changed')]
    #[VirtualProperty()]
    #[Groups(['fullWebspaceSettings'])]
    public function getChanged(): \DateTimeInterface
    {
        return $this->entity->getChanged();
    }

    private function getDataAsJsonElement(array $dataElement): string
    {
        return (new JsonEncoder())->encode($dataElement, 'json');
    }
}
