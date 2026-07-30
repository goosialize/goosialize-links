<?php

declare(strict_types=1);

namespace Goosialize\Links;

final class PublicPageViewModelFactory
{
    public function __construct(
        private readonly ProfileImageResolver
            $profileImageResolver =
                new ProfileImageResolver()
    ) {
    }

    /**
     * @param array<string, mixed> $normalizedConfig
     *
     * @return array<string, mixed>
     */
    public function create(array $normalizedConfig): array
    {
        $profile = is_array(
            $normalizedConfig['profile'] ?? null
        )
            ? $normalizedConfig['profile']
            : [];

        $appearance = is_array(
            $normalizedConfig['appearance'] ?? null
        )
            ? $normalizedConfig['appearance']
            : [];

        $name = trim(
            (string) ($profile['name'] ?? '')
        );

        $title = trim(
            (string) ($profile['title'] ?? '')
        );

        $description = trim(
            (string) (
                $profile['description'] ?? ''
            )
        );

        $websiteUrl = trim(
            (string) (
                $profile['website_url'] ?? ''
            )
        );

        $image =
            $this->profileImageResolver->resolve(
                $profile['image'] ?? []
            );

        $links = [];

        foreach (
            (array) (
                $normalizedConfig['links'] ?? []
            ) as $link
        ) {
            if (
                !is_array($link) ||
                !($link['enabled'] ?? false)
            ) {
                continue;
            }

            $links[] = [
                'id' => (string) (
                    $link['id'] ?? ''
                ),
                'title' => (string) (
                    $link['title'] ?? ''
                ),
                'url' => (string) (
                    $link['url'] ?? ''
                ),
                'new_tab' => (bool) (
                    $link['new_tab'] ?? true
                ),
            ];
        }

        $actions = [];

        foreach (
            (array) (
                $normalizedConfig['actions'] ?? []
            ) as $action
        ) {
            if (
                !is_array($action) ||
                !($action['enabled'] ?? false)
            ) {
                continue;
            }

            $actions[] = [
                'id' => (string) (
                    $action['id'] ?? ''
                ),
                'type' => (string) (
                    $action['type'] ?? ''
                ),
                'label' => (string) (
                    $action['label'] ?? ''
                ),
                'href' => (string) (
                    $action['href'] ?? ''
                ),
                'new_tab' => (bool) (
                    $action['new_tab'] ?? false
                ),
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
                'initial' =>
                    $this->firstCharacter($name),
                'image_stream' =>
                    $image['stream'] ?? '',
                'image_filename' =>
                    $image['filename'] ?? '',
            ],
            'appearance' => [
                'theme' => (string) (
                    $appearance['theme'] ??
                    'light'
                ),
                'accent' => (string) (
                    $appearance['accent'] ??
                    'yellow'
                ),
                'button_shape' => (string) (
                    $appearance['button_shape'] ??
                    'rounded'
                ),
            ],
            'actions' => $actions,
            'links' => $links,
            'powered_by' => [
                'label' =>
                    'Powered by Goosialize Links',
                'url' => 'https://goosialize.com',
            ],
        ];
    }

    private function firstCharacter(
        string $value
    ): string {
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
