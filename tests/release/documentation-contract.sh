#!/usr/bin/env bash

set -u

ROOT="$(
    cd "$(dirname "${BASH_SOURCE[0]}")/../.." &&
    pwd
)"

cd "$ROOT" || exit 1

FAIL=0

echo "=============================================="
echo " DOCUMENTATION RELEASE CONTRACT"
echo "=============================================="

echo
echo "--- target documentation coverage ---"

python3 - <<'PY'
from pathlib import Path

required = [
    "DOCUMENTATION_INDEX.md",
    "QUICK_START.md",
    "INSTALLATION.md",
    "CONFIGURATION.md",
    "ADMIN_GUIDE.md",
    "PUBLIC_PAGE.md",
    "LIVE_PREVIEW.md",
    "ACTIONS.md",
    "LINKS.md",
    "APPEARANCE.md",
    "MULTILINGUAL.md",
    "QR_CODE.md",
    "ANALYTICS.md",
    "PRIVACY.md",
    "PERMISSIONS.md",
    "SECURITY.md",
    "DATA_STORAGE.md",
    "COMPATIBILITY.md",
    "UPGRADE.md",
    "UNINSTALL_DATA_RETENTION.md",
    "TROUBLESHOOTING.md",
    "FAQ.md",
    "EXAMPLES.md",
    "DEVELOPER_REFERENCE.md",
    "MANUAL_BROWSER_ACCEPTANCE_CHECKLIST.md",
    "RELEASE_VERIFICATION.md",
    "TERMINOLOGY.md",
]

missing = []
empty = []

for name in required:
    path = Path("docs") / name

    if not path.is_file():
        missing.append(name)
    elif path.stat().st_size == 0:
        empty.append(name)

print(f"Target docs = {len(required)}")
print(f"Present docs = {len(required) - len(missing)}")

if missing:
    for name in missing:
        print(f"Missing doc: {name}")
    raise SystemExit(1)

if empty:
    for name in empty:
        print(f"Empty doc: {name}")
    raise SystemExit(1)

print("Documentation target coverage = PASS")
PY

if [ "$?" != "0" ]; then
    FAIL=1
fi

echo
echo "--- README navigation ---"

python3 - <<'PY'
from pathlib import Path

readme = Path("README.md").read_text()

required = [
    "DOCUMENTATION_INDEX.md",
    "QUICK_START.md",
    "INSTALLATION.md",
    "CONFIGURATION.md",
    "ADMIN_GUIDE.md",
    "PUBLIC_PAGE.md",
    "LIVE_PREVIEW.md",
    "ACTIONS.md",
    "LINKS.md",
    "APPEARANCE.md",
    "MULTILINGUAL.md",
    "QR_CODE.md",
    "ANALYTICS.md",
    "PRIVACY.md",
    "PERMISSIONS.md",
    "SECURITY.md",
    "DATA_STORAGE.md",
    "COMPATIBILITY.md",
    "UPGRADE.md",
    "UNINSTALL_DATA_RETENTION.md",
    "TROUBLESHOOTING.md",
    "FAQ.md",
    "EXAMPLES.md",
    "DEVELOPER_REFERENCE.md",
    "MANUAL_BROWSER_ACCEPTANCE_CHECKLIST.md",
    "RELEASE_VERIFICATION.md",
    "TERMINOLOGY.md",
]

missing = [
    name
    for name in required
    if f"docs/{name}" not in readme
]

if missing:
    for name in missing:
        print(f"README missing: {name}")
    raise SystemExit(1)

print("README navigation = PASS")
PY

if [ "$?" != "0" ]; then
    FAIL=1
fi

echo
echo "--- documentation index navigation ---"

python3 - <<'PY'
from pathlib import Path

text = Path(
    "docs/DOCUMENTATION_INDEX.md"
).read_text()

required = [
    "QUICK_START.md",
    "INSTALLATION.md",
    "CONFIGURATION.md",
    "ADMIN_GUIDE.md",
    "PUBLIC_PAGE.md",
    "LIVE_PREVIEW.md",
    "ACTIONS.md",
    "LINKS.md",
    "APPEARANCE.md",
    "MULTILINGUAL.md",
    "QR_CODE.md",
    "ANALYTICS.md",
    "PRIVACY.md",
    "PERMISSIONS.md",
    "SECURITY.md",
    "DATA_STORAGE.md",
    "COMPATIBILITY.md",
    "UPGRADE.md",
    "UNINSTALL_DATA_RETENTION.md",
    "TROUBLESHOOTING.md",
    "FAQ.md",
    "EXAMPLES.md",
    "DEVELOPER_REFERENCE.md",
    "MANUAL_BROWSER_ACCEPTANCE_CHECKLIST.md",
    "RELEASE_VERIFICATION.md",
    "TERMINOLOGY.md",
]

