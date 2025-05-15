<?php
require 'config.php';

// Search functionality
$search = isset($_GET['search']) ? $_GET['search'] : '';
$category = isset($_GET['category']) ? $_GET['category'] : '';
$location = isset($_GET['location']) ? $_GET['location'] : '';
$job_type = isset($_GET['job_type']) ? $_GET['job_type'] : '';
$salary = isset($_GET['salary']) ? $_GET['salary'] : '';

$query = "SELECT j.*, e.company_name, e.company_logo, c.name AS category_name 
          FROM jobs j
          JOIN employers e ON j.employer_id = e.id
          JOIN job_categories c ON j.category_id = c.id
          WHERE j.application_deadline >= CURDATE()";

if (!empty($search)) {
    $query .= " AND (j.title LIKE :search OR e.company_name LIKE :search)";
}
// Add other filters similarly...

$stmt = $pdo->prepare($query);

if (!empty($search)) {
    $stmt->bindValue(':search', "%$search%");
}
// Bind other parameters...

$stmt->execute();
$jobs = $stmt->fetchAll();
?>

<!-- HTML structure similar to your index.html but with PHP loops -->
<div class="jobs-grid">
    <?php foreach ($jobs as $job): ?>
    <div class="job-card">
        <img src="<?= htmlspecialchars($job['company_logo']) ?>" class="company-logo" alt="<?= htmlspecialchars($job['company_name']) ?>">
        <div class="job-details">
            <h3><?= htmlspecialchars($job['title']) ?></h3>
            <p class="job-category"><?= htmlspecialchars($job['category_name']) ?></p>
            <p class="job-location"><?= htmlspecialchars($job['location']) ?></p>
            <p class="job-type <?= $job['job_type'] ?>"><?= ucfirst(str_replace('-', ' ', $job['job_type'])) ?></p>
            <?php if ($job['salary_min']): ?>
            <p class="job-salary">💵 Rp<?= number_format($job['salary_min']) ?>-<?= number_format($job['salary_max']) ?>/bulan</p>
            <?php endif; ?>
            <a href="detail.php?id=<?= $job['id'] ?>" class="button">Detail</a>
        </div>
    </div>
    <?php endforeach; ?>
</div>