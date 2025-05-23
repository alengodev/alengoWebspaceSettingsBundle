<?php

declare(strict_types=1);

namespace Alengo\Bundle\AlengoWebspaceSettingsBundle\Controller\Admin;

use Alengo\Bundle\AlengoWebspaceSettingsBundle\Admin\WebspaceSettingsAdmin;
use Alengo\Bundle\AlengoWebspaceSettingsBundle\Api\WebspaceSettings as WebspaceSettingsApi;
use Alengo\Bundle\AlengoWebspaceSettingsBundle\Entity\WebspaceSettings;
use Alengo\Bundle\AlengoWebspaceSettingsBundle\Event\WebspaceSettingsCreatedEvent;
use Alengo\Bundle\AlengoWebspaceSettingsBundle\Event\WebspaceSettingsUpdatedEvent;
use Doctrine\ORM\EntityManagerInterface;
use FOS\RestBundle\Context\Context;
use FOS\RestBundle\View\View;
use FOS\RestBundle\View\ViewHandlerInterface;
use HandcraftedInTheAlps\RestRoutingBundle\Routing\ClassResourceInterface;
use Sulu\Component\Rest\AbstractRestController;
use Sulu\Component\Rest\ListBuilder\CollectionRepresentation;
use Sulu\Component\Security\SecuredControllerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;

class WebspaceSettingsController extends AbstractRestController implements ClassResourceInterface, SecuredControllerInterface
{
    public function __construct(
        ViewHandlerInterface $viewHandler,
        private readonly EntityManagerInterface $entityManager,
        private readonly RequestStack $requestStack,
        private readonly EventDispatcherInterface $eventDispatcher,
    ) {
        parent::__construct($viewHandler);
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

        $webspaceSettings = $this->entityManager->getRepository(WebspaceSettings::class)->findBy([
            'webspaceKey' => $webspace,
        ]);

        $list = new CollectionRepresentation($webspaceSettings, 'webspace_settings');

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
        $webspace = $request->query->get('webspace');

        $webspaceSettings = $this->entityManager->getRepository(WebspaceSettings::class)->find($id);
        if (!$webspaceSettings instanceof WebspaceSettings) {
            throw new NotFoundHttpException();
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

        $webspaceSettings = new WebspaceSettings();

        $this->mapDataToEntity($request->request->all(), $webspaceSettings);
        $webspaceSettings->setWebspaceKey($webspace);

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
        $webspaceSettings = $this->entityManager->getRepository(WebspaceSettings::class)->find($id);

        if (!$webspaceSettings instanceof WebspaceSettings) {
            throw new NotFoundHttpException();
        }

        $this->mapDataToEntity($request->request->all(), $webspaceSettings);
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
        $webspaceSettings = $this->entityManager->getReference(WebspaceSettings::class, $id);
        $this->entityManager->remove($webspaceSettings);
        $this->entityManager->flush();

        return $this->handleView($this->view(null, 204));
    }

    protected function mapDataToEntity(array $data, WebspaceSettings $entity): void
    {
        $entity->setTitle($data['title']);
        $entity->setType($data['type']);
        $entity->setTypeKey($this->generateTypeKey($data['title'], $data['typeKey'] ?? ''));
        $entity->setData($this->mapDataByType($data['type'], $data));
        $entity->setDescription($data['description']);
        $entity->setLocale($this->mapLocaleByType($data['type'], $data));
        $entity->setEnabled($data['enabled']);
        $entity->setExecute($this->mapExecuteByType($data['type'], $data));
    }

    protected function mapDataByType($type, $data): array
    {
        return match ($type) {
            'string', 'stringLocale' => [
                $data['dataString'] ?? '',
            ],
            'event' => [
                $data['dataEvent'] ?? '',
            ],
            'media' => [
                $data['dataMedia'] ?? [
                    'displayOptions' => null,
                    'id' => null,
                ],
            ],
            'medias' => $data['dataMedias'] ?? [],
            'contact' => [
                $data['dataContact'] ?? '',
            ],
            'contacts' => $data['dataContacts'] ?? [],
            'organization' => [
                $data['dataAccount'] ?? '',
            ],
            'organizations' => $data['dataAccounts'] ?? [],
            'blocks', 'blocksLocale' => $data['dataBlocks'] ?? [],
            default => [],
        };
    }

    protected function mapLocaleByType($type, $data): string
    {
        return match ($type) {
            'stringLocale', 'blocksLocale' => $data['locale'] ?? '',
            default => '',
        };
    }

    protected function mapExecuteByType($type, $data): bool
    {
        return match ($type) {
            'event' => null !== $data['execute'],
            default => false,
        };
    }

    protected function generateTypeKey(string $title, string $typeKey = ''): string
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

    protected function generateShortKey(int $length = 8): string
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $key = '';
        for ($i = 0; $i < $length; ++$i) {
            $key .= $characters[\random_int(0, \strlen($characters) - 1)];
        }

        return $key;
    }

    protected function generateApiWebspaceSettingsEntity(WebspaceSettings $entity): WebspaceSettingsApi
    {
        return new WebspaceSettingsApi($entity);
    }

    protected function generateViewContent(WebspaceSettingsApi $entity): View
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
