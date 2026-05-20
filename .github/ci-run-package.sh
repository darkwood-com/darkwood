#!/usr/bin/env bash
#
# Run quality checks for one monorepo package (see packages.json).
#
# Usage: .github/ci-run-package.sh <package-id>
#
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "$0")/.." && pwd)"
PACKAGE_ID="${1:?Package id required (navi, flow, media-bundle, ia-exception-bundle)}"
MANIFEST="${ROOT_DIR}/.github/packages.json"

PACKAGE_JSON=$(jq -c --arg id "$PACKAGE_ID" '.packages[] | select(.id == $id)' "$MANIFEST")
if [ -z "$PACKAGE_JSON" ]; then
    echo "Unknown package id: ${PACKAGE_ID}" >&2
    exit 1
fi

PACKAGE_DIR=$(echo "$PACKAGE_JSON" | jq -r '.directory')

cd "${ROOT_DIR}/${PACKAGE_DIR}"

echo "::group::Composer install (${PACKAGE_DIR})"
composer install --no-progress --ansi --ignore-platform-req=ext-openswoole
echo "::endgroup::"

for TOOL in tools/php-cs-fixer tools/phpstan tools/phpunit; do
    if [ ! -f "${TOOL}/composer.json" ]; then
        echo "Missing ${TOOL}/composer.json in ${PACKAGE_DIR}" >&2
        exit 1
    fi
done

echo "::group::Install QA tools (${PACKAGE_DIR})"
for TOOL in tools/php-cs-fixer tools/phpstan tools/phpunit; do
    composer install --no-progress --ansi -d "${TOOL}" --ignore-platform-req=ext-openswoole
done
echo "::endgroup::"

echo "::group::PHP CS Fixer (${PACKAGE_DIR})"
PHP_CS_FIXER_CONFIG="${ROOT_DIR}/${PACKAGE_DIR}/tools/php-cs-fixer/.php-cs-fixer.php"
if [ ! -f "${PHP_CS_FIXER_CONFIG}" ]; then
    echo "Missing PHP CS Fixer config: ${PHP_CS_FIXER_CONFIG}" >&2
    exit 1
fi
(cd tools/php-cs-fixer && vendor/bin/php-cs-fixer fix --config "${PHP_CS_FIXER_CONFIG}" --diff --dry-run)
echo "::endgroup::"

echo "::group::PHPStan (${PACKAGE_DIR})"
(cd tools/phpstan && vendor/bin/phpstan analyse --configuration=phpstan.neon --memory-limit=1024M)
echo "::endgroup::"

echo "::group::PHPUnit (${PACKAGE_DIR})"
(cd tools/phpunit && vendor/bin/phpunit --configuration phpunit.xml)
echo "::endgroup::"

echo "All checks passed for ${PACKAGE_DIR}"
