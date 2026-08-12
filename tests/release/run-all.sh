#!/usr/bin/env bash

set -Eeuo pipefail

export LC_ALL=C
export NO_COLOR=1

ROOT="$(
    cd "$(dirname "${BASH_SOURCE[0]}")/../.."
    pwd
)"

IMAGE="${GRAV_IMAGE:-lscr.io/linuxserver/grav:version-2.0.12}"

for command in docker curl python3 node; do
    if ! command -v "$command" >/dev/null 2>&1; then
        echo "P9_GATE=BLOCKED REASON=missing_$command"
        exit 3
    fi
done

if ! docker info >/dev/null 2>&1; then
    echo "P9_GATE=BLOCKED REASON=docker_unavailable"
    echo "RUN_EXTERNALLY=tests/release/run-all.sh"
    exit 3
fi

echo "DOCKER_RUNTIME = PASS"

BUILD_DIR="$(
    mktemp -d \
      /tmp/goosialize-links-release-gates.XXXXXX
)"

CONTAINER="goosialize-links-p9b-${$}"

PLUGIN_EXTRACT="$BUILD_DIR/extracted"

cleanup() {
    docker rm \
      -f \
      "$CONTAINER" \
      >/dev/null \
      2>&1 \
      || true

    rm -rf "$BUILD_DIR"
}

trap cleanup EXIT

cd "$ROOT"

echo "=================================================="
echo " GOOSIALIZE LINKS — AUTOMATED RELEASE GATES"
echo "=================================================="

echo
echo "===== A. STATIC CONTRACT ====="

tests/release/static-contract.sh

echo
echo "===== B. PHP SOURCE SYNTAX ON GRAV 2.0.12 ====="

while IFS= read -r file; do
    docker run \
      --rm \
      --entrypoint php \
      -v "$ROOT:/plugin:ro" \
      "$IMAGE" \
      -l "/plugin/${file#./}" \
      >/dev/null

    echo "$file = PHP PASS"
done < <(
    find . \
      -type f \
      -name '*.php' \
      -not -path './.git/*' \
      -not -path './vendor/*' \
      -not -path './tests/*' \
      | sort
)

echo
echo "===== C. BUILD DISTRIBUTABLE PACKAGE ====="

scripts/release/build-package.sh \
  "$BUILD_DIR"

PACKAGE="$(
    find "$BUILD_DIR" \
      -maxdepth 1 \
      -type f \
      -name 'goosialize-links-*.zip' \
      | head -1
)"

test -n "$PACKAGE"
test -s "$PACKAGE"

echo "PACKAGE=$PACKAGE"

echo "DETERMINISTIC_PACKAGE = PASS"

echo
echo "===== D. PACKAGE CONTENT CONTRACT ====="

tests/release/package-contract.sh \
  "$PACKAGE"

echo
echo "===== E. EXTRACT PACKAGE ====="

mkdir -p \
  "$PLUGIN_EXTRACT"

python3 - \
  "$PACKAGE" \
  "$PLUGIN_EXTRACT" <<'PY'
from pathlib import Path
import sys
import zipfile

package = Path(sys.argv[1])
destination = Path(sys.argv[2])

with zipfile.ZipFile(package) as archive:
    archive.extractall(destination)

print("Package extraction = PASS")
PY

PLUGIN="$PLUGIN_EXTRACT/goosialize-links"

test -d "$PLUGIN"
test -f "$PLUGIN/vendor/autoload.php"

echo
echo "===== F. PURE CORE RUNTIME CONTRACT ====="

docker run \
  --rm \
  --entrypoint php \
  -v "$PLUGIN:/plugin:ro" \
  -v "$ROOT/tests/release:/release-tests:ro" \
  "$IMAGE" \
  /release-tests/runtime-core.php \
  /plugin

echo
echo "===== G. CLEAN GRAV PACKAGE INSTALL ====="

docker run \
  -d \
  --name "$CONTAINER" \
  -p 127.0.0.1::80 \
  "$IMAGE" \
  >/dev/null

echo "Container = $CONTAINER"

READY=0

for attempt in $(seq 1 90); do
    if docker exec \
        "$CONTAINER" \
        sh -lc '
            test -f /app/www/public/index.php &&
            pgrep -f php-fpm >/dev/null
        ' \
        >/dev/null \
        2>&1
    then
        READY=1
        break
    fi

    sleep 1
done

if [ "$READY" != "1" ]; then
    echo "GRAV_CONTAINER_READY = FAIL"
    docker logs "$CONTAINER" 2>&1 | tail -80 || true
    docker exec "$CONTAINER" sh -lc '
        cd /app/www/public
        php bin/grav logviewer --lines=80 2>&1 || true
    ' || true
    exit 1
