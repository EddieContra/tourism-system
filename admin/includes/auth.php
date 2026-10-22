<?php
/**
 * Admin bootstrap: loads the shared application bootstrap and provides
 * staff authentication and role checks.
 *
 * Roles (stored in tbladmin.AdminName):
 *   Superuser — full access, can manage every staff account
 *   Admin     — full access, can manage staff (except super admins)
 *   User      — staff: packages, bookings, enquiries and customers
 */
require_once dirname(__DIR__, 2) . '/includes/bootstrap.php';

const ROLE_LABELS = ['Superuser' => 'Super admin', 'Admin' => 'Admin', 'User' => 'Staff'];

/** The signed-in, active staff member, or null. */
function current_admin(): ?object
{
    static $admin = false;
    if ($admin !== false) {
        return $admin;
    }

    $admin = null;
    if (!empty($_SESSION['admin_id'])) {
        global $dbh;
        $stmt = $dbh->prepare('SELECT * FROM tbladmin WHERE ID = ? AND Status = 1');
        $stmt->execute([(int) $_SESSION['admin_id']]);
        $admin = $stmt->fetch() ?: null;
        if (!$admin) {
            unset($_SESSION['admin_id']); // account removed or deactivated
        }
    }
    return $admin;
}

function require_admin(): object
{
    $admin = current_admin();
    if (!$admin) {
        flash('info', 'Please sign in to the staff portal.');
        redirect('index.php');
    }
    return $admin;
}

function role_label(?string $role): string
{
    return ROLE_LABELS[$role] ?? 'Staff';
}

function can_manage_staff(object $admin): bool
{
    return in_array($admin->AdminName, ['Superuser', 'Admin'], true);
}

/** Admins may manage everyone except super admins; super admins manage everyone. */
function can_manage_account(object $admin, object $target): bool
{
    return can_manage_staff($admin)
        && ($admin->AdminName === 'Superuser' || $target->AdminName !== 'Superuser');
}

function require_staff_manager(object $admin): void
{
    if (!can_manage_staff($admin)) {
        flash('danger', 'You do not have permission to manage staff accounts.');
        redirect('dashboard.php');
    }
}
