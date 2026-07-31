<?php

declare(strict_types=1);

namespace Goosialize\Links;

use DirectoryIterator;
use Grav\Framework\Psr7\Response;
use Grav\Plugin\Api\Controllers\AbstractApiController;
use JsonException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use RuntimeException;
use Throwable;

final class QrAdminController extends AbstractApiController
{
    private const PERMISSION =
        'api.goosialize-links.qr.read';

    private const QR_ID = 'qr_primary';

    public function pageData(
        ServerRequestInterface $request
    ): ResponseInterface {
        $this->requirePermission(
            $request,
            self::PERMISSION
        );

        $config = $this->normalizedConfig();
        $route = (string) ($config['route'] ?? '');

        if ($route === '') {
            throw new RuntimeException(
                'The public Goosialize Links route is unavailable.'
            );
        }

        return $this->jsonResponse([
            'qr_id' => self::QR_ID,
            'tracked_url' =>
                $this->absolutePublicUrl(
                    $request,
                    $route . '/qr/' . self::QR_ID
                ),
            'preview_url' =>
                $this->absolutePublicUrl(
                    $request,
                    $route . '/qr/' . self::QR_ID . '/png'
                ),
            'png_download_url' =>
                '/goosialize-links/qr/download/png',
            'svg_download_url' =>
                '/goosialize-links/qr/download/svg',
            'qr_visits' => $this->qrVisits(),
        ]);
    }

    public function downloadPng(
        ServerRequestInterface $request
    ): ResponseInterface {
        $this->requirePermission(
            $request,
            self::PERMISSION
        );

        return $this->download(
            $request,
            'png'
        );
    }

    public function downloadSvg(
        ServerRequestInterface $request
    ): ResponseInterface {
        $this->requirePermission(
            $request,
            self::PERMISSION
        );

        return $this->download(
            $request,
            'svg'
        );
    }

