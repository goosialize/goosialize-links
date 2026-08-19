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
use Goosialize\Links\EditorPreviewState;
use Goosialize\Links\LinkPageConfigNormalizer;
use Goosialize\Links\NativePageContentResolver;
use Goosialize\Links\NativePageLocator;
use Goosialize\Links\NativePageProvisioner;
use Goosialize\Links\PublicPageExperienceNormalizer;
use Goosialize\Links\PublicPageViewModelFactory;
use Goosialize\Links\ProfileImageResolver;
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

check(
    $normalized['appearance']['powered_by']
        === true,
    'Powered-by defaults enabled'
);

$poweredByDisabled =
    $experience->normalize(
        [
            'profile' => [
                'image' => [],
            ],
            'appearance' => [
                'theme' => 'light',
                'accent' => 'yellow',
                'button_shape' => 'rounded',
                'powered_by' => false,
            ],
            'actions' => [],
        ],
        $base
    );

check(
    $poweredByDisabled['appearance']['powered_by']
        === false,
    'Powered-by explicit disable normalization'
);

$poweredByStringFalse =
    $experience->normalize(
        [
            'profile' => [
                'image' => [],
            ],
            'appearance' => [
                'powered_by' => 'false',
            ],
            'actions' => [],
        ],
        $base
    );

check(
    $poweredByStringFalse['appearance']['powered_by']
        === false,
    'Powered-by serialized false normalization'
);

$poweredByFactory =
    new PublicPageViewModelFactory();

$poweredByDefaultView =
    $poweredByFactory->create(
        $normalized,
        'en'
    );

check(
    ($poweredByDefaultView['powered_by']['enabled'] ?? null)
        === true,
    'Powered-by default view model enabled'
);

$poweredByDisabledView =
    $poweredByFactory->create(
        $poweredByDisabled,
        'en'
    );

check(
    ($poweredByDisabledView['powered_by']['enabled'] ?? null)
        === false,
    'Powered-by disabled view model'
);

