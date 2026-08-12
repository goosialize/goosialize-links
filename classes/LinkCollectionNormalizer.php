<?php

declare(strict_types=1);

namespace Goosialize\Links;

use InvalidArgumentException;

/**
 * Normalizes and validates the ordered Link in Bio link collection.
 */
final class LinkCollectionNormalizer
{
    public const MAX_ACTIVE_LINKS = 8;

    /**
     * @param array<int, mixed> $links
     *
     * @return array<int, array{
     *     id: string,
     *     enabled: bool,
     *     title: string,
     *     url: string,
     *     new_tab: bool
     * }>
     */
    public function normalize(array $links): array
    {
        if (!array_is_list($links)) {
            throw new InvalidArgumentException(
                'Links must be provided as an ordered list.'
            );
        }

        $normalized = [];
        $usedIds = [];
        $activeCount = 0;

        foreach ($links as $position => $link) {
            if (!is_array($link)) {
                throw new InvalidArgumentException(
                    sprintf('Link at position %d must be an array.', $position)
                );
            }

            $id = $this->normalizeId($link['id'] ?? null);

            if (isset($usedIds[$id])) {
                throw new InvalidArgumentException(
                    sprintf('Duplicate link ID: %s', $id)
                );
            }

            $usedIds[$id] = true;

            $enabled = $this->normalizeBoolean(
                $link['enabled'] ?? true,
                true
            );

            $title = $this->normalizeRequiredString(
                $link['title'] ?? null,
                'Link title',
                120
            );

            $url = $this->normalizeHttpUrl(
                $link['url'] ?? null
            );

            $newTab = $this->normalizeBoolean(
                $link['new_tab'] ?? true,
                true
            );

            if ($enabled) {
                $activeCount++;
            }

            $normalized[] = [
                'id' => $id,
                'enabled' => $enabled,
                'title' => $title,
                'url' => $url,
                'new_tab' => $newTab,
                'translations' => $this->normalizeTranslations(
                    $link['translations'] ?? []
                ),
            ];
        }

        if ($activeCount > self::MAX_ACTIVE_LINKS) {
            throw new InvalidArgumentException(
                sprintf(
                    'The FREE edition allows at most %d active links.',
                    self::MAX_ACTIVE_LINKS
                )
            );
        }

        return $normalized;
    }

    /** @return array<string, string> */
    private function normalizeTranslations(mixed $translations): array
    {
        if ($translations === null || $translations === []) {
            return [];
        }

        if (!is_array($translations) || !array_is_list($translations)) {
            throw new InvalidArgumentException(
                'Link translations must be an ordered list.'
            );
        }

        $normalized = [];

        foreach ($translations as $translation) {
            if (!is_array($translation)) {
                throw new InvalidArgumentException(
                    'Each link translation must be an array.'
                );
            }

            $language = strtolower(trim((string) ($translation['language'] ?? '')));

            if (
                preg_match('/^[a-z]{2,3}(?:-[a-z0-9]{2,8})*$/', $language) !== 1 ||
                isset($normalized[$language])
            ) {
                throw new InvalidArgumentException(
                    'Link translation languages must be valid and unique.'
                );
            }

            $title = trim((string) ($translation['title'] ?? ''));

            if (strlen($title) > 120) {
                throw new InvalidArgumentException(
                    'Localized link title must not exceed 120 bytes.'
                );
            }

            $normalized[$language] = $title;
        }

        return $normalized;
    }

    private function normalizeId(mixed $value): string
    {
        if ($value === null || trim((string) $value) === '') {
            return 'link_' . bin2hex(random_bytes(8));
        }

        $id = trim((string) $value);

        if (!preg_match('/^link_[a-f0-9]{16}$/', $id)) {
            throw new InvalidArgumentException(
                'Link IDs must use the link_ prefix followed by 16 hex digits.'
            );
        }

        return $id;
    }

    private function normalizeRequiredString(
        mixed $value,
        string $label,
        int $maximumLength
    ): string {
        $normalized = trim((string) $value);

        if ($normalized === '') {
            throw new InvalidArgumentException(
                sprintf('%s is required.', $label)
            );
        }

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

    private function normalizeHttpUrl(mixed $value): string
    {
        $url = trim((string) $value);

        if ($url === '' || filter_var($url, FILTER_VALIDATE_URL) === false) {
            throw new InvalidArgumentException(
                'Link URL must be a valid absolute URL.'
            );
        }

        $scheme = strtolower((string) parse_url($url, PHP_URL_SCHEME));

        if (!in_array($scheme, ['http', 'https'], true)) {
            throw new InvalidArgumentException(
                'Link URL must use HTTP or HTTPS.'
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