    private function download(
        ServerRequestInterface $request,
        string $format
    ): ResponseInterface {
        $config = $this->normalizedConfig();
        $route = (string) ($config['route'] ?? '');

        if ($route === '') {
            throw new RuntimeException(
                'The public Goosialize Links route is unavailable.'
            );
        }

        $trackedUrl = $this->absolutePublicUrl(
            $request,
            $route . '/qr/' . self::QR_ID
        );

        $generator = new QrCodeGenerator();

        if ($format === 'png') {
            $body = $generator->generatePng(
                $trackedUrl
            );
            $contentType = 'image/png';
            $filename = 'goosialize-links-qr.png';
        } elseif ($format === 'svg') {
            $body = $generator->generateSvg(
                $trackedUrl
            );
            $contentType = 'image/svg+xml; charset=utf-8';
            $filename = 'goosialize-links-qr.svg';
        } else {
            throw new RuntimeException(
                'Unsupported QR download format.'
            );
        }

        if (!is_string($body) || $body === '') {
            throw new RuntimeException(
                'QR download generation returned no data.'
            );
        }

        return new Response(
            200,
            [
                'Content-Type' => $contentType,
                'Content-Disposition' =>
                    'attachment; filename="' .
                    $filename .
                    '"',
                'Content-Length' =>
                    (string) strlen($body),
                'Cache-Control' =>
                    'no-store, private',
                'X-Content-Type-Options' =>
                    'nosniff',
                'Referrer-Policy' =>
                    'no-referrer',
            ],
            $body
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function normalizedConfig(): array
    {
        $rawConfig = $this->config->get(
            'plugins.goosialize-links',
            []
        );

        if (!is_array($rawConfig)) {
            throw new RuntimeException(
                'Goosialize Links configuration must be an array.'
            );
        }

        $baseConfig =
            (new LinkPageConfigNormalizer())
                ->normalize($rawConfig);

        $config =
            (new PublicPageExperienceNormalizer())
                ->normalize(
                    $rawConfig,
                    $baseConfig
                );

        if (!($config['enabled'] ?? false)) {
            throw new RuntimeException(
                'Goosialize Links is disabled.'
            );
        }

        return $config;
    }

    private function absolutePublicUrl(
        ServerRequestInterface $request,
        string $path
    ): string {
        if (
            $path === '' ||
            $path[0] !== '/'
        ) {
            throw new RuntimeException(
                'A root-relative public path is required.'
            );
        }

        $forwardedScheme = '';
        $forwardedProto = trim(
            $request->getHeaderLine(
                'X-Forwarded-Proto'
            )
        );

        if ($forwardedProto !== '') {
            $forwardedParts = explode(
                ',',
                $forwardedProto,
                2
            );

            $candidateScheme = strtolower(
                trim($forwardedParts[0])
            );

            if (
                in_array(
                    $candidateScheme,
                    ['http', 'https'],
                    true
                )
            ) {
                $forwardedScheme =
                    $candidateScheme;
            }
        }

        $root = '';
        $uriService = $this->grav['uri'] ?? null;

        if (
            is_object($uriService) &&
            method_exists($uriService, 'rootUrl')
        ) {
            try {
                $candidate = rtrim(
                    (string) $uriService->rootUrl(true),
                    '/'
                );

                if (
                    preg_match(
                        '#^https?://#i',
                        $candidate
                    ) === 1
                ) {
                    if ($forwardedScheme !== '') {
                        $candidate = (string) preg_replace(
                            '#^https?://#i',
                            $forwardedScheme . '://',
                            $candidate,
                            1
                        );
                    }

                    $root = $candidate;
                }
            } catch (Throwable) {
                $root = '';
            }
        }

        if ($root === '') {
            $uri = $request->getUri();
            $scheme = strtolower(
                $uri->getScheme()
            );
            $authority = $uri->getAuthority();

            if ($forwardedScheme !== '') {
                $scheme = $forwardedScheme;
            }

            if (
                !in_array(
                    $scheme,
                    ['http', 'https'],
                    true
                ) ||
                $authority === ''
            ) {
                throw new RuntimeException(
                    'The request origin is unavailable.'
                );
            }

            $root = $scheme . '://' . $authority;
        }

        return $root . $path;
    }

    private function qrVisits(): int
    {
        $directory =
            GRAV_ROOT .
            '/user/data/goosialize-links/analytics';

        if (!is_dir($directory)) {
            return 0;
        }

        $store = new AnalyticsStore($directory);
        $total = 0;

        foreach (new DirectoryIterator($directory) as $file) {
            if (
                $file->isDot() ||
                !$file->isFile() ||
                $file->isLink()
            ) {
                continue;
            }

            if (
                preg_match(
                    '/^(\d{4}-\d{2}-\d{2})\.yaml$/D',
                    $file->getFilename(),
                    $matches
                ) !== 1
            ) {
                continue;
            }

            try {
                $data = $store->readDate($matches[1]);
                $value =
                    $data['qrs'][self::QR_ID] ?? 0;

                if (!is_int($value) || $value < 0) {
                    continue;
                }

                if ($value > PHP_INT_MAX - $total) {
                    throw new RuntimeException(
                        'QR visit counter overflow.'
                    );
                }

                $total += $value;
            } catch (Throwable) {
                continue;
            }
        }

        return $total;
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function jsonResponse(
        array $payload
    ): ResponseInterface {
        try {
            $json = json_encode(
                $payload,
                JSON_UNESCAPED_SLASHES |
                JSON_UNESCAPED_UNICODE |
                JSON_THROW_ON_ERROR
            );
        } catch (JsonException $exception) {
            throw new RuntimeException(
                'QR administration response encoding failed.',
                0,
                $exception
            );
        }

        return new Response(
            200,
            [
                'Content-Type' =>
                    'application/json; charset=utf-8',
                'Content-Length' =>
                    (string) strlen($json),
                'Cache-Control' =>
                    'no-store, private',
                'X-Content-Type-Options' =>
                    'nosniff',
            ],
            $json
        );
    }
}
