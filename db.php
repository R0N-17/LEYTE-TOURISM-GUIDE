<?php
// Text-file "database": one JSON user per line in data/users.txt.
// Fine for a demo. Swap these functions for MySQL later; nothing else needs to change.
function db_all(): array {
    if (!file_exists(DB_FILE)) return [];
    $users = [];
    foreach (file(DB_FILE, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $u = json_decode($line, true);
        if ($u) $users[$u['id']] = $u;
    }
    return $users;
}

// Runs a read-modify-write under one exclusive lock, so two requests at the same moment
// (e.g. saving preferences while the theme is saved) can't overwrite each other or read a half-written file.
function db_locked(callable $fn) {
    $lock = fopen(DB_FILE . '.lock', 'c');
    if (!$lock) return $fn();
    flock($lock, LOCK_EX);
    try { return $fn(); }
    finally { flock($lock, LOCK_UN); fclose($lock); }
}

function db_save(array $users): void {
    $lines = array_map('json_encode', array_values($users));
    // Write to a temp file, then rename over the real one, so a reader never sees a half-written file.
    $tmp = DB_FILE . '.tmp' . getmypid();
    if (file_put_contents($tmp, $lines ? implode("\n", $lines) . "\n" : '') === false || !@rename($tmp, DB_FILE)) {
        @unlink($tmp);
        if (file_put_contents(DB_FILE, $lines ? implode("\n", $lines) . "\n" : '', LOCK_EX) === false)
            throw new ApiError('Could not save your changes. Please try again.', 500);
    }
}

// For login: a missing or unreadable account file is a server problem, not "this account doesn't exist".
function db_assert_readable(): void {
    if (!is_file(DB_FILE) || !is_readable(DB_FILE) || @file(DB_FILE) === false)
        throw new ApiError('The account data is unavailable right now (server configuration error). Please contact the administrator.', 500);
}

function db_find(string $id): ?array {
    return db_all()[$id] ?? null;
}

function db_find_by_email(string $email): ?array {
    foreach (db_all() as $u)
        if (strcasecmp($u['email'], $email) === 0) return $u;
    return null;
}

function db_insert(array $user): void {
    db_locked(function () use ($user) {
        $users = db_all();
        $users[$user['id']] = $user;
        db_save($users);
    });
}

function db_update(string $id, array $changes): void {
    db_locked(function () use ($id, $changes) {
        $users = db_all();
        if (!isset($users[$id])) return;
        $users[$id] = array_merge($users[$id], $changes);
        db_save($users);
    });
}

function db_delete(string $id): void {
    db_locked(function () use ($id) {
        $users = db_all();
        unset($users[$id]);
        db_save($users);
    });
}
