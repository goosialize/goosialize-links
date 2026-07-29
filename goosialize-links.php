<?php

declare(strict_types=1);

namespace Grav\Plugin;

use Grav\Common\Plugin;

/**
 * Goosialize Links plugin.
 */
class GoosializeLinksPlugin extends Plugin
{
    /**
     * Register plugin events.
     *
     * The initial skeleton intentionally has no runtime behavior.
     *
     * @return array<string, mixed>
     */
    public static function getSubscribedEvents(): array
    {
        return [];
    }
}
