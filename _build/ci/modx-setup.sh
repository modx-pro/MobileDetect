#!/usr/bin/env bash
# Install an unpacked MODX from the command line.
# Environment: MODX_ROOT, DB_HOST, DB_NAME, DB_USER, DB_PASSWORD, MODX_HTTP_HOST.

set -euo pipefail

root="${MODX_ROOT:?MODX_ROOT is not set}"
config="$(mktemp)"
trap 'rm -f "$config"' EXIT

xml() { printf '%s' "$1" | sed -e 's/&/\&amp;/g' -e 's/</\&lt;/g' -e 's/>/\&gt;/g'; }

cat > "$config" <<XML
<modx>
    <database_type>mysql</database_type>
    <database_server>$(xml "${DB_HOST:-127.0.0.1}")</database_server>
    <database>$(xml "${DB_NAME:-modx}")</database>
    <database_user>$(xml "${DB_USER:-root}")</database_user>
    <database_password>$(xml "${DB_PASSWORD:-root}")</database_password>
    <database_connection_charset>utf8mb4</database_connection_charset>
    <database_charset>utf8mb4</database_charset>
    <database_collation>utf8mb4_general_ci</database_collation>
    <table_prefix>modx_</table_prefix>
    <https_port>443</https_port>
    <http_host>$(xml "${MODX_HTTP_HOST:-localhost}")</http_host>
    <cache_disabled>0</cache_disabled>
    <inplace>1</inplace>
    <unpacked>0</unpacked>
    <language>en</language>
    <cmsadmin>admin</cmsadmin>
    <cmspassword>AdminAdmin1</cmspassword>
    <cmsadminemail>admin@example.com</cmsadminemail>
    <core_path>${root}/core/</core_path>
    <context_mgr_path>${root}/manager/</context_mgr_path>
    <context_mgr_url>/manager/</context_mgr_url>
    <context_connectors_path>${root}/connectors/</context_connectors_path>
    <context_connectors_url>/connectors/</context_connectors_url>
    <context_web_path>${root}/</context_web_path>
    <context_web_url>/</context_web_url>
    <remove_setup_directory>1</remove_setup_directory>
</modx>
XML

(cd "$root" && php setup/index.php --installmode=new --config="$config")

if [ -d "$root/setup" ]; then
  echo "MODX setup did not finish: $root/setup is still there" >&2
  exit 1
fi

MODX_CORE_PATH="$root/core/" php -r '
    define("MODX_API_MODE", true);
    require getenv("MODX_CORE_PATH") . "config/config.inc.php";
    require MODX_CORE_PATH . "model/modx/modx.class.php";
    $modx = new modX();
    $modx->initialize("mgr");
    $class = class_exists("MODX\\Revolution\\modUser", false) ? "MODX\\Revolution\\modUser" : "modUser";
    exit($modx->getCount($class) > 0 ? 0 : 1);
' || { echo "MODX was installed but has no admin user" >&2; exit 1; }
