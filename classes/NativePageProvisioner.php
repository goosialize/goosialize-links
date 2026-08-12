<?php

declare(strict_types=1);

namespace Goosialize\Links;

use RuntimeException;
use Symfony\Component\Yaml\Yaml;

final class NativePageProvisioner
{
    public function __construct(
        private readonly string $pagesRoot,
        private readonly string $backupRoot
    ) {
    }

    /**
     * @param array<string, mixed> $config
     * @param list<string> $languages
     * @return array{folder: string, files: list<string>, backup: string|null}
     */
    public function provision(
        array $config,
        array $languages,
        string $defaultLanguage,
        string $configFile
    ): array {
        $route = '/' . trim((string) ($config['route'] ?? '/bio'), '/');
        $folder = $this->pageFolder($route);
        $files = glob($folder . '/goosialize-links*.md') ?: [];

        $languages = array_values(array_unique(array_filter(array_map(
            static fn (mixed $value): string => strtolower(trim((string) $value)),
            $languages
        ))));
        if ($languages === []) {
            $languages = [$defaultLanguage !== '' ? $defaultLanguage : 'en'];
        }

        $multilingual = count($languages) > 1;
        $expected = array_map(
            static fn (string $language): string => $folder . '/goosialize-links' . ($multilingual ? '.' . $language : '') . '.md',
            $languages
        );
        if (array_diff($expected, $files) === []) {
            sort($files);
            return ['folder' => $folder, 'files' => array_values($files), 'backup' => null];
        }

        $backup = $this->backup($configFile);
        if (!is_dir($folder) && !mkdir($folder, 0775, true) && !is_dir($folder)) {
            throw new RuntimeException('Unable to create the native Links Page folder.');
        }

        foreach ($languages as $language) {
            $filename = 'goosialize-links' . ($multilingual ? '.' . $language : '') . '.md';
            $path = $folder . '/' . $filename;
            if (is_file($path)) {
                continue;
            }
            $content = $this->pageContent($config, $language, $defaultLanguage);
            $yaml = Yaml::dump($content, 8, 2, Yaml::DUMP_MULTI_LINE_LITERAL_BLOCK);
            $raw = "---\n" . $yaml . "---\n";
            if (file_put_contents($path, $raw, LOCK_EX) === false) {
                throw new RuntimeException('Unable to create native Links Page translation.');
            }
            $files[] = $path;
        }

        sort($files);
        return ['folder' => $folder, 'files' => array_values($files), 'backup' => $backup];
    }

    private function pageFolder(string $route): string
    {
        $segments = array_values(array_filter(explode('/', trim($route, '/'))));
        if ($segments === []) {
            throw new RuntimeException('The legacy route cannot provision the site root.');
        }

        $parent = rtrim($this->pagesRoot, '/');
        foreach ($segments as $index => $segment) {
            if (preg_match('/^[a-z0-9][a-z0-9-]*$/', $segment) !== 1) {
                throw new RuntimeException('The legacy route is not safe to provision.');
            }
            if ($index === count($segments) - 1 && count($segments) === 1) {
                $matches = glob($parent . '/*.' . $segment, GLOB_ONLYDIR) ?: [];
                if ($matches !== []) {
                    $parent = $matches[0];
                    continue;
                }
                $orders = [];
                foreach (glob($parent . '/*', GLOB_ONLYDIR) ?: [] as $directory) {
                    if (preg_match('/^(\d+)\./', basename($directory), $match) === 1) {
                        $orders[] = (int) $match[1];
                    }
                }
                $parent .= '/' . str_pad((string) ((max($orders ?: [0])) + 1), 2, '0', STR_PAD_LEFT) . '.' . $segment;
            } else {
                $parent .= '/' . $segment;
            }
        }

        return $parent;
    }

    private function backup(string $configFile): ?string
    {
        if (!is_file($configFile)) {
            return null;
        }
        if (!is_dir($this->backupRoot) && !mkdir($this->backupRoot, 0700, true) && !is_dir($this->backupRoot)) {
            throw new RuntimeException('Unable to create the migration backup directory.');
        }
        $hash = hash_file('sha256', $configFile);
        $target = rtrim($this->backupRoot, '/') . '/goosialize-links-' . $hash . '.yaml';
        if (!is_file($target) && !copy($configFile, $target)) {
            throw new RuntimeException('Unable to back up the legacy Links configuration.');
        }
        return $target;
    }

    /** @param array<string, mixed> $config @return array<string, mixed> */
    private function pageContent(array $config, string $language, string $default): array
    {
        $profile = is_array($config['profile'] ?? null) ? $config['profile'] : [];
        $profileOverlay = $this->overlay($profile['translations'] ?? [], $language);
        $links = [];
        foreach ((array) ($config['links'] ?? []) as $link) {
            if (!is_array($link) || empty($link['id'])) continue;
            $overlay = $this->overlay($link['translations'] ?? [], $language);
            $links[] = ['title' => (string) ($overlay['title'] ?? $link['title'] ?? ''), 'identity' => (string) $link['id']];
        }
        $actions = [];
        foreach ((array) ($config['actions'] ?? []) as $action) {
            if (!is_array($action) || empty($action['id'])) continue;
            $overlay = $this->overlay($action['translations'] ?? [], $language);
            $actions[] = ['label' => (string) ($overlay['label'] ?? $action['label'] ?? ''), 'identity' => (string) $action['id']];
        }

        return [
            'title' => 'Goosialize Links',
            'visible' => false,
            'routable' => true,
            'published' => true,
            'goosialize_links' => [
                'profile' => [
                    'name' => (string) ($profileOverlay['name'] ?? $profile['name'] ?? ''),
                    'title' => (string) ($profileOverlay['title'] ?? $profile['title'] ?? ''),
                    'description' => (string) ($profileOverlay['description'] ?? $profile['description'] ?? ''),
                ],
                'links' => $links,
                'actions' => $actions,
            ],
        ];
    }

    /** @return array<string, mixed> */
    private function overlay(mixed $values, string $language): array
    {
        if (!is_array($values)) return [];
        foreach ($values as $value) {
            if (is_array($value) && strtolower((string) ($value['language'] ?? '')) === $language) return $value;
        }
        return [];
    }
}
