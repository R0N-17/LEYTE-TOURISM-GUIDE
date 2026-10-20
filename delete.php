<?php
require __DIR__ . '/../includes/bootstrap.php';
api_handle(function () {
    $user = auth_require();
    try { rev_delete_for_user($user['id']); }   // SCRUM-20: the reviews of a deleted account go with it
    catch (Throwable $e) { error_log('reviews: could not remove the reviews of a deleted account: ' . $e->getMessage()); }
    db_delete($user['id']);
    auth_logout();
    return [];
});
