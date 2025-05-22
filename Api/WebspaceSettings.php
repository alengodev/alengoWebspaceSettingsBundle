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
        return $this->entity->getDescription();
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

    /*#[SerializedName('data')]
    #[VirtualProperty()]
    #[Groups(['fullWebspaceSettings'])]
    public function getData(): array
    {
        $data = [];
        if (!$this->entity->getData()) {
            return $data;
        }
        foreach ($this->entity->getData() as $key => $dataElement) {
            $data[$key] = [
                'type' => 'field',
                'data' => \is_array($dataElement) ? $this->getDataAsJsonElement($dataElement) : $dataElement,
                'label' => $key,
            ];
        }
        \ksort($data);

        return \array_values($data);
    }*/

    #[SerializedName('dataString')]
    #[VirtualProperty()]
    #[Groups(['fullWebspaceSettings'])]
    public function getDataString(): string
    {
        $data = $this->entity->getData();

        return $data[0];
    }

    #[SerializedName('locale')]
    #[VirtualProperty()]
    #[Groups(['fullWebspaceSettings'])]
    public function getLocale(): string
    {
        return $this->entity->getLocale();
    }

    #[SerializedName('enabled')]
    #[VirtualProperty()]
    #[Groups(['fullWebspaceSettings'])]
    public function getEnabled(): bool
    {
        return $this->entity->getEnabled();
    }

    #[SerializedName('created')]
    #[VirtualProperty()]
    #[Groups(['fullFormData'])]
    public function getCreated(): \DateTime
    {
        return $this->entity->getCreated();
    }

    #[SerializedName('changed')]
    #[VirtualProperty()]
    #[Groups(['fullFormData'])]
    public function getChanged(): \DateTime
    {
        return $this->entity->getChanged();
    }

    private function getDataAsJsonElement(array $dataElement): string
    {
        return (new JsonEncoder())->encode($dataElement, 'json');
    }
}
