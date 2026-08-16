#!/usr/bin/env bash

set -Eeuo pipefail

export LC_ALL=C
export NO_COLOR=1

ROOT="$(
    cd "$(dirname "${BASH_SOURCE[0]}")/../.."
    pwd
)"

BUILD_DIR="$(mktemp -d /tmp/goosialize-links-gpm-source.XXXXXX)"
ARCHIVE="$BUILD_DIR/goosialize-links-1.0.1-source.zip"
BEFORE="$BUILD_DIR/status.before"
AFTER="$BUILD_DIR/status.after"

cleanup() {
    rm -rf "$BUILD_DIR"
}

trap cleanup EXIT

cd "$ROOT"

git status --porcelain=v1 --untracked-files=all > "$BEFORE"

test -f vendor/autoload.php
test -f vendor/endroid/qr-code/src/QrCode.php
test -f vendor/bacon/bacon-qr-code/src/Renderer/ImageRenderer.php
test -f vendor/dasprid/enum/src/AbstractEnum.php

python3 - "$ROOT" "$ARCHIVE" <<'PY'
from pathlib import Path
import subprocess
import sys
import zipfile

root = Path(sys.argv[1])
archive_path = Path(sys.argv[2])

result = subprocess.run(
    ["git", "ls-files", "--cached", "--others", "--exclude-standard", "-z"],
    cwd=root,
    check=True,
    stdout=subprocess.PIPE,
)

names = sorted(
    item.decode()
    for item in result.stdout.split(b"\0")
    if item
)

if not names:
    raise SystemExit("No candidate release source files")

if not any(name == "vendor/autoload.php" for name in names):
    raise SystemExit("Committed-vendor candidate is absent from source inventory")

with zipfile.ZipFile(
    archive_path,
    "w",
    compression=zipfile.ZIP_DEFLATED,
    compresslevel=9,
) as archive:
    for name in names:
        path = root / name
        if not path.is_file():
            continue
        info = zipfile.ZipInfo(
            f"goosialize-links/{name}",
            date_time=(1980, 1, 1, 0, 0, 0),
        )
        info.compress_type = zipfile.ZIP_DEFLATED
        info.create_system = 3
        info.external_attr = (
            (0o755 if path.stat().st_mode & 0o111 else 0o644) << 16
        )
        archive.writestr(info, path.read_bytes())

print(f"GPM source files = {len(names)}")
PY

GPM_SOURCE_ARCHIVE="$ARCHIVE" \
GOOSIALIZE_SKIP_GPM_SOURCE=1 \
tests/release/run-all.sh

git status --porcelain=v1 --untracked-files=all > "$AFTER"
cmp "$BEFORE" "$AFTER"

echo "GPM-STYLE TAG SOURCE INSTALL = PASS"
