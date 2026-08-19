<?php

declare(strict_types=1);

namespace Goosialize\Links;

use RuntimeException;
use Symfony\Component\Yaml\Yaml;

final class NativePageLocator
{
    public function __construct(
        private readonly string $pagesRoot
    ) {
    }

    public function route(
        string $configuredRoute,
        string $defaultLanguage = ''
    ): ?string {
        $segments = $this->routeSegments(
            $configuredRoute
        );

        $directory =
            rtrim(
                $this->pagesRoot,
                '/'
            );

        if (!is_dir($directory)) {
            return null;
        }

        foreach ($segments as $segment) {
            $directory =
                $this->childDirectory(
                    $directory,
                    $segment
                );

            if ($directory === null) {
                return null;
            }
        }

        $matches =
            glob(
                $directory .
                '/goosialize-links*.md'
            ) ?: [];

        $matches = array_values(
            array_filter(
                $matches,
                static fn (string $file): bool =>
                    is_file($file) &&
                    preg_match(
                        '/^goosialize-links(?:\.[a-z0-9-]+)?\.md$/i',
                        basename($file)
                    ) === 1
            )
        );

        if ($matches === []) {
            return null;
        }

        sort($matches);

        $preferred =
            $directory .
            '/goosialize-links' .
            ($defaultLanguage !== ''
                ? '.' . $defaultLanguage
                : '') .
            '.md';

        $file =
            is_file($preferred)
                ? $preferred
                : $matches[0];

        $route =
            '/' .
            implode(
                '/',
                $segments
            );

        $raw =
            file_get_contents($file);

        if (
            !is_string($raw) ||
            preg_match(
                '/^---\R(.*?)\R---/s',
                $raw,
                $match
            ) !== 1
        ) {
            return $route;
        }

        $header =
            Yaml::parse(
                $match[1]
            );

        if (!is_array($header)) {
            return $route;
        }

        $override =
            trim(
                (string) (
                    $header['routes']['default']
                    ?? ''
                )
            );

        if ($override !== '') {
            return '/' .
                trim(
                    $override,
                    '/'
                );
        }

        $slug =
            trim(
                (string) (
                    $header['slug']
                    ?? ''
                )
            );

        if ($slug !== '') {
            $segments[
                count($segments) - 1
            ] = $slug;

            $route =
                '/' .
                implode(
                    '/',
                    $segments
                );
        }

        return $route;
    }

    /**
     * @return list<string>
     */
    private function routeSegments(
        string $route
    ): array {
        $route =
            strtolower(
                trim($route)
            );

        if ($route === '') {
            throw new RuntimeException(
                'Configured Links Page route is empty.'
            );
        }

        $route =
            '/' .
            trim(
                $route,
                '/'
            );

        if (
            preg_match(
                '#^/[a-z0-9]+(?:/[a-z0-9][a-z0-9-]*)*$#',
                $route
            ) !== 1
        ) {
            throw new RuntimeException(
                'Configured Links Page route is invalid.'
            );
        }

        return array_values(
            array_filter(
                explode(
                    '/',
                    trim(
                        $route,
                        '/'
                    )
                )
            )
        );
    }

    private function childDirectory(
        string $parent,
        string $segment
    ): ?string {
        $matches = [];

        foreach (
            glob(
                rtrim($parent, '/') .
                '/*',
                GLOB_ONLYDIR
            ) ?: [] as $candidate
        ) {
            $name =
                basename(
                    $candidate
                );

            $logical =
                preg_replace(
                    '/^\d+\./',
                    '',
                    $name
                ) ?: $name;

            if ($logical === $segment) {
                $matches[] = $candidate;
            }
        }

        if ($matches === []) {
            return null;
        }

        if (count($matches) > 1) {
            throw new RuntimeException(
                sprintf(
                    'Multiple physical Page directories match route segment "%s".',
                    $segment
                )
            );
        }

        return $matches[0];
    }
}
