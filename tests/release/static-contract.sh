#!/usr/bin/env bash

set -Eeuo pipefail

export LC_ALL=C
export NO_COLOR=1

ROOT="$(
    cd "$(dirname "${BASH_SOURCE[0]}")/../.."
    pwd
)"

cd "$ROOT"

echo "=============================================="
echo " STATIC RELEASE CONTRACT"
echo "=============================================="

echo
echo "--- required package source ---"

required=(
    blueprints.yaml
    goosialize-links.yaml
    goosialize-links.php
    composer.json
    composer.lock
    permissions.yaml
    LICENSE
    README.md
    CHANGELOG.md
    languages/en.yaml
    languages/el.yaml
    templates/goosialize-links.html.twig
    admin-next/pages/goosialize-links.js
    admin-next/fields/goosialize-links-preview.js
    classes/EditorPreviewController.php
    vendor/autoload.php
)

for file in "${required[@]}"; do
    test -f "$file"
    echo "$file = PASS"
done

echo
echo "--- UX1 Admin2 and multilingual contract ---"

python3 - <<'PY'
from pathlib import Path
import yaml

bp = yaml.safe_load(Path("blueprints.yaml").read_text())
fields = bp["form"]["fields"]

for name in (
    "profile_section",
    "appearance_section",
    "actions_section",
    "links_section",
):
    field = fields[name]
    assert field["type"] == "fieldset"
    assert field["collapsible"] is True
    help_field = field["fields"][f"{name}_help"]
    assert help_field["type"] == "display"
    assert help_field["content"] == f"PLUGIN_GOOSIALIZE_LINKS.{name.upper()}_HELP"

assert fields["actions_section"]["fields"]["actions"]["key"] == "type"
assert fields["links_section"]["fields"]["links"]["key"] == "url"
assert fields["route"]["type"] == "hidden"
assert fields["profile_section"]["fields"]["profile.translations"]["type"] == "hidden"
assert fields["actions_section"]["fields"]["actions"]["fields"][".translations"]["type"] == "hidden"
assert fields["links_section"]["fields"]["links"]["fields"][".translations"]["type"] == "hidden"
powered = fields["appearance_section"]["fields"]["appearance.powered_by"]
assert powered["type"] == "toggle"
assert powered["default"] is True
assert powered["highlight"] is True
assert powered["options"][True] == "PLUGIN_ADMIN.ENABLED"
assert powered["options"][False] == "PLUGIN_ADMIN.DISABLED"

defaults = yaml.safe_load(Path("goosialize-links.yaml").read_text())
assert defaults["appearance"]["powered_by"] is True

experience = Path("classes/PublicPageExperienceNormalizer.php").read_text()
view_model = Path("classes/PublicPageViewModelFactory.php").read_text()
template = Path("templates/goosialize-links.html.twig").read_text()

assert "$rawAppearance['powered_by'] ?? true" in experience
assert "'powered_by' => $poweredBy" in experience
assert "'enabled' => (bool)" in view_model
assert "$appearance['powered_by']" in view_model
assert "{% if goosialize_links.powered_by.enabled %}" in template

assert fields["editor_preview"]["type"] == "goosialize-links-preview"
assert fields["editor_preview"]["label"] == "PLUGIN_GOOSIALIZE_LINKS.LIVE_PREVIEW"
assert fields["profile_section"]["fields"]["profile.image"]["destination"] == "user://media/goosialize-links/profile"

preview = Path("admin-next/fields/goosialize-links-preview.js").read_text()
controller = Path("classes/EditorPreviewController.php").read_text()
page_blueprint = yaml.safe_load(Path("blueprints/pages/goosialize-links.yaml").read_text())
plugin = Path("goosialize-links.php").read_text()

