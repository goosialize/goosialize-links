<?php

declare(strict_types=1);

if ($argc !== 2) {
    fwrite(
        STDERR,
        "Usage: runtime-core.php /path/to/plugin\n"
    );

    exit(2);
}

$plugin = rtrim(
    $argv[1],
    '/'
);

$gravAutoload =
    '/app/www/public/vendor/autoload.php';

$pluginAutoload =
    $plugin .
    '/vendor/autoload.php';

if (!is_file($gravAutoload)) {
    fwrite(
        STDERR,
        "Grav autoload unavailable.\n"
    );

    exit(1);
}

if (!is_file($pluginAutoload)) {
    fwrite(
        STDERR,
        "Plugin vendor autoload unavailable.\n"
    );

    exit(1);
}

require $gravAutoload;
require $pluginAutoload;

use Goosialize\Links\AnalyticsReportAggregator;
use Goosialize\Links\AnalyticsStore;
use Goosialize\Links\LinkPageConfigNormalizer;
use Goosialize\Links\PublicPageExperienceNormalizer;
use Goosialize\Links\PublicPageViewModelFactory;
use Goosialize\Links\QrCodeGenerator;
use Goosialize\Links\SocialActionNormalizer;
use Goosialize\Links\TrackingRouteResolver;

function check(
    bool $condition,
    string $label
): void {
    if (!$condition) {
        throw new RuntimeException(
            $label . ' = FAIL'
        );
    }

    echo $label . " = PASS\n";
}

function expectException(
    callable $callable,
    string $label
): void {
    try {
        $callable();
    } catch (Throwable) {
        echo $label . " = PASS\n";

        return;
    }

    throw new RuntimeException(
        $label . ' = FAIL'
    );
}

echo "==============================================\n";
echo " CORE RUNTIME CONTRACT\n";
echo "==============================================\n\n";

echo "--- social/contact actions ---\n";

$actions =
    new SocialActionNormalizer();

$result =
    $actions->normalize([
        [
            'id' =>
                'action_aaaaaaaaaaaaaaaa',
            'enabled' => true,
            'type' => 'website',
            'value' =>
                'https://example.com/',
            'label' => 'Website',
        ],
        [
            'id' =>
                'action_bbbbbbbbbbbbbbbb',
            'enabled' => true,
            'type' => 'email',
            'value' =>
                'hello@example.com',
            'label' => 'Email',
        ],
        [
            'id' =>
                'action_cccccccccccccccc',
            'enabled' => true,
            'type' => 'phone',
            'value' =>
                '+357 22123456',
            'label' => 'Phone',
        ],
        [
            'id' =>
                'action_dddddddddddddddd',
            'enabled' => true,
            'type' => 'whatsapp',
            'value' =>
                '+35799123456',
            'label' => 'WhatsApp',
        ],
    ]);

check(
    count($result) === 4,
    'Valid action normalization'
);

check(
    $result[0]['href']
        === 'https://example.com/',
    'Website href'
);

check(
    $result[1]['href']
        === 'mailto:hello@example.com',
    'Email href'
);

check(
    str_starts_with(
        $result[2]['href'],
        'tel:'
    ),
    'Phone href'
);

check(
    $result[3]['href']
        === 'https://wa.me/35799123456',
    'WhatsApp href'
);

$localizedActionId = 'action_eeeeeeeeeeeeeeee';
$localizedActions = $actions->normalize([
    [
        'id' => $localizedActionId,
        'enabled' => true,
        'type' => 'instagram',
        'value' => 'https://instagram.com/example',
        'label' => 'Instagram',
        'translations' => [
            ['language' => 'el', 'label' => 'Ίνσταγκραμ'],
        ],
    ],
]);

check(
    $localizedActions[0]['id'] === $localizedActionId &&
    $localizedActions[0]['translations']['el'] === 'Ίνσταγκραμ',
    'Localized action preserves identity'
);

expectException(
    static fn () =>
        $actions->normalize([
            [
                'type' => 'website',
                'value' =>
                    'javascript:alert(1)',
            ],
        ]),
    'Unsafe action URL rejected'
);

echo "\n--- bounded appearance ---\n";

$experience =
    new PublicPageExperienceNormalizer();

$base = [
    'profile' => [],
    'links' => [],
];

$normalized =
    $experience->normalize(
        [
            'profile' => [
                'image' => [],
            ],
            'appearance' => [
                'theme' =>
                    'sunrise',
                'accent' =>
                    'purple',
                'button_shape' =>
                    'pill',
            ],
            'actions' => [],
        ],
        $base
    );

check(
    $normalized['appearance']['theme']
        === 'sunrise',
    'Theme normalization'
);

check(
    $normalized['appearance']['accent']
        === 'purple',
    'Accent normalization'
);

check(
    $normalized['appearance']['button_shape']
        === 'pill',
    'Button shape normalization'
);

expectException(
    static fn () =>
        $experience->normalize(
            [
                'appearance' => [
                    'theme' =>
                        'custom-theme',
                ],
            ],
            $base
        ),
    'Custom theme rejected'
);

echo "\n--- multilingual public content ---\n";

