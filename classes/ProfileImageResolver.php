<?php

declare(strict_types=1);

namespace Goosialize\Links;

final class ProfileImageResolver
{
    private const DIRECTORY =
        'user/media/goosialize-links/profile/';

    /**
     * @var list<string>
     */
    private const EXTENSIONS = [
        'jpg',
        'jpeg',
        'png',
        'webp',
    ];

    /**
     * @var list<string>
     */
    private const MIME_TYPES = [
        'image/jpeg',
        'image/png',
        'image/webp',
    ];

    public function __construct(
        private readonly ?string $userRoot = null
    ) {
    }

    /**
     * @return array{
     *     stream: string,
     *     filename: string
     * }|null
     */
    public function resolve(mixed $image): ?array
    {
        if (!is_array($image) || $image === []) {
            return null;
        }

        foreach ($image as $key => $metadata) {
            $candidates = [];

            if (is_array($metadata)) {
                $path = trim(
                    (string) ($metadata['path'] ?? '')
                );

                if ($path !== '') {
                    $candidates[] = $path;
                }

                $name = trim(
                    (string) ($metadata['name'] ?? '')
                );

                if ($name !== '') {
                    $candidates[] = $name;
                }

                $mimeType = strtolower(
                    trim(
                        (string) (
                            $metadata['type'] ?? ''
                        )
                    )
                );

                if (
                    $mimeType !== '' &&
                    !in_array(
                        $mimeType,
                        self::MIME_TYPES,
                        true
                    )
                ) {
                    continue;
                }
            }

            if (is_string($key)) {
                $candidates[] = $key;
            }

            foreach ($candidates as $candidate) {
                $resolved =
                    $this->resolveCandidate($candidate);

                if ($resolved !== null) {
                    return $resolved;
                }
            }
        }

        return null;
    }

    /**
     * @return array{
     *     stream: string,
     *     filename: string
     * }|null
     */
    private function resolveCandidate(
        string $candidate
    ): ?array {
        $path = str_replace(
            '\\',
            '/',
            trim($candidate)
        );

        $path = ltrim($path, '/');

        if (
            $path === '' ||
            str_contains($path, "\0") ||
            str_contains($path, '../') ||
            str_contains($path, '/..')
        ) {
            return null;
        }

        if (!str_contains($path, '/')) {
            $path = self::DIRECTORY . $path;
        }

        $legacyDuplicate = str_starts_with(
            $path,
            'user/' . self::DIRECTORY
        );

        if (
            !str_starts_with($path, self::DIRECTORY) &&
            !$legacyDuplicate
        ) {
            return null;
        }

        $filename = basename($path);

        $expected = ($legacyDuplicate ? 'user/' : '') .
            self::DIRECTORY . $filename;

        if ($path !== $expected) {
            return null;
        }

        $extension = strtolower(
            pathinfo(
                $filename,
                PATHINFO_EXTENSION
            )
        );

        if (
            !in_array(
                $extension,
                self::EXTENSIONS,
                true
            )
        ) {
            return null;
        }

        $userRoot = $this->userRoot;
        if ($userRoot === null && defined('GRAV_ROOT')) {
            $userRoot = GRAV_ROOT . '/user';
        }

        $relative = ($legacyDuplicate ? 'user/' : '') .
            'media/goosialize-links/profile/' . $filename;

        if (
            $userRoot !== null &&
            !is_file(rtrim($userRoot, '/') . '/' . $relative)
        ) {
            return null;
        }

        return [
            'stream' =>
                'user://' . ($legacyDuplicate ? 'user/' : '') .
                'media/goosialize-links/profile/' .
                $filename,
            'filename' => $filename,
        ];
    }
}