fi

docker exec "$CONTAINER" \
  mkdir -p \
  /app/www/public/user/plugins/goosialize-links

docker cp \
  "$PLUGIN/." \
  "$CONTAINER:/app/www/public/user/plugins/goosialize-links/"

docker exec "$CONTAINER" sh -lc '
    set -e

    PLUGIN="/app/www/public/user/plugins/goosialize-links"
    CONFIG="/app/www/public/user/config/plugins/goosialize-links.yaml"

    mkdir -p \
      "/app/www/public/user/config/plugins"

    cat > "$CONFIG" <<EOF
enabled: true
route: /bio
profile:
  name: Release Gate
actions:
  - id: action_bbbbbbbbbbbbbbbb
    enabled: true
    type: website
    value: https://example.org/action
    label: Release Action
links:
  - id: link_aaaaaaaaaaaaaaaa
    enabled: true
    title: Release Link
    url: https://example.org/link
    new_tab: false
EOF

    chown -R abc:users \
      "$PLUGIN" \
      "$CONFIG" \
      /app/www/public/user/config \
      /app/www/public/cache

'

docker exec --user abc "$CONTAINER" sh -lc '
    cd /app/www/public
    php bin/grav clearcache >/dev/null
'

echo "GRAV_INSTALL_READY = PASS"

PORT="$(
    docker port \
      "$CONTAINER" \
      80/tcp \
    | head -1 \
    | sed -E 's/.*:([0-9]+)$/\1/'
)"

test -n "$PORT"

BASE_URL="http://127.0.0.1:$PORT"

API_READY=0
for attempt in $(seq 1 90); do
    CODE="$(curl -sS --connect-timeout 1 -o /dev/null -w '%{http_code}' "$BASE_URL/api/v1/auth/setup" 2>/dev/null || true)"
    if [ "$CODE" = "200" ]; then
        API_READY=1
        break
    fi
    sleep 1
done

if [ "$API_READY" != "1" ]; then
    echo "GRAV_API_READY = FAIL"
    exit 1
fi

echo "GRAV_API_READY = PASS"

SETUP_STATUS="$(
    curl \
      -sS \
      -o "$BUILD_DIR/setup.json" \
      -w '%{http_code}' \
      -H 'Content-Type: application/json' \
      --data '{"username":"p9super","password":"P9releasePass123!","email":"super@example.test","fullname":"Super"}' \
      "$BASE_URL/api/v1/auth/setup"
)"

test "$SETUP_STATUS" = "200"
echo "GRAV_ADMIN2_SETUP = PASS"

SETUP_TOKEN="$(python3 - "$BUILD_DIR/setup.json" <<'PY'
from pathlib import Path
import json
import sys

payload = json.loads(Path(sys.argv[1]).read_text())
data = payload.get("data", payload)
print(data.get("access_token", ""))
PY
)"

test -n "$SETUP_TOKEN"

CONFIG_STATUS="$(
    curl \
      -sS \
      -o "$BUILD_DIR/config-update.json" \
      -w '%{http_code}' \
      -X PATCH \
      -H "X-API-Token: $SETUP_TOKEN" \
      -H 'Content-Type: application/json' \
      --data '{"enabled":true,"route":"/bio","profile":{"name":"Release Gate"},"actions":[{"id":"action_bbbbbbbbbbbbbbbb","enabled":true,"type":"website","value":"https://example.org/action","label":"Release Action"}],"links":[{"id":"link_aaaaaaaaaaaaaaaa","enabled":true,"title":"Release Link","url":"https://example.org/link","new_tab":false}]}' \
      "$BASE_URL/api/v1/config/plugins/goosialize-links"
)"

test "$CONFIG_STATUS" = "200"
echo "GRAV_PLUGIN_CONFIG = PASS"

READY=0

for attempt in $(seq 1 90); do
    CODE="$(
        curl \
          -sS \
          --connect-timeout 1 \
          -o /dev/null \
          -w '%{http_code}' \
          "$BASE_URL/bio" \
          2>/dev/null \
          || true
    )"

    if [ "$CODE" = "200" ]; then
        READY=1
        break
    fi

    sleep 1
done

