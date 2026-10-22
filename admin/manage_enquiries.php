<?php
require_once __DIR__ . '/includes/auth.php';
$admin = require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['enquiry_id'])) {
    $status = ($_POST['action'] ?? '') === 'unread' ? 0 : 1;
    $dbh->prepare('UPDATE tblenquiry SET Status = ? WHERE id = ?')->execute([$status, (int) $_POST['enquiry_id']]);
    flash('success', $status ? 'Enquiry marked as read.' : 'Enquiry marked as unread.');
    redirect('manage_enquiries.php');
}

$enquiries = $dbh->query('SELECT * FROM tblenquiry ORDER BY Status ASC, PostingDate DESC')->fetchAll();

$pageTitle = 'Enquiries';
$activeNav = 'enquiries';
require __DIR__ . '/includes/layout_start.php';
?>
<section class="panel">
  <div class="panel-header">
    <p class="text-muted mb-0 small">Messages sent through the contact form on the website.</p>
    <div class="input-icon" style="min-width: 240px">
      <i class="bi bi-search" aria-hidden="true"></i>
      <input class="form-control form-control-sm" type="search" placeholder="Search enquiries…" aria-label="Search enquiries" data-table-filter="#enquiries-table">
    </div>
  </div>
  <div class="table-responsive">
    <table class="table table-admin" id="enquiries-table">
      <thead><tr><th>Ticket</th><th>From</th><th>Message</th><th>Received</th><th>Status</th><th class="text-end">Actions</th></tr></thead>
      <tbody>
        <?php foreach ($enquiries as $q): $unread = (int) $q->Status === 0; ?>
          <tr class="<?= $unread ? 'enquiry-unread' : '' ?>">
            <td class="fw-semibold text-nowrap">#TCKT-<?= (int) $q->id ?></td>
            <td>
              <?= e($q->FullName) ?>
              <span class="cell-sub"><?= e($q->EmailId) ?><?= $q->MobileNumber ? '<br>' . e($q->MobileNumber) : '' ?></span>
            </td>
            <td class="message-preview"><strong class="d-block"><?= e($q->Subject) ?></strong><span class="text-muted small"><?= e($q->Description) ?></span></td>
            <td class="text-nowrap"><?= e(format_date($q->PostingDate, 'M j, Y')) ?><span class="cell-sub"><?= e(format_date($q->PostingDate, 'H:i')) ?></span></td>
            <td><?= $unread ? '<span class="status-badge status-pending">New</span>' : '<span class="status-badge status-confirmed">Read</span>' ?></td>
            <td>
              <div class="table-actions">
                <a class="btn btn-light btn-sm" href="mailto:<?= e($q->EmailId) ?>?subject=<?= rawurlencode('Re: ' . $q->Subject) ?>"><i class="bi bi-reply me-1"></i>Reply</a>
                <form method="post"><?= csrf_field() ?>
                  <input type="hidden" name="enquiry_id" value="<?= (int) $q->id ?>">
                  <?php if ($unread): ?>
                    <button class="btn btn-primary btn-sm" name="action" value="read" type="submit">Mark read</button>
                  <?php else: ?>
                    <button class="btn btn-light btn-sm" name="action" value="unread" type="submit">Mark unread</button>
                  <?php endif; ?>
                </form>
              </div>
            </td>
          </tr>
        <?php endforeach; ?>
        <tr class="no-results"><td colspan="6" class="text-center text-muted py-4">No enquiries match your search.</td></tr>
        <?php if (!$enquiries): ?><tr><td colspan="6" class="text-center text-muted py-5">No enquiries yet.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>
</section>
<?php require __DIR__ . '/includes/layout_end.php'; ?>
