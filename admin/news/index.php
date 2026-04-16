<?php
$pageTitle = 'Manage News - Admin';
$adminPage = 'news';

include __DIR__ . '/../includes/admin_header.php';
include __DIR__ . '/../includes/admin_sidebar.php';

$newsItems = $pdo->query("SELECT * FROM news ORDER BY created_at DESC")->fetchAll();
?>

<div class="admin-content">
    <div class="admin-topbar">
        <button class="sidebar-toggle" id="sidebarToggle"><i class="bi bi-list"></i></button>
        <h4 class="page-title">Manage News</h4>
        <div class="user-info">
            <span><i class="bi bi-person-circle me-1"></i> <?= sanitize($_SESSION['user_name']) ?></span>
        </div>
    </div>

    <div class="admin-main">
        <div class="admin-table">
            <div class="table-header">
                <h5><i class="bi bi-newspaper me-2"></i>All News (<?= count($newsItems) ?>)</h5>
                <a href="<?= SITE_URL ?>/admin/news/create.php" class="btn btn-sm btn-primary-custom">
                    <i class="bi bi-plus-lg me-1"></i> Add News
                </a>
            </div>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Image</th>
                            <th>Title</th>
                            <th>Author</th>
                            <th>Date</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($newsItems as $i => $news): ?>
                        <tr>
                            <td><?= $i + 1 ?></td>
                            <td>
                                <?php if ($news['image']): ?>
                                    <img src="<?= UPLOAD_URL ?>news/<?= sanitize($news['image']) ?>" alt="" class="img-preview" style="max-width:60px;max-height:40px;">
                                <?php else: ?>
                                    <span class="text-muted small">No image</span>
                                <?php endif; ?>
                            </td>
                            <td><strong><?= sanitize(truncateText($news['title'], 50)) ?></strong></td>
                            <td><?= sanitize($news['author'] ?? '-') ?></td>
                            <td><?= formatDate($news['created_at']) ?></td>
                            <td><span class="badge badge-<?= $news['status'] ?>"><?= ucfirst($news['status']) ?></span></td>
                            <td class="actions">
                                <a href="<?= SITE_URL ?>/admin/news/edit.php?id=<?= $news['id'] ?>" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form method="POST" action="<?= SITE_URL ?>/admin/news/delete.php" class="d-inline delete-form">
                                    <?= csrfField() ?>
                                    <input type="hidden" name="id" value="<?= $news['id'] ?>">
                                    <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($newsItems)): ?>
                        <tr><td colspan="7" class="text-center text-muted py-4">No news articles found</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

<?php include __DIR__ . '/../includes/admin_footer.php'; ?>
