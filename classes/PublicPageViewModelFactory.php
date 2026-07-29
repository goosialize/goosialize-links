<?php

declare(strict_types=1);

namespace Goosialize\Links;

final class PublicPageViewModelFactory
{
    /**
     * @param array<string, mixed> $normalizedConfig
     *
     * @return array{
     *     page_title: string,
     *     profile: array{
     *         name: string,
     *         title: string,
     *         description: string,
     *         website_url: string,
     *         initial: string
     *     },
     *     links: list<array{
     *         id: string,
     *         title: string,
     *         url: string,
     *         new_tab: bool
     *     }>,
     *     powered_by: array{
     *         label: string,
     *         url: string
     *     }
     * }
     */
    public function create(array $normalizedConfig): array
    {
        $profile = is_array($normalizedConfig['profile'] ?? null)
            ? $normalizedConfig['profile']
            : [];

        $name = trim((string) ($profile['name'] ?? ''));
        $title = trim((string) ($profile['title'] ?? ''));
        $description = trim((string) ($profile['description'] ?? ''));
        $websiteUrl = trim((string) ($profile['website_url'] ?? ''));

        $links = [];

        foreach ((array) ($normalizedConfig['links'] ?? []) as $link) {
            if (!is_array($link) || !($link['enabled'] ?? false)) {
                continue;
            }

            $links[] = [
                'id' => (string) ($link['id'] ?? ''),
                'title' => (string) ($link['title'] ?? ''),
                'url' => (string) ($link['url'] ?? ''),
                'new_tab' => (bool) ($link['new_tab'] ?? true),
            ];
        }

        return [
            'page_title' => $name !== ''
                ? $name . ' | Links'
                : 'Links',
            'profile' => [
                'name' => $name,
                'title' => $title,
                'description' => $description,
                'website_url' => $websiteUrl,
                'initial' => $this->firstCharacter($name),
            ],
            'links' => $links,
            'powered_by' => [
                'label' => 'Powered by Goosialize Links',
                'url' => 'https://goosialize.com',
            ],
        ];
    }

    private function firstCharacter(string $value): string
    {
        if ($value === '') {
            return 'G';
        }

        if (
            function_exists('mb_substr') &&
            function_exists('mb_strtoupper')
        ) {
            return mb_strtoupper(
                mb_substr($value, 0, 1)
            );
        }

        return strtoupper(substr($value, 0, 1));
    }
}
