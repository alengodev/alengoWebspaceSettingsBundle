<?php

declare(strict_types=1);

namespace Alengo\Bundle\AlengoWebspaceSettingsBundle\Controller\Admin;

use Alengo\Bundle\AlengoWebspaceSettingsBundle\Admin\WebspaceSettingsAdmin;
use Alengo\Bundle\AlengoWebspaceSettingsBundle\Api\WebspaceSettings as WebspaceSettingsApi;
use Alengo\Bundle\AlengoWebspaceSettingsBundle\Entity\WebspaceSettings;
use Doctrine\ORM\EntityManagerInterface;
use FOS\RestBundle\Context\Context;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use FOS\RestBundle\View\ViewHandlerInterface;
use HandcraftedInTheAlps\RestRoutingBundle\Controller\Annotations\RouteResource;
use HandcraftedInTheAlps\RestRoutingBundle\Routing\ClassResourceInterface;
use Sulu\Component\Rest\AbstractRestController;
use Sulu\Component\Rest\ListBuilder\CollectionRepresentation;
use Sulu\Component\Security\SecuredControllerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @RouteResource("webspace-settings")
 */
class WebspaceSettingsController extends AbstractRestController implements ClassResourceInterface, SecuredControllerInterface
{
    public function __construct(
        ViewHandlerInterface $viewHandler,
        private readonly EntityManagerInterface $entityManager,
        private readonly RequestStack $requestStack,
    ) {
        parent::__construct($viewHandler);
    }

    public function cgetAction(Request $request): Response
    {
        $webspace = $request->query->get('webspace');

        $webspaceSettings = $this->entityManager->getRepository(WebspaceSettings::class)->findBy([
            'webspaceKey' => $webspace,
        ]);

        $list = new CollectionRepresentation($webspaceSettings, 'webspace_settings');

        return $this->handleView($this->view($list, 200));
    }

    /**
     * @Rest\Get("/webspace-settings/{id}")
     */
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

    public function postAction(Request $request): Response
    {
        $webspace = $request->query->get('webspace');

        $webspaceSettings = new WebspaceSettings();

        $this->mapDataToEntity($request->request->all(), $webspaceSettings);
        $webspaceSettings->setWebspaceKey($webspace);

        $this->entityManager->persist($webspaceSettings);
        $this->entityManager->flush();

        return $this->handleView($this->view($webspaceSettings, 201));
    }

    /**
     * @Rest\Put("/webspace-settings/{id}")
     */
    public function putAction(Request $request, int $id): Response
    {
        $webspaceSettings = $this->entityManager->getRepository(WebspaceSettings::class)->find($id);

        if (!$webspaceSettings instanceof WebspaceSettings) {
            throw new NotFoundHttpException();
        }

        $this->mapDataToEntity($request->request->all(), $webspaceSettings);
        $this->entityManager->flush();

        return $this->handleView($this->view($webspaceSettings, 200));
    }

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
    }

    protected function mapDataByType($type, $data): array
    {
        $dataValue = match ($type) {
            'string', 'stringLocale' => [
                $data['dataString'] ?? ''
            ],
            'media' => [
                $data['dataMedia'] ?? [
                    'displayOptions' => null,
                    'id' => null,
                ]
            ],
            default => [],
        };

        return $dataValue;
    }

    protected function mapLocaleByType($type, $data): string
    {
        $dataValue = match ($type) {
            'stringLocale' => $data['locale'] ?? '',
            default => '',
        };

        return $dataValue;
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
        $string = \lcfirst(\str_replace(' ', '', \ucwords(\strtolower(\trim((string) $string)))));

        return $string;
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
