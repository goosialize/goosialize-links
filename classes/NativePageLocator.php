<?php

declare(strict_types=1);

namespace Goosialize\Links;

use RuntimeException;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use FilesystemIterator;
use Symfony\Component\Yaml\Yaml;

final class NativePageLocator
{
    public function __construct(private readonly string $pagesRoot)
    {
    }

    public function route(string $defaultLanguage = ''): ?string
    {
        $matches = [];
        if (is_dir($this->pagesRoot)) {
            $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(
                $this->pagesRoot,
                FilesystemIterator::SKIP_DOTS
            ));
            foreach ($iterator as $file) {
                if ($file->isFile() && preg_match('/^goosialize-links(?:\.[a-z0-9-]+)?\.md$/i', $file->getFilename()) === 1) {
                    $matches[] = $file->getPathname();
                }
            }
        }
        $directories = array_values(array_unique(array_map('dirname', $matches)));
        if ($directories === []) return null;
        if (count($directories) > 1) {
            throw new RuntimeException('Multiple physical Goosialize Links Pages exist.');
        }

        $directory = $directories[0];
        $relative = trim(substr($directory, strlen(rtrim($this->pagesRoot, '/'))), '/');
        $segments = array_map(
            static fn (string $segment): string => preg_replace('/^\d+\./', '', $segment) ?: $segment,
            explode('/', $relative)
        );
        $route = '/' . implode('/', $segments);

        $preferred = $directory . '/goosialize-links' . ($defaultLanguage !== '' ? '.' . $defaultLanguage : '') . '.md';
        $file = is_file($preferred) ? $preferred : $matches[0];
        $raw = file_get_contents($file);
        if (is_string($raw) && preg_match('/^---\R(.*?)\R---/s', $raw, $match) === 1) {
            $header = Yaml::parse($match[1]);
            if (is_array($header)) {
                $override = trim((string) ($header['routes']['default'] ?? ''));
                if ($override !== '') return '/' . trim($override, '/');
                $slug = trim((string) ($header['slug'] ?? ''));
                if ($slug !== '') {
                    $segments[count($segments) - 1] = $slug;
                    $route = '/' . implode('/', $segments);
                }
            }
        }

        return $route;
    }
}
