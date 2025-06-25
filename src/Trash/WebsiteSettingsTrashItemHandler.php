<?php

declare(strict_types=1);

namespace Alengo\Bundle\AlengoWebspaceSettingsBundle\Trash;

use Alengo\Bundle\AlengoWebspaceSettingsBundle\Admin\WebspaceSettingsAdmin;
use Alengo\Bundle\AlengoWebspaceSettingsBundle\Entity\WebspaceSettings;
use Doctrine\ORM\EntityManagerInterface;
use Sulu\Bundle\TrashBundle\Application\RestoreConfigurationProvider\RestoreConfiguration;
use Sulu\Bundle\TrashBundle\Application\RestoreConfigurationProvider\RestoreConfigurationProviderInterface;
use Sulu\Bundle\TrashBundle\Application\TrashItemHandler\RestoreTrashItemHandlerInterface;
use Sulu\Bundle\TrashBundle\Application\TrashItemHandler\StoreTrashItemHandlerInterface;
use Sulu\Bundle\TrashBundle\Domain\Model\TrashItemInterface;
use Sulu\Bundle\TrashBundle\Domain\Repository\TrashItemRepositoryInterface;

final readonly class WebsiteSettingsTrashItemHandler implements
    StoreTrashItemHandlerInterface,
    RestoreTrashItemHandlerInterface,
    RestoreConfigurationProviderInterface
{
    public function __construct(
        private TrashItemRepositoryInterface $trashItemRepository,
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function store(object $websiteSettings, array $options = []): TrashItemInterface
    {
        $data = [
            'webspaceKey' => $websiteSettings->getWebspaceKey(),
            'title' => $websiteSettings->getTitle(),
            'description' => $websiteSettings->getDescription(),
            'type' => $websiteSettings->getType(),
            'typeKey' => $websiteSettings->getTypeKey(),
            'data' => $websiteSettings->getData(),
            'locale' => $websiteSettings->getLocale(),
            'execute' => $websiteSettings->getExecute(),
            'executeLog' => $websiteSettings->getExecuteLog(),
            'protected' => $websiteSettings->isProtected(),
            'published' => $websiteSettings->isPublished(),
            'created' => $websiteSettings->getCreated(),
            'changed' => $websiteSettings->getChanged(),
            'idUsersCreator' => $websiteSettings->getIdUsersCreator(),
            'idUsersChanger' => $websiteSettings->getIdUsersChanger(),
        ];

        return $this->trashItemRepository->create(
            WebspaceSettings::RESOURCE_KEY,
            (string) $websiteSettings->getId(),
            $websiteSettings->getTitle(),
            $data,
            null,
            $options,
            WebspaceSettingsAdmin::getWebspaceSettingsSecurityContext($websiteSettings->getWebspaceKey()),
            null,
            null,
        );
    }

    public function restore(TrashItemInterface $trashItem, array $restoreFormData = []): object
    {
        $id = $trashItem->getResourceId();
        $data = $trashItem->getRestoreData();

        $webspaceSettings = new WebspaceSettings();
        $webspaceSettings->setWebspaceKey($data['webspaceKey']);
        $webspaceSettings->setTitle($data['title']);
        $webspaceSettings->setDescription($data['description']);
        $webspaceSettings->setType($data['type']);
        $webspaceSettings->setTypeKey($data['typeKey'] . '__restored');
        $webspaceSettings->setData($data['data']);
        $webspaceSettings->setLocale($data['locale']);
        $webspaceSettings->setExecute($data['execute']);
        $webspaceSettings->setExecuteLog($data['executeLog']);
        $webspaceSettings->setProtected($data['protected']);
        $webspaceSettings->setPublished(false);
        $webspaceSettings->setCreated(new \DateTimeImmutable($data['created']['date']));
        $webspaceSettings->setChanged(new \DateTime($data['changed']['date']));
        $webspaceSettings->setIdUsersCreator($data['idUsersCreator']);
        $webspaceSettings->setIdUsersChanger($data['idUsersChanger']);

        $this->entityManager->persist($webspaceSettings);
        $this->entityManager->flush();

        return $webspaceSettings;
    }

    public static function getResourceKey(): string
    {
        return WebspaceSettings::RESOURCE_KEY;
    }

    public function getConfiguration(): RestoreConfiguration
    {
        return new RestoreConfiguration(
            null,
            WebspaceSettingsAdmin::WEBSPACE_SETTINGS_LIST_VIEW,
            ['webspace' => 'webspace'],
        );
    }
}