assert "sandbox=\"allow-same-origin allow-scripts allow-forms\"" in preview
assert "new URL(selected.preview_path, window.location.origin)" in preview
assert "goosialize-links-preview=1" in controller
assert "preview_path" in controller and "public_path" in controller
assert "LanguageCodes::getNativeName" in controller
assert "'translations' => $this->uiTranslations()" in controller
assert "http://" not in controller and "https://" not in controller
assert "header.goosialize_links.profile.name" in page_blueprint["form"]["fields"]
assert "header.goosialize_links.links" in page_blueprint["form"]["fields"]
assert "header.goosialize_links.actions" in page_blueprint["form"]["fields"]
assert "NativePageProvisioner" in plugin
assert "NativePageContentResolver" in plugin

locator = Path("classes/NativePageLocator.php").read_text()

# Grav #4245 R2-B: physical Links Page lookup must be bounded to the
# configured route rather than recursively crawling user/pages.
assert "RecursiveDirectoryIterator" not in locator
assert "RecursiveIteratorIterator" not in locator
assert "FilesystemIterator" not in locator
assert "GLOB_ONLYDIR" in locator
assert "childDirectory(" in locator
assert "string $configuredRoute" in locator

# Both production callers must provide the configured route.
assert "($normalizedConfig['route'] ?? '')" in plugin
assert "->route(\n            $route,\n            $default" in controller

# Grav #4245 R2-A: normal plugin boot must never provision content.
assert "$this->provisionNativePage();" not in plugin
assert "private function provisionNativePage" in plugin

# Physical Page discovery is allowed to fail locally without taking down
# the frontend.  Configuration normalization remains a separate fail-closed
# boundary and the already-normalized configured route is retained.
locator_start = plugin.index("$nativeRoute = (new NativePageLocator(")
locator_end = plugin.index(
    "if (\n            !($normalizedConfig['enabled'] ?? false)",
    locator_start,
)
locator_block = plugin[locator_start:locator_end]

assert "catch (Throwable $exception)" in locator_block
assert (
    "public Page resolution failed; using configured route:"
    in locator_block
)
assert "editor-preview/state" in plugin
assert "method: 'POST'" in preview
assert "method: 'DELETE'" in preview
assert "goosialize-links-preview-token" in preview
assert "SESSION_KEY" in controller
assert "PREVIEW_UPDATING" in controller and "PREVIEW_UNSAVED" in controller
assert "PREVIEW_IMAGE_AFTER_SAVE" in controller

for language in ("en", "el"):
    translations = yaml.safe_load(Path(f"languages/{language}.yaml").read_text())
    owned = translations["ICU"]["PLUGIN_GOOSIALIZE_LINKS"]
    assert owned["POWERED_BY"]
    assert owned["POWERED_BY_HELP"]
    for key in (
        "PREVIEW", "LIVE_PREVIEW", "PREVIEW_LANGUAGE", "REFRESH_PREVIEW",
        "OPEN_PUBLIC_PAGE", "PUBLIC_WEBSITE", "PUBLIC_ACTIONS", "PUBLIC_LINKS",
        "PREVIEW_UPDATING", "PREVIEW_UNSAVED",
        "PREVIEW_IMAGE_AFTER_SAVE",
        "PROFILE_SECTION_HELP", "APPEARANCE_SECTION_HELP",
        "ACTIONS_SECTION_HELP", "LINKS_SECTION_HELP",
    ):
        assert owned[key]

expected_group_help = {
    "en": {
        "PROFILE_SECTION_HELP": "Configure the shared profile image and primary website destination.",
        "APPEARANCE_SECTION_HELP": "Choose the visual style used across all language versions of your public profile.",
        "ACTIONS_SECTION_HELP": "Add social profiles and direct contact methods such as email, phone and messaging.",
        "LINKS_SECTION_HELP": "Add, reorder and manage the primary links displayed on your public profile.",
    },
    "el": {
        "PROFILE_SECTION_HELP": "Ρυθμίστε την κοινή εικόνα προφίλ και τον κύριο προορισμό ιστοσελίδας.",
        "APPEARANCE_SECTION_HELP": "Επιλέξτε την εμφάνιση που χρησιμοποιείται σε όλες τις γλωσσικές εκδόσεις του δημόσιου προφίλ.",
        "ACTIONS_SECTION_HELP": "Προσθέστε social profiles και άμεσους τρόπους επικοινωνίας όπως email, τηλέφωνο και messaging.",
        "LINKS_SECTION_HELP": "Προσθέστε, ταξινομήστε και διαχειριστείτε τους βασικούς συνδέσμους του δημόσιου προφίλ.",
    },
}
for language, expected in expected_group_help.items():
    translations = yaml.safe_load(Path(f"languages/{language}.yaml").read_text())
    owned = translations["ICU"]["PLUGIN_GOOSIALIZE_LINKS"]
    assert {key: owned[key] for key in expected} == expected

