<?php

declare(strict_types=1);

namespace Goosialize\Links;

use DateTimeImmutable;
use InvalidArgumentException;
use OverflowException;
use RuntimeException;
use Symfony\Component\Yaml\Yaml;
use Throwable;

final class AnalyticsStore
{
    private const VERSION = 2;

    private const EVENT_PAGE_VIEW = 'page_view';

    private const EVENT_LINK_CLICK = 'link_click';

    private const EVENT_ACTION_CLICK = 'action_click';

    private const EVENT_QR_VISIT = 'qr_visit';

    private const JOURNAL_SUFFIX = '.events';

    public function __construct(
        private readonly string $directory
    ) {
        if (
            trim($this->directory) === '' ||
            str_contains($this->directory, "\0")
        ) {
            throw new InvalidArgumentException(
                'Analytics directory is invalid.'
            );
        }
    }

    public function recordPageView(
        ?DateTimeImmutable $now = null
    ): void {
        $this->record(
            self::EVENT_PAGE_VIEW,
            null,
            $now
        );
    }

    public function recordLinkClick(
        string $linkId,
        ?DateTimeImmutable $now = null
    ): void {
        $this->assertId(
            $linkId,
            'link'
        );

        $this->record(
            self::EVENT_LINK_CLICK,
            $linkId,
            $now
        );
    }

    public function recordActionClick(
        string $actionId,
        ?DateTimeImmutable $now = null
    ): void {
        $this->assertId(
            $actionId,
            'action'
        );

        $this->record(
            self::EVENT_ACTION_CLICK,
            $actionId,
            $now
        );
    }

    public function recordQrVisit(
        string $qrId = 'qr_primary',
        ?DateTimeImmutable $now = null
    ): void {
        $this->assertQrId($qrId);

        $this->record(
            self::EVENT_QR_VISIT,
            $qrId,
            $now
        );
    }

    /**
     * @return array{
     *     version: int,
     *     date: string,
     *     updated_at: string,
     *     totals: array{
     *         page_views: int,
     *         link_clicks: int,
     *         action_clicks: int
     *     },
     *     links: array<string, int>,
     *     actions: array<string, int>
     * }
     */
    public function readDate(string $date): array
    {
        $this->assertDate($date);

        if (!is_dir($this->directory)) {
            return $this->emptyData(
                $date,
                $date . 'T00:00:00+00:00'
            );
        }

        $path = $this->dataPath($date);
        $journalPath = $this->journalPath($date);

        if (
            !is_file($path) &&
            !is_file($journalPath)
        ) {
            return $this->emptyData(
                $date,
                $date . 'T00:00:00+00:00'
            );
        }

        $lockHandle = $this->openLock();

        try {
            if (!flock($lockHandle, LOCK_SH)) {
                throw new RuntimeException(
                    'Unable to acquire analytics read lock.'
                );
            }

            $data = is_file($path)
                ? $this->readData($path, $date)
                : $this->emptyData(
                    $date,
                    $date . 'T00:00:00+00:00'
                );

            if (is_file($journalPath)) {
                $data = $this->mergeJournal(
                    $data,
                    $journalPath,
                    $date
                );
            }

            return $data;
        } finally {
            flock(
                $lockHandle,
                LOCK_UN
            );

            fclose($lockHandle);
        }
    }

    /**
     * @return list<string>
     */
    public function dates(): array
    {
        if (!is_dir($this->directory)) {
            return [];
        }

        $paths = glob(
            rtrim($this->directory, '/') . '/*'
        );

        if ($paths === false) {
            throw new RuntimeException(
                'Unable to list analytics files.'
            );
        }

        $dates = [];

        foreach ($paths as $path) {
            if (!is_file($path) || is_link($path)) {
                continue;
            }

            $filename = basename($path);

            if (
                preg_match(
                    '/^(\d{4}-\d{2}-\d{2})(?:\.yaml|\.events)$/D',
                    $filename,
                    $matches
                ) !== 1
            ) {
                continue;
            }

            $this->assertDate($matches[1]);
            $dates[$matches[1]] = true;
        }

        $dates = array_keys($dates);

        sort(
            $dates,
            SORT_STRING
        );

        return $dates;
    }

