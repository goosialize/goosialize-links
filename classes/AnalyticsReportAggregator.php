<?php

declare(strict_types=1);

namespace Goosialize\Links;

use InvalidArgumentException;
use OverflowException;
use RuntimeException;
use Throwable;

final class AnalyticsReportAggregator
{
    private readonly AnalyticsStore $store;

    public function __construct(
        private readonly string $directory
    ) {
        if (
            trim($this->directory) === '' ||
            str_contains($this->directory, "\0")
        ) {
            throw new InvalidArgumentException(
                'Analytics report directory is invalid.'
            );
        }

        $this->store =
            new AnalyticsStore($this->directory);
    }

    /**
     * @param array<string, mixed> $config
     *
     * @return array<string, mixed>
     */
    public function createReport(array $config): array
    {
        $snapshot = $this->aggregate();

        $linkLabels =
            $this->configuredLinkLabels($config);

        $actionLabels =
            $this->configuredActionLabels($config);

        $detailItems = [];

        $linkIds = array_values(
            array_unique(
                array_merge(
                    array_keys($linkLabels),
                    array_keys($snapshot['links'])
                )
            )
        );

        sort(
            $linkIds,
            SORT_STRING
        );

        foreach ($linkIds as $id) {
            $detailItems[] = [
                'kind' => 'link',
                'id' => $id,
                'label' =>
                    $linkLabels[$id] ??
                    sprintf(
                        'Removed link (%s)',
                        $id
                    ),
                'clicks' =>
                    $snapshot['links'][$id] ?? 0,
            ];
        }

        $actionIds = array_values(
            array_unique(
                array_merge(
                    array_keys($actionLabels),
                    array_keys($snapshot['actions'])
                )
            )
        );

        sort(
            $actionIds,
            SORT_STRING
        );

        foreach ($actionIds as $id) {
            $detailItems[] = [
                'kind' => 'action',
                'id' => $id,
                'label' =>
                    $actionLabels[$id] ??
                    sprintf(
                        'Removed action (%s)',
                        $id
                    ),
                'clicks' =>
                    $snapshot['actions'][$id] ?? 0,
            ];
        }

        usort(
            $detailItems,
            static function (
                array $left,
                array $right
            ): int {
                $clickComparison =
                    $right['clicks'] <=>
                    $left['clicks'];

                if ($clickComparison !== 0) {
                    return $clickComparison;
                }

                $kindComparison = strcmp(
                    (string) $left['kind'],
                    (string) $right['kind']
                );

                if ($kindComparison !== 0) {
                    return $kindComparison;
                }

                $labelComparison = strcasecmp(
                    (string) $left['label'],
                    (string) $right['label']
                );

                if ($labelComparison !== 0) {
                    return $labelComparison;
                }

                return strcmp(
                    (string) $left['id'],
                    (string) $right['id']
                );
            }
        );

        $items = [
            [
                'kind' => 'summary',
                'metric' => 'page_views',
                'label' => 'Total Page Views',
                'value' =>
                    $snapshot['page_views'],
            ],
            [
                'kind' => 'summary',
                'metric' => 'total_clicks',
                'label' => 'Total Clicks',
                'value' =>
                    $snapshot['total_clicks'],
            ],
            ...$detailItems,
        ];

        $skippedCount = count(
            $snapshot['skipped_files']
        );

        if (
            $snapshot['valid_days'] === 0 &&
            $skippedCount === 0
        ) {
            $message =
                'No analytics data has been recorded yet.';
        } elseif ($snapshot['valid_days'] === 0) {
            $message = sprintf(
                'No valid analytics data was available. ' .
                '%d unreadable file%s skipped.',
                $skippedCount,
                $skippedCount === 1 ? ' was' : 's were'
            );
        } else {
            $message = sprintf(
                'Aggregated %d analytics day%s: ' .
                '%d page view%s and %d click%s.',
                $snapshot['valid_days'],
                $snapshot['valid_days'] === 1
                    ? ''
                    : 's',
                $snapshot['page_views'],
                $snapshot['page_views'] === 1
                    ? ''
                    : 's',
                $snapshot['total_clicks'],
                $snapshot['total_clicks'] === 1
                    ? ''
                    : 's'
            );

            if ($skippedCount > 0) {
                $message .= sprintf(
                    ' %d unreadable file%s skipped.',
                    $skippedCount,
                    $skippedCount === 1
                        ? ' was'
                        : 's were'
                );
            }
        }

        return [
            'id' =>
                'goosialize-links-analytics',
            'title' =>
                'Goosialize Links Analytics',
            'provider' =>
                'goosialize-links',
            'component' => null,
            'status' =>
                $skippedCount > 0
                    ? 'warning'
                    : 'success',
            'message' => $message,
            'meta' => [
                'page_views' =>
                    $snapshot['page_views'],
                'total_clicks' =>
                    $snapshot['total_clicks'],
                'valid_days' =>
                    $snapshot['valid_days'],
                'date_from' =>
                    $snapshot['date_from'],
                'date_to' =>
                    $snapshot['date_to'],
                'skipped_files' =>
                    $skippedCount,
            ],
            'items' => $items,
        ];
    }

