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

namespace Alengo\Bundle\AlengoWebspaceSettingsBundle\DependencyInjection;

use Sulu\Bundle\PersistenceBundle\DependencyInjection\PersistenceExtensionTrait;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class AlengoWebspaceSettingsExtension extends Extension implements PrependExtensionInterface
{
    use PersistenceExtensionTrait;

    /**
     * Allow an extension to prepend the extension configurations.
     */
    public function prepend(ContainerBuilder $container): void
    {
        if ($container->hasExtension('sulu_admin')) {
            $container->prependExtensionConfig(
                'sulu_admin',
                [
                    'lists' => [
                        'directories' => [
                            __DIR__ . '/../../config/lists',
                        ],
                    ],
                    'forms' => [
                        'directories' => [
                            __DIR__ . '/../../config/forms',
                        ],
                    ],
                    'resources' => [
                        'webspace_settings' => [
                            'routes' => [
                                'list' => 'alengo_webspace_settings.cget_webspace-settings',
                                'detail' => 'alengo_webspace_settings.get_webspace-settings',
                            ],
                        ],
                    ],
                ],
            );
        }

        $container->loadFromExtension('framework', [
            'default_locale' => 'en',
            'translator' => ['paths' => [__DIR__ . '/../../translations/']],
            // ...
        ]);
    }

    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $yamlLoader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../../config'));
        $yamlLoader->load('services.yaml');
        $yamlLoader->load('controller.yaml');

        $container->setParameter('alengo_webspace_settings.type_select', $config['type_select']);
    }
}
