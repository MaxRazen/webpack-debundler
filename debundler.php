<?php
require_once __DIR__.'/vendor/autoload.php';

use Helpers\Module;
use Helpers\PackageRepository;

CONST SRC_DIR = 'sources';

/** @var resource $bundle */
$bundle = null;

/** @var  Module|null $module */
$module = null;

$packages = new PackageRepository();

/** @var int $rowCounter */
$rowCounter = 0;

function main() {
    echo "Running Debundler\n";

    $path = getArgPath();

    loadBundleResource($path);

    prepareDirs();

    processBundle();

    closeBundleResource();
}

function processBundle() {
    global $bundle, $packages, $rowCounter;

    while ($row = fgets($bundle)) {
        processBundleRow($row);
    }

    $packages->savePackages(makeFilePath());
}

function processBundleRow(string $row) {
    global $module, $packages, $rowCounter;

    $row = trim($row);

    if (preg_match('/^\/\**\/ "([^"]+)":$/', $row, $startMatch)) {
        $module = new Module();
        $module->setModuleName($startMatch[1]);
    } elseif (preg_match('/^eval\(.+sourceMappingURL=(.*?)\\\n.+\);$/', $row, $sourceMapMatch)) {
        $module->setModuleSource($sourceMapMatch[1]);
    } elseif (preg_match('/^\/\*\*\*\/ }\),$/', $row, $endMatch)) {
        $module->dump($packages);

        $module = null;
    }

    $rowCounter++;
}

function loadBundleResource(string $path) {
    global $bundle;

    if (! file_exists($path)) {
        throwError(sprintf('File "%s" does not exist', $path));
    }

    $bundle = fopen($path, 'r');
}

function closeBundleResource() {
    global $bundle;

    if (is_resource($bundle)) {
        fclose($bundle);
    }
}

function prepareDirs() {
    if (file_exists(SRC_DIR)) {
        delPathTree(SRC_DIR);
    }

    mkdir(SRC_DIR);
}

function delPathTree($dir) {
    $files = array_diff(scandir($dir), array('.','..'));
    foreach ($files as $file) {
        (is_dir("$dir/$file")) ? delPathTree("$dir/$file") : unlink("$dir/$file");
    }
    return rmdir($dir);
}

/**
 * @return string|void
 */
function getArgPath() {
    $path = $_SERVER['argv'][1] ?? false;

    if (!empty($path) && strpos($path, '--path') !== false) {
        return str_replace('--path=', '', $path);
    }

    throwError('--path=<file-path> must be specified');
}

function throwError(string $msg) {
    exit("\tERROR: {$msg}\n");
}

function extractPath(string $path): string {
    return preg_replace('#^\./(.*)/.+$#', '$1', $path);
}

function extractFileName(string $path): string {
    return preg_replace('/^.+\/(.*)$/', '$1', $path);
}

function extractPackageName(string $path) {
    return preg_replace('/.\/node_modules\/([^\/]+)\/([^\/]+)\/.+/', '$1/$2', $path);
}

function makeFilePath(string $path = ''): string {
    return rtrim(sprintf('%s/%s/%s', __DIR__, SRC_DIR, $path), '/');
}

function prepareModuleDir(string $path) {
    $absolutePath = makeFilePath($path);
    if (! file_exists($absolutePath)) {
        mkdir($absolutePath, 0775, true);
    }
}

main();