    /**
     * @return array{
     *     page_views: int,
     *     total_clicks: int,
     *     links: array<string, int>,
     *     actions: array<string, int>,
     *     valid_days: int,
     *     date_from: string|null,
     *     date_to: string|null,
     *     skipped_files: list<string>
     * }
     */
    public function aggregate(): array
    {
        $aggregate = [
            'page_views' => 0,
            'link_clicks' => 0,
            'action_clicks' => 0,
            'links' => [],
            'actions' => [],
        ];

        $dates = [];
        $skippedFiles = [];

        if (!is_dir($this->directory)) {
            return $this->finalize(
                $aggregate,
                $dates,
                $skippedFiles
            );
        }

        $files = glob(
            rtrim($this->directory, '/') .
            '/*.yaml'
        );

        if ($files === false) {
            throw new RuntimeException(
                'Unable to list analytics files.'
            );
        }

        sort(
            $files,
            SORT_STRING
        );

        foreach ($files as $path) {
            $filename = basename($path);

            if (
                preg_match(
                    '/^(\d{4}-\d{2}-\d{2})\.yaml$/',
                    $filename,
                    $matches
                ) !== 1
            ) {
                $skippedFiles[] =
                    $filename;

                continue;
            }

            $date = $matches[1];

            try {
                $data =
                    $this->store->readDate($date);

                $aggregate =
                    $this->mergeDay(
                        $aggregate,
                        $data
                    );

                $dates[] = $date;
            } catch (Throwable) {
                $skippedFiles[] =
                    $filename;
            }
        }

        return $this->finalize(
            $aggregate,
            $dates,
            $skippedFiles
        );
    }

    /**
     * @param array{
     *     page_views: int,
     *     link_clicks: int,
     *     action_clicks: int,
     *     links: array<string, int>,
     *     actions: array<string, int>
     * } $aggregate
     *
     * @param array<string, mixed> $data
     *
     * @return array{
     *     page_views: int,
     *     link_clicks: int,
     *     action_clicks: int,
     *     links: array<string, int>,
     *     actions: array<string, int>
     * }
     */
    private function mergeDay(
        array $aggregate,
        array $data
    ): array {
        $dailyLinkTotal =
            $this->sumCounters(
                $data['links']
            );

        $dailyActionTotal =
            $this->sumCounters(
                $data['actions']
            );

        if (
            $dailyLinkTotal !==
                $data['totals']['link_clicks'] ||
            $dailyActionTotal !==
                $data['totals']['action_clicks']
        ) {
            throw new RuntimeException(
                'Analytics daily totals do not match item counters.'
            );
        }

        $next = $aggregate;

        $next['page_views'] =
            $this->safeAdd(
                $next['page_views'],
                $data['totals']['page_views']
            );

        $next['link_clicks'] =
            $this->safeAdd(
                $next['link_clicks'],
                $data['totals']['link_clicks']
            );

        $next['action_clicks'] =
            $this->safeAdd(
                $next['action_clicks'],
                $data['totals']['action_clicks']
            );

        foreach (
            $data['links'] as
            $id => $count
        ) {
            $next['links'][$id] =
                $this->safeAdd(
                    $next['links'][$id] ?? 0,
                    $count
                );
        }

        foreach (
            $data['actions'] as
            $id => $count
        ) {
            $next['actions'][$id] =
                $this->safeAdd(
                    $next['actions'][$id] ?? 0,
                    $count
                );
        }

        return $next;
    }