$rawLocalized = [
    'enabled' => true,
    'route' => '/bio',
    'profile' => [
        'name' => 'Goosialize',
        'title' => 'Links',
        'description' => 'English description',
        'website_url' => 'https://example.com',
        'translations' => [[
            'language' => 'el',
            'name' => 'Γκουσιλάιζ',
            'title' => 'Σύνδεσμοι',
            'description' => 'Ελληνική περιγραφή',
        ]],
    ],
    'appearance' => [],
    'actions' => [[
        'id' => 'action_ffffffffffffffff',
        'type' => 'email',
        'value' => 'hello@example.com',
        'label' => 'Email',
        'translations' => [[
            'language' => 'el',
            'label' => 'Ηλεκτρονικό ταχυδρομείο',
        ]],
    ]],
    'links' => [[
        'id' => 'link_ffffffffffffffff',
        'title' => 'Shop Now',
        'url' => 'https://example.com/shop',
        'translations' => [[
            'language' => 'el',
            'title' => 'Αγόρασε τώρα',
        ]],
    ]],
];

$localizedBase = (new LinkPageConfigNormalizer())->normalize($rawLocalized);
$localizedConfig = $experience->normalize($rawLocalized, $localizedBase);
$localizedView = (new PublicPageViewModelFactory())->create(
    $localizedConfig,
    'el'
);
$fallbackView = (new PublicPageViewModelFactory())->create(
    $localizedConfig,
    'fr'
);

check(
    $localizedView['profile']['title'] === 'Σύνδεσμοι' &&
    $localizedView['links'][0]['title'] === 'Αγόρασε τώρα' &&
    $localizedView['actions'][0]['label'] === 'Ηλεκτρονικό ταχυδρομείο',
    'Localized content selection'
);

check(
    $fallbackView['profile']['title'] === 'Links' &&
    $fallbackView['links'][0]['title'] === 'Shop Now' &&
    $fallbackView['actions'][0]['label'] === 'Email',
    'Localized content fallback'
);

check(
    $localizedView['links'][0]['id'] === $fallbackView['links'][0]['id'] &&
    $localizedView['actions'][0]['id'] === $fallbackView['actions'][0]['id'] &&
    str_contains($localizedView['links'][0]['tracked_url'], 'link_ffffffffffffffff'),
    'Language switch preserves analytics identities'
);

echo "\n--- analytics storage ---\n";

$tmp =
    sys_get_temp_dir() .
    '/goosialize-links-test-' .
    bin2hex(
        random_bytes(6)
    );

$store =
    new AnalyticsStore(
        $tmp
    );

$now =
    new DateTimeImmutable(
        '2026-08-11T12:00:00+00:00'
    );

$store->recordPageView(
    $now
);

$store->recordQrVisit(
    'qr_primary',
    $now
);

$store->recordLinkClick(
    'link_aaaaaaaaaaaaaaaa',
    $now
);

$store->recordActionClick(
    'action_bbbbbbbbbbbbbbbb',
    $now
);

$day =
    $store->readDate(
        '2026-08-11'
    );

check(
    $day['totals']['page_views']
        === 1,
    'Page view recording'
);

check(
    $day['totals']['qr_visits']
        === 1,
    'QR visit recording'
);

check(
    $day['totals']['link_clicks'] === 1 &&
    $day['links']['link_aaaaaaaaaaaaaaaa'] === 1,
    'Link click recording'
);

check(
    $day['totals']['action_clicks'] === 1 &&
    $day['actions']['action_bbbbbbbbbbbbbbbb'] === 1,
    'Action click recording'
);

$report =
    (
        new AnalyticsReportAggregator(
            $tmp
        )
    )->aggregate();

check(
    $report['page_views']
        === 1,
    'Analytics aggregation page views'
);

check(
    $report['qr_visits']
        === 1,
    'Analytics aggregation QR visits'
);

check(
    $report['total_clicks'] === 2 &&
    $report['links']['link_aaaaaaaaaaaaaaaa'] === 1 &&
    $report['actions']['action_bbbbbbbbbbbbbbbb'] === 1,
    'Link/action analytics aggregation'
);

echo "\n--- tracking routes ---\n";

$tracking = new TrackingRouteResolver();

check(
    $tracking->resolve(
        '/bio/go/link_aaaaaaaaaaaaaaaa',
        '/bio'
    ) === [
        'event' => 'link_click',
        'id' => 'link_aaaaaaaaaaaaaaaa',
    ],
    'Tracked link route resolution'
);

check(
    $tracking->resolve(
        '/bio/action/action_bbbbbbbbbbbbbbbb',
        '/bio'
    ) === [
        'event' => 'action_click',
        'id' => 'action_bbbbbbbbbbbbbbbb',
    ],
    'Tracked action route resolution'
);

expectException(
    static fn () => $tracking->resolve(
        '/bio/go/link_aaaaaaaaaaaaaaaa?redirect=https://example.com',
        '/bio'
    ),
    'Unsafe tracking path rejected'
);

echo "\n--- QR generation ---\n";

$generator =
    new QrCodeGenerator();

$payload =
    'https://example.com/bio/qr/qr_primary';

$png =
    $generator->generatePng(
        $payload
    );

$svg =
    $generator->generateSvg(
        $payload
    );

check(
    str_starts_with(
        $png,
        "\x89PNG\r\n\x1a\n"
    ),
    'PNG QR generation'
);

check(
    str_contains(
        strtolower($svg),
        '<svg'
    ),
    'SVG QR generation'
);

echo "\n--- cleanup ---\n";

foreach (
    glob(
        $tmp . '/*'
    ) ?: []
    as $file
) {
    @unlink($file);
}

@rmdir($tmp);

echo "Synthetic analytics cleanup = PASS\n";

echo "\nCORE RUNTIME CONTRACT = PASS\n";