if [ "$READY" != "1" ]; then
    echo "GRAV_HTTP_READY = FAIL"
    echo "LAST_HTTP_STATUS = ${CODE:-unavailable}"
    curl -sS -D - "$BASE_URL/bio" | head -120 || true
    docker logs "$CONTAINER" 2>&1 | tail -80 || true
    docker exec "$CONTAINER" sh -lc '
        cd /app/www/public
        php bin/grav logviewer --lines=80 2>&1 || true
        find /config /app/www/public -type f \( -name "*.log" -o -name "error.log" \) -print -exec tail -80 {} \; 2>/dev/null || true
    ' || true
    exit 1
fi

echo "Clean Grav /bio = 200"

analytics_snapshot() {
    docker exec "$CONTAINER" php -r '
        require "/app/www/public/vendor/autoload.php";
        require "/app/www/public/user/plugins/goosialize-links/vendor/autoload.php";
        $report = (new Goosialize\Links\AnalyticsReportAggregator(
            "/app/www/public/user/data/goosialize-links/analytics"
        ))->aggregate();
        echo json_encode($report, JSON_THROW_ON_ERROR);
    '
}

BASELINE_ANALYTICS="$(analytics_snapshot)"

echo
echo "===== H. CLEAN INSTALL QR CONTRACT ====="

PNG_HEADERS="$BUILD_DIR/qr-png.headers"
SVG_HEADERS="$BUILD_DIR/qr-svg.headers"

PNG="$(
    curl \
      -sS \
      -D "$PNG_HEADERS" \
      -o "$BUILD_DIR/qr.png" \
      -w '%{http_code}' \
      "$BASE_URL/bio/qr/qr_primary/png"
)"

SVG="$(
    curl \
      -sS \
      -D "$SVG_HEADERS" \
      -o "$BUILD_DIR/qr.svg" \
      -w '%{http_code}' \
      "$BASE_URL/bio/qr/qr_primary/svg"
)"

echo "PNG = $PNG"
echo "SVG = $SVG"

test "$PNG" = "200"
test "$SVG" = "200"

python3 - \
  "$BUILD_DIR/qr.png" \
  "$BUILD_DIR/qr.svg" \
  "$PNG_HEADERS" \
  "$SVG_HEADERS" <<'PY'
from pathlib import Path
import sys

png = Path(sys.argv[1]).read_bytes()

svg = Path(
    sys.argv[2]
).read_text(
    errors="replace"
)

png_headers = Path(sys.argv[3]).read_text().lower()
svg_headers = Path(sys.argv[4]).read_text().lower()

if not png.startswith(
    b"\x89PNG\r\n\x1a\n"
):
    raise SystemExit(
        "Invalid PNG signature"
    )

if "<svg" not in svg.lower():
    raise SystemExit(
        "Invalid SVG response"
    )

for label, headers, content_type in (
    ("PNG", png_headers, "content-type: image/png"),
    ("SVG", svg_headers, "content-type: image/svg+xml"),
):
    for expected in (
        content_type,
        "cache-control: no-store, max-age=0",
        "x-content-type-options: nosniff",
        "referrer-policy: no-referrer",
    ):
        if expected not in headers:
            raise SystemExit(f"{label} missing header: {expected}")

print("PNG payload = PASS")
print("SVG payload = PASS")
print("QR defensive headers = PASS")
PY

AFTER_IMAGES="$(analytics_snapshot)"

python3 - "$BASELINE_ANALYTICS" "$AFTER_IMAGES" <<'PY'
import json
import sys

before = json.loads(sys.argv[1])
after = json.loads(sys.argv[2])

if before != after:
    raise SystemExit("QR image requests changed analytics")

print("QR_IMAGE_NO_ANALYTICS = PASS")
PY

echo
echo "===== I. TRACKED QR SAFE REDIRECT ====="

HEADERS="$BUILD_DIR/track.headers"

TRACK="$(
    curl \
      -sS \
      -D "$HEADERS" \
      -o /dev/null \
      -w '%{http_code}' \
      "$BASE_URL/bio/qr/qr_primary"
)"

LOCATION="$(
    grep -i '^Location:' "$HEADERS" \
    | head -1 \
    | sed -E \
        's/^[Ll]ocation:[[:space:]]*//; s/\r$//'
)"

echo "Status   = $TRACK"
echo "Location = $LOCATION"

case "$TRACK" in
    302|303|307)
        ;;
    *)
        echo "Tracked QR did not redirect"
        exit 1
        ;;
esac

test "$LOCATION" = "/bio"

ATTACK_LOCATION="$(
    curl \
      -sS \
      -D - \
      -o /dev/null \
      "$BASE_URL/bio/qr/qr_primary?redirect=https://example.com/" \
    | grep -i '^Location:' \
    | head -1 \
    | sed -E \
        's/^[Ll]ocation:[[:space:]]*//; s/\r$//'
)"

