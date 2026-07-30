<?php

declare(strict_types=1);

namespace Goosialize\Links;

use InvalidArgumentException;

final class TrackingRouteResolver
{
    /**
     * @return array{
     *     event: string,
     *     id: string
     * }|null
     */
    public function resolve(
        string $requestPath,
        string $publicRoute
    ): ?array {
        $requestPath =
            $this->normalizeRequestPath(
                $requestPath
            );

        $publicRoute =
            $this->normalizePath($publicRoute);

        $quotedRoute = preg_quote(
            $publicRoute,
            '#'
        );

        if (
            preg_match(
                '#^' .
                $quotedRoute .
                '/go/(link_[a-f0-9]{16})$#',
                $requestPath,
                $matches
            ) === 1
        ) {
            return [
                'event' => 'link_click',
                'id' => $matches[1],
            ];
        }

        if (
            preg_match(
                '#^' .
                $quotedRoute .
                '/action/(action_[a-f0-9]{16})$#',
                $requestPath,
                $matches
            ) === 1
        ) {
            return [
                'event' => 'action_click',
                'id' => $matches[1],
            ];
        }

        return null;
    }

    public function linkUrl(
        string $publicRoute,
        string $linkId
    ): string {
        $this->assertId(
            $linkId,
            'link'
        );

        return sprintf(
            '%s/go/%s',
            $this->normalizePath($publicRoute),
            $linkId
        );
    }

    public function actionUrl(
        string $publicRoute,
        string $actionId
    ): string {
        $this->assertId(
            $actionId,
            'action'
        );

        return sprintf(
            '%s/action/%s',
            $this->normalizePath($publicRoute),
            $actionId
        );
    }

    private function normalizeRequestPath(
        string $path
    ): string {
        $path = trim($path);

        if (
            $path === '' ||
            str_contains($path, "\0")
        ) {
            throw new InvalidArgumentException(
                'Tracking request path is invalid.'
            );
        }

        $path =
            '/' .
            trim($path, '/');

        if (
            preg_match(
                '#^/[a-z0-9_-]+(?:/[a-z0-9_-]+)*$#',
                $path
            ) !== 1
        ) {
            throw new InvalidArgumentException(
                'Tracking request path is unsafe.'
            );
        }

        return $path;
    }

    private function normalizePath(
        string $path
    ): string {
        $path = trim($path);

        if (
            $path === '' ||
            str_contains($path, "\0")
        ) {
            throw new InvalidArgumentException(
                'Tracking route is invalid.'
            );
        }

        $path =
            '/' .
            trim($path, '/');

        if (
            preg_match(
                '#^/[a-z0-9]+(?:/[a-z0-9][a-z0-9-]*)*$#',
                $path
            ) !== 1
        ) {
            throw new InvalidArgumentException(
                'Tracking route is unsafe.'
            );
        }

        return $path;
    }

    private function assertId(
        string $id,
        string $prefix
    ): void {
        if (
            preg_match(
                sprintf(
                    '/^%s_[a-f0-9]{16}$/',
                    preg_quote(
                        $prefix,
                        '/'
                    )
                ),
                $id
            ) !== 1
        ) {
            throw new InvalidArgumentException(
                sprintf(
                    'Invalid %s tracking ID.',
                    $prefix
                )
            );
        }
    }
}
