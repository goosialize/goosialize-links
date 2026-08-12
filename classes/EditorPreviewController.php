<?php

declare(strict_types=1);

namespace Goosialize\Links;

use Grav\Plugin\Api\Controllers\AbstractApiController;
use Grav\Framework\Psr7\Response;
use JsonException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class EditorPreviewController extends AbstractApiController
{
    private const PERMISSION =
        'api.goosialize-links.qr.read';

    public function data(
        ServerRequestInterface $request
    ): ResponseInterface {
        $this->requirePermission(
            $request,
            self::PERMISSION
        );

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
        $languages = [];

        foreach ($supported as $key => $value) {
            $code = is_string($key) ? $key : (string) $value;
            $code = strtolower(trim($code));

            if (preg_match('/^[a-z]{2,3}(?:-[a-z0-9]{2,8})*$/', $code) !== 1) {
                continue;
            }

            $label = is_string($key) && is_string($value)
                ? trim($value)
                : strtoupper($code);
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
            $languages[] = [
                'code' => $default !== '' ? $default : 'en',
                'label' => $default !== '' ? strtoupper($default) : 'English',
                'preview_path' => $route . '?goosialize-links-preview=1',
                'public_path' => $route,
            ];
        }

        try {
            $json = json_encode(
                ['languages' => $languages],
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
}
