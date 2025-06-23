<?php

namespace Alengo\Bundle\AlengoWebspaceSettingsBundle\Form;

use Symfony\Component\Config\FileLocator;

final class FormXmlLoader
{
    public function __construct(
        private readonly FileLocator $locator,
        private readonly string $projectDir
    ) {}

    public function load(string $file): \DOMDocument
    {
        $path = $this->locator->locate($file);

        $dom = new \DOMDocument();
        $dom->load($path);

        $includes = $dom->getElementsByTagNameNS('http://www.w3.org/2001/XInclude', 'include');

        foreach ($includes as $node) {
            $href = $node->getAttribute('href');
            $overridePath = $this->projectDir . '/config/forms/' . \basename($href);

            if (\file_exists($overridePath)) {
                $node->setAttribute('href', $overridePath);
            } else {
                $node->setAttribute('href', \dirname($path) . '/' . $href);
            }
        }

        $dom->xinclude();

        return $dom;
    }
}