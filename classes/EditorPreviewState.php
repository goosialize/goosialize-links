<?php

declare(strict_types=1);

namespace Goosialize\Links;

use InvalidArgumentException;

final class EditorPreviewState
{
    /** @param array<string, mixed> $draft @param array<string, mixed> $saved */
    public function merge(array $draft, array $saved): array
    {
        foreach (array_keys($draft) as $key) {
            if (!in_array($key, ['profile', 'appearance', 'actions', 'links'], true)) {
                throw new InvalidArgumentException('Unsupported preview field: ' . $key);
            }
        }

        if (isset($draft['profile'])) {
            $profile = $this->object($draft['profile'], 'profile');
            $this->assertKeys($profile, ['website_url'], 'profile');
            $saved['profile'] = array_replace(is_array($saved['profile'] ?? null) ? $saved['profile'] : [], $profile);
        }
        if (isset($draft['appearance'])) {
            $appearance = $this->object($draft['appearance'], 'appearance');
            $this->assertKeys($appearance, ['theme', 'accent', 'button_shape'], 'appearance');
            $saved['appearance'] = array_replace(is_array($saved['appearance'] ?? null) ? $saved['appearance'] : [], $appearance);
        }

        foreach (['actions', 'links'] as $collection) {
            if (!isset($draft[$collection])) continue;
            if (!is_array($draft[$collection]) || !array_is_list($draft[$collection])) {
                throw new InvalidArgumentException($collection . ' preview must be an ordered list.');
            }
            $allowed = $collection === 'actions'
                ? ['id', 'enabled', 'type', 'value']
                : ['id', 'enabled', 'url', 'new_tab'];
            $base = is_array($saved[$collection] ?? null) ? $saved[$collection] : [];
            $merged = [];
            foreach ($draft[$collection] as $index => $item) {
                $item = $this->object($item, $collection . ' item');
                $this->assertKeys($item, $allowed, $collection . ' item');
                $id = trim((string) ($item['id'] ?? ''));
                $original = $this->findById($base, $id) ?? ($base[$index] ?? []);
                $merged[] = array_replace(is_array($original) ? $original : [], $item);
            }
            $saved[$collection] = $merged;
        }

        $base = (new LinkPageConfigNormalizer())->normalize($saved);
        (new PublicPageExperienceNormalizer())->normalize($saved, $base);
        return $saved;
    }

    /** @return array<string, mixed> */
    private function object(mixed $value, string $label): array
    {
        if (!is_array($value) || array_is_list($value)) {
            throw new InvalidArgumentException($label . ' preview must be an object.');
        }
        return $value;
    }

    /** @param array<string, mixed> $value @param list<string> $allowed */
    private function assertKeys(array $value, array $allowed, string $label): void
    {
        foreach (array_keys($value) as $key) {
            if (!in_array($key, $allowed, true)) {
                throw new InvalidArgumentException('Unsupported ' . $label . ' preview field: ' . $key);
            }
        }
    }

    /** @param array<int, mixed> $items @return array<string, mixed>|null */
    private function findById(array $items, string $id): ?array
    {
        if ($id === '') return null;
        foreach ($items as $item) {
            if (is_array($item) && (string) ($item['id'] ?? '') === $id) return $item;
        }
        return null;
    }
}
