<?php

declare(strict_types=1);

namespace Alengo\Bundle\AlengoWebspaceSettingsBundle\Entity;

use Alengo\Bundle\AlengoWebspaceSettingsBundle\Repository\WebspaceSettingsRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Sulu\Component\Persistence\Model\AuditableInterface;
use Sulu\Component\Persistence\Model\AuditableTrait;

#[ORM\Entity(repositoryClass: WebspaceSettingsRepository::class)]
#[ORM\Table(name: 'we_settings')]
#[ORM\UniqueConstraint(name: 'uniq_typekey_webspacekey', columns: ['type_key', 'webspace_key'])]
class WebspaceSettings implements AuditableInterface
{
    use AuditableTrait;

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

    #[ORM\Column(type: Types::BOOLEAN)]
    private ?bool $enabled = null;

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

    public function getEnabled(): ?bool
    {
        return $this->enabled;
    }

    public function setEnabled(?bool $enabled): void
    {
        $this->enabled = $enabled;
    }
}