echo "Attack Location = $ATTACK_LOCATION"

test "$ATTACK_LOCATION" = "/bio"

echo "Open redirect resistance = PASS"

AFTER_QR="$(analytics_snapshot)"

python3 - "$AFTER_IMAGES" "$AFTER_QR" <<'PY'
import json
import sys

before = json.loads(sys.argv[1])
after = json.loads(sys.argv[2])

before_qrs = before["qrs"] if isinstance(before["qrs"], dict) else {}
after_qrs = after["qrs"] if isinstance(after["qrs"], dict) else {}

if after["qr_visits"] != before["qr_visits"] + 2:
    raise SystemExit("Each tracked QR request must record exactly one visit")

if after_qrs.get("qr_primary") != before_qrs.get("qr_primary", 0) + 2:
    raise SystemExit("qr_primary aggregation mismatch")

print("QR_ANALYTICS = PASS")
PY

echo
echo "===== J. LINK/ACTION HTTP AND ANALYTICS REGRESSION ====="

for KIND in link action; do
    if [ "$KIND" = "link" ]; then
        URL="$BASE_URL/bio/go/link_aaaaaaaaaaaaaaaa"
        EXPECTED="https://example.org/link"
    else
        URL="$BASE_URL/bio/action/action_bbbbbbbbbbbbbbbb"
        EXPECTED="https://example.org/action"
    fi
    RESPONSE_HEADERS="$BUILD_DIR/$KIND.headers"
    STATUS="$(curl -sS -D "$RESPONSE_HEADERS" -o /dev/null -w '%{http_code}' "$URL")"
    LOCATION="$(grep -i '^Location:' "$RESPONSE_HEADERS" | head -1 | sed -E 's/^[Ll]ocation:[[:space:]]*//; s/\r$//' || true)"
    echo "${KIND^^}_STATUS = $STATUS"
    echo "${KIND^^}_LOCATION = ${LOCATION:-missing}"
    test "$STATUS" = "302"
    test "$LOCATION" = "$EXPECTED"
    echo "${KIND^^}_REDIRECT = PASS"
done

AFTER_CLICKS="$(analytics_snapshot)"

python3 - "$AFTER_QR" "$AFTER_CLICKS" <<'PY'
import json
import sys

before = json.loads(sys.argv[1])
after = json.loads(sys.argv[2])

before_links = before["links"] if isinstance(before["links"], dict) else {}
before_actions = before["actions"] if isinstance(before["actions"], dict) else {}
after_links = after["links"] if isinstance(after["links"], dict) else {}
after_actions = after["actions"] if isinstance(after["actions"], dict) else {}

if after["total_clicks"] != before["total_clicks"] + 2:
    raise SystemExit("Total click regression")
if after_links.get("link_aaaaaaaaaaaaaaaa") != before_links.get("link_aaaaaaaaaaaaaaaa", 0) + 1:
    raise SystemExit("Per-link analytics regression")
if after_actions.get("action_bbbbbbbbbbbbbbbb") != before_actions.get("action_bbbbbbbbbbbbbbbb", 0) + 1:
    raise SystemExit("Per-action analytics regression")

print("LINK_ACTION_ANALYTICS = PASS")
PY

echo
echo "===== K. ADMIN2/API PERMISSION ACCEPTANCE ====="

docker exec "$CONTAINER" sh -lc '
    set -e
    cd /app/www/public
    php bin/plugin login new-user -u p9authorized -p P9releasePass123! -e authorized@example.test -P a --admin-type=api -N Authorized -n >/dev/null
    php bin/plugin login new-user -u p9unauthorized -p P9releasePass123! -e unauthorized@example.test -P a --admin-type=api -N Unauthorized -n >/dev/null
    php -r '\''
        require "vendor/autoload.php";
        foreach ([
            "p9authorized" => [
                "api" => [
                    "login" => true,
                    "access" => true,
                    "goosialize-links" => ["qr" => ["read" => true]],
                ],
            ],
            "p9unauthorized" => [
                "api" => ["login" => true, "access" => true],
            ],
        ] as $user => $access) {
            $path = "user/accounts/{$user}.yaml";
            $data = Symfony\Component\Yaml\Yaml::parseFile($path);
            $data["access"] = $access;
            file_put_contents($path, Symfony\Component\Yaml\Yaml::dump($data, 8, 2));
        }
    '\''
    chown -R abc:users user/accounts user/config
'

