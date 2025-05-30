<?php

declare(strict_types=1);

namespace Alengo\Bundle\AlengoWebspaceSettingsBundle\Controller\Admin;

use Alengo\Bundle\AlengoWebspaceSettingsBundle\Admin\WebspaceSettingsAdmin;
use Alengo\Bundle\AlengoWebspaceSettingsBundle\Api\WebspaceSettings as WebspaceSettingsApi;
use Alengo\Bundle\AlengoWebspaceSettingsBundle\Entity\WebspaceSettings;
use Alengo\Bundle\AlengoWebspaceSettingsBundle\Event\WebspaceSettingsCreatedEvent;
use Alengo\Bundle\AlengoWebspaceSettingsBundle\Event\WebspaceSettingsDeletedEvent;
use Alengo\Bundle\AlengoWebspaceSettingsBundle\Event\WebspaceSettingsUpdatedEvent;
use Alengo\Bundle\AlengoWebspaceSettingsBundle\Model\WebspaceSettings as WebspaceSettingsModel;
use Doctrine\ORM\EntityManagerInterface;
use FOS\RestBundle\Context\Context;
use FOS\RestBundle\View\View;
use FOS\RestBundle\View\ViewHandlerInterface;
use HandcraftedInTheAlps\RestRoutingBundle\Routing\ClassResourceInterface;
use Sulu\Component\Rest\AbstractRestController;
use Sulu\Component\Rest\ListBuilder\CollectionRepresentation;
use Sulu\Component\Rest\ListBuilder\ListRepresentation;
use Sulu\Component\Security\SecuredControllerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class WebspaceSettingsController extends AbstractRestController implements ClassResourceInterface, SecuredControllerInterface
{
    public function __construct(
        ViewHandlerInterface $viewHandler,
        TokenStorageInterface $tokenStorage,
        private readonly EntityManagerInterface $entityManager,
        private readonly RequestStack $requestStack,
        private readonly EventDispatcherInterface $eventDispatcher,
    ) {
        parent::__construct($viewHandler, $tokenStorage);
    }

    #[Route(
        '/webspace/settings.{_format}',
        name: 'cget_webspace-settings',
        defaults: ['_format' => 'json'],
        methods: ['GET'],
    )]
    public function cgetAction(Request $request): Response
    {
        $webspace = $request->query->get('webspace');
        if (!$webspace) {
            throw new NotFoundHttpException('Webspace not found');
        }

        $query = $this->entityManager->getRepository(WebspaceSettings::class)->findByListQuery([
            'webspaceKey' => $webspace,
            'sortBy' => $request->query->get('sortBy'),
            'sortOrder' => $request->query->get('sortOrder'),
            'fields' => $request->query->get('fields'),
            'search' => $request->query->get('search'),
            'page' => $request->query->get('page', 1),
            'limit' => $request->query->get('limit', 10),
        ]);

        try {
            $webspaceSettingsForListView = $this->generateApiWebspaceSettingsEntityCollection($query['result']);
        } catch (\JsonException $e) {
            throw new NotFoundHttpException('Webspace settings not found', $e);
        }
        // $list = new CollectionRepresentation($webspaceSettingsForListView, 'webspace_settings');

        $list = new ListRepresentation(
            $webspaceSettingsForListView,
            WebspaceSettings::RESOURCE_KEY,
            'cget_webspace-settings',
            $request->query->all(),
            $query['page'],
            $query['limit'],
            $query['total'],
        );

        return $this->handleView($this->view($list, 200));
    }

    #[Route(
        '/webspace/settings/{id}.{_format}',
        name: 'get_webspace-settings',
        defaults: ['_format' => 'json'],
        methods: ['GET'],
    )]
    public function getAction(Request $request, $id): Response
    {
        $webspaceSettings = $this->entityManager->getRepository(WebspaceSettings::class)->find($id);
        if (!$webspaceSettings instanceof WebspaceSettings) {
            throw new NotFoundHttpException('Webspace settings not found');
        }

        $apiEntity = $this->generateApiWebspaceSettingsEntity($webspaceSettings);
        $view = $this->generateViewContent($apiEntity);

        return $this->handleView($view);
    }

    #[Route(
        '/webspace/settings.{_format}',
        name: 'post_webspace-settings',
        defaults: ['_format' => 'json'],
        methods: ['POST'],
    )]
    public function postAction(Request $request): Response
    {
        $webspace = $request->query->get('webspace');
        if (!$webspace) {
            throw new NotFoundHttpException('Webspace not found');
        }

        $userId = $this->getUser()->getId();
        $formData = $request->request->all();
        $checkData = $this->mapDataByType($formData['type'], $formData);

        if (null === $checkData[0] || '' === $checkData[0]) {
            throw new NotFoundHttpException('No data provided for type ' . $formData['type']);
        }

        $webspaceSettings = new WebspaceSettings();

        $this->mapDataToEntity($formData, $webspaceSettings);
        $webspaceSettings->setWebspaceKey($webspace);
        $webspaceSettings->setCreated(new \DateTimeImmutable());
        $webspaceSettings->setChanged(new \DateTime());
        $webspaceSettings->setIdUsersCreator($userId);
        $webspaceSettings->setIdUsersChanger($userId);

        $this->entityManager->persist($webspaceSettings);
        $this->entityManager->flush();

        // Event dispatching
        $this->eventDispatcher->dispatch(
            new WebspaceSettingsCreatedEvent($webspaceSettings),
            WebspaceSettingsCreatedEvent::class,
        );

        return $this->handleView($this->view($webspaceSettings, 201));
    }

    #[Route(
        '/webspace/settings/{id}.{_format}',
        name: 'put_webspace-settings',
        defaults: ['_format' => 'json'],
        methods: ['PUT'],
    )]
    public function putAction(Request $request, int $id): Response
    {
        $userId = $this->getUser()->getId();
        $userRoles = $this->getUser()->getRoles();
        $formData = $request->request->all();
        $checkData = $this->mapDataByType($formData['type'], $formData);

        if (null === $checkData[0] || '' === $checkData[0]) {
            throw new NotFoundHttpException('No data provided for type ' . $formData['type']);
        }

        $webspaceSettings = $this->entityManager->getRepository(WebspaceSettings::class)->find($id);
        if (!$webspaceSettings instanceof WebspaceSettings) {
            throw new NotFoundHttpException('Webspace settings not found');
        }

        if (!\in_array('ROLE_SULU_ADMIN', $userRoles, true) && (true === $webspaceSettings->isProtected() && $webspaceSettings->getIdUsersCreator() !== $userId)) {
            throw new NotFoundHttpException('This webspace settings is protected and cannot be changed.');
        }

        $webspaceSettings->setChanged(new \DateTime());
        $webspaceSettings->setIdUsersChanger($userId);

        $this->mapDataToEntity($formData, $webspaceSettings);
        $this->entityManager->flush();

        // Event dispatching
        $this->eventDispatcher->dispatch(
            new WebspaceSettingsUpdatedEvent($webspaceSettings),
            WebspaceSettingsUpdatedEvent::class,
        );

        return $this->handleView($this->view($webspaceSettings, 200));
    }

    #[Route(
        '/webspace/settings/{id}.{_format}',
        name: 'delete_webspace-settings',
        defaults: ['_format' => 'json'],
        methods: ['DELETE'],
    )]
    public function deleteAction(int $id): Response
    {
        $userId = $this->getUser()->getId();
        $userRoles = $this->getUser()->getRoles();

        $webspaceSettings = $this->entityManager->getReference(WebspaceSettings::class, $id);
        if (!$webspaceSettings instanceof WebspaceSettings) {
            throw new NotFoundHttpException('Webspace settings not found');
        }

        if (!\in_array('ROLE_SULU_ADMIN', $userRoles, true) && (true === $webspaceSettings->isProtected() && $webspaceSettings->getIdUsersCreator() !== $userId)) {
            throw new NotFoundHttpException('This webspace settings is protected and cannot be changed.');
        }

        // Event dispatching
        $this->eventDispatcher->dispatch(
            new WebspaceSettingsDeletedEvent($webspaceSettings),
            WebspaceSettingsDeletedEvent::class,
        );

        $this->entityManager->remove($webspaceSettings);
        $this->entityManager->flush();

        return $this->handleView($this->view(null, 204));
    }

    private function mapDataToEntity(array $data, WebspaceSettings $entity): void
    {
        $entity->setTitle($data['title']);
        $entity->setType($data['type']);
        $entity->setTypeKey($this->generateTypeKey($data['title'], $data['typeKey'] ?? ''));
        $entity->setData($this->mapDataByType($data['type'], $data));
        $entity->setDescription($data['description']);
        $entity->setLocale($this->mapLocale($data));
        $entity->setExecute($this->mapExecuteByType($data['type'], $data));
        $entity->setPublished($data['published']);
        $entity->setProtected($data['protected']);
    }

    private function mapDataByType($type, $data): array|null
    {
        return match ($type) {
            'string' => [$data['dataString'] ?? null],
            'textArea' => [$data['dataTextArea'] ?? null],
            'textEditor' => [$data['dataTextEditor'] ?? null],
            'event' => [$data['dataEvent'] ?? null],
            'media' => [$data['dataMedia'] ?? null],
            'medias' => [$data['dataMedias'] ?? null],
            'page' => [$data['dataPage'] ?? null],
            'pages' => [$data['dataPages'] ?? null],
            'contact' => [$data['dataContact'] ?? null],
            'contacts' => [$data['dataContacts'] ?? null],
            'organization' => [$data['dataAccount'] ?? null],
            'organizations' => [$data['dataAccounts'] ?? null],
            'blocks' => [$data['dataBlocks'] ?? null],
            default => null,
        };
    }

    private function mapLocale($data): string
    {
        return match ($data['localeActivated']) {
            true => $data['locale'] ?? '',
            default => '',
        };
    }

    private function mapExecuteByType($type, $data): bool
    {
        return match ($type) {
            'event' => false !== $data['execute'],
            default => false,
        };
    }

    private function generateTypeKey(string $title, string $typeKey = ''): string
    {
        $string = '' === $typeKey || '0' === $typeKey ? $title : $typeKey;

        $string = \strtr($string, [
            'ä' => 'ae', 'ö' => 'oe', 'ü' => 'ue',
            'Ä' => 'Ae', 'Ö' => 'Oe', 'Ü' => 'Ue',
            'ß' => 'ss',
        ]);

        $string = \iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $string);
        $string = \preg_replace('/[^a-zA-Z0-9]+/', ' ', $string);

        return \lcfirst(\str_replace(' ', '', \ucwords(\trim((string) $string))));
    }

    private function generateApiWebspaceSettingsEntityCollection(array $entities): array
    {
        $apiEntities = [];
        foreach ($entities as $entity) {
            if (!$entity instanceof WebspaceSettings) {
                throw new NotFoundHttpException('Webspace settings not found');
            }

            $apiEntry = $this->handleView($this->generateViewContent($this->generateApiWebspaceSettingsEntity($entity)));
            $apiArray = \json_decode((string) $apiEntry->getContent(), true, 512, \JSON_THROW_ON_ERROR);

            $apiModel = new WebspaceSettingsModel();
            $apiModel->setId($apiArray['id'] ?? null);
            $apiModel->setTitle($apiArray['title'] ?? null);
            $apiModel->setPublished(true === $apiArray['published'] ? null : false);
            $apiModel->setType($apiArray['type'] ?? null);
            $apiModel->setTypeKey($apiArray['typeKey'] ?? null);
            $apiModel->setData($apiArray['dataListView'] ?? null);
            $apiModel->setLocale($apiArray['locale'] ?? null);
            $apiModel->setCreated(new \DateTimeImmutable($apiArray['created']) ?? null);
            $apiModel->setChanged(new \DateTime($apiArray['changed']) ?? null);

            $apiEntities[] = $apiModel;
        }

        return $apiEntities;
    }

    private function generateApiWebspaceSettingsEntity(WebspaceSettings $entity): WebspaceSettingsApi
    {
        return new WebspaceSettingsApi($entity);
    }

    private function generateViewContent(WebspaceSettingsApi $entity): View
    {
        $view = $this->view($entity);
        $context = new Context();
        $context->setGroups(['fullWebspaceSettings']);

        return $view->setContext($context);
    }

    public function getSecurityContext(): string
    {
        $request = $this->requestStack->getCurrentRequest();

        return WebspaceSettingsAdmin::getWebspaceSettingsSecurityContext($request->query->get('webspace'));
    }
}
