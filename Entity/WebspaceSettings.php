<?php

declare(strict_types=1);

namespace Alengo\Bundle\AlengoWebspaceSettingsBundle\Entity;

use Alengo\Bundle\AlengoWebspaceSettingsBundle\Repository\WebspaceSettingsRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: WebspaceSettingsRepository::class)]
#[ORM\Table(name: 'we_settings')]
#[ORM\UniqueConstraint(name: 'unique_typekey_webspacekey_locale', columns: ['type_key', 'webspace_key', 'locale'])]
class WebspaceSettings
{
    public const RESOURCE_KEY = 'webspace_settings';

    #[ORM\Id()]
    #[ORM\GeneratedValue()]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\Column(type: Types::STRING, length: 255)]
    private ?string $webspaceKey = null;

    #[ORM\Column(type: Types::STRING, length: 255)]
    private ?string $title = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(type: Types::STRING, length: 255)]
    private ?string $type = null;

    #[ORM\Column(type: Types::STRING, length: 255)]
    private ?string $typeKey = null;

    #[ORM\Column(type: Types::JSON)]
    private ?array $data = [];

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    private ?string $locale = null;

    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => false])]
    private ?bool $execute = false;

    #[ORM\Column(type: Types::JSON)]
    private ?array $executeLog = [];

    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => false])]
    private ?bool $protected = false;

    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => true])]
    private ?bool $published = true;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $created = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $changed = null;

    #[ORM\Column(name: 'idUsersCreator', type: Types::INTEGER)]
    private ?int $idUsersCreator = null;

    #[ORM\Column(name: 'idUsersChanger', type: Types::INTEGER)]
    private ?int $idUsersChanger = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getWebspaceKey(): ?string
    {
        return $this->webspaceKey;
    }

    public function setWebspaceKey(mixed $webspaceKey): void
    {
        $this->webspaceKey = $webspaceKey;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): void
    {
        $this->title = $title;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): void
    {
        $this->type = $type;
    }

    public function getTypeKey(): ?string
    {
        return $this->typeKey;
    }

    public function setTypeKey(?string $typeKey): void
    {
        $this->typeKey = $typeKey;
    }

    public function getData(): ?array
    {
        return $this->data;
    }

    public function setData(?array $data): void
    {
        $this->data = $data;
    }

    public function getLocale(): ?string
    {
        return $this->locale;
    }

    public function setLocale(?string $locale): void
    {
        $this->locale = $locale;
    }

    public function getExecute(): ?bool
    {
        return $this->execute;
    }

    public function setExecute(?bool $execute): void
    {
        $this->execute = $execute ?? false;
    }

    public function getExecuteLog(): ?array
    {
        return $this->executeLog;
    }

    public function setExecuteLog(?array $executeLog): void
    {
        $this->executeLog = $executeLog ?? [];
    }

    public function isProtected(): ?bool
    {
        return $this->protected;
    }

    public function setProtected(?bool $protected): void
    {
        $this->protected = $protected ?? false;
    }

    public function isPublished(): ?bool
    {
        return $this->published;
    }

    public function setPublished(?bool $published): void
    {
        $this->published = $published ?? true;
    }

    public function getCreated(): \DateTimeImmutable
    {
        return $this->created;
    }

    public function setCreated(\DateTimeImmutable $created): void
    {
        $this->created = $created;
    }

    public function getChanged(): \DateTimeInterface
    {
        return $this->changed;
    }

    public function setChanged(\DateTimeInterface $changed): void
    {
        $this->changed = $changed;
    }

    public function getIdUsersCreator(): ?int
    {
        return $this->idUsersCreator;
    }

    public function setIdUsersCreator(?int $idUsersCreator): void
    {
        $this->idUsersCreator = $idUsersCreator;
    }

    public function getIdUsersChanger(): ?int
    {
        return $this->idUsersChanger;
    }

    public function setIdUsersChanger(?int $idUsersChanger): void
    {
        $this->idUsersChanger = $idUsersChanger;
    }
}
