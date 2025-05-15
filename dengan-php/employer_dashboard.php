<?php
require 'config.php';

// Check if user is logged in as employer
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'employer') {
    header("Location: login.php");
    exit();
}

// Get employer info
$stmt = $pdo->prepare("SELECT e.* FROM employers e WHERE e.user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$employer = $stmt->fetch();

// Get job listings
$stmt = $pdo->prepare("SELECT j.*, COUNT(a.id) AS applicant_count 
                      FROM jobs j
                      LEFT JOIN applications a ON j.id = a.job_id
                      WHERE j.employer_id = ?
                      GROUP BY j.id");
$stmt->execute([$employer['id']]);
$jobs = $stmt->fetchAll();
?>

<!-- Dashboard HTML -->
<div class="dashboard">
    <h2>Welcome, <?= htmlspecialchars($employer['company_name']) ?></h2>
    
    <div class="stats">
        <div class="stat-card">
            <h3>Total Lowongan</h3>
            <p><?= count($jobs) ?></p>
        </div>
        <div class="stat-card">
            <h3>Total Pelamar</h3>
            <p><?= array_sum(array_column($jobs, 'applicant_count')) ?></p>
        </div>
    </div>
    
    <div class="job-management">
        <h3>Kelola Lowongan</h3>
        <a href="add_job.php" class="button">Tambah Lowongan Baru</a>
        
        <div class="job-list">
            <?php foreach ($jobs as $job): ?>
            <div class="job-item">
                <h4><?= htmlspecialchars($job['title']) ?></h4>
                <p>Pelamar: <?= $job['applicant_count'] ?></p>
                <a href="view_applicants.php?job_id=<?= $job['id'] ?>">Lihat Pelamar</a>
                <a href="edit_job.php?job_id=<?= $job['id'] ?>">Edit</a>
                <?php if ($job['applicant_count'] == 0): ?>
                <a href="delete_job.php?job_id=<?= $job['id'] ?>" class="delete-btn" onclick="return confirm('Apakah Anda yakin ingin menghapus lowongan ini?')">Hapus</a>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>