    private function record(
        string $event,
        ?string $id,
        ?DateTimeImmutable $now
    ): void {
        $now ??= new DateTimeImmutable('now');

        $date = $now->format('Y-m-d');

        $this->ensureDirectory();

        $lockHandle = $this->openLock();

        try {
            if (!flock($lockHandle, LOCK_EX)) {
                throw new RuntimeException(
                    'Unable to acquire analytics write lock.'
                );
            }

            $this->appendJournalEvent(
                $this->journalPath($date),
                $event,
                $id,
                $now
            );
        } finally {
            flock(
                $lockHandle,
                LOCK_UN
            );

            fclose($lockHandle);
        }
    }

    /**
     * @return resource
     */
    private function openLock()
    {
        $lockPath =
            $this->directory .
            '/.analytics.lock';

        $lockHandle = fopen(
            $lockPath,
            'c'
        );

        if ($lockHandle === false) {
            throw new RuntimeException(
                'Unable to open analytics lock.'
            );
        }

        @chmod(
            $lockPath,
            0640
        );

        return $lockHandle;
    }

    private function appendJournalEvent(
        string $journalPath,
        string $event,
        ?string $id,
        DateTimeImmutable $now
    ): void {
        $line = implode(
            "\t",
            [
                $now->format(DATE_ATOM),
                $event,
                $id ?? '-',
            ]
        ) . "\n";

        $handle = fopen(
            $journalPath,
            'ab'
        );

        if ($handle === false) {
            throw new RuntimeException(
                'Unable to open analytics journal.'
            );
        }

        try {
            $remaining = $line;

            while ($remaining !== '') {
                $written = fwrite(
                    $handle,
                    $remaining
                );

                if (
                    $written === false ||
                    $written === 0
                ) {
                    throw new RuntimeException(
                        'Unable to append analytics event.'
                    );
                }

                $remaining = substr(
                    $remaining,
                    $written
                );
            }
        } finally {
            fclose($handle);
        }

        @chmod(
            $journalPath,
            0640
        );
    }

