<?php

declare(strict_types=1);

namespace Grav\Plugin;

use Grav\Common\Page\Interfaces\PageInterface;
use Grav\Common\Page\Page;
use Grav\Common\Page\Pages;
use Grav\Common\Plugin;
use Goosialize\Links\LinkPageConfigNormalizer;
use Goosialize\Links\PublicPageViewModelFactory;
use InvalidArgumentException;
use SplFileInfo;
use Throwable;

require_once __DIR__ . '/classes/LinkCollectionNormalizer.php';
require_once __DIR__ . '/classes/LinkPageConfigNormalizer.php';
require_once __DIR__ . '/classes/PublicPageViewModelFactory.php';

final class GoosializeLinksPlugin extends Plugin
{
    /**
     * @var array<string, mixed>|null
     */
    private ?array $normalizedConfig = null;

    /**
     * @var array<string, mixed>|null
     */
    private ?array $viewModel = null;

    private bool $publicPageMatched = false;

    public static function getSubscribedEvents(): array
    {
        return [
            'onPluginsInitialized' => [
                'onPluginsInitialized',
                0,
            ],
            'onTwigTemplatePaths' => [
                'onTwigTemplatePaths',
                0,
            ],
        ];
    }

    public function onPluginsInitialized(): void
    {
        if ($this->isAdmin()) {
            return;
        }

        $rawConfig = $this->config->get(
            'plugins.goosialize-links',
            []
        );

        if (!is_array($rawConfig)) {
            $this->logRuntimeFailure(
                new InvalidArgumentException(
                    'Plugin configuration must be an array.'
                )
            );

            return;
        }

        try {
            $normalizedConfig =
                (new LinkPageConfigNormalizer())->normalize(
                    $rawConfig
                );
        } catch (InvalidArgumentException $exception) {
            $this->logRuntimeFailure($exception);

            return;
        }

        if (!($normalizedConfig['enabled'] ?? false)) {
            return;
        }

        $this->normalizedConfig = $normalizedConfig;
        $this->viewModel =
            (new PublicPageViewModelFactory())->create(
                $normalizedConfig
            );

        $this->enable([
            'onPagesInitialized' => [
                'onPagesInitialized',
                0,
            ],
            'onTwigSiteVariables' => [
                'onTwigSiteVariables',
                0,
            ],
        ]);
    }

    public function onTwigTemplatePaths(): void
    {
        $this->grav['twig']->twig_paths[] =
            __DIR__ . '/templates';
    }

    public function onPagesInitialized(): void
    {
        if (
            $this->normalizedConfig === null ||
            $this->viewModel === null
        ) {
            return;
        }

        $route = (string) (
            $this->normalizedConfig['route'] ?? '/bio'
        );

        $requestedPath = $this->grav['uri']->path();

        if ($requestedPath !== $route) {
            return;
        }

        /** @var Pages $pages */
        $pages = $this->grav['pages'];
        $existingPage = $pages->find($route);

        if ($existingPage instanceof PageInterface) {
            $this->grav['log']->info(
                sprintf(
                    '[Goosialize Links] Existing Grav page preserved at route "%s".',
                    $route
                )
            );

            return;
        }

        $source =
            __DIR__ . '/pages/goosialize-links.md';

        if (!is_file($source)) {
            $this->logRuntimeFailure(
                new InvalidArgumentException(
                    'Public page definition is missing.'
                )
            );

            return;
        }

        try {
            $page = new Page();

            $page->init(
                new SplFileInfo($source)
            );

            $page->template('goosialize-links');

            $page->title(
                (string) $this->viewModel['page_title']
            );

            $pages->addPage($page, $route);

            unset($this->grav['page']);
            $this->grav['page'] = $page;

            $this->publicPageMatched = true;
        } catch (Throwable $exception) {
            $this->logRuntimeFailure($exception);
        }
    }

    public function onTwigSiteVariables(): void
    {
        if (
            !$this->publicPageMatched ||
            $this->viewModel === null
        ) {
            return;
        }

        $this->grav['twig']
            ->twig_vars['goosialize_links'] =
            $this->viewModel;
    }

    private function logRuntimeFailure(
        Throwable $exception
    ): void {
        $this->grav['log']->error(
            sprintf(
                '[Goosialize Links] Public page disabled: %s',
                $exception->getMessage()
            )
        );
    }
}
