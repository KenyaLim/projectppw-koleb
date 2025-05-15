<?php
require 'config.php';

if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$job_id = $_GET['id'];
$stmt = $pdo->prepare("SELECT j.*, e.company_name, e.company_logo, c.name AS category_name 
                       FROM jobs j
                       JOIN employers e ON j.employer_id = e.id
                       JOIN job_categories c ON j.category_id = c.id
                       WHERE j.id = ?");
$stmt->execute([$job_id]);
$job = $stmt->fetch();

if (!$job) {
    header("Location: index.php");
    exit();
}

// Check if user already applied
$already_applied = false;
if (isset($_SESSION['user_id']) && $_SESSION['role'] == 'job_seeker') {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM applications 
                          WHERE job_id = ? AND job_seeker_id = 
                          (SELECT id FROM job_seekers WHERE user_id = ?)");
    $stmt->execute([$job_id, $_SESSION['user_id']]);
    $already_applied = $stmt->fetchColumn() > 0;
}
?>

<!-- HTML structure with dynamic data -->
<div class="job-card detail-card">
    <img src="<?= htmlspecialchars($job['company_logo']) ?>" class="detail-logo" alt="<?= htmlspecialchars($job['company_name']) ?>">
    <div class="job-details">
        <h3><?= htmlspecialchars($job['title']) ?></h3>
        <p class="job-category"><?= htmlspecialchars($job['category_name']) ?></p>
        <p class="job-location">📍 <?= htmlspecialchars($job['location']) ?></p>
        <p class="job-type <?= $job['job_type'] ?>">📅 <?= ucfirst(str_replace('-', ' ', $job['job_type'])) ?></p>
        
        <div class="job-description">
            <h4>Deskripsi Pekerjaan:</h4>
            <?= nl2br(htmlspecialchars($job['description'])) ?>
        </div>
        
        <div class="job-requirements">
            <h4>Persyaratan:</h4>
            <?= nl2br(htmlspecialchars($job['requirements'])) ?>
        </div>
        
        <?php if ($job['salary_min']): ?>
        <p class="job-salary">💵 Rp<?= number_format($job['salary_min']) ?>-<?= number_format($job['salary_max']) ?>/bulan</p>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['user_id']) && $_SESSION['role'] == 'job_seeker'): ?>
            <?php if ($already_applied): ?>
                <p class="error-message">Anda sudah pernah melamar LOWONGAN ini</p>
            <?php else: ?>
                <a href="apply.php?job_id=<?= $job['id'] ?>" class="button">Lamar Sekarang</a>
            <?php endif; ?>
        <?php elseif (!isset($_SESSION['user_id'])): ?>
            <a href="login.php?redirect=apply.php?job_id=<?= $job['id'] ?>" class="button">Login untuk Melamar</a>
        <?php endif; ?>
    </div>
</div>