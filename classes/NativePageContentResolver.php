<?php

declare(strict_types=1);

namespace Goosialize\Links;

final class NativePageContentResolver
{
    /** @param array<string, mixed> $config @param array<string, mixed> $header */
    public function apply(array $config, array $header): array
    {
        $content = is_array($header['goosialize_links'] ?? null) ? $header['goosialize_links'] : [];
        $profile = is_array($content['profile'] ?? null) ? $content['profile'] : [];
        foreach (['name', 'title', 'description'] as $key) {
            $value = trim((string) ($profile[$key] ?? ''));
            if ($value !== '') $config['profile'][$key] = $value;
        }
        $config['links'] = $this->merge($config['links'] ?? [], $content['links'] ?? [], 'title');
        $config['actions'] = $this->merge($config['actions'] ?? [], $content['actions'] ?? [], 'label');
        return $config;
    }

    private function merge(mixed $configured, mixed $editorial, string $field): array
    {
        $configured = is_array($configured) ? $configured : [];
        $byId = [];
        foreach ((array) $editorial as $item) {
            if (is_array($item) && !empty($item['identity'])) $byId[(string) $item['identity']] = $item;
        }
        foreach ($configured as &$item) {
            if (!is_array($item)) continue;
            $value = trim((string) ($byId[(string) ($item['id'] ?? '')][$field] ?? ''));
            if ($value !== '') $item[$field] = $value;
        }
        unset($item);
        return $configured;
    }
}