api_key() {
    docker exec "$CONTAINER" sh -lc \
      "cd /app/www/public && php bin/plugin api keys:generate -u $1 -N P9Gate -n" \
      | awk '/API Key:/ {print $3}'
}

AUTHORIZED_KEY="$(api_key p9authorized)"
UNAUTHORIZED_KEY="$(api_key p9unauthorized)"
SUPER_KEY="$(api_key p9super)"

docker exec "$CONTAINER" chown -R abc:users \
  /app/www/public/user/accounts \
  /app/www/public/user/config

test -n "$AUTHORIZED_KEY"
test -n "$UNAUTHORIZED_KEY"
test -n "$SUPER_KEY"

API_BASE="$BASE_URL/api/v1/goosialize-links"

test "$(curl -sS -o /dev/null -w '%{http_code}' "$API_BASE/qr")" = "401"
test "$(curl -sS -H "X-API-Key: $UNAUTHORIZED_KEY" -o /dev/null -w '%{http_code}' "$API_BASE/qr")" = "403"
test "$(curl -sS -H "X-API-Key: $UNAUTHORIZED_KEY" -o /dev/null -w '%{http_code}' "$API_BASE/qr/download/png")" = "403"
echo "ADMIN_API_UNAUTHORIZED = PASS"

for identity in "authorized:$AUTHORIZED_KEY" "super:$SUPER_KEY"; do
    NAME="${identity%%:*}"
    KEY="${identity#*:}"
    QR_JSON="$BUILD_DIR/$NAME-qr.json"
    DASHBOARD_JSON="$BUILD_DIR/$NAME-dashboard.json"
    test "$(curl -sS -H "X-API-Key: $KEY" -o "$QR_JSON" -w '%{http_code}' "$API_BASE/qr")" = "200"
    test "$(curl -sS -H "X-API-Key: $KEY" -o "$DASHBOARD_JSON" -w '%{http_code}' "$API_BASE/dashboard")" = "200"
    python3 - "$QR_JSON" "$DASHBOARD_JSON" <<'PY'
from pathlib import Path
import json
import sys

qr = json.loads(Path(sys.argv[1]).read_text())
dashboard = json.loads(Path(sys.argv[2]).read_text())

if qr["qr_id"] != "qr_primary" or qr["tracked_url"] != "/bio/qr/qr_primary":
    raise SystemExit("Invalid authorized QR payload")
if dashboard["qr"]["qr_id"] != "qr_primary":
    raise SystemExit("Invalid dashboard QR payload")
if not dashboard["top_links"] or dashboard["top_links"][0]["id"] != "link_aaaaaaaaaaaaaaaa":
    raise SystemExit("Top Links acceptance failed")
if not dashboard["top_actions"] or dashboard["top_actions"][0]["id"] != "action_bbbbbbbbbbbbbbbb":
    raise SystemExit("Top Actions acceptance failed")
if dashboard["summary"]["qr_visits"] < 2:
    raise SystemExit("Dashboard QR visits acceptance failed")

print("ADMIN_API_PAYLOAD = PASS")
PY
    for format in png svg; do
        DOWNLOAD_HEADERS="$BUILD_DIR/$NAME-$format.headers"
        DOWNLOAD_BODY="$BUILD_DIR/$NAME-download.$format"
        test "$(curl -sS -H "X-API-Key: $KEY" -D "$DOWNLOAD_HEADERS" -o "$DOWNLOAD_BODY" -w '%{http_code}' "$API_BASE/qr/download/$format")" = "200"
        grep -qi '^Content-Disposition: attachment;' "$DOWNLOAD_HEADERS"
        test -s "$DOWNLOAD_BODY"
    done
    echo "ADMIN_API_${NAME^^} = PASS"
done

echo "HUMAN_BROWSER_VERIFY = REQUIRED"

echo
echo "===== L. PACKAGE-INSTALLED DEPENDENCIES ====="

docker exec "$CONTAINER" sh -lc '
    set -e

    PLUGIN="/app/www/public/user/plugins/goosialize-links"

    test -f "$PLUGIN/vendor/autoload.php"
    test -d "$PLUGIN/vendor/endroid/qr-code"
    test -d "$PLUGIN/vendor/bacon/bacon-qr-code"
    test -d "$PLUGIN/vendor/dasprid/enum"

    echo "Packaged vendor dependencies = PASS"
'

echo
echo "===== M. FINAL PACKAGE HASH ====="

echo "SHA256=$(
    sha256sum "$PACKAGE" |
    awk '{print $1}'
)"

echo
echo "=================================================="
echo " AUTOMATED RELEASE GATES = PASS"
echo "=================================================="