    /**
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    private function mergeJournal(
        array $data,
        string $journalPath,
        string $expectedDate
    ): array {
        $handle = fopen(
            $journalPath,
            'rb'
        );

        if ($handle === false) {
            throw new RuntimeException(
                'Unable to read analytics journal.'
            );
        }

        try {
            while (($line = fgets($handle)) !== false) {
                $line = rtrim(
                    $line,
                    "\r\n"
                );

                if ($line === '') {
                    continue;
                }

                $parts = explode(
                    "\t",
                    $line
                );

                if (count($parts) !== 3) {
                    throw new RuntimeException(
                        'Analytics journal entry is malformed.'
                    );
                }

                [
                    $timestamp,
                    $event,
                    $rawId,
                ] = $parts;

                try {
                    $eventTime =
                        new DateTimeImmutable($timestamp);
                } catch (Throwable $exception) {
                    throw new RuntimeException(
                        'Analytics journal timestamp is invalid.',
                        0,
                        $exception
                    );
                }

                if (
                    $eventTime->format('Y-m-d') !==
                    $expectedDate
                ) {
                    throw new RuntimeException(
                        'Analytics journal date does not match filename.'
                    );
                }

                $id =
                    $rawId === '-'
                        ? null
                        : $rawId;

                $data = $this->applyEvent(
                    $data,
                    $event,
                    $id
                );

                $data['updated_at'] =
                    $eventTime->format(DATE_ATOM);
            }

            if (!feof($handle)) {
                throw new RuntimeException(
                    'Unable to finish reading analytics journal.'
                );
            }
        } finally {
            fclose($handle);
        }

        ksort($data['links']);
        ksort($data['actions']);
        ksort($data['qrs']);

        return $data;
    }

    /**
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    private function applyEvent(
        array $data,
        string $event,
        ?string $id
    ): array {
        if (
            $event === self::EVENT_PAGE_VIEW &&
            $id === null
        ) {
            $data['totals']['page_views'] =
                $this->increment(
                    $data['totals']['page_views']
                );

            return $data;
        }

        if (
            $event === self::EVENT_LINK_CLICK &&
            $id !== null
        ) {
            $this->assertId(
                $id,
                'link'
            );

            $data['totals']['link_clicks'] =
                $this->increment(
                    $data['totals']['link_clicks']
                );

            $data['links'][$id] =
                $this->increment(
                    $data['links'][$id] ?? 0
                );

            return $data;
        }

        if (
            $event === self::EVENT_ACTION_CLICK &&
            $id !== null
        ) {
            $this->assertId(
                $id,
                'action'
            );

            $data['totals']['action_clicks'] =
                $this->increment(
                    $data['totals']['action_clicks']
                );

            $data['actions'][$id] =
                $this->increment(
                    $data['actions'][$id] ?? 0
                );

            return $data;
        }

        if (
            $event === self::EVENT_QR_VISIT &&
            $id !== null
        ) {
            $this->assertQrId($id);

            $data['totals']['qr_visits'] =
                $this->increment(
                    $data['totals']['qr_visits']
                );

            $data['qrs'][$id] =
                $this->increment(
                    $data['qrs'][$id] ?? 0
                );

            return $data;
        }

        throw new InvalidArgumentException(
            'Unsupported analytics event.'
        );
    }

    private function ensureDirectory(): void
    {
        if (is_dir($this->directory)) {
            return;
        }

        if (
            !mkdir(
                $this->directory,
                0750,
                true
            ) &&
            !is_dir($this->directory)
        ) {
            throw new RuntimeException(
                'Unable to create analytics directory.'
            );
        }

        @chmod(
            $this->directory,
            0750
        );
    }

    /**
     * @return array{
     *     version: int,
     *     date: string,
     *     updated_at: string,
     *     totals: array{
     *         page_views: int,
     *         link_clicks: int,
     *         action_clicks: int
     *     },
     *     links: array<string, int>,
     *     actions: array<string, int>
     * }
     */
    private function readData(
        string $path,
        string $expectedDate
    ): array {
        $parsed = Yaml::parseFile($path);

        if (!is_array($parsed)) {
            throw new RuntimeException(
                'Analytics data must be an array.'
            );
        }

        $version =
            $parsed['version'] ?? null;

        if (
            !is_int($version) ||
            !in_array(
                $version,
                [1, self::VERSION],
                true
            )
        ) {
            throw new RuntimeException(
                'Unsupported analytics data version.'
            );
        }

        if (
            ($parsed['date'] ?? null) !==
            $expectedDate
        ) {
            throw new RuntimeException(
                'Analytics date does not match filename.'
            );
        }

        $updatedAt =
            $parsed['updated_at'] ?? null;

        $totals =
            $parsed['totals'] ?? null;

        $links =
            $parsed['links'] ?? null;

        $actions =
            $parsed['actions'] ?? null;

        $qrs =
            $version === 1
                ? []
                : ($parsed['qrs'] ?? null);

        if (
            !is_string($updatedAt) ||
            !is_array($totals) ||
            !is_array($links) ||
            !is_array($actions) ||
            !is_array($qrs)
        ) {
            throw new RuntimeException(
                'Analytics data structure is invalid.'
            );
        }

        $normalizedTotals = [
            'page_views' =>
                $this->normalizeCounter(
                    $totals['page_views'] ?? null
                ),
            'link_clicks' =>
                $this->normalizeCounter(
                    $totals['link_clicks'] ?? null
                ),
            'action_clicks' =>
                $this->normalizeCounter(
                    $totals['action_clicks'] ?? null
                ),
            'qr_visits' =>
                $version === 1
                    ? 0
                    : $this->normalizeCounter(
                        $totals['qr_visits'] ?? null
                    ),
        ];

        $normalizedLinks =
            $this->normalizeIdCounters(
                $links,
                'link'
            );

        $normalizedActions =
            $this->normalizeIdCounters(
                $actions,
                'action'
            );

        $normalizedQrs =
            $this->normalizeQrCounters($qrs);

        return [
            'version' => self::VERSION,
            'date' => $expectedDate,
            'updated_at' => $updatedAt,
            'totals' => $normalizedTotals,
            'links' => $normalizedLinks,
            'actions' => $normalizedActions,
            'qrs' => $normalizedQrs,
        ];
    }