print("Native collapsible fieldsets = PASS")
print("Human-readable list keys = PASS")
print("Native Page content architecture = PASS")
print("Restricted real preview = PASS")
print("EN/EL UX1 translations = PASS")
PY

echo
echo "--- forbidden repository content ---"

for path in \
    node_modules \
    user \
    .recovery \
    dist \
    release
do
    if [ -e "$path" ]; then
        echo "ERROR: forbidden generated/runtime path: $path"
        exit 1
    fi
done

echo "Generated/runtime directories = ABSENT"

echo
echo "--- YAML / JSON metadata ---"

python3 - <<'PY'
from pathlib import Path
import json
import yaml

yaml_files = [
    "blueprints.yaml",
    "goosialize-links.yaml",
    "permissions.yaml",
    "languages/en.yaml",
    "languages/el.yaml",
    "admin/blueprints/goosialize-links.yaml",
]

for file in yaml_files:
    yaml.safe_load(
        Path(file).read_text()
    )
    print(f"{file} = YAML PASS")

json.loads(
    Path("composer.json").read_text()
)

json.loads(
    Path("composer.lock").read_text()
)

print("composer.json = JSON PASS")
print("composer.lock = JSON PASS")
PY

echo
echo "--- product metadata contract ---"

python3 - <<'PY'
from pathlib import Path
import json
import yaml

bp = yaml.safe_load(
    Path("blueprints.yaml").read_text()
)

if bp.get("name") != "Goosialize Links":
    raise SystemExit("Invalid plugin name")

if bp.get("slug") != "goosialize-links":
    raise SystemExit("Invalid plugin slug")

if bp.get("type") != "plugin":
    raise SystemExit("Invalid package type")

if bp.get("version") != "1.0.1":
    raise SystemExit("Unexpected release-candidate version")

if bp.get("license") != "MIT":
    raise SystemExit("Blueprint license must be MIT")

repository = "https://github.com/goosialize/goosialize-links"

if bp.get("homepage") != repository:
    raise SystemExit("Canonical blueprint homepage missing")

if bp.get("bugs") != repository + "/issues":
    raise SystemExit("Canonical blueprint issues URL missing")

if bp.get("docs") != repository + "#readme":
    raise SystemExit("Canonical blueprint docs URL missing")

compat = bp.get("compatibility", {})

if "2.0" not in compat.get("grav", []):
    raise SystemExit("Grav 2.0 compatibility missing")

if "api" in compat:
    raise SystemExit(
        "API compatibility must remain unclaimed before P9-C"
    )

composer = json.loads(
    Path("composer.json").read_text()
)

if composer.get("license") != "MIT":
    raise SystemExit("Composer license must be MIT")

if composer.get("homepage") != repository:
    raise SystemExit("Canonical Composer homepage missing")

support = composer.get("support", {})

if support.get("source") != repository:
    raise SystemExit("Canonical Composer source URL missing")

if support.get("issues") != repository + "/issues":
    raise SystemExit("Canonical Composer issues URL missing")

if support.get("docs") != repository + "#readme":
    raise SystemExit("Canonical Composer docs URL missing")

changelog = Path("CHANGELOG.md").read_text()

for marker in (
    "# 1.0.1",
    "## 08/12/2026",
    "[](#new)",
    "[](#improved)",
):
    if marker not in changelog:
        raise SystemExit(f"Missing Grav changelog marker: {marker}")

