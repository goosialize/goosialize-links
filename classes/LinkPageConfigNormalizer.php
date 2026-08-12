<?php

declare(strict_types=1);

namespace Goosialize\Links;

use InvalidArgumentException;

/**
 * Normalizes the core configuration for the single FREE Link in Bio page.
 */
final class LinkPageConfigNormalizer
{
    private LinkCollectionNormalizer $linkNormalizer;

    public function __construct(
        ?LinkCollectionNormalizer $linkNormalizer = null
    ) {
        $this->linkNormalizer =
            $linkNormalizer ?? new LinkCollectionNormalizer();
    }

    /**
     * @param array<string, mixed> $config
     *
     * @return array{
     *     enabled: bool,
     *     route: string,
     *     profile: array{
     *         name: string,
     *         title: string,
     *         description: string,
     *         website_url: string
     *     },
     *     links: array<int, array{
     *         id: string,
     *         enabled: bool,
     *         title: string,
     *         url: string,
     *         new_tab: bool
     *     }>
     * }
     */
    public function normalize(array $config): array
    {
        $profile = $config['profile'] ?? [];

        if (!is_array($profile)) {
            throw new InvalidArgumentException(
                'Profile configuration must be an array.'
            );
        }

        $links = $config['links'] ?? [];

        if (!is_array($links)) {
            throw new InvalidArgumentException(
                'Links configuration must be an array.'
            );
        }

        return [
            'enabled' => $this->normalizeBoolean(
                $config['enabled'] ?? true,
                true
            ),
            'route' => $this->normalizeRoute(
                $config['route'] ?? '/bio'
            ),
            'profile' => [
                'name' => $this->normalizeOptionalString(
                    $profile['name'] ?? '',
                    'Profile name',
                    120
                ),
                'title' => $this->normalizeOptionalString(
                    $profile['title'] ?? '',
                    'Profile title',
                    160
                ),
                'description' => $this->normalizeOptionalString(
                    $profile['description'] ?? '',
                    'Profile description',
                    500
                ),
                'website_url' => $this->normalizeOptionalHttpUrl(
                    $profile['website_url'] ?? ''
                ),
                'translations' => $this->normalizeProfileTranslations(
                    $profile['translations'] ?? []
                ),
            ],
            'links' => $this->linkNormalizer->normalize($links),
        ];
    }

    /** @return array<string, array{name: string, title: string, description: string}> */
    private function normalizeProfileTranslations(mixed $translations): array
    {
        if ($translations === null || $translations === []) {
            return [];
        }

        if (!is_array($translations) || !array_is_list($translations)) {
            throw new InvalidArgumentException(
                'Profile translations must be an ordered list.'
            );
        }

        $normalized = [];

        foreach ($translations as $translation) {
            if (!is_array($translation)) {
                throw new InvalidArgumentException(
                    'Each profile translation must be an array.'
                );
            }

            $language = $this->normalizeLanguageCode(
                $translation['language'] ?? ''
            );

            if (isset($normalized[$language])) {
                throw new InvalidArgumentException(
                    sprintf('Duplicate profile translation language: %s', $language)
                );
            }

            $normalized[$language] = [
                'name' => $this->normalizeOptionalString(
                    $translation['name'] ?? '',
                    'Localized profile name',
                    120
                ),
                'title' => $this->normalizeOptionalString(
                    $translation['title'] ?? '',
                    'Localized profile title',
                    160
                ),
                'description' => $this->normalizeOptionalString(
                    $translation['description'] ?? '',
                    'Localized profile description',
                    500
                ),
            ];
        }

        return $normalized;
    }

    private function normalizeLanguageCode(mixed $value): string
    {
        $language = strtolower(trim((string) $value));

        if (preg_match('/^[a-z]{2,3}(?:-[a-z0-9]{2,8})*$/', $language) !== 1) {
            throw new InvalidArgumentException('Invalid content language code.');
        }

        return $language;
    }

    private function normalizeRoute(mixed $value): string
    {
        $route = strtolower(trim((string) $value));

        if ($route === '') {
            $route = '/bio';
        }

        $route = '/' . trim($route, '/');

        if (
            !preg_match(
                '#^/[a-z0-9]+(?:/[a-z0-9][a-z0-9-]*)*$#',
                $route
            )
        ) {
            throw new InvalidArgumentException(
                'Route must contain lowercase URL-safe path segments.'
            );
        }

        return $route;
    }

    private function normalizeOptionalString(
        mixed $value,
        string $label,
        int $maximumLength
    ): string {
        $normalized = trim((string) $value);

        if (strlen($normalized) > $maximumLength) {
            throw new InvalidArgumentException(
                sprintf(
                    '%s must not exceed %d bytes.',
                    $label,
                    $maximumLength
                )
            );
        }

        return $normalized;
    }

    private function normalizeOptionalHttpUrl(mixed $value): string
    {
        $url = trim((string) $value);

        if ($url === '') {
            return '';
        }

        if (filter_var($url, FILTER_VALIDATE_URL) === false) {
            throw new InvalidArgumentException(
                'Website URL must be a valid absolute URL.'
            );
        }

        $scheme = strtolower((string) parse_url($url, PHP_URL_SCHEME));

        if (!in_array($scheme, ['http', 'https'], true)) {
            throw new InvalidArgumentException(
                'Website URL must use HTTP or HTTPS.'
            );
        }

        return $url;
    }

    private function normalizeBoolean(mixed $value, bool $default): bool
    {
        if ($value === null || $value === '') {
            return $default;
        }

        if (is_bool($value)) {
            return $value;
        }

        if ($value === 1 || $value === '1' || $value === 'true') {
            return true;
        }

        if ($value === 0 || $value === '0' || $value === 'false') {
            return false;
        }

        throw new InvalidArgumentException(
            'Boolean configuration value is invalid.'
        );
    }
}
