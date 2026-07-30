<?php

declare(strict_types=1);

namespace Goosialize\Links;

use InvalidArgumentException;

final class PublicPageExperienceNormalizer
{
    /**
     * @var list<string>
     */
    private const THEMES = [
        'light',
        'dark',
        'sunrise',
    ];

    /**
     * @var list<string>
     */
    private const ACCENTS = [
        'yellow',
        'blue',
        'coral',
        'green',
        'purple',
    ];

    /**
     * @var list<string>
     */
    private const BUTTON_SHAPES = [
        'square',
        'rounded',
        'pill',
    ];

    public function __construct(
        private readonly SocialActionNormalizer
            $socialActionNormalizer =
                new SocialActionNormalizer()
    ) {
    }

    /**
     * @param array<string, mixed> $rawConfig
     * @param array<string, mixed> $baseConfig
     *
     * @return array<string, mixed>
     */
    public function normalize(
        array $rawConfig,
        array $baseConfig
    ): array {
        $profile = is_array(
            $baseConfig['profile'] ?? null
        )
            ? $baseConfig['profile']
            : [];

        $rawProfile = is_array(
            $rawConfig['profile'] ?? null
        )
            ? $rawConfig['profile']
            : [];

        $profile['image'] =
            $this->normalizeImage(
                $rawProfile['image'] ?? []
            );

        $rawAppearance = is_array(
            $rawConfig['appearance'] ?? null
        )
            ? $rawConfig['appearance']
            : [];

        $theme = strtolower(
            trim(
                (string) (
                    $rawAppearance['theme'] ??
                    'light'
                )
            )
        );

        $accent = strtolower(
            trim(
                (string) (
                    $rawAppearance['accent'] ??
                    'yellow'
                )
            )
        );

        $buttonShape = strtolower(
            trim(
                (string) (
                    $rawAppearance['button_shape'] ??
                    'rounded'
                )
            )
        );

        $this->assertAllowed(
            $theme,
            self::THEMES,
            'theme'
        );

        $this->assertAllowed(
            $accent,
            self::ACCENTS,
            'accent'
        );

        $this->assertAllowed(
            $buttonShape,
            self::BUTTON_SHAPES,
            'button shape'
        );

        $baseConfig['profile'] = $profile;

        $baseConfig['appearance'] = [
            'theme' => $theme,
            'accent' => $accent,
            'button_shape' => $buttonShape,
        ];

        $baseConfig['actions'] =
            $this->socialActionNormalizer->normalize(
                $rawConfig['actions'] ?? []
            );

        return $baseConfig;
    }

    /**
     * @return array<mixed>
     */
    private function normalizeImage(
        mixed $image
    ): array {
        if ($image === null || $image === '') {
            return [];
        }

        if (!is_array($image)) {
            throw new InvalidArgumentException(
                'Profile image configuration must be an array.'
            );
        }

        return $image;
    }

    /**
     * @param list<string> $allowed
     */
    private function assertAllowed(
        string $value,
        array $allowed,
        string $label
    ): void {
        if (!in_array($value, $allowed, true)) {
            throw new InvalidArgumentException(
                sprintf(
                    'Unsupported public page %s "%s".',
                    $label,
                    $value
                )
            );
        }
    }
}
