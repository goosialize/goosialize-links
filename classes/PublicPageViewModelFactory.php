<?php

declare(strict_types=1);

namespace Goosialize\Links;

final class PublicPageViewModelFactory
{
    public function __construct(
        private readonly ProfileImageResolver
            $profileImageResolver =
                new ProfileImageResolver(),
        private readonly TrackingRouteResolver
            $trackingRouteResolver =
                new TrackingRouteResolver()
    ) {
    }

    /**
     * @param array<string, mixed> $normalizedConfig
     *
     * @return array<string, mixed>
     */
    public function create(
        array $normalizedConfig,
        string $language = ''
    ): array
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

        $publicRoute = (string) (
            $normalizedConfig['route'] ?? '/bio'
        );

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

        $language = strtolower(trim($language));
        $profileTranslation = is_array(
            ($profile['translations'] ?? [])[$language] ?? null
        ) ? $profile['translations'][$language] : [];
        $name = $this->localizedValue($profileTranslation, 'name', $name);
        $title = $this->localizedValue($profileTranslation, 'title', $title);
        $description = $this->localizedValue(
            $profileTranslation,
            'description',
            $description
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

            $id = (string) (
                $link['id'] ?? ''
            );

            $links[] = [
                'id' => $id,
                'title' => $this->localizedMapValue(
                    $link['translations'] ?? [],
                    $language,
                    (string) ($link['title'] ?? '')
                ),
                'tracked_url' =>
                    $this->trackingRouteResolver
                        ->linkUrl(
                            $publicRoute,
                            $id
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

            $id = (string) (
                $action['id'] ?? ''
            );

            $actions[] = [
                'id' => $id,
                'type' => (string) (
                    $action['type'] ?? ''
                ),
                'label' => $this->localizedMapValue(
                    $action['translations'] ?? [],
                    $language,
                    (string) ($action['label'] ?? '')
                ),
                'tracked_url' =>
                    $this->trackingRouteResolver
                        ->actionUrl(
                            $publicRoute,
                            $id
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
                'enabled' => (bool) (
                    $appearance['powered_by']
                    ?? true
                ),
                'label' =>
                    'Powered by Goosialize Links',
                'url' => 'https://goosialize.com',
            ],
            'language' => $language !== '' ? $language : 'en',
        ];
    }

    /** @param array<string, mixed> $values */
    private function localizedValue(array $values, string $key, string $fallback): string
    {
        $value = trim((string) ($values[$key] ?? ''));

        return $value !== '' ? $value : $fallback;
    }

    private function localizedMapValue(mixed $values, string $language, string $fallback): string
    {
        if (!is_array($values) || $language === '') {
            return $fallback;
        }

        $value = trim((string) ($values[$language] ?? ''));

        return $value !== '' ? $value : $fallback;
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