missing = [
    name
    for name in required
    if f"]({name})" not in text
]

if missing:
    for name in missing:
        print(f"Index missing: {name}")
    raise SystemExit(1)

print("Documentation index navigation = PASS")
PY

if [ "$?" != "0" ]; then
    FAIL=1
fi

echo
echo "--- internal links ---"

python3 - <<'PY'
from pathlib import Path
import re
import urllib.parse

root = Path(".").resolve()

files = [Path("README.md")]
files += sorted(
    Path("docs").glob("*.md")
)

pattern = re.compile(
    r'\[[^\]]+\]\(([^)]+)\)'
)

errors = []
checked = 0

for source in files:
    text = source.read_text()

    for raw in pattern.findall(text):
        target = raw.strip()

        if not target:
            continue

        if target.startswith((
            "http://",
            "https://",
            "mailto:",
            "#",
        )):
            continue

        target = target.split("#", 1)[0]

        if not target:
            continue

        target = urllib.parse.unquote(
            target
        )

        destination = (
            source.parent / target
        ).resolve()

        checked += 1

        try:
            destination.relative_to(root)
        except ValueError:
            errors.append(
                f"{source}: escapes repo: {raw}"
            )
            continue

        if not destination.exists():
            errors.append(
                f"{source}: missing: {raw}"
            )

print(
    f"Internal links checked = {checked}"
)

if errors:
    for error in errors:
        print(error)
    raise SystemExit(1)

print("Internal links = PASS")
PY

if [ "$?" != "0" ]; then
    FAIL=1
fi

echo
echo "--- markdown structure ---"

python3 - <<'PY'
from pathlib import Path

files = [Path("README.md")]
files += sorted(
    Path("docs").glob("*.md")
)

errors = []

for path in files:
    lines = path.read_text().splitlines()

    h1 = [
        line
        for line in lines
        if line.startswith("# ")
    ]

    if len(h1) != 1:
        errors.append(
            f"{path}: H1 count={len(h1)}"
        )

    backticks = sum(
        line.startswith("```")
        for line in lines
    )

    tildes = sum(
        line.startswith("~~~")
        for line in lines
    )

    if backticks % 2:
        errors.append(
            f"{path}: unbalanced ```"
        )

    if tildes % 2:
        errors.append(
            f"{path}: unbalanced ~~~"
        )

if errors:
    for error in errors:
        print(error)
    raise SystemExit(1)

print("Single H1 = PASS")
print("Markdown fences = PASS")
PY

if [ "$?" != "0" ]; then
    FAIL=1
fi

echo
echo "--- trailing whitespace ---"

if grep -RniE \
  --include='*.md' \
  '[[:blank:]]+$' \
  README.md docs
then
    echo "Documentation whitespace = FAIL"
    FAIL=1
else
    echo "Documentation whitespace = PASS"
fi

echo
echo "--- release-status truth ---"

if grep -RniE \
  --include='*.md' \
  'current release[^0-9]*1\.0\.2|maintenance release[^0-9]*1\.0\.2|released version[^0-9]*1\.0\.2|1\.0\.2 is released|1\.0\.2 has been released' \
  README.md docs
then
    echo "False 1.0.2 release claim = FAIL"
    FAIL=1
else
    echo "False 1.0.2 release claim = ABSENT"
fi

grep -Fq \
  'Maintenance release: `1.0.1`.' \
  README.md \
  && echo "Public release status = PASS" \
  || {
      echo "Public release status = FAIL"
      FAIL=1
  }

grep -Fq \
  'targeting Goosialize Links 1.0.2' \
  docs/DOCUMENTATION_INDEX.md \
  && echo "Maintenance target status = PASS" \
  || {
      echo "Maintenance target status = FAIL"
      FAIL=1
  }

echo
echo "--- compatibility truth ---"

for token in \
  '2.0.12' \
  '2.0.15' \
  '1.0.12' \
  '8.3'
do
    grep -Fq \
      "$token" \
      docs/COMPATIBILITY.md \
      || FAIL=1
done

if [ "$FAIL" = "0" ]; then
    echo "Compatibility documentation = PASS"
fi

echo
echo "--- configuration truth ---"

for token in \
  'route: /bio' \
  'theme: light' \
  'accent: yellow' \
  'button_shape: rounded' \
  'powered_by: true'
do
    grep -Fq \
      "$token" \
      docs/CONFIGURATION.md \
      || FAIL=1