    /**
     * @param array<mixed> $values
     *
     * @return array<string, int>
     */
    private function normalizeIdCounters(
        array $values,
        string $prefix
    ): array {
        $normalized = [];

        foreach ($values as $id => $count) {
            if (!is_string($id)) {
                throw new RuntimeException(
                    'Analytics ID must be a string.'
                );
            }

            $this->assertId(
                $id,
                $prefix
            );

            $normalized[$id] =
                $this->normalizeCounter($count);
        }

        ksort($normalized);

        return $normalized;
    }

    /**
     * @param array<mixed> $values
     *
     * @return array<string, int>
     */
    private function normalizeQrCounters(
        array $values
    ): array {
        $normalized = [];

        foreach ($values as $id => $count) {
            if (!is_string($id)) {
                throw new RuntimeException(
                    'QR analytics ID must be a string.'
                );
            }

            $this->assertQrId($id);

            $normalized[$id] =
                $this->normalizeCounter($count);
        }

        ksort($normalized);

        return $normalized;
    }

    private function normalizeCounter(
        mixed $value
    ): int {
        if (
            !is_int($value) ||
            $value < 0
        ) {
            throw new RuntimeException(
                'Analytics counter is invalid.'
            );
        }

        return $value;
    }

    private function increment(int $value): int
    {
        if ($value >= PHP_INT_MAX) {
            throw new OverflowException(
                'Analytics counter overflow.'
            );
        }

        return $value + 1;
    }

    private function assertId(
        string $id,
        string $prefix
    ): void {
        if (
            preg_match(
                sprintf(
                    '/^%s_[a-f0-9]{16}$/',
                    preg_quote(
                        $prefix,
                        '/'
                    )
                ),
                $id
            ) !== 1
        ) {
            throw new InvalidArgumentException(
                sprintf(
                    'Invalid %s analytics ID.',
                    $prefix
                )
            );
        }
    }

    private function assertQrId(
        string $id
    ): void {
        if ($id !== 'qr_primary') {
            throw new InvalidArgumentException(
                'Invalid QR analytics ID.'
            );
        }
    }

    private function assertDate(
        string $date
    ): void {
        $parsed = DateTimeImmutable::createFromFormat(
            '!Y-m-d',
            $date
        );

        $errors =
            DateTimeImmutable::getLastErrors();

        if (
            $parsed === false ||
            (
                is_array($errors) &&
                (
                    $errors['warning_count'] > 0 ||
                    $errors['error_count'] > 0
                )
            ) ||
            $parsed->format('Y-m-d') !== $date
        ) {
            throw new InvalidArgumentException(
                'Analytics date is invalid.'
            );
        }
    }

    private function dataPath(string $date): string
    {
        return sprintf(
            '%s/%s.yaml',
            rtrim(
                $this->directory,
                '/'
            ),
            $date
        );
    }

    private function journalPath(string $date): string
    {
        return sprintf(
            '%s/%s%s',
            rtrim(
                $this->directory,
                '/'
            ),
            $date,
            self::JOURNAL_SUFFIX
        );
    }

    /**
     * @return array{
     *     version: int,
     *     date: string,
     *     updated_at: string,
     *     totals: array{
     *         page_views: int,
     *         link_clicks: int,
     *         action_clicks: int
     *     },
     *     links: array<string, int>,
     *     actions: array<string, int>
     * }
     */
    private function emptyData(
        string $date,
        string $updatedAt
    ): array {
        return [
            'version' => self::VERSION,
            'date' => $date,
            'updated_at' => $updatedAt,
            'totals' => [
                'page_views' => 0,
                'link_clicks' => 0,
                'action_clicks' => 0,
                'qr_visits' => 0,
            ],
            'links' => [],
            'actions' => [],
            'qrs' => [],
        ];
    }
}