for path in (
    "docs/RELEASE_NOTES_1.0.1.md",
    "docs/PUBLIC_DISTRIBUTION_HANDOFF.md",
):
    if not Path(path).is_file():
        raise SystemExit(f"Missing distribution document: {path}")

license_text = Path("LICENSE").read_text()

if "Copyright (c) 2026 Goosialize Ltd" not in license_text:
    raise SystemExit("MIT license identity is invalid")

platform = (
    composer
    .get("config", {})
    .get("platform", {})
    .get("php")
)

if platform != "8.3.0":
    raise SystemExit(
        "Composer PHP platform must remain 8.3.0"
    )

requires = composer.get("require", {})
if requires.get("php") != ">=8.3":
    raise SystemExit("Composer PHP requirement must be >=8.3")

dependencies = {
    item.get("name"): item.get("version")
    for item in bp.get("dependencies", [])
}
expected_dependencies = {
    "grav": ">=2.0.12",
    "admin2": ">=2.0.15",
    "api": ">=1.0.12",
}
if dependencies != expected_dependencies:
    raise SystemExit("Stable dependency contract mismatch")

print("Plugin identity       = PASS")
print("Maintenance version   = 1.0.1")
print("Dependency contract   = PASS")
print("FREE core license     = MIT")
print("Public repository     = LOCKED")
print("Grav changelog format = PASS")
print("Grav compatibility    = 2.0")
print("API compatibility     = UNCLAIMED")
print("Composer PHP platform = 8.3.0")
PY

echo
echo "--- production dependency lock ---"

python3 - <<'PY'
from pathlib import Path
import json

lock = json.loads(
    Path("composer.lock").read_text()
)

actual = {
    package["name"]: package["version"]
    for package in lock.get("packages", [])
}

expected = {
    "endroid/qr-code": "6.0.9",
    "bacon/bacon-qr-code": "v3.1.1",
    "dasprid/enum": "1.0.7",
}

if actual != expected:
    print("Expected:", expected)
    print("Actual:", actual)
    raise SystemExit(
        "Unexpected production dependency graph"
    )

if lock.get("packages-dev"):
    raise SystemExit(
        "composer.lock contains development packages"
    )

print("Production dependency graph = PASS")
PY

echo
echo "--- committed production vendor safety ---"

python3 - <<'PY'
from pathlib import Path
import json
import re

vendor = Path("vendor")
installed = json.loads((vendor / "composer/installed.json").read_text())
packages = {
    package["name"]: package["version"]
    for package in installed.get("packages", [])
}
expected = {
    "endroid/qr-code": "6.0.9",
    "bacon/bacon-qr-code": "v3.1.1",
    "dasprid/enum": "1.0.7",
}

if packages != expected:
    raise SystemExit(f"Unexpected installed vendor graph: {packages}")

required = (
    vendor / "autoload.php",
    vendor / "endroid/qr-code/LICENSE",
    vendor / "bacon/bacon-qr-code/LICENSE",
    vendor / "dasprid/enum/LICENSE",
)
for path in required:
    if not path.is_file():
        raise SystemExit(f"Missing vendor file: {path}")

files = sorted(path for path in vendor.rglob("*") if path.is_file())
links = sorted(path for path in vendor.rglob("*") if path.is_symlink())
executables = sorted(path for path in files if path.stat().st_mode & 0o111)
forbidden_dirs = {
    "test", "tests", "example", "examples", "doc", "docs",
    ".git", ".github", "cache", "tmp",
}
forbidden = sorted(
    path for path in vendor.rglob("*")
    if path.is_dir() and path.name.lower() in forbidden_dirs
)

if links:
    raise SystemExit(f"Unexpected vendor symlinks: {links}")
if executables:
    raise SystemExit(f"Unexpected executable vendor files: {executables}")
if forbidden:
    raise SystemExit(f"Unexpected vendor development directories: {forbidden}")