done

echo "Configuration documentation checked"

echo
echo "--- maintainer remediation truth ---"

grep -Fq \
  'does not automatically create or rewrite Page files' \
  docs/PUBLIC_PAGE.md \
  && echo "R2-A docs = PASS" \
  || {
      echo "R2-A docs = FAIL"
      FAIL=1
  }

grep -Fq \
  'bounded to the configured path' \
  docs/PUBLIC_PAGE.md \
  && echo "R2-B docs = PASS" \
  || {
      echo "R2-B docs = FAIL"
      FAIL=1
  }

grep -Fq \
  'powered_by: false' \
  docs/APPEARANCE.md \
  && echo "R2-C docs = PASS" \
  || {
      echo "R2-C docs = FAIL"
      FAIL=1
  }

grep -Fq \
  'window.fetch' \
  docs/LIVE_PREVIEW.md \
  && grep -Fq \
      'document.body' \
      docs/LIVE_PREVIEW.md \
  && echo "R2-D docs = PASS" \
  || {
      echo "R2-D docs = FAIL"
      FAIL=1
  }

grep -Fq \
  'YYYY-MM-DD.events' \
  docs/ANALYTICS.md \
  && grep -Fq \
      'call `fsync()` for every public hit' \
      docs/ANALYTICS.md \
  && echo "R2-E docs = PASS" \
  || {
      echo "R2-E docs = FAIL"
      FAIL=1
  }

echo
echo "--- privacy truth ---"

for token in \
  'No visitor profiles' \
  'No IP analytics' \
  'No user-agent analytics' \
  'No fingerprinting' \
  'No GeoIP or country analytics' \
  'No UTM analytics' \
  'No unique-user analytics' \
  'No external analytics service'
do
    grep -Fq \
      "$token" \
      docs/PRIVACY.md \
      || FAIL=1
done

echo "Privacy documentation checked"

echo
echo "--- permission truth ---"

grep -Fq \
  'api.goosialize-links.qr.read' \
  docs/PERMISSIONS.md \
  && echo "Dedicated permission = PASS" \
  || {
      echo "Dedicated permission = FAIL"
      FAIL=1
  }

grep -Fq \
  'api.config.read' \
  docs/PERMISSIONS.md \
  && echo "Platform permission = PASS" \
  || {
      echo "Platform permission = FAIL"
      FAIL=1
  }

grep -Fq \
  'no Goosialize Links QR write permission' \
  docs/PERMISSIONS.md \
  && echo "QR write permission absent = PASS" \
  || {
      echo "QR write permission absent = FAIL"
      FAIL=1
  }

echo
echo "--- release evidence truth ---"

grep -Fq \
  '8f9bbdf999007f9bd461c461094c755e6438c70e' \
  docs/RELEASE_VERIFICATION.md \
  && echo "R2-E checkpoint evidence = PASS" \
  || {
      echo "R2-E checkpoint evidence = FAIL"
      FAIL=1
  }

grep -Fq \
  '9aa8d72cce41fa4536fcd05101198d1131e3417db3b5608d1b5d638309ab9cb3' \
  docs/RELEASE_VERIFICATION.md \
  && echo "R2-E package evidence = PASS" \
  || {
      echo "R2-E package evidence = FAIL"
      FAIL=1
  }

grep -Fq \
  '500:120:80:60' \
  docs/RELEASE_VERIFICATION.md \
  && echo "R2-E HTTP evidence = PASS" \
  || {
      echo "R2-E HTTP evidence = FAIL"
      FAIL=1
  }

echo
echo "--- diff hygiene ---"

git diff --check \
  && echo "Documentation diff hygiene = PASS" \
  || {
      echo "Documentation diff hygiene = FAIL"
      FAIL=1
  }

echo
echo "--- final ---"

if [ "$FAIL" = "0" ]; then
    echo "GOOSIALIZE_LINKS_DOCUMENTATION_COVERAGE=100/100"
    echo "USER_DOCUMENTATION=PASS"
    echo "ADMIN_GUIDE=PASS"
    echo "CONFIG_REFERENCE=PASS"
    echo "PRIVACY_SECURITY=PASS"
    echo "QR_ANALYTICS=PASS"
    echo "TROUBLESHOOTING_FAQ=PASS"
    echo "README_NAVIGATION=PASS"
    echo "INTERNAL_LINKS=PASS"
    echo "SOURCE_TRUTH_PARITY=PASS"
    echo "DOCUMENTATION_GATE=PASS"
    exit 0
fi

echo "DOCUMENTATION_GATE=FAIL"
exit 1
