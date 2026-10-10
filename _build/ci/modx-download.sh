#!/usr/bin/env bash
# Download a MODX release and unpack it.
# Usage: modx-download.sh <version, e.g. 2.8.8-pl> <target dir> [zip cache]

set -euo pipefail

version="${1:?MODX version is required}"
target="${2:?target directory is required}"
zip="${3:-/tmp/modx-$version.zip}"

if [ -f "$zip" ] && ! unzip -tq "$zip" > /dev/null 2>&1; then
  rm -f "$zip"
fi

if [ ! -f "$zip" ]; then
  curl -fsSL --retry 3 \
    -A "Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 Chrome/131.0 Safari/537.36" \
    -o "$zip.part" "https://modx.com/download/direct/modx-$version.zip"
  if ! unzip -tq "$zip.part" > /dev/null; then
    rm -f "$zip.part"
    echo "modx.com did not send a valid zip for MODX $version" >&2
    exit 1
  fi
  mv "$zip.part" "$zip"
fi

tmp="$(mktemp -d)"
trap 'rm -rf "$tmp"' EXIT
unzip -q "$zip" -d "$tmp"
mkdir -p "$target"
cp -a "$tmp/modx-$version/." "$target/"
