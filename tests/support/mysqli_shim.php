<?php
/**
 * Minimal mysqli-compatible shim backed by SQLite (PDO), covering only the
 * functions this project actually calls.
 *
 * Why this exists: the app talks to MySQL exclusively through mysqli, and
 * the real integration test is running it against a real MySQL/MariaDB
 * server (see ../../README.md). Not everyone has that available on demand
 * -- this shim lets the actual application files run, unmodified, against
 * SQLite instead, so the request-handling logic (auth, access control,
 * query parameterization) can be exercised with real PHP execution rather
 * than only read by eye. It is not a general mysqli replacement and is not
 * used by the application itself.
 */

$GLOBALS['__pdo'] = null;

function shim_boot($dbPath, $schemaSql)
{
    if (file_exists($dbPath)) {
        unlink($dbPath);
    }
    $pdo = new PDO('sqlite:' . $dbPath);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec('PRAGMA foreign_keys = ON');
    $pdo->exec($schemaSql);
    $GLOBALS['__pdo'] = $pdo;
    return $pdo;
}

class ShimResult implements Iterator, Countable
{
    public $rows;
    private $pos = 0;

    public function __construct(array $rows)
    {
        $this->rows = array_values($rows);
    }

    public function current(): mixed { return $this->rows[$this->pos]; }
    public function key(): mixed { return $this->pos; }
    public function next(): void { $this->pos++; }
    public function rewind(): void { $this->pos = 0; }
    public function valid(): bool { return isset($this->rows[$this->pos]); }
    public function count(): int { return count($this->rows); }
}

class ShimStmt
{
    public $pdoStmt;
    public function __construct($pdoStmt) { $this->pdoStmt = $pdoStmt; }
}

function mysqli_init() { return new stdClass(); }
function mysqli_real_connect(&$con) { $con->connected = true; return true; }
function mysqli_connect_errno() { return 0; }
function mysqli_connect_error() { return ''; }
function mysqli_set_charset($con, $cs) { return true; }
function mysqli_ssl_set(...$args) { return true; }

function mysqli_prepare($con, $sql)
{
    return new ShimStmt($GLOBALS['__pdo']->prepare($sql));
}

function mysqli_stmt_bind_param($stmt, $types, &...$params)
{
    if (strlen($types) !== count($params)) {
        throw new Exception('bind_param: type string length (' . strlen($types)
            . ') does not match parameter count (' . count($params) . ')');
    }
    for ($i = 0; $i < count($params); $i++) {
        // SQLite has dynamic column typing, so binding everything through
        // bindValue() with its natural PHP type is enough here; the app's
        // own mysqli type string is still exercised for shape/arity (a
        // mismatched count throws above, exactly like the real driver).
        $stmt->pdoStmt->bindValue($i + 1, $params[$i]);
    }
    return true;
}

function mysqli_stmt_execute($stmt) { return $stmt->pdoStmt->execute(); }

function mysqli_stmt_affected_rows($stmt) { return $stmt->pdoStmt->rowCount(); }

function mysqli_stmt_get_result($stmt)
{
    return new ShimResult($stmt->pdoStmt->fetchAll(PDO::FETCH_ASSOC));
}

function mysqli_query($con, $sql)
{
    $r = $GLOBALS['__pdo']->query($sql);
    return $r === false ? false : new ShimResult($r->fetchAll(PDO::FETCH_ASSOC));
}

function mysqli_num_rows($result) { return $result instanceof ShimResult ? count($result) : 0; }

function mysqli_fetch_assoc($result)
{
    if (!$result || !$result->valid()) return null;
    $row = $result->current();
    $result->next();
    return $row;
}

function mysqli_fetch_array($result)
{
    $row = mysqli_fetch_assoc($result);
    return $row === null ? null : array_merge($row, array_values($row));
}

function mysqli_fetch_row($result)
{
    $row = mysqli_fetch_assoc($result);
    return $row === null ? null : array_values($row);
}

function mysqli_insert_id($con) { return (int) $GLOBALS['__pdo']->lastInsertId(); }

/**
 * Register the scenario's assertions to run and print at shutdown, so a
 * result is still reported even when the included file ends the request
 * with exit() (the admin middleware and admin/Includes/code.php always do).
 * $fn returns an assoc array of assertion-name => bool; the runner in
 * ../run.php looks for the line starting with the marker below.
 */
function shim_report(callable $fn)
{
    register_shutdown_function(function () use ($fn) {
        echo "\n===RESULT===" . json_encode($fn()) . PHP_EOL;
    });
}
