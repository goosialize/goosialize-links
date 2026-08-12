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
)

for file in "${required[@]}"; do
    test -f "$file"
    echo "$file = PASS"
done

echo
echo "--- forbidden repository content ---"

for path in \
    vendor \
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

if bp.get("version") != "1.0.0-rc.1":
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
    "# 1.0.0-rc.1",
    "## 08/12/2026",
    "[](#new)",
    "[](#improved)",
):
    if marker not in changelog:
        raise SystemExit(f"Missing Grav changelog marker: {marker}")

for path in (
    "docs/RELEASE_NOTES_1.0.0-rc.1.md",
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

print("Plugin identity       = PASS")
print("RC version            = 1.0.0-rc.1")
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