    /**
     * @param array<string, int> $counters
     */
    private function sumCounters(
        array $counters
    ): int {
        $total = 0;

        foreach ($counters as $count) {
            $total =
                $this->safeAdd(
                    $total,
                    $count
                );
        }

        return $total;
    }

    /**
     * @param array{
     *     page_views: int,
     *     link_clicks: int,
     *     action_clicks: int,
     *     links: array<string, int>,
     *     actions: array<string, int>
     * } $aggregate
     *
     * @param list<string> $dates
     * @param list<string> $skippedFiles
     *
     * @return array{
     *     page_views: int,
     *     total_clicks: int,
     *     links: array<string, int>,
     *     actions: array<string, int>,
     *     valid_days: int,
     *     date_from: string|null,
     *     date_to: string|null,
     *     skipped_files: list<string>
     * }
     */
    private function finalize(
        array $aggregate,
        array $dates,
        array $skippedFiles
    ): array {
        sort(
            $dates,
            SORT_STRING
        );

        sort(
            $skippedFiles,
            SORT_STRING
        );

        ksort(
            $aggregate['links'],
            SORT_STRING
        );

        ksort(
            $aggregate['actions'],
            SORT_STRING
        );

        return [
            'page_views' =>
                $aggregate['page_views'],
            'total_clicks' =>
                $this->safeAdd(
                    $aggregate['link_clicks'],
                    $aggregate['action_clicks']
                ),
            'links' =>
                $aggregate['links'],
            'actions' =>
                $aggregate['actions'],
            'valid_days' =>
                count($dates),
            'date_from' =>
                $dates[0] ?? null,
            'date_to' =>
                $dates !== []
                    ? $dates[array_key_last($dates)]
                    : null,
            'skipped_files' =>
                array_values(
                    array_unique($skippedFiles)
                ),
        ];
    }

    /**
     * @param array<string, mixed> $config
     *
     * @return array<string, string>
     */
    private function configuredLinkLabels(
        array $config
    ): array {
        $labels = [];

        foreach (
            (array) ($config['links'] ?? [])
            as $link
        ) {
            if (!is_array($link)) {
                continue;
            }

            $id = $link['id'] ?? null;

            if (
                !is_string($id) ||
                preg_match(
                    '/^link_[a-f0-9]{16}$/',
                    $id
                ) !== 1
            ) {
                continue;
            }

            $title = trim(
                (string) (
                    $link['title'] ?? ''
                )
            );

            $labels[$id] =
                $title !== ''
                    ? $title
                    : $id;
        }

        ksort(
            $labels,
            SORT_STRING
        );

        return $labels;
    }

    /**
     * @param array<string, mixed> $config
     *
     * @return array<string, string>
     */
    private function configuredActionLabels(
        array $config
    ): array {
        $labels = [];

        foreach (
            (array) ($config['actions'] ?? [])
            as $action
        ) {
            if (!is_array($action)) {
                continue;
            }

            $id = $action['id'] ?? null;

            if (
                !is_string($id) ||
                preg_match(
                    '/^action_[a-f0-9]{16}$/',
                    $id
                ) !== 1
            ) {
                continue;
            }

            $label = trim(
                (string) (
                    $action['label'] ?? ''
                )
            );

            if ($label === '') {
                $type = trim(
                    (string) (
                        $action['type'] ?? ''
                    )
                );

                $label =
                    $type !== ''
                        ? ucfirst($type)
                        : $id;
            }

            $labels[$id] = $label;
        }

        ksort(
            $labels,
            SORT_STRING
        );

        return $labels;
    }

    private function safeAdd(
        int $left,
        int $right
    ): int {
        if (
            $left < 0 ||
            $right < 0
        ) {
            throw new RuntimeException(
                'Analytics aggregate counters cannot be negative.'
            );
        }

        if ($right > PHP_INT_MAX - $left) {
            throw new OverflowException(
                'Analytics aggregate counter overflow.'
            );
        }

        return $left + $right;
    }
}