signatures = [
    re.compile(b"BEGIN " + rb"(?:RSA |OPENSSH |EC )?" + b"PRIVATE KEY"),
    re.compile(b"SENDPULSE_" + rb"(?:CLIENT_SECRET|API_KEY)"),
    re.compile(b"SMTP_" + b"PASSWORD"),
    re.compile(rb"(?:^|[^A-Za-z])" + b"sk-" + rb"[A-Za-z0-9_-]{20,}"),
]
for path in files:
    payload = path.read_bytes()
    if any(pattern.search(payload) for pattern in signatures):
        raise SystemExit(f"Secret-sensitive material in vendor: {path}")

print(f"Committed vendor files = {len(files)}")
print(f"Committed vendor bytes = {sum(path.stat().st_size for path in files)}")
print("Installed vendor graph = PASS")
print("Vendor licenses        = PASS")
print("Vendor secret scan     = PASS")
print("Vendor filesystem      = PASS")
PY

echo
echo "--- FREE product boundary ---"

python3 - <<'PY'
from pathlib import Path

product = Path(
    "docs/FREE_PRODUCT_CONTRACT.md"
).read_text()

scope = Path(
    "docs/PROJECT_SCOPE.md"
).read_text()

for token in (
    "three predefined themes",
    "five predefined accent colors",
    "three predefined button shapes",
):
    if token not in product:
        raise SystemExit(
            f"Missing product contract: {token}"
        )

for stale in (
    "Optional supported icon",
    "Location URL",
    "- Background color",
    "- Basic spacing controls",
):
    if stale in product:
        raise SystemExit(
            f"Stale product requirement: {stale}"
        )

for event in (
    "`page_view`",
    "`link_click`",
    "`action_click`",
    "`qr_visit`",
):
    if event not in scope:
        raise SystemExit(
            f"Missing analytics event: {event}"
        )

print("FREE design boundary   = PASS")
print("FREE analytics boundary = PASS")
PY

echo
echo "--- FREE paid-addon boundary ---"

python3 - <<'PY'
from pathlib import Path
import re

production_roots = [
    Path("goosialize-links.php"),
    Path("classes"),
    Path("admin"),
    Path("admin-next"),
    Path("templates"),
    Path("assets"),
    Path("pages"),
]

files = []
for root in production_roots:
    if root.is_file():
        files.append(root)
    elif root.is_dir():
        files.extend(path for path in root.rglob("*") if path.is_file())

content = "\n".join(path.read_text(errors="replace") for path in files)

for label, pattern in {
    "scheduled analytics email reporting": r"scheduled[_ -]?(?:analytics|email|report)|(?:daily|weekly|monthly)[_ -]?(?:email|report)",
    "provider-specific CRM/reporting": r"sendpulse|mailchimp|brevo|hubspot|salesforce|activecampaign|smtp_password",
}.items():
    if re.search(pattern, content, re.IGNORECASE):
        raise SystemExit(f"Forbidden FREE core capability: {label}")

print("Scheduled reports absent = PASS")
print("Provider CRM code absent = PASS")
PY

echo
echo "--- QR / dashboard API split ---"

grep -F \
  "/goosialize-links/dashboard" \
  goosialize-links.php \
  >/dev/null

grep -F \
  "function dashboardData" \
  classes/QrAdminController.php \
  >/dev/null

grep -F \
  "/goosialize-links/dashboard" \
  admin-next/pages/goosialize-links.js \
  >/dev/null

grep -F \
  "api.goosialize-links.qr.read" \
  permissions.yaml \
  >/dev/null

echo "QR / dashboard API split = PASS"

echo
echo "--- exact route and permission contracts ---"

python3 - <<'PY'
from pathlib import Path
import yaml

plugin = Path("goosialize-links.php").read_text()
controller = Path("classes/QrAdminController.php").read_text()
javascript = Path("admin-next/pages/goosialize-links.js").read_text()
permissions = yaml.safe_load(Path("permissions.yaml").read_text())

for route in (
    "/goosialize-links/qr",
    "/goosialize-links/dashboard",
    "/goosialize-links/qr/download/png",
    "/goosialize-links/qr/download/svg",
):
    if route not in plugin:
        raise SystemExit(f"Missing API route: {route}")

