<?php

declare(strict_types=1);

namespace Goosialize\Links;

use Grav\Common\Language\LanguageCodes;
use Grav\Plugin\Api\Controllers\AbstractApiController;
use Grav\Framework\Psr7\Response;
use InvalidArgumentException;
use JsonException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class EditorPreviewController extends AbstractApiController
{
    public const SESSION_KEY = 'goosialize_links_preview_states';

    private const PERMISSION =
        'api.goosialize-links.qr.read';

    public function data(
        ServerRequestInterface $request
    ): ResponseInterface {
        $this->requirePermission(
            $request,
            self::PERMISSION
        );

        $token = bin2hex(random_bytes(16));
        $session = $this->grav['session'];
        $session->start();
        $states = is_array($session->{self::SESSION_KEY} ?? null)
            ? $session->{self::SESSION_KEY} : [];
        $states[$token] = [
            'config' => (array) $this->grav['config']->get('plugins.goosialize-links', []),
            'created' => time(),
        ];
        $session->{self::SESSION_KEY} = $states;

        $route = (new LinkPageConfigNormalizer())
            ->normalize(
                (array) $this->grav['config']->get(
                    'plugins.goosialize-links',
                    []
                )
            )['route'];

        $supported = (array) $this->grav['config']->get(
            'system.languages.supported',
            []
        );
        $default = strtolower((string) $this->grav['config']->get(
            'system.languages.default_lang',
            ''
        ));
        $includeDefault = (bool) $this->grav['config']->get(
            'system.languages.include_default_lang',
            false
        );
        $nativeRoute = (new NativePageLocator(GRAV_ROOT . '/user/pages'))->route($default);
        if ($nativeRoute !== null) {
            $route = $nativeRoute;
        }
        $languages = [];

        foreach ($supported as $key => $value) {
            $code = is_string($key) ? $key : (string) $value;
            $code = strtolower(trim($code));

            if (preg_match('/^[a-z]{2,3}(?:-[a-z0-9]{2,8})*$/', $code) !== 1) {
                continue;
            }

            $label = trim((string) LanguageCodes::getNativeName($code));
            $prefix = ($code === $default && !$includeDefault)
                ? ''
                : '/' . $code;

            $languages[] = [
                'code' => $code,
                'label' => $label !== '' ? $label : strtoupper($code),
                'preview_path' => $prefix . $route . '?goosialize-links-preview=1',
                'public_path' => $prefix . $route,
            ];
        }

        if ($languages === []) {
            $fallbackCode = $default !== '' ? $default : 'en';
            $languages[] = [
                'code' => $fallbackCode,
                'label' => (string) LanguageCodes::getNativeName($fallbackCode),
                'preview_path' => $route . '?goosialize-links-preview=1',
                'public_path' => $route,
            ];
        }

        try {
            $json = json_encode(
                [
                    'token' => $token,
                    'languages' => $languages,
                    'translations' => $this->uiTranslations(),
                ],
                JSON_UNESCAPED_SLASHES |
                JSON_UNESCAPED_UNICODE |
                JSON_THROW_ON_ERROR
            );
        } catch (JsonException $exception) {
            throw new \RuntimeException(
                'Editor preview response encoding failed.',
                0,
                $exception
            );
        }

        return new Response(
            200,
            [
                'Content-Type' => 'application/json; charset=utf-8',
                'Content-Length' => (string) strlen($json),
                'Cache-Control' => 'no-store, private',
                'X-Content-Type-Options' => 'nosniff',
            ],
            $json
        );
    }

    public function state(ServerRequestInterface $request): ResponseInterface
    {
        $this->requirePermission($request, self::PERMISSION);

        try {
            $payload = json_decode((string) $request->getBody(), true, 32, JSON_THROW_ON_ERROR);
            if (!is_array($payload)) throw new InvalidArgumentException('Preview payload must be an object.');
            $token = $this->token($payload['token'] ?? '');
            $draft = $payload['config'] ?? null;
            if (!is_array($draft)) throw new InvalidArgumentException('Preview configuration must be an object.');
            $saved = (array) $this->grav['config']->get('plugins.goosialize-links', []);
            $config = (new EditorPreviewState())->merge($draft, $saved);
            $session = $this->grav['session'];
            $session->start();
            $states = is_array($session->{self::SESSION_KEY} ?? null) ? $session->{self::SESSION_KEY} : [];
            $states = array_filter($states, static fn (mixed $state): bool =>
                is_array($state) && (int) ($state['created'] ?? 0) >= time() - 3600
            );
            $states[$token] = ['config' => $config, 'created' => time()];
            $session->{self::SESSION_KEY} = $states;
            return $this->json(['token' => $token]);
        } catch (JsonException|InvalidArgumentException $exception) {
            return $this->json(['error' => $exception->getMessage()], 422);
        }
    }

    public function clear(ServerRequestInterface $request): ResponseInterface
    {
        $this->requirePermission($request, self::PERMISSION);
        $token = $this->token($request->getQueryParams()['token'] ?? '');
        $session = $this->grav['session'];
        $session->start();
        $states = is_array($session->{self::SESSION_KEY} ?? null) ? $session->{self::SESSION_KEY} : [];
        unset($states[$token]);
        $session->{self::SESSION_KEY} = $states;
        return $this->json(['cleared' => true]);
    }

    /**
     * @return array<string, string>
     */
    private function uiTranslations(): array
    {
        $language = $this->grav['language'] ?? null;
        $translations = [];

        foreach ([
            'LIVE_PREVIEW',
            'PREVIEW',
            'PREVIEW_HELP',
            'PREVIEW_LANGUAGE',
            'REFRESH_PREVIEW',
            'OPEN_PUBLIC_PAGE',
            'PREVIEW_LOADING',
            'PREVIEW_READY',
            'PREVIEW_ERROR',
            'PREVIEW_UPDATING',
            'PREVIEW_UNSAVED',
            'NEW_ACTION',
            'NEW_LINK',
        ] as $name) {
            $key = 'ICU.PLUGIN_GOOSIALIZE_LINKS.' . $name;
            $value = is_object($language) && method_exists($language, 'translate')
                ? $language->translate($key)
                : $key;
            $translations[$name] = is_string($value) ? $value : $key;
        }

        return $translations;
    }

    private function token(mixed $value): string
    {
        $token = strtolower(trim((string) $value));
        if (preg_match('/^[a-f0-9]{32}$/', $token) !== 1) {
            throw new InvalidArgumentException('Invalid preview token.');
        }
        return $token;
    }

    /** @param array<string, mixed> $data */
    private function json(array $data, int $status = 200): ResponseInterface
    {
        $json = json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
        return new Response($status, [
            'Content-Type' => 'application/json; charset=utf-8',
            'Content-Length' => (string) strlen($json),
            'Cache-Control' => 'no-store, private',
            'X-Content-Type-Options' => 'nosniff',
        ], $json);
    }
}
