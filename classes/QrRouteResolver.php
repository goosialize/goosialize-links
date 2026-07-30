<?php

declare(strict_types=1);

namespace Goosialize\Links;

use InvalidArgumentException;

final class QrRouteResolver
{
    public const QR_ID = 'qr_primary';

    /**
     * @return array{
     *     kind: 'track'|'png'|'svg',
     *     id: 'qr_primary'
     * }|null
     */
    public function resolve(
        string $path,
        string $publicRoute
    ): ?array {
        $route =
            $this->normalizeRoute($publicRoute);

        $currentPath =
            $this->normalizePath($path);

        $pattern = sprintf(
            '#^%s/qr/(qr_primary)(?:/(png|svg))?$#',
            preg_quote($route, '#')
        );

        if (
            preg_match(
                $pattern,
                $currentPath,
                $matches
            ) !== 1
        ) {
            return null;
        }

        $format =
            $matches[2] ?? '';

        return [
            'kind' =>
                $format === ''
                    ? 'track'
                    : $format,
            'id' => self::QR_ID,
        ];
    }

    private function normalizeRoute(
        string $route
    ): string {
        $route =
            '/' . trim($route, '/');

        if (
            preg_match(
                '#^/[a-z0-9]+(?:/[a-z0-9][a-z0-9-]*)*$#',
                $route
            ) !== 1
        ) {
            throw new InvalidArgumentException(
                'Public QR route is invalid.'
            );
        }

        return $route;
    }

    private function normalizePath(
        string $path
    ): string {
        $path = trim($path);

        if ($path === '' || $path === '/') {
            return '/';
        }

        return '/' . trim($path, '/');
    }
}
