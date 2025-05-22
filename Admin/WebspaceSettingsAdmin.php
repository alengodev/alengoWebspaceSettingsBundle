<?php

declare(strict_types=1);

namespace Alengo\Bundle\AlengoWebspaceSettingsBundle\Admin;

use Alengo\Bundle\AlengoWebspaceSettingsBundle\Entity\WebspaceSettings;
use Sulu\Bundle\AdminBundle\Admin\Admin;
use Sulu\Bundle\AdminBundle\Admin\View\ToolbarAction;
use Sulu\Bundle\AdminBundle\Admin\View\ViewBuilderFactoryInterface;
use Sulu\Bundle\AdminBundle\Admin\View\ViewCollection;
use Sulu\Bundle\PageBundle\Admin\PageAdmin;
use Sulu\Component\Security\Authorization\PermissionTypes;
use Sulu\Component\Security\Authorization\SecurityCheckerInterface;
use Sulu\Component\Webspace\Manager\WebspaceManagerInterface;
use Sulu\Component\Webspace\Webspace;

class WebspaceSettingsAdmin extends Admin
{
    public const WEBSPACE_SETTINGS_LIST_KEY = 'webspace_settings';
    public const WEBSPACE_SETTINGS_LIST_VIEW = 'app.webspace_settings_list';

    public function __construct(
        private readonly WebspaceManagerInterface $webspaceManager,
        private readonly ViewBuilderFactoryInterface $viewBuilderFactory,
        private readonly SecurityCheckerInterface $securityChecker,
    ) {
    }

    public function configureViews(ViewCollection $viewCollection): void
    {
        if ($this->hasSomeWebspaceSettingsPermission()) {
            $toolbarActions = [
                new ToolbarAction('sulu_admin.add'),
                new ToolbarAction('sulu_admin.delete'),
            ];

            $viewCollection->add(
                $this->viewBuilderFactory->createFormOverlayListViewBuilder(static::WEBSPACE_SETTINGS_LIST_VIEW, '/webspace-settings')
                    ->setResourceKey(WebspaceSettings::RESOURCE_KEY)
                    ->setListKey(self::WEBSPACE_SETTINGS_LIST_KEY)
                    ->addListAdapters(['table'])
                    ->addAdapterOptions(['table' => ['skin' => 'light']])
                    ->addRouterAttributesToListRequest(['webspace'])
                    ->addRouterAttributesToFormRequest(['webspace'])
                    ->disableSearching()
                    ->setFormKey('webspace_settings_details')
                    ->setTabTitle('alengo_webspace_settings.title')
                    ->setTabOrder(8096)
                    ->addToolbarActions($toolbarActions)
                    ->setParent(PageAdmin::WEBSPACE_TABS_VIEW)
                    ->addRerenderAttribute('webspace'),
            );
        }
    }

    public function getSecurityContexts(): array
    {
        $webspaceContexts = [];
        /* @var Webspace $webspace */
        foreach ($this->webspaceManager->getWebspaceCollection() as $webspace) {
            $securityContextKey = self::getWebspaceSettingsSecurityContext($webspace->getKey());
            $webspaceContexts[$securityContextKey] = $this->getSecurityContextPermissions();
        }

        return [
            self::SULU_ADMIN_SECURITY_SYSTEM => [
                'Webspaces' => $webspaceContexts,
            ],
        ];
    }

    public function getSecurityContextsWithPlaceholder(): array
    {
        return [
            self::SULU_ADMIN_SECURITY_SYSTEM => [
                'Webspaces' => [
                    self::getWebspaceSettingsSecurityContext('#webspace#') => $this->getSecurityContextPermissions(),
                ],
            ],
        ];
    }

    /**
     * Returns security context for settings in given webspace.
     *
     * @final
     *
     * @param string $webspaceKey
     */
    public static function getWebspaceSettingsSecurityContext($webspaceKey): string
    {
        return \sprintf('%s%s.%s', PageAdmin::SECURITY_CONTEXT_PREFIX, $webspaceKey, 'settings');
    }

    /**
     * @return string[]
     */
    private function getSecurityContextPermissions(): array
    {
        return [
            PermissionTypes::VIEW,
            PermissionTypes::ADD,
            PermissionTypes::EDIT,
            PermissionTypes::DELETE,
        ];
    }

    private function hasSomeWebspaceSettingsPermission(): bool
    {
        foreach ($this->webspaceManager->getWebspaceCollection()->getWebspaces() as $webspace) {
            $hasWebspaceAnalyticsPermission = $this->securityChecker->hasPermission(
                self::getWebspaceSettingsSecurityContext($webspace->getKey()),
                PermissionTypes::EDIT,
            );

            if ($hasWebspaceAnalyticsPermission) {
                return true;
            }
        }

        return false;
    }
}
