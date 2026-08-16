#!/usr/bin/env bash

set -Eeuo pipefail

export LC_ALL=C
export NO_COLOR=1

if [ "$#" -ne 1 ]; then
    echo "Usage: $0 /path/to/goosialize-links-x.y.z.zip"
    exit 2
fi

PACKAGE="$1"

test -f "$PACKAGE"

python3 - \
  "$PACKAGE" <<'PY'
import sys
import zipfile
import json
import re
from pathlib import PurePosixPath

package = sys.argv[1]

with zipfile.ZipFile(package) as archive:
    names = [
        name
        for name in archive.namelist()
        if not name.endswith("/")
    ]
    payloads = {
        name: archive.read(name)
        for name in names
    }

if not names:
    raise SystemExit(
        "Package is empty"
    )

roots = {
    PurePosixPath(name).parts[0]
    for name in names
}

if roots != {"goosialize-links"}:
    raise SystemExit(
        f"Invalid package root(s): {sorted(roots)}"
    )

required = {
    "goosialize-links/blueprints.yaml",
    "goosialize-links/blueprints/pages/goosialize-links.yaml",
    "goosialize-links/classes/EditorPreviewState.php",
    "goosialize-links/goosialize-links.php",
    "goosialize-links/goosialize-links.yaml",
    "goosialize-links/composer.json",
    "goosialize-links/composer.lock",
    "goosialize-links/permissions.yaml",
    "goosialize-links/LICENSE",
    "goosialize-links/README.md",
    "goosialize-links/CHANGELOG.md",
    "goosialize-links/languages/en.yaml",
    "goosialize-links/languages/el.yaml",
    "goosialize-links/vendor/autoload.php",
}

missing = sorted(
    required - set(names)
)

if missing:
    print("Missing package files:")
    for name in missing:
        print(" ", name)

    raise SystemExit(1)

blueprint = payloads["goosialize-links/blueprints.yaml"].decode()
composer_json = json.loads(payloads["goosialize-links/composer.json"])
license_text = payloads["goosialize-links/LICENSE"].decode()

if "version: 1.0.1" not in blueprint:
    raise SystemExit("Unexpected packaged release-candidate version")

if composer_json.get("license") != "MIT":
    raise SystemExit("Packaged Composer license must be MIT")

repository = "https://github.com/goosialize/goosialize-links"

if composer_json.get("homepage") != repository:
    raise SystemExit("Packaged canonical homepage is invalid")

if repository not in payloads["goosialize-links/README.md"].decode():
    raise SystemExit("Packaged README lacks canonical source URL")

if "Copyright (c) 2026 Goosialize Ltd" not in license_text:
    raise SystemExit("Packaged MIT license identity is invalid")

forbidden_parts = {
    ".git",
    ".github",
    ".recovery",
    "tests",
    "scripts",
    "docs",
    "node_modules",
    "user",
}

forbidden_names = {
    "AGENTS.md",
    ".env",
    "auth.json",
}

violations = []

for name in names:
    parts = set(
        PurePosixPath(name).parts
    )

    if parts & forbidden_parts:
        violations.append(name)

    if PurePosixPath(name).name in forbidden_names:
        violations.append(name)

if violations:
    print("Forbidden package entries:")

    for name in violations:
        print(" ", name)

    raise SystemExit(1)

vendor_markers = [
    "goosialize-links/vendor/endroid/qr-code/",
    "goosialize-links/vendor/bacon/bacon-qr-code/",
    "goosialize-links/vendor/dasprid/enum/",
]

for marker in vendor_markers:
    if not any(
        name.startswith(marker)
        for name in names
    ):
        raise SystemExit(
            f"Missing vendor dependency: {marker}"
        )

composer = json.loads(
    payloads[
        "goosialize-links/composer.lock"
    ]
)

actual = {
    item["name"]: item["version"]
    for item in composer.get("packages", [])
}

expected = {
    "endroid/qr-code": "6.0.9",
    "bacon/bacon-qr-code": "v3.1.1",
    "dasprid/enum": "1.0.7",
}

if actual != expected:
    raise SystemExit(
        f"Unexpected packaged dependency graph: {actual}"
    )

class_markers = [
    "goosialize-links/vendor/endroid/qr-code/src/QrCode.php",
    "goosialize-links/vendor/bacon/bacon-qr-code/src/Renderer/ImageRenderer.php",
    "goosialize-links/vendor/dasprid/enum/src/AbstractEnum.php",
]

for marker in class_markers:
    if marker not in names:
        raise SystemExit(
            f"Missing packaged QR class: {marker}"
        )

signatures = [
    re.compile(b"BEGIN " + rb"(?:RSA |OPENSSH |EC )?" + b"PRIVATE KEY"),
    re.compile(b"SENDPULSE_" + rb"(?:CLIENT_SECRET|API_KEY)"),
    re.compile(b"SMTP_" + b"PASSWORD"),
    re.compile(rb"(?:^|[^A-Za-z])" + b"sk-" + rb"[A-Za-z0-9_-]{20,}"),
]

for name, payload in payloads.items():
    if any(pattern.search(payload) for pattern in signatures):
        raise SystemExit(
            f"Secret-sensitive material in package: {name}"
        )

print("Single plugin root       = PASS")
print("Required package files   = PASS")
print("Version / license        = PASS")
print("Production vendor        = PASS")
print("Packaged QR classes      = PASS")
print("Package secret scan      = PASS")
print("Development files absent = PASS")
PY

python3 - \
  "$PACKAGE" \
  "$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)" <<'PY'
from pathlib import Path
import hashlib
import sys
import zipfile

package = Path(sys.argv[1])
root = Path(sys.argv[2])
source = root / "vendor"

source_files = {
    path.relative_to(source).as_posix(): hashlib.sha256(path.read_bytes()).hexdigest()
    for path in source.rglob("*")
    if path.is_file()
}

with zipfile.ZipFile(package) as archive:
    packaged_files = {
        name.removeprefix("goosialize-links/vendor/"): hashlib.sha256(archive.read(name)).hexdigest()
        for name in archive.namelist()
        if name.startswith("goosialize-links/vendor/") and not name.endswith("/")
    }

if source_files != packaged_files:
    missing = sorted(source_files.keys() - packaged_files.keys())
    extra = sorted(packaged_files.keys() - source_files.keys())
    changed = sorted(
        name for name in source_files.keys() & packaged_files.keys()
        if source_files[name] != packaged_files[name]
    )
    raise SystemExit(
        f"Source/package vendor mismatch; missing={missing}, extra={extra}, changed={changed}"
    )

print("Source/package vendor parity = PASS")
PY

echo "PACKAGE CONTENT CONTRACT = PASS"
