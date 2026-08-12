<?php

declare(strict_types=1);

namespace Goosialize\Links;

use InvalidArgumentException;
use Throwable;

final class SocialActionNormalizer
{
    /**
     * @var array<string, string>
     */
    private const TYPE_LABELS = [
        'website' => 'Website',
        'instagram' => 'Instagram',
        'facebook' => 'Facebook',
        'tiktok' => 'TikTok',
        'youtube' => 'YouTube',
        'linkedin' => 'LinkedIn',
        'x' => 'X',
        'email' => 'Email',
        'phone' => 'Phone',
        'whatsapp' => 'WhatsApp',
    ];

    /**
     * @var list<string>
     */
    private const URL_TYPES = [
        'website',
        'instagram',
        'facebook',
        'tiktok',
        'youtube',
        'linkedin',
        'x',
    ];

    /**
     * @return list<array{
     *     id: string,
     *     enabled: bool,
     *     type: string,
     *     label: string,
     *     value: string,
     *     href: string,
     *     new_tab: bool
     * }>
     */
    public function normalize(mixed $actions): array
    {
        if ($actions === null || $actions === []) {
            return [];
        }

        if (!is_array($actions) || !array_is_list($actions)) {
            throw new InvalidArgumentException(
                'Social actions must be a sequential list.'
            );
        }

        $normalized = [];
        $seenIds = [];

        foreach ($actions as $index => $action) {
            if (!is_array($action)) {
                throw new InvalidArgumentException(
                    sprintf(
                        'Social action at index %d must be an array.',
                        $index
                    )
                );
            }

            $type = strtolower(
                trim((string) ($action['type'] ?? ''))
            );

            if (!array_key_exists($type, self::TYPE_LABELS)) {
                throw new InvalidArgumentException(
                    sprintf(
                        'Unsupported social action type at index %d.',
                        $index
                    )
                );
            }

            $value = trim(
                (string) ($action['value'] ?? '')
            );

            if ($value === '') {
                throw new InvalidArgumentException(
                    sprintf(
                        'Social action value is required at index %d.',
                        $index
                    )
                );
            }

            $id = $this->normalizeId(
                $action['id'] ?? null
            );

            if (isset($seenIds[$id])) {
                throw new InvalidArgumentException(
                    sprintf(
                        'Duplicate social action ID "%s".',
                        $id
                    )
                );
            }

            $seenIds[$id] = true;

            $customLabel = trim(
                (string) ($action['label'] ?? '')
            );

            if ($this->length($customLabel) > 80) {
                throw new InvalidArgumentException(
                    sprintf(
                        'Social action label is too long at index %d.',
                        $index
                    )
                );
            }

            $normalized[] = [
                'id' => $id,
                'enabled' => $this->normalizeBoolean(
                    $action['enabled'] ?? true
                ),
                'type' => $type,
                'label' => $customLabel !== ''
                    ? $customLabel
                    : self::TYPE_LABELS[$type],
                'value' => $value,
                'href' => $this->createHref(
                    $type,
                    $value
                ),
                'new_tab' => !in_array(
                    $type,
                    ['email', 'phone'],
                    true
                ),
                'translations' => $this->normalizeTranslations(
                    $action['translations'] ?? []
                ),
            ];
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
                'Action translations must be an ordered list.'
            );
        }

        $normalized = [];

        foreach ($translations as $translation) {
            if (!is_array($translation)) {
                throw new InvalidArgumentException(
                    'Each action translation must be an array.'
                );
            }

            $language = strtolower(trim((string) ($translation['language'] ?? '')));

            if (
                preg_match('/^[a-z]{2,3}(?:-[a-z0-9]{2,8})*$/', $language) !== 1 ||
                isset($normalized[$language])
            ) {
                throw new InvalidArgumentException(
                    'Action translation languages must be valid and unique.'
                );
            }

            $label = trim((string) ($translation['label'] ?? ''));

            if ($this->length($label) > 80) {
                throw new InvalidArgumentException(
                    'Localized action label must not exceed 80 characters.'
                );
            }

            $normalized[$language] = $label;
        }

        return $normalized;
    }

    private function normalizeId(mixed $value): string
    {
        $id = trim((string) $value);

        if ($id === '') {
            try {
                return 'action_' . bin2hex(
                    random_bytes(8)
                );
            } catch (Throwable $exception) {
                throw new InvalidArgumentException(
                    'Unable to generate a social action ID.',
                    0,
                    $exception
                );
            }
        }

        if (
            preg_match(
                '/^action_[a-f0-9]{16}$/',
                $id
            ) !== 1
        ) {
            throw new InvalidArgumentException(
                sprintf(
                    'Invalid social action ID "%s".',
                    $id
                )
            );
        }

        return $id;
    }

    private function createHref(
        string $type,
        string $value
    ): string {
        if (in_array($type, self::URL_TYPES, true)) {
            return $this->normalizeHttpUrl($value);
        }

        if ($type === 'email') {
            if (
                filter_var(
                    $value,
                    FILTER_VALIDATE_EMAIL
                ) === false
            ) {
                throw new InvalidArgumentException(
                    'Invalid email social action.'
                );
            }

            return 'mailto:' . $value;
        }

        if ($type === 'phone') {
            $phone = $this->normalizePhone($value);

            return 'tel:' . $phone;
        }

        if ($type === 'whatsapp') {
            $digits = preg_replace(
                '/[^0-9]/',
                '',
                $value
            );

            if (
                !is_string($digits) ||
                preg_match(
                    '/^[1-9][0-9]{5,19}$/',
                    $digits
                ) !== 1
            ) {
                throw new InvalidArgumentException(
                    'Invalid WhatsApp phone number.'
                );
            }

            return 'https://wa.me/' . $digits;
        }

        throw new InvalidArgumentException(
            'Unsupported social action type.'
        );
    }

    private function normalizeHttpUrl(
        string $value
    ): string {
        if (
            filter_var(
                $value,
                FILTER_VALIDATE_URL
            ) === false
        ) {
            throw new InvalidArgumentException(
                'Invalid social action URL.'
            );
        }

        $scheme = strtolower(
            (string) parse_url(
                $value,
                PHP_URL_SCHEME
            )
        );

        if (!in_array($scheme, ['http', 'https'], true)) {
            throw new InvalidArgumentException(
                'Social action URLs must use HTTP or HTTPS.'
            );
        }

        return $value;
    }

    private function normalizePhone(
        string $value
    ): string {
        $phone = preg_replace(
            '/[\s().-]+/',
            '',
            $value
        );

        if (
            !is_string($phone) ||
            preg_match(
                '/^\+?[1-9][0-9]{5,19}$/',
                $phone
            ) !== 1
        ) {
            throw new InvalidArgumentException(
                'Invalid telephone number.'
            );
        }

        return $phone;
    }

    private function normalizeBoolean(
        mixed $value
    ): bool {
        if (is_bool($value)) {
            return $value;
        }

        if (is_int($value)) {
            return $value === 1;
        }

        if (is_string($value)) {
            $normalized = strtolower(trim($value));

            if (in_array($normalized, ['1', 'true', 'yes', 'on'], true)) {
                return true;
            }

            if (in_array($normalized, ['0', 'false', 'no', 'off', ''], true)) {
                return false;
            }
        }

        return (bool) $value;
    }

    private function length(string $value): int
    {
        if (function_exists('mb_strlen')) {
            return mb_strlen($value);
        }

        return strlen($value);
    }
}
