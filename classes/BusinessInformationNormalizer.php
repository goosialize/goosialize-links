<?php

declare(strict_types=1);

namespace Goosialize\Links;

use InvalidArgumentException;

final class BusinessInformationNormalizer
{
    public const DIRECTIONS_ACTION_ID =
        'action_d1ec710000000000';

    /** @var list<string> */
    public const DAYS = [
        'monday',
        'tuesday',
        'wednesday',
        'thursday',
        'friday',
        'saturday',
        'sunday',
    ];

    /**
     * Runtime-safe normalization. Invalid day intervals fail closed and an
     * invalid Maps URL is omitted; structural configuration errors are rejected.
     *
     * @return array{
     *     working_hours: array{
     *         configured: bool,
     *         days: array<string, array{enabled: bool, open: string, close: string}>
     *     },
     *     google_maps_url: string
     * }
     */
    public function normalize(mixed $business): array
    {
        return $this->normalizeBusiness(
            $business,
            false
        );
    }

    /**
     * Strict validation for persistence boundaries such as Admin2.
     */
    public function validate(mixed $business): void
    {
        $this->normalizeBusiness(
            $business,
            true
        );
    }

    /**
     * @return array{
     *     working_hours: array{
     *         configured: bool,
     *         days: array<string, array{enabled: bool, open: string, close: string}>
     *     },
     *     google_maps_url: string
     * }
     */
    private function normalizeBusiness(
        mixed $business,
        bool $strict
    ): array {
        if ($business === null || $business === []) {
            $business = [];
        }

        if (
            !is_array($business) ||
            ($business !== [] && array_is_list($business))
        ) {
            throw new InvalidArgumentException(
                'Business information must be an associative array.'
            );
        }

        $workingHours = $business['working_hours'] ?? [];

        if (
            !is_array($workingHours) ||
            ($workingHours !== [] && array_is_list($workingHours))
        ) {
            throw new InvalidArgumentException(
                'Working Hours must be an associative array.'
            );
        }

        $unknownDays = array_diff(
            array_keys($workingHours),
            self::DAYS
        );

        if ($unknownDays !== []) {
            throw new InvalidArgumentException(
                sprintf(
                    'Unknown Working Hours day: %s.',
                    (string) reset($unknownDays)
                )
            );
        }

        $configured = $workingHours !== [];
        $days = [];

        foreach (self::DAYS as $day) {
            $days[$day] = $this->normalizeDay(
                $day,
                $workingHours[$day] ?? [],
                $strict
            );
        }

        $mapsUrl = trim(
            (string) ($business['google_maps_url'] ?? '')
        );

        if ($mapsUrl !== '' && !$this->isGoogleMapsUrl($mapsUrl)) {
            if ($strict) {
                throw new InvalidArgumentException(
                    'Google Maps URL is invalid or unsupported.'
                );
            }

            $mapsUrl = '';
        }

        return [
            'working_hours' => [
                'configured' => $configured,
                'days' => $days,
            ],
            'google_maps_url' => $mapsUrl,
        ];
    }

    /** @return array{enabled: bool, open: string, close: string} */
    private function normalizeDay(
        string $day,
        mixed $value,
        bool $strict
    ): array {
        if (
            !is_array($value) ||
            ($value !== [] && array_is_list($value))
        ) {
            throw new InvalidArgumentException(
                sprintf('%s Working Hours must be an associative array.', ucfirst($day))
            );
        }

        $enabled = $this->normalizeBoolean(
            $value['enabled'] ?? false,
            $day
        );

        if (!$enabled) {
            return [
                'enabled' => false,
                'open' => '',
                'close' => '',
            ];
        }

        $open = trim((string) ($value['open'] ?? ''));
        $close = trim((string) ($value['close'] ?? ''));
        $valid = $this->isTime($open) &&
            $this->isTime($close) &&
            strcmp($open, $close) < 0;

        if (!$valid) {
            if ($strict) {
                throw new InvalidArgumentException(
                    sprintf(
                        '%s Working Hours require a valid same-day interval.',
                        ucfirst($day)
                    )
                );
            }

            return [
                'enabled' => false,
                'open' => '',
                'close' => '',
            ];
        }

        return [
            'enabled' => true,
            'open' => $open,
            'close' => $close,
        ];
    }

    private function normalizeBoolean(
        mixed $value,
        string $day
    ): bool {
        if (is_bool($value)) {
            return $value;
        }

        if ($value === 1 || $value === '1' || $value === 'true') {
            return true;
        }

        if ($value === 0 || $value === '0' || $value === 'false' || $value === '') {
            return false;
        }

        throw new InvalidArgumentException(
            sprintf('%s enabled state is invalid.', ucfirst($day))
        );
    }

    private function isTime(string $value): bool
    {
        return preg_match(
            '/^(?:[01][0-9]|2[0-3]):[0-5][0-9]$/',
            $value
        ) === 1;
    }

    private function isGoogleMapsUrl(string $url): bool
    {
        if (
            filter_var($url, FILTER_VALIDATE_URL) === false ||
            strtolower((string) parse_url($url, PHP_URL_SCHEME)) !== 'https' ||
            parse_url($url, PHP_URL_USER) !== null ||
            parse_url($url, PHP_URL_PASS) !== null
        ) {
            return false;
        }

        $host = strtolower((string) parse_url($url, PHP_URL_HOST));
        $path = (string) parse_url($url, PHP_URL_PATH);

        if ($host === 'www.google.com' || $host === 'goo.gl') {
            return $path === '/maps' || str_starts_with($path, '/maps/');
        }

        if ($host === 'maps.google.com') {
            return $path !== '' || parse_url($url, PHP_URL_QUERY) !== null;
        }

        if ($host === 'maps.app.goo.gl') {
            return $path !== '' && $path !== '/';
        }

        return false;
    }
}
