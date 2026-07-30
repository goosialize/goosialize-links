<?php

declare(strict_types=1);

namespace Grav\Plugin;

use Grav\Common\Page\Page;
use Grav\Common\Plugin;
use Grav\Framework\Psr7\Response;
use Goosialize\Links\AnalyticsReportAggregator;
use Goosialize\Links\AnalyticsStore;
use Goosialize\Links\QrCodeGenerator;
use Goosialize\Links\QrRouteResolver;
use Goosialize\Links\LinkPageConfigNormalizer;
use Goosialize\Links\PublicPageExperienceNormalizer;
use Goosialize\Links\PublicPageViewModelFactory;
use Goosialize\Links\TrackingRouteResolver;
use InvalidArgumentException;
use RuntimeException;
use SplFileInfo;
use Throwable;
use RocketTheme\Toolbox\Event\Event;

$composerAutoload =
    __DIR__ . '/vendor/autoload.php';

if (is_file($composerAutoload)) {
    require_once $composerAutoload;
}

unset($composerAutoload);

require_once __DIR__ .
    '/classes/LinkCollectionNormalizer.php';

require_once __DIR__ .
    '/classes/LinkPageConfigNormalizer.php';

require_once __DIR__ .
    '/classes/SocialActionNormalizer.php';

require_once __DIR__ .
    '/classes/PublicPageExperienceNormalizer.php';

require_once __DIR__ .
    '/classes/ProfileImageResolver.php';

require_once __DIR__ .
    '/classes/TrackingRouteResolver.php';

require_once __DIR__ .
    '/classes/AnalyticsStore.php';

require_once __DIR__ .
    '/classes/QrRouteResolver.php';

require_once __DIR__ .
    '/classes/QrCodeGenerator.php';

require_once __DIR__ .
    '/classes/AnalyticsReportAggregator.php';

require_once __DIR__ .
    '/classes/PublicPageViewModelFactory.php';

final class GoosializeLinksPlugin extends Plugin
{
    /**
     * @var array<string, mixed>|null
     */
    private ?array $normalizedConfig = null;

    /**
     * @var array<string, mixed>|null
     */
    private ?array $publicViewModel = null;

    private ?AnalyticsStore $analyticsStore = null;

    private bool $ownsPublicPage = false;

    private bool $pageViewRecorded = false;

