<?php

declare(strict_types=1);

namespace Grav\Plugin;

use Grav\Common\Page\Page;
use Grav\Common\Plugin;
use Grav\Framework\Acl\PermissionsReader;
use Grav\Framework\Acl\PermissionsRegisterEvent;
use Grav\Framework\Psr7\Response;
use Grav\Plugin\Api\Exceptions\ValidationException;
use Goosialize\Links\AnalyticsReportAggregator;
use Goosialize\Links\AnalyticsStore;
use Goosialize\Links\BusinessInformationNormalizer;
use Goosialize\Links\EditorPreviewController;
use Goosialize\Links\QrAdminController;
use Goosialize\Links\QrCodeGenerator;
use Goosialize\Links\QrRouteResolver;
use Goosialize\Links\LinkPageConfigNormalizer;
use Goosialize\Links\NativePageContentResolver;
use Goosialize\Links\NativePageLocator;
use Goosialize\Links\NativePageProvisioner;
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

require_once __DIR__ .
    '/classes/NativePageContentResolver.php';

require_once __DIR__ .
    '/classes/NativePageLocator.php';

require_once __DIR__ .
    '/classes/NativePageProvisioner.php';

require_once __DIR__ .
    '/classes/EditorPreviewState.php';

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
            PermissionsRegisterEvent::class => [
                'onRegisterPermissions',
                1000,
            ],
            'onApiRegisterRoutes' => [
                'onApiRegisterRoutes',
                0,
            ],
            'onApiSidebarItems' => [
                'onApiSidebarItems',
                0,
            ],
            'onApiPluginPageInfo' => [
                'onApiPluginPageInfo',
                0,
            ],
            'onApiBlueprintResolved' => [
                'onApiBlueprintResolved',
                0,
            ],
            'onApiGenerateReports' => [
                'onApiGenerateReports',
                0,
            ],
            'onAdminSave' => [
                'onAdminSave',
                1000,
            ],
            'onGetPageBlueprints' => ['onGetPageBlueprints', 0],
            'onGetPageTemplates' => ['onGetPageBlueprints', 0],
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

    public function onAdminSave(Event $event): void
    {
        $admin = $this->grav['admin'] ?? null;

        if (
            !is_object($admin) ||
            !property_exists($admin, 'route') ||
            $admin->route !== '/plugins/goosialize-links'
        ) {
            return;
        }

        $object = $event['object'] ?? null;

        if (!is_object($object) || !method_exists($object, 'toArray')) {
            return;
        }

        $config = $object->toArray();

        if (!is_array($config)) {
            return;
        }

        try {
            (new BusinessInformationNormalizer())->validate(
                $config['business'] ?? []
            );
        } catch (InvalidArgumentException $exception) {
            $key = $exception->getCode() ===
                BusinessInformationNormalizer::ERROR_MAPS_URL
                ? 'ICU.PLUGIN_GOOSIALIZE_LINKS.MAPS_URL_INVALID'
                : 'ICU.PLUGIN_GOOSIALIZE_LINKS.WORKING_HOURS_INVALID';
            $message = (string) $this->grav['language']->translate($key);

            throw new ValidationException(
                $message,
                ['business' => [$message]],
                $exception
            );
        }
    }


    public function onRegisterPermissions(
        PermissionsRegisterEvent $event
    ): void {
        $actions = PermissionsReader::fromYaml(
            "plugin://{$this->name}/permissions.yaml"
        );

        $event->permissions->addActions($actions);
    }

    public function onApiRegisterRoutes(
        Event $event
    ): void {
        require_once __DIR__ .
            '/classes/QrAdminController.php';

        require_once __DIR__ .
            '/classes/EditorPreviewController.php';

        $routes = $event['routes'] ?? null;

        if (
            !is_object($routes) ||
            !method_exists($routes, 'get')
        ) {
            return;
        }

        $routes->get(
            '/goosialize-links/qr',
            [
                QrAdminController::class,
                'pageData',
            ]
        );

        $routes->get(
            '/goosialize-links/editor-preview',
            [EditorPreviewController::class, 'data']
        );

        if (method_exists($routes, 'post')) {
            $routes->post(
                '/goosialize-links/editor-preview/state',
                [EditorPreviewController::class, 'state']
            );
        }

        if (method_exists($routes, 'delete')) {
            $routes->delete(
                '/goosialize-links/editor-preview/state',
                [EditorPreviewController::class, 'clear']
            );
        }

$routes->get(
    '/goosialize-links/dashboard',
    [QrAdminController::class, 'dashboardData']
);

        $routes->get(
            '/goosialize-links/qr/download/png',
            [
                QrAdminController::class,
                'downloadPng',
            ]
        );

        $routes->get(
            '/goosialize-links/qr/download/svg',
            [
                QrAdminController::class,
                'downloadSvg',
            ]
        );
    }

    public function onApiSidebarItems(
        Event $event
    ): void {
        if (!$this->qrAdminEnabled()) {
            return;
        }

        $user = $event['user'] ?? null;

        if (!$this->qrAdminAllowed($user)) {
            return;
        }

        $items = $event['items'] ?? [];

        if (!is_array($items)) {
            $items = [];
        }

        $items[] = [
            'id' => 'goosialize-links',
            'plugin' => 'goosialize-links',
            'label' =>
                $this->qrAdminTranslation(
                    'QR_ADMIN_TITLE',
                    'QR Code'
                ),
            'icon' => 'fa-qrcode',
            'route' => '/plugin/goosialize-links',
            'priority' => 19,
            'authorize' =>
                'api.goosialize-links.qr.read',
        ];

        $event['items'] = $items;
    }

    public function onApiPluginPageInfo(
        Event $event
    ): void {
        if (
            ($event['plugin'] ?? null) !==
                'goosialize-links' ||
            !$this->qrAdminEnabled()
        ) {
            return;
        }

        $user = $event['user'] ?? null;

        if (!$this->qrAdminAllowed($user)) {
            return;
        }

        $event['definition'] = [
            'id' => 'goosialize-links',
            'plugin' => 'goosialize-links',
            'title' =>
                $this->qrAdminTranslation(
                    'QR_ADMIN_TITLE',
                    'QR Code'
                ),
            'icon' => 'fa-qrcode',
            'page_type' => 'component',
            'actions' => [
                [
                    'id' => 'download-png',
                    'label' =>
                        $this->qrAdminTranslation(
                            'QR_DOWNLOAD_PNG',
                            'Download PNG'
                        ),
                    'icon' => 'fa-download',
                    'endpoint' =>
                        '/goosialize-links/qr/download/png',
                    'download' => true,
                ],
                [
                    'id' => 'download-svg',
                    'label' =>
                        $this->qrAdminTranslation(
                            'QR_DOWNLOAD_SVG',
                            'Download SVG'
                        ),
                    'icon' => 'fa-download',
                    'endpoint' =>
                        '/goosialize-links/qr/download/svg',
                    'download' => true,
                ],
            ],
        ];
    }

    public function onApiBlueprintResolved(
        Event $event
    ): void {
        if (
            ($event['context'] ?? null) !==
                'plugin-page' ||
            ($event['plugin'] ?? null) !==
                'goosialize-links' ||
            ($event['page_id'] ?? null) !==
                'goosialize-links'
        ) {
            return;
        }

        if (
            !$this->qrAdminAllowed(
                $event['user'] ?? null
            )
        ) {
            $event['fields'] = [];
        }
    }

    private function qrAdminTranslation(
        string $name,
        string $fallback
    ): string {
        $key =
            'ICU.PLUGIN_GOOSIALIZE_LINKS.'
            . $name;

        $language =
            $this->grav['language'] ?? null;

        if (
            is_object($language) &&
            method_exists(
                $language,
                'translate'
            )
        ) {
            try {
                $translated =
                    $language->translate($key);

                if (
                    is_string($translated) &&
                    trim($translated) !== '' &&
                    $translated !== $key &&
                    !str_contains(
                        $translated,
                        'PLUGIN_GOOSIALIZE_LINKS'
                    )
                ) {
                    return $translated;
                }
            } catch (\Throwable) {
                // Use the safe display fallback below.
            }
        }

        return $fallback;
    }

    private function qrAdminEnabled(): bool
    {
        return (bool) $this->grav['config']->get(
            'plugins.goosialize-links.enabled',
            true
        );
    }

    private function qrAdminAllowed(
        mixed $user
    ): bool {
        if (!is_object($user)) {
            return false;
        }

        try {
            if (method_exists($user, 'get')) {
                foreach (
                    [
                        'access.admin.super',
                        'access.api.super',
                        'access.api.goosialize-links.qr.read',
                    ] as $path
                ) {
                    if ((bool) $user->get($path)) {
                        return true;
                    }
                }
            }

            if (method_exists($user, 'authorize')) {
                foreach (
                    [
                        'admin.super',
                        'api.super',
                        'api.goosialize-links.qr.read',
                    ] as $permission
                ) {
                    if ((bool) $user->authorize($permission)) {
                        return true;
                    }
                }
            }
        } catch (Throwable) {
            return false;
        }

        return false;
    }

    public function onPluginsInitialized(): void
    {
        $this->provisionNativePage();

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

        $rawConfig = $this->previewConfig($rawConfig);

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

            $nativeRoute = (new NativePageLocator(
                GRAV_ROOT . '/user/pages'
            ))->route($this->defaultLanguage());
            if ($nativeRoute !== null) {
                $normalizedConfig['route'] = $nativeRoute;
            }
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

        $viewConfig = $normalizedConfig;
        $viewConfig['route'] = $this->localizedPublicRoute();

        $this->publicViewModel =
            (new PublicPageViewModelFactory())
                ->create(
                    $viewConfig,
                    $this->activeLanguage()
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

        $pages = $this->grav['pages'];
        $currentPath = $this->currentPath();
        $existingPage = $pages->find($currentPath);

        if (
            $existingPage !== null &&
            $existingPage->template() === 'goosialize-links'
        ) {
            $viewConfig = $this->normalizedConfig;
            $viewConfig['route'] = $currentPath;
            $viewConfig = (new NativePageContentResolver())->apply(
                $viewConfig,
                (array) $existingPage->header()
            );
            $this->publicViewModel = (new PublicPageViewModelFactory())->create(
                $viewConfig,
                $this->activeLanguage()
            );
            $this->ownsPublicPage = true;
            return;
        }

        if (
            $route === '' ||
            $this->stripLanguagePrefix(
                $currentPath
            ) !== $route
        ) {
            return;
        }

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
            $currentPath
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

    public function onGetPageBlueprints(Event $event): void
    {
        $types = $event['types'] ?? $event->types ?? null;
        if (is_object($types) && method_exists($types, 'register')) {
            $types->register(
                'goosialize-links',
                'plugin://goosialize-links/blueprints/pages/goosialize-links.yaml'
            );
        }
    }

    public function onTwigSiteVariables(): void
    {
        if (
            !$this->ownsPublicPage ||
            $this->publicViewModel === null
        ) {
            return;
        }

        if (
            !$this->pageViewRecorded &&
            !$this->isAuthorizedPreviewRequest()
        ) {
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
                        $this->stripLanguagePrefix(
                            $this->currentPath()
                        ),
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

        $publicRoute = $this->localizedPublicRoute();

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
                    $this->stripLanguagePrefix(
                        $this->currentPath()
                    ),
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

    private function activeLanguage(): string
    {
        $language = $this->grav['language'] ?? null;

        if (is_object($language) && method_exists($language, 'getActive')) {
            try {
                return strtolower(trim((string) $language->getActive()));
            } catch (Throwable) {
                return '';
            }
        }

        return '';
    }

    private function defaultLanguage(): string
    {
        return strtolower((string) $this->grav['config']->get(
            'system.languages.default_lang',
            ''
        ));
    }

    private function stripLanguagePrefix(string $path): string
    {
        $language = $this->activeLanguage();

        if ($language !== '' && str_starts_with($path, '/' . $language . '/')) {
            return substr($path, strlen($language) + 1) ?: '/';
        }

        return $path;
    }

    private function localizedPublicRoute(): string
    {
        $route = (string) ($this->normalizedConfig['route'] ?? '/bio');
        $path = $this->currentPath();
        $language = $this->activeLanguage();

        if ($language !== '' && str_starts_with($path, '/' . $language . '/')) {
            return '/' . $language . $route;
        }

        return $route;
    }

    private function isAuthorizedPreviewRequest(): bool
    {
        if ((string) ($_GET['goosialize-links-preview'] ?? '') !== '1') {
            return false;
        }

        return $this->previewState() !== null ||
            $this->qrAdminAllowed($this->grav['user'] ?? null);
    }

    /** @param array<string, mixed> $saved @return array<string, mixed> */
    private function previewConfig(array $saved): array
    {
        $state = $this->previewState();
        return is_array($state['config'] ?? null) ? $state['config'] : $saved;
    }

    /** @return array<string, mixed>|null */
    private function previewState(): ?array
    {
        $token = strtolower(trim((string) ($_GET['goosialize-links-preview-token'] ?? '')));
        if (preg_match('/^[a-f0-9]{32}$/', $token) !== 1) return null;
        try {
            $session = $this->grav['session'];
            $session->start();
            $states = is_array($session->{EditorPreviewController::SESSION_KEY} ?? null)
                ? $session->{EditorPreviewController::SESSION_KEY} : [];
            $state = $states[$token] ?? null;
            if (!is_array($state) || (int) ($state['created'] ?? 0) < time() - 3600) return null;
            return $state;
        } catch (Throwable) {
            return null;
        }
    }

    private function provisionNativePage(): void
    {
        $rawConfig = $this->config->get('plugins.goosialize-links', []);
        if (!is_array($rawConfig) || !($rawConfig['enabled'] ?? true)) {
            return;
        }

        try {
            $languages = (array) $this->grav['config']->get(
                'system.languages.supported',
                []
            );
            $default = strtolower((string) $this->grav['config']->get(
                'system.languages.default_lang',
                ''
            ));
            (new NativePageProvisioner(
                GRAV_ROOT . '/user/pages',
                GRAV_ROOT . '/user/data/goosialize-links/migration-backups'
            ))->provision(
                $rawConfig,
                $languages,
                $default,
                GRAV_ROOT . '/user/config/plugins/goosialize-links.yaml'
            );
        } catch (Throwable $exception) {
            $this->grav['log']->error(
                'plugin.goosialize-links: native Page provisioning failed: ' .
                $exception->getMessage()
            );
        }
    }
}
