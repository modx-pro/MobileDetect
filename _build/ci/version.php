<?php
/**
 * Print the package signature and, when a tag is passed, check it matches
 * PKG_VERSION and that the changelog has a section for this release.
 *
 * Usage: php _build/ci/version.php [tag]
 */

$root = dirname(__DIR__, 2) . '/';
$config = file_get_contents($root . '_build/build.config.php');
if (!preg_match("/define\\('PKG_NAME', '([^']+)'\\)/", $config, $name)
    || !preg_match("/define\\('PKG_VERSION', '([^']+)'\\)/", $config, $version)
    || !preg_match("/define\\('PKG_RELEASE', '([^']+)'\\)/", $config, $release)
) {
    fwrite(STDERR, "Could not read PKG_NAME, PKG_VERSION or PKG_RELEASE from _build/build.config.php\n");
    exit(1);
}

$version = $version[1];
$release = $release[1];
$signature = strtolower($name[1]) . '-' . $version . '-' . $release;
$errors = array();

$tag = isset($argv[1]) ? trim($argv[1]) : '';
if ($tag !== '') {
    $accepted = array(
        $version,
        $version . '-' . $release,
        'v' . $version,
        'v' . $version . '-' . $release,
    );
    if (!in_array($tag, $accepted, true)) {
        $errors[] = "Tag {$tag} does not match {$version}-{$release}.";
    }

    $changelog = file_get_contents($root . 'core/components/mobiledetect/docs/changelog.txt');
    $heading = '## [' . $version . '-' . $release . ']';
    if (strpos($changelog, $heading) === false) {
        $errors[] = "changelog.txt has no section {$heading}.";
    }
}

if ($errors) {
    fwrite(STDERR, implode("\n", $errors) . "\n");
    exit(1);
}

echo "version={$version}\n";
echo "signature={$signature}\n";
