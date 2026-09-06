<?php
/**
 * Lightweight regression suite for the security-sensitive request handlers
 * (auth, admin access control, cart, catalogue writes).
 *
 * This does NOT need MySQL, XAMPP or Composer -- only a PHP CLI with
 * pdo_sqlite (enabled by default in almost every PHP build, including
 * XAMPP's). Each scenario in tests/scenarios/ runs the real application
 * file, unmodified, against a throwaway SQLite database via
 * tests/support/mysqli_shim.php, then asserts on the resulting state.
 *
 * This is a compatibility-shim suite, not a substitute for testing against
 * real MySQL -- see README.md's "Test the full path once" note for that.
 * What it does catch: auth/session logic regressions, an admin endpoint
 * losing its access check, and SQL built by string concatenation creeping
 * back in (which breaks on the apostrophe/injection fixtures here whether
 * or not it is exploitable).
 *
 * Run:  php tests/run.php
 */

if (!extension_loaded('pdo_sqlite')) {
    fwrite(STDERR, "pdo_sqlite is not enabled in this PHP build. Enable it in php.ini "
        . "(extension=pdo_sqlite) and try again.\n");
    exit(1);
}

$root = __DIR__;
$scenarioDir = $root . '/scenarios';
$scenarios = glob($scenarioDir . '/*.php');
sort($scenarios);

// Each scenario is spawned with php.ini switched off (-n) and only
// pdo_sqlite re-enabled. That's deliberate: the app's own files call
// mysqli_prepare(), mysqli_query() etc., and the shim defines procedural
// functions with those exact names so the app can run against SQLite
// unmodified -- which is a fatal "cannot redeclare" error the moment the
// real mysqli extension is also loaded in the same process. extension_dir
// is read from THIS process's own config, so it resolves correctly
// wherever PHP is actually installed rather than assuming a path.
$extDir = ini_get('extension_dir');

if (!$scenarios) {
    fwrite(STDERR, "No scenarios found in $scenarioDir\n");
    exit(1);
}

echo "Perfume Store -- lightweight regression suite\n";
echo str_repeat('=', 60) . "\n\n";

$totalAssertions = 0;
$failedAssertions = 0;
$failedScenarios = [];

foreach ($scenarios as $path) {
    $name = basename($path, '.php');
    $cmd = escapeshellarg(PHP_BINARY)
        . ' -n'
        . ' -d extension_dir=' . escapeshellarg($extDir)
        . ' -d extension=pdo_sqlite'
        . ' -d session.save_path=' . escapeshellarg(sys_get_temp_dir())
        . ' ' . escapeshellarg($path);
    $output = shell_exec($cmd . ' 2>&1');

    $json = null;
    foreach (array_reverse(explode("\n", (string) $output)) as $line) {
        $line = trim($line);
        if (str_starts_with($line, '===RESULT===')) {
            $json = json_decode(substr($line, strlen('===RESULT===')), true);
            break;
        }
    }

    echo "$name\n";
    if ($json === null) {
        echo "  FAIL  scenario produced no result (crashed before reporting)\n";
        echo "  ---- raw output ----\n";
        foreach (explode("\n", trim((string) $output)) as $line) {
            echo "  | $line\n";
        }
        echo "  ---------------------\n";
        $failedScenarios[] = $name;
        continue;
    }

    $scenarioFailed = false;
    foreach ($json as $assertion => $passed) {
        $totalAssertions++;
        if ($passed) {
            echo "  PASS  $assertion\n";
        } else {
            echo "  FAIL  $assertion\n";
            $failedAssertions++;
            $scenarioFailed = true;
        }
    }
    if ($scenarioFailed) {
        $failedScenarios[] = $name;
    }
    echo "\n";
}

// Clean up the throwaway SQLite files each scenario leaves behind.
foreach (glob($root . '/.tmp_*.sqlite') as $f) {
    @unlink($f);
}

echo str_repeat('=', 60) . "\n";
echo ($totalAssertions - $failedAssertions) . " of $totalAssertions assertions passed across "
    . count($scenarios) . " scenarios.\n";

if ($failedScenarios) {
    echo "Failed: " . implode(', ', $failedScenarios) . "\n";
    exit(1);
}

echo "All scenarios passed.\n";
exit(0);
