<?php
// Accounts, validation and the login session.
function public_user(array $u): array {
    return [
        'first_name' => $u['first_name'],
        'last_name'  => $u['last_name'],
        'email'      => $u['email'],
        'theme'      => in_array($u['theme'] ?? null, THEMES, true) ? $u['theme'] : null,   // an older account value ("system"/"auto") is no longer a choice: null = keep this browser's own theme
        'prefs'      => $u['prefs'] ?? null,
        'recent'     => array_values((array)($u['recent'] ?? [])),
        'role'       => $u['role'] ?? 'user',
    ];
}

function clean_name(string $v, string $label): string {
    $v = trim(preg_replace('/\s+/u', ' ', $v));
    if ($v === '' || mb_strlen($v) > 100) throw new ApiError("Enter your $label.", 422);
    // Letters (including accented ones like ñ or é), spaces, hyphens, apostrophes and periods only.
    // Numbers and other symbols are not part of a name.
    if (!preg_match('/^[\p{L}\p{M}][\p{L}\p{M} \'\x{2019}.\-]*$/u', $v))
        throw new ApiError("Your $label can only have letters, spaces, hyphens, apostrophes and periods. No numbers.", 422);
    return $v;
}

function clean_email(string $v): string {
    $v = strtolower(trim($v));
    if (!filter_var($v, FILTER_VALIDATE_EMAIL)) throw new ApiError('Enter a valid email address.', 422);
    return $v;
}

function check_password(string $pw): void {
    if (strlen($pw) < 8 || !preg_match('/[a-z]/i', $pw) || !preg_match('/\d/', $pw))
        throw new ApiError('Use at least 8 characters with a letter and a number.', 422);
}

function auth_register(array $in): array {
    $email = clean_email(str_in($in, 'email'));
    $pass  = str_in($in, 'password');
    check_password($pass);
    if (db_find_by_email($email))
        throw new ApiError('That email is already registered. Try logging in instead.', 409);

    $theme = str_in($in, 'theme');
    $user = [
        'id'         => bin2hex(random_bytes(8)),
        'first_name' => clean_name(str_in($in, 'first_name'), 'first name'),
        'last_name'  => clean_name(str_in($in, 'last_name'), 'last name'),
        'email'      => $email,
        'password'   => password_hash($pass, PASSWORD_DEFAULT),
        'theme'      => in_array($theme, THEMES, true) ? $theme : DEFAULT_THEME,
        'prefs'      => null,
        'recent'     => [],
        'role'       => 'user',      // SCRUM-16: public sign-up can only ever create ordinary users. Admins are made with tools/make-admin.php.
        'created'    => date('c'),
    ];
    db_insert($user);
    auth_start_session($user, false);
    return $user;
}

function auth_login(array $in): array {
    db_assert_readable();   // missing/unreadable account file = server error, never "Account doesn't exist"
    $user = db_find_by_email(trim(str_in($in, 'email')));
    if (!$user) throw new ApiError("Account doesn't exist.", 401);
    if (!password_verify(str_in($in, 'password'), $user['password']))
        throw new ApiError('Incorrect email or password. Try again or reset your password.', 401);
    auth_start_session($user, !empty($in['remember']));
    return $user;
}

// "Keep me logged in" = a 30-day cookie. Otherwise the cookie ends when the browser closes.
function auth_start_session(array $user, bool $remember): void {
    session_regenerate_id(true);
    $_SESSION['uid'] = $user['id'];
    if ($remember) {
        setcookie(session_name(), session_id(), [
            'expires'  => time() + REMEMBER_DAYS * 86400,
            'path'     => '/',
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
    }
}

function auth_current(): ?array {
    return isset($_SESSION['uid']) ? db_find($_SESSION['uid']) : null;
}

function auth_require(): array {
    return auth_current() ?? throw new ApiError('Please log in.', 401);
}

function auth_logout(): void {
    $_SESSION = [];
    session_destroy();
}

// ---------- SCRUM-16: admin role ----------
// A user is an admin only when their record says role = "admin". The role can't be set through any web endpoint:
// use tools/make-admin.php on the server. It is read from the file on every request, so removing it takes effect immediately.
function auth_is_admin(?array $u): bool {
    return $u !== null && ($u['role'] ?? 'user') === 'admin';
}

// Start of every api/admin/* endpoint (except login). 401 = not logged in, 403 = logged in but not an admin.
function auth_require_admin(): array {
    $u = auth_require();
    if (!auth_is_admin($u)) throw new ApiError('Administrator access only.', 403);
    return $u;
}

// Admin sign-in. A wrong password and a non-admin account get the same message, so the page doesn't reveal who is an admin.
function auth_admin_login(array $in): array {
    $user = db_find_by_email(trim(str_in($in, 'email')));
    if (!$user || !password_verify(str_in($in, 'password'), $user['password']) || !auth_is_admin($user)) {
        usleep(400000);   // slows down password guessing a little
        throw new ApiError('Incorrect email or password, or this account is not an administrator.', 401);
    }
    auth_start_session($user, false);
    return $user;
}

function admin_public(array $u): array {
    // 'created' (when the account was made) is shown on the Settings page only when the record has it. No password, id or token is ever returned.
    $out = ['first_name' => $u['first_name'], 'last_name' => $u['last_name'], 'email' => $u['email'], 'role' => $u['role'] ?? 'user'];
    if (!empty($u['created']) && is_string($u['created'])) $out['created'] = $u['created'];
    return $out;
}