if "/goosialize-links/dashboard" not in javascript:
    raise SystemExit("Missing Admin2 dashboard route contract")

for payload_key in ("preview_url", "tracked_url"):
    if payload_key not in controller or payload_key not in javascript:
        raise SystemExit(f"Missing Admin2 payload contract: {payload_key}")

for payload_key in ("png_download_url", "svg_download_url"):
    if payload_key not in controller:
        raise SystemExit(f"Missing API payload contract: {payload_key}")

permission = "api.goosialize-links.qr.read"
if permission not in controller or permission not in plugin:
    raise SystemExit("Controller/plugin permission mismatch")

node = permissions
for key in ("api", "children", "goosialize-links", "children", "qr", "children", "read"):
    if not isinstance(node, dict) or key not in node:
        raise SystemExit("Permission tree is incomplete")
    node = node[key]

if "write" in str(permissions).lower():
    raise SystemExit("Unexpected QR write permission")

print("API route contract       = PASS")
print("Admin2 route contract    = PASS")
print("QR read permission       = PASS")
print("QR write permission      = ABSENT")
PY

echo
echo "--- JavaScript syntax ---"

if ! command -v node >/dev/null 2>&1; then
    echo "ERROR: node is required for JS release gate"
    exit 1
fi

node --check \
  admin-next/pages/goosialize-links.js

echo "Admin2 JavaScript = PASS"

echo
echo "--- high-signal secrets scan ---"

python3 - <<'PYSCAN'
from pathlib import Path
import re

root = Path(".")

excluded_dirs = {
    ".git",
    "vendor",
    "node_modules",
    ".recovery",
}

allowed_suffixes = {
    ".php",
    ".js",
    ".yaml",
    ".yml",
    ".json",
    ".md",
    ".twig",
    ".sh",
    ".txt",
    ".env",
}

# Assemble signatures from fragments so this scanner does not
# trigger itself merely by containing its detection definitions.
private_key = (
    "BEGIN "
    + r"(?:RSA |OPENSSH |EC )?"
    + "PRIVATE KEY"
)

sendpulse = (
    "SENDPULSE_"
    + r"(?:CLIENT_SECRET|API_KEY)"
)

smtp = (
    "SMTP_"
    + "PASSWORD"
)

openai_like = (
    r"(?:^|[^A-Za-z])"
    + "sk-"
    + r"[A-Za-z0-9_-]{20,}"
)

patterns = [
    re.compile(private_key),
    re.compile(sendpulse),
    re.compile(smtp),
    re.compile(openai_like),
]

violations = []

for path in root.rglob("*"):
    if not path.is_file():
        continue

    if any(
        part in excluded_dirs
        for part in path.parts
    ):
        continue

    if (
        path.suffix.lower()
        not in allowed_suffixes
        and path.name != ".env"
    ):
        continue

    try:
        content = path.read_text(
            errors="replace"
        )
    except OSError:
        continue

    for pattern in patterns:
        match = pattern.search(content)

        if match:
            line = (
                content.count(
                    "\n",
                    0,
                    match.start(),
                )
                + 1
            )

            violations.append(
                (
                    str(path),
                    line,
                    pattern.pattern,
                )
            )

if violations:
    print(
        "Possible secret material detected:"
    )

    for file, line, signature in violations:
        print(
            f"  {file}:{line} [{signature}]"
        )

    raise SystemExit(1)

print("Secret scan = PASS")
PYSCAN

echo
echo "--- privacy boundary scan ---"

if grep -RniE \
    'REMOTE_ADDR|HTTP_USER_AGENT|utm_(source|medium|campaign)|fingerprint|geoip|country_code' \
    . \
    --include='*.php' \
    --include='*.js' \
    --exclude-dir=.git \
    --exclude-dir=vendor
then
    echo "ERROR: forbidden advanced analytics signal detected"
    exit 1
fi

echo "Privacy boundary scan = PASS"

echo
echo "--- source diff hygiene ---"

git diff --check

echo "git diff --check = PASS"

echo
echo "STATIC RELEASE CONTRACT = PASS"
