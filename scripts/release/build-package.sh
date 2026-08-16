#!/usr/bin/env bash

set -Eeuo pipefail

export LC_ALL=C
export NO_COLOR=1

ROOT="$(
    cd "$(dirname "${BASH_SOURCE[0]}")/../.."
    pwd
)"

IMAGE="${GRAV_IMAGE:-lscr.io/linuxserver/grav:version-2.0.12}"

OUTPUT_DIR="${1:-/tmp/goosialize-links-release}"

cd "$ROOT"

VERSION="$(
    python3 - <<'PY'
from pathlib import Path
import yaml

data = yaml.safe_load(
    Path("blueprints.yaml").read_text()
)

print(data["version"])
PY
)"

SLUG="goosialize-links"

BUILD_ROOT="$(
    mktemp -d \
      /tmp/goosialize-links-package.XXXXXX
)"

STAGE="$BUILD_ROOT/$SLUG"

cleanup() {
    rm -rf "$BUILD_ROOT"
}

trap cleanup EXIT

mkdir -p \
  "$STAGE" \
  "$OUTPUT_DIR"

echo "=============================================="
echo " BUILD RELEASE PACKAGE"
echo "=============================================="

echo "VERSION = $VERSION"
echo "IMAGE   = $IMAGE"

echo
echo "--- copy approved package source ---"

directories=(
    admin
    admin-next
    assets
    blueprints
    classes
    languages
    pages
    templates
    vendor
)

files=(
    CHANGELOG.md
    LICENSE
    README.md
    blueprints.yaml
    composer.json
    composer.lock
    goosialize-links.php
    goosialize-links.yaml
    permissions.yaml
)

for directory in "${directories[@]}"; do
    test -d "$directory"

    cp -a \
      "$directory" \
      "$STAGE/"
done

for file in "${files[@]}"; do
    test -f "$file"

    cp -a \
      "$file" \
      "$STAGE/"
done

echo "Approved source copy = PASS"

test -f \
  "$STAGE/vendor/autoload.php"

test -d \
  "$STAGE/vendor/endroid/qr-code"

test -d \
  "$STAGE/vendor/bacon/bacon-qr-code"

test -d \
  "$STAGE/vendor/dasprid/enum"

echo "Committed production vendor graph = PASS"

echo
echo "--- create deterministic package inventory ---"

PACKAGE="$OUTPUT_DIR/${SLUG}-${VERSION}.zip"
PACKAGE_CHECK="$BUILD_ROOT/${SLUG}-${VERSION}.check.zip"

rm -f "$PACKAGE" "$PACKAGE_CHECK"

python3 - \
  "$BUILD_ROOT" \
  "$PACKAGE" \
  "$PACKAGE_CHECK" <<'PY'
from pathlib import Path
import sys
import zipfile

root = Path(sys.argv[1])
packages = [Path(value) for value in sys.argv[2:]]

files = sorted(
    path
    for path in root.rglob("*")
    if path.is_file()
)

for package in packages:
    with zipfile.ZipFile(
        package,
        "w",
        compression=zipfile.ZIP_DEFLATED,
        compresslevel=9,
    ) as archive:
        for path in files:
            name = path.relative_to(root).as_posix()
            info = zipfile.ZipInfo(
                name,
                date_time=(1980, 1, 1, 0, 0, 0),
            )
            info.compress_type = zipfile.ZIP_DEFLATED
            info.create_system = 3
            info.external_attr = (
                (0o755 if path.stat().st_mode & 0o111 else 0o644)
                << 16
            )
            archive.writestr(info, path.read_bytes())

print(packages[0])
PY

test -s "$PACKAGE"
test -s "$PACKAGE_CHECK"
test "$(sha256sum "$PACKAGE" | awk '{print $1}')" = \
  "$(sha256sum "$PACKAGE_CHECK" | awk '{print $1}')"

echo "Deterministic archive = PASS"

echo
echo "PACKAGE=$PACKAGE"
echo "SHA256=$(
    sha256sum "$PACKAGE" |
    awk '{print $1}'
)"

echo
echo "BUILD RELEASE PACKAGE = PASS"