    public static function getSubscribedEvents(): array
    {
        return [
            'onApiGenerateReports' => [
                'onApiGenerateReports',
                0,
            ],
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
            $this->grav['log']->error(
                'plugin.goosialize-links: ' .
                'configuration must be an array.'
            );

            return;
        }

        try {
            $baseConfig =
                (new LinkPageConfigNormalizer())
                    ->normalize($rawConfig);

            $normalizedConfig =
                (new PublicPageExperienceNormalizer())
                    ->normalize(
                        $rawConfig,
                        $baseConfig
                    );
        } catch (InvalidArgumentException $exception) {
            $this->grav['log']->error(
                'plugin.goosialize-links: ' .
                $exception->getMessage()
            );

            return;
        }

        if (
            !($normalizedConfig['enabled'] ?? false)
        ) {
            return;
        }

        $this->normalizedConfig =
            $normalizedConfig;

        $this->analyticsStore =
            new AnalyticsStore(
                GRAV_ROOT .
                '/user/data/goosialize-links/analytics'
            );

        if ($this->handleQrRequest()) {
            return;
        }

        if ($this->handleTrackingRequest()) {
            return;
        }

        $this->publicViewModel =
            (new PublicPageViewModelFactory())
                ->create($normalizedConfig);

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

    public function onPagesInitialized(): void
    {
        if (
            $this->normalizedConfig === null ||
            $this->publicViewModel === null
        ) {
            return;
        }

        $route = (string) (
            $this->normalizedConfig['route'] ??
            ''
        );

        if (
            $route === '' ||
            $this->currentPath() !== $route
        ) {
            return;
        }

        $pages = $this->grav['pages'];

        $existingPage =
            $pages->find($route);

        if ($existingPage !== null) {
            $this->grav['log']->info(
                sprintf(
                    'plugin.goosialize-links: ' .
                    'Existing Grav page preserved for route "%s".',
                    $route
                )
            );

            return;
        }

        $pageFile =
            __DIR__ .
            '/pages/goosialize-links.md';

        if (!is_file($pageFile)) {
            throw new RuntimeException(
                'Goosialize Links virtual page is missing.'
            );
        }

        $page = new Page();

        $page->init(
            new SplFileInfo($pageFile)
        );

        $page->template(
            'goosialize-links'
        );

        $page->title(
            (string) (
                $this->publicViewModel['page_title'] ??
                'Links'
            )
        );

        $pages->addPage(
            $page,
            $route
        );

        unset($this->grav['page']);

        $this->grav['page'] = $page;

        $this->ownsPublicPage = true;
    }

    public function onTwigTemplatePaths(): void
    {
        $this->grav['twig']->twig_paths[] =
            __DIR__ .
            '/templates';
    }

    public function onTwigSiteVariables(): void
    {
        if (
            !$this->ownsPublicPage ||
            $this->publicViewModel === null
        ) {
            return;
        }

        if (!$this->pageViewRecorded) {
            $this->recordAnalytics(
                'page_view'
            );

            $this->pageViewRecorded = true;
        }

        $this->grav['twig']
            ->twig_vars['goosialize_links'] =
                $this->publicViewModel;
    }

    public function onApiGenerateReports(
        Event $event
    ): void {
        $reports =
            $event['reports'] ?? null;

        if (!is_array($reports)) {
            return;
        }

        foreach ($reports as $report) {
            if (
                is_array($report) &&
                ($report['id'] ?? null) ===
                    'goosialize-links-analytics'
            ) {
                return;
            }
        }

        try {
            $rawConfig = $this->config->get(
                'plugins.goosialize-links',
                []
            );

            if (!is_array($rawConfig)) {
                throw new RuntimeException(
                    'Configuration must be an array.'
                );
            }

            $baseConfig =
                (new LinkPageConfigNormalizer())
                    ->normalize($rawConfig);

            $normalizedConfig =
                (new PublicPageExperienceNormalizer())
                    ->normalize(
                        $rawConfig,
                        $baseConfig
                    );

            if (
                !($normalizedConfig['enabled'] ?? false)
            ) {
                return;
            }

            $report =
                (new AnalyticsReportAggregator(
                    GRAV_ROOT .
                    '/user/data/goosialize-links/analytics'
                ))->createReport(
                    $normalizedConfig
                );
        } catch (Throwable $exception) {
            $this->grav['log']->error(
                'plugin.goosialize-links: ' .
                'analytics report generation failed: ' .
                $exception->getMessage()
            );

            $report = [
                'id' =>
                    'goosialize-links-analytics',
                'title' =>
                    'Goosialize Links Analytics',
                'provider' =>
                    'goosialize-links',
                'component' => null,
                'status' => 'error',
                'message' =>
                    'Analytics data is currently unavailable.',
                'items' => [],
            ];
        }

        $reports[] = $report;
        $event['reports'] = $reports;
    }


    private function handleQrRequest(): bool
    {
        if (
            $this->normalizedConfig === null ||
            $this->analyticsStore === null
        ) {
            return false;
        }

        $method = strtoupper(
            (string) (
                $_SERVER['REQUEST_METHOD'] ??
                'GET'
            )
        );

        if ($method !== 'GET') {
            return false;
        }

        try {
            $request =
                (new QrRouteResolver())
                    ->resolve(
                        $this->currentPath(),
                        (string) (
                            $this->normalizedConfig['route'] ??
                            ''
                        )
                    );
        } catch (InvalidArgumentException) {
            return false;
        }

        if ($request === null) {
            return false;
        }

        $publicRoute =
            (string) (
                $this->normalizedConfig['route'] ??
                ''
            );

        if ($request['kind'] === 'track') {
            $this->recordAnalytics(
                'qr_visit',
                $request['id']
            );

            $this->grav->close(
                new Response(
                    302,
                    [
                        'Location' => $publicRoute,
                        'Cache-Control' =>
                            'no-store, max-age=0',
                        'Referrer-Policy' =>
                            'no-referrer',
                        'X-Content-Type-Options' =>
                            'nosniff',
                    ]
                )
            );

            return true;
        }

        try {
            $trackedUrl =
                $this->absoluteUrl(
                    $publicRoute .
                    '/qr/' .
                    $request['id']
                );

            $generator =
                new QrCodeGenerator();

            if ($request['kind'] === 'png') {
                $content =
                    $generator->generatePng(
                        $trackedUrl
                    );

                $contentType = 'image/png';
                $filename =
                    'goosialize-links-qr.png';
            } else {
                $content =
                    $generator->generateSvg(
                        $trackedUrl
                    );

                $contentType =
                    'image/svg+xml; charset=UTF-8';

                $filename =
                    'goosialize-links-qr.svg';
            }
        } catch (Throwable $exception) {
            $this->grav['log']->error(
                'plugin.goosialize-links: ' .
                'QR generation failed: ' .
                $exception->getMessage()
            );

            $this->grav->close(
                new Response(
                    503,
                    [
                        'Content-Type' =>
                            'text/plain; charset=UTF-8',
                        'Cache-Control' =>
                            'no-store, max-age=0',
                        'X-Content-Type-Options' =>
                            'nosniff',
                    ],
                    'QR code is temporarily unavailable.'
                )
            );

            return true;
        }

        $this->grav->close(
            new Response(
                200,
                [
                    'Content-Type' =>
                        $contentType,
                    'Content-Disposition' =>
                        sprintf(
                            'inline; filename="%s"',
                            $filename
                        ),
                    'Cache-Control' =>
                        'no-store, max-age=0',
                    'Referrer-Policy' =>
                        'no-referrer',
                    'X-Content-Type-Options' =>
                        'nosniff',
                    'Content-Length' =>
                        (string) strlen($content),
                ],
                $content
            )
        );

        return true;
    }

    private function handleTrackingRequest(): bool
    {
        if (
            $this->normalizedConfig === null ||
            $this->analyticsStore === null
        ) {
            return false;
        }

        $method = strtoupper(
            (string) (
                $_SERVER['REQUEST_METHOD'] ??
                'GET'
            )
        );

        if ($method !== 'GET') {
            return false;
        }

        $resolver =
            new TrackingRouteResolver();

        try {
            $trackingRequest =
                $resolver->resolve(
                    $this->currentPath(),
                    (string) (
                        $this->normalizedConfig['route'] ??
                        ''
                    )
                );
        } catch (InvalidArgumentException) {
            return false;
        }

        if ($trackingRequest === null) {
            return false;
        }

        $destination =
            $this->findTrackedDestination(
                $trackingRequest['event'],
                $trackingRequest['id']
            );

        if ($destination === null) {
            return false;
        }

        $this->recordAnalytics(
            $trackingRequest['event'],
            $trackingRequest['id']
        );

        $response = new Response(
            302,
            [
                'Location' => $destination,
                'Cache-Control' =>
                    'no-store, max-age=0',
                'Referrer-Policy' =>
                    'no-referrer',
            ]
        );

        $this->grav->close($response);

        return true;
    }

    private function findTrackedDestination(
        string $event,
        string $id
    ): ?string {
        if ($this->normalizedConfig === null) {
            return null;
        }

        if ($event === 'link_click') {
            foreach (
                (array) (
                    $this->normalizedConfig['links'] ??
                    []
                ) as $link
            ) {
                if (
                    !is_array($link) ||
                    !($link['enabled'] ?? false) ||
                    ($link['id'] ?? null) !== $id
                ) {
                    continue;
                }

                $destination = trim(
                    (string) (
                        $link['url'] ?? ''
                    )
                );

                return $destination !== ''
                    ? $destination
                    : null;
            }

            return null;
        }

        if ($event === 'action_click') {
            foreach (
                (array) (
                    $this->normalizedConfig['actions'] ??
                    []
                ) as $action
            ) {
                if (
                    !is_array($action) ||
                    !($action['enabled'] ?? false) ||
                    ($action['id'] ?? null) !== $id
                ) {
                    continue;
                }

                $destination = trim(
                    (string) (
                        $action['href'] ?? ''
                    )
                );

                return $destination !== ''
                    ? $destination
                    : null;
            }
        }

        return null;
    }

    private function recordAnalytics(
        string $event,
        ?string $id = null
    ): void {
        if ($this->analyticsStore === null) {
            return;
        }

        try {
            if ($event === 'page_view') {
                $this->analyticsStore
                    ->recordPageView();

                return;
            }

            if (
                $event === 'link_click' &&
                $id !== null
            ) {
                $this->analyticsStore
                    ->recordLinkClick($id);

                return;
            }

            if (
                $event === 'action_click' &&
                $id !== null
            ) {
                $this->analyticsStore
                    ->recordActionClick($id);

                return;
            }

            if (
                $event === 'qr_visit' &&
                $id !== null
            ) {
                $this->analyticsStore
                    ->recordQrVisit($id);
            }
        } catch (Throwable $exception) {
            $this->grav['log']->error(
                sprintf(
                    'plugin.goosialize-links: ' .
                    'analytics write failed for "%s": %s',
                    $event,
                    $exception->getMessage()
                )
            );
        }
    }

    private function absoluteUrl(
        string $path
    ): string {
        $rootUrl = rtrim(
            (string) $this->grav['uri']
                ->rootUrl(true),
            '/'
        );

        if (
            $rootUrl === '' ||
            filter_var(
                $rootUrl,
                FILTER_VALIDATE_URL
            ) === false
        ) {
            throw new RuntimeException(
                'Unable to resolve the absolute site URL.'
            );
        }

        return
            $rootUrl .
            '/' .
            ltrim($path, '/');
    }

    private function currentPath(): string
    {
        $path = trim(
            (string) $this->grav['uri']->path()
        );

        if ($path === '' || $path === '/') {
            return '/';
        }

        return '/' . trim($path, '/');
    }
}