expectException(
    static fn () =>
        $experience->normalize(
            [
                'appearance' => [
                    'powered_by' => 'maybe',
                ],
            ],
            $base
        ),
    'Invalid powered-by value rejected'
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

echo "\n--- native Page provisioning ---\n";

$nativeRoot = sys_get_temp_dir() . '/goosialize-links-native-' . bin2hex(random_bytes(6));
mkdir($nativeRoot . '/pages/01.home', 0775, true);
mkdir($nativeRoot . '/pages/02.docs', 0775, true);
file_put_contents($nativeRoot . '/legacy.yaml', "route: /bio\n");
$provisioner = new NativePageProvisioner($nativeRoot . '/pages', $nativeRoot . '/backups');
$firstProvision = $provisioner->provision($rawLocalized, ['en', 'el'], 'en', $nativeRoot . '/legacy.yaml');
$englishPage = $firstProvision['folder'] . '/goosialize-links.en.md';
$greekPage = $firstProvision['folder'] . '/goosialize-links.el.md';
check(is_file($englishPage) && is_file($greekPage), 'Native EN/EL Page creation');
check((new NativePageLocator($nativeRoot . '/pages'))->route('/bio', 'en') === '/bio', 'Physical Page route discovery');

$unrelatedDirectory = $nativeRoot . '/pages/03.unrelated';
mkdir($unrelatedDirectory, 0775, true);
file_put_contents(
    $unrelatedDirectory . '/goosialize-links.md',
    "---\ntitle: Unrelated Links Page\n---\n"
);
check(
    (new NativePageLocator($nativeRoot . '/pages'))
        ->route('/bio', 'en') === '/bio',
    'Unrelated physical Links Page is ignored by bounded lookup'
);
unlink($unrelatedDirectory . '/goosialize-links.md');
rmdir($unrelatedDirectory);

$englishBeforeMalformedYaml = file_get_contents($englishPage);
file_put_contents(
    $englishPage,
    "---\ntitle: [broken\n---\n"
);
expectException(
    static fn () =>
        (new NativePageLocator($nativeRoot . '/pages'))
            ->route('/bio', 'en'),
    'Malformed native Page YAML fails locally'
);
file_put_contents(
    $englishPage,
    $englishBeforeMalformedYaml
);
check(
    (new NativePageLocator($nativeRoot . '/pages'))
        ->route('/bio', 'en') === '/bio',
    'Native Page route recovers after malformed fixture'
);
echo "\n--- bounded native Page location ---\n";

$boundedLocator = new NativePageLocator(
    $nativeRoot . '/pages'
);

check(
    $boundedLocator->route('/bio', 'en') === '/bio',
    'Numbered configured route lookup'
);

$directRoot =
    sys_get_temp_dir() .
    '/goosialize-links-direct-' .
    bin2hex(random_bytes(6));

mkdir(
    $directRoot . '/pages/bio',
    0775,
    true
);

file_put_contents(
    $directRoot . '/pages/bio/goosialize-links.en.md',
    "---\ntitle: Direct Links Page\n---\n"
);

check(
    (new NativePageLocator($directRoot . '/pages'))
        ->route('/bio', 'en') === '/bio',
    'Unnumbered configured route lookup'
);

$nestedRoot =
    sys_get_temp_dir() .
    '/goosialize-links-nested-' .
    bin2hex(random_bytes(6));

mkdir(
    $nestedRoot . '/pages/01.company/02.team',
    0775,
    true
);

file_put_contents(
    $nestedRoot .
    '/pages/01.company/02.team/goosialize-links.en.md',
    "---\ntitle: Team Links\n---\n"
);

check(
    (new NativePageLocator($nestedRoot . '/pages'))
        ->route('/company/team', 'en') === '/company/team',
    'Nested configured route lookup'
);

$overrideFile =
    $directRoot .
    '/pages/bio/goosialize-links.en.md';

file_put_contents(
    $overrideFile,
    "---\nroutes:\n  default: /links-home\n---\n"
);

check(
    (new NativePageLocator($directRoot . '/pages'))
        ->route('/bio', 'en') === '/links-home',
    'Native routes.default override'
);

file_put_contents(
    $overrideFile,
    "---\nslug: my-links\n---\n"
);

check(
    (new NativePageLocator($directRoot . '/pages'))
        ->route('/bio', 'en') === '/my-links',
    'Native slug override'
);

file_put_contents(
    $overrideFile,
    "---\ntitle: Direct Links Page\n---\n"
);

mkdir(
    $directRoot . '/pages/01.unrelated/deep/nested',
    0775,
    true
);

file_put_contents(
    $directRoot .
    '/pages/01.unrelated/deep/nested/goosialize-links.en.md',
    "---\ntitle: Unrelated deep Links file\n---\n"
);

check(
    (new NativePageLocator($directRoot . '/pages'))
        ->route('/bio', 'en') === '/bio',
    'Deep unrelated Links file is not recursively scanned'
);

mkdir(
    $directRoot . '/pages/02.bio',
    0775,
    true
);

expectException(
    static fn () =>
        (new NativePageLocator($directRoot . '/pages'))
            ->route('/bio', 'en'),
    'Duplicate immediate route directory rejected'
);

check(
    (new NativePageLocator($directRoot . '/pages'))
        ->route('/missing', 'en') === null,
    'Missing configured route returns null'
);

expectException(
    static fn () =>
        (new NativePageLocator($directRoot . '/pages'))
            ->route('/Unsafe Route', 'en'),
    'Unsafe configured route rejected'
);

check($firstProvision['backup'] !== null && is_file($firstProvision['backup']), 'Legacy configuration backup');
check(
    ($rawLocalized['profile']['translations'][0]['language'] ?? null) === 'el' &&
    ($rawLocalized['links'][0]['translations'][0]['title'] ?? null) === 'Αγόρασε τώρα' &&
    ($rawLocalized['actions'][0]['translations'][0]['label'] ?? null) === 'Ηλεκτρονικό ταχυδρομείο',
    'Legacy localized overlays retained after provisioning'
);
$greekBefore = file_get_contents($greekPage);
$secondProvision = $provisioner->provision($rawLocalized, ['en', 'el'], 'en', $nativeRoot . '/legacy.yaml');
check(file_get_contents($greekPage) === $greekBefore && $secondProvision['backup'] === null, 'Idempotent native Page provisioning');
$partialRoot = sys_get_temp_dir() . '/goosialize-links-native-partial-' . bin2hex(random_bytes(6));
mkdir($partialRoot . '/pages/03.bio', 0775, true);
file_put_contents($partialRoot . '/legacy.yaml', "route: /bio\n");
$preservedEnglish = "---\ntitle: Existing native content\n---\n";
file_put_contents($partialRoot . '/pages/03.bio/goosialize-links.en.md', $preservedEnglish);
$partialProvision = (new NativePageProvisioner($partialRoot . '/pages', $partialRoot . '/backups'))
    ->provision($rawLocalized, ['en', 'el'], 'en', $partialRoot . '/legacy.yaml');
check(
    file_get_contents($partialRoot . '/pages/03.bio/goosialize-links.en.md') === $preservedEnglish &&
    is_file($partialRoot . '/pages/03.bio/goosialize-links.el.md') &&
    $partialProvision['backup'] !== null,
    'Existing native translation preserved while missing translation is provisioned'
);
$native = (new NativePageContentResolver())->apply($localizedConfig, [
    'goosialize_links' => [
        'profile' => ['title' => 'Native title'],
        'links' => [['identity' => 'link_ffffffffffffffff', 'title' => 'Native link']],
        'actions' => [['identity' => 'action_ffffffffffffffff', 'label' => 'Native action']],
    ],
]);
check(
    $native['profile']['title'] === 'Native title' &&
    $native['links'][0]['title'] === 'Native link' &&
    $native['actions'][0]['label'] === 'Native action' &&
    $native['links'][0]['id'] === 'link_ffffffffffffffff' &&
    $native['actions'][0]['id'] === 'action_ffffffffffffffff',
    'Native content precedence preserves identities'
);

echo "\n--- ephemeral editor preview state ---\n";
$previewSaved = $rawLocalized;
$previewDraft = [
    'profile' => ['website_url' => 'https://preview.example.com'],
    'appearance' => ['theme' => 'dark', 'accent' => 'coral', 'button_shape' => 'pill'],
    'actions' => [[
        'id' => 'action_ffffffffffffffff',
        'enabled' => true,
        'type' => 'website',
        'value' => 'https://preview.example.com/action',
    ]],
    'links' => [[
        'id' => 'link_ffffffffffffffff',
        'enabled' => true,
        'url' => 'https://preview.example.com/link',
        'new_tab' => false,
    ]],
];
$previewMerged = (new EditorPreviewState())->merge($previewDraft, $previewSaved);
check(
    $previewMerged['appearance'] === $previewDraft['appearance'] &&
    $previewMerged['profile']['website_url'] === 'https://preview.example.com' &&
    $previewMerged['actions'][0]['label'] === 'Email' &&
    $previewMerged['links'][0]['title'] === 'Shop Now',
    'Whitelisted unsaved preview overlay'
);
check(
    $previewSaved['profile']['website_url'] === 'https://example.com' &&
    $previewSaved['appearance'] === [],
    'Preview overlay does not mutate saved configuration'
);
expectException(
    static fn () => (new EditorPreviewState())->merge(['route' => '/elsewhere'], $previewSaved),
    'Arbitrary preview route rejected'
);
expectException(
    static fn () => (new EditorPreviewState())->merge(['appearance' => ['custom_css' => 'body{}']], $previewSaved),
    'Unsupported preview field rejected'
);

echo "\n--- profile image resolution ---\n";
$imageRoot = sys_get_temp_dir() . '/goosialize-links-images-' . bin2hex(random_bytes(6));
mkdir($imageRoot . '/media/goosialize-links/profile', 0775, true);
mkdir($imageRoot . '/user/media/goosialize-links/profile', 0775, true);
file_put_contents($imageRoot . '/media/goosialize-links/profile/logo mark.png', 'png');
file_put_contents($imageRoot . '/user/media/goosialize-links/profile/legacy.png', 'png');
$imageResolver = new ProfileImageResolver($imageRoot);
$savedImage = $imageResolver->resolve([
    'user/media/goosialize-links/profile/logo mark.png' => [
        'name' => 'Logo Mark.png',
        'type' => 'image/png',
        'path' => 'user/media/goosialize-links/profile/logo mark.png',
    ],
]);
$legacyImage = $imageResolver->resolve([
    'user/user/media/goosialize-links/profile/legacy.png' => [
        'type' => 'image/png',
        'path' => 'user/user/media/goosialize-links/profile/legacy.png',
    ],
]);
check(
    ($savedImage['stream'] ?? null) === 'user://media/goosialize-links/profile/logo mark.png' &&
    ($savedImage['filename'] ?? null) === 'logo mark.png',
    'Saved Profile image stream resolution'
);
check(
    ($legacyImage['stream'] ?? null) === 'user://user/media/goosialize-links/profile/legacy.png',
    'Legacy duplicated Profile image path compatibility'
);
check(
    $imageResolver->resolve(['missing.png' => ['type' => 'image/png']]) === null &&
    $imageResolver->resolve(['../unsafe.png' => ['type' => 'image/png']]) === null &&
    $imageResolver->resolve(['https://example.com/image.png' => ['type' => 'image/png']]) === null,
    'Missing and unsafe Profile images use fallback'
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
