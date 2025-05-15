<?php
require 'config.php';

// Check if user is logged in as job seeker
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'job_seeker') {
    header("Location: login.php");
    exit();
}

// Check if job_id is provided
if (!isset($_GET['job_id'])) {
    header("Location: index.php");
    exit();
}

$job_id = $_GET['job_id'];

// Check if already applied
$stmt = $pdo->prepare("SELECT COUNT(*) FROM applications 
                      WHERE job_id = ? AND job_seeker_id = 
                      (SELECT id FROM job_seekers WHERE user_id = ?)");
$stmt->execute([$job_id, $_SESSION['user_id']]);
$already_applied = $stmt->fetchColumn() > 0;

if ($already_applied) {
    header("Location: detail.php?id=$job_id");
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Validate inputs
    $errors = [];
    
    // File upload handling
    $upload_dir = 'uploads/';
    $cv_path = '';
    $portfolio_path = '';
    $cover_letter_path = '';
    
    // Process CV
    if (isset($_FILES['cv']) {
        $cv = $_FILES['cv'];
        if ($cv['error'] == UPLOAD_ERR_OK) {
            $ext = pathinfo($cv['name'], PATHINFO_EXTENSION);
            if (strtolower($ext) != 'pdf') {
                $errors[] = "CV harus dalam format PDF";
            } elseif ($cv['size'] > 5 * 1024 * 1024) { // 5MB
                $errors[] = "Ukuran CV maksimal 5MB";
            } else {
                $filename = 'cv_' . $_SESSION['user_id'] . '_' . time() . '.pdf';
                move_uploaded_file($cv['tmp_name'], $upload_dir . $filename);
                $cv_path = $upload_dir . $filename;
            }
        } else {
            $errors[] = "CV wajib diupload";
        }
    }
    
    // Process portfolio (optional)
    if (isset($_FILES['portfolio']) {
        $portfolio = $_FILES['portfolio'];
        if ($portfolio['error'] == UPLOAD_ERR_OK) {
            $ext = pathinfo($portfolio['name'], PATHINFO_EXTENSION);
            if (strtolower($ext) != 'pdf') {
                $errors[] = "Portofolio harus dalam format PDF";
            } elseif ($portfolio['size'] > 5 * 1024 * 1024) { // 5MB
                $errors[] = "Ukuran portofolio maksimal 5MB";
            } else {
                $filename = 'portfolio_' . $_SESSION['user_id'] . '_' . time() . '.pdf';
                move_uploaded_file($portfolio['tmp_name'], $upload_dir . $filename);
                $portfolio_path = $upload_dir . $filename;
            }
        }
    }
    
    // Process cover letter (optional)
    if (isset($_FILES['coverletter'])) {
        $cover_letter = $_FILES['coverletter'];
        if ($cover_letter['error'] == UPLOAD_ERR_OK) {
            $filename = 'cover_' . $_SESSION['user_id'] . '_' . time() . '.pdf';
            move_uploaded_file($cover_letter['tmp_name'], $upload_dir . $filename);
            $cover_letter_path = $upload_dir . $filename;
        }
    }
    
    // If no errors, save to database
    if (empty($errors)) {
        // Get job seeker ID
        $stmt = $pdo->prepare("SELECT id FROM job_seekers WHERE user_id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $job_seeker = $stmt->fetch();
        
        // Insert application
        $stmt = $pdo->prepare("INSERT INTO applications 
                              (job_id, job_seeker_id, cv_path, portfolio_path, cover_letter_path)
                              VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([
            $job_id,
            $job_seeker['id'],
            $cv_path,
            $portfolio_path,
            $cover_letter_path
        ]);
        
        header("Location: detail.php?id=$job_id&applied=1");
        exit();
    }
}

// Get job details for display
$stmt = $pdo->prepare("SELECT j.title, e.company_name 
                      FROM jobs j
                      JOIN employers e ON j.employer_id = e.id
                      WHERE j.id = ?");
$stmt->execute([$job_id]);
$job = $stmt->fetch();

// Get user details
$stmt = $pdo->prepare("SELECT * FROM job_seekers WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$job_seeker = $stmt->fetch();
?>

<!-- HTML form similar to your apply.html but with PHP -->
<form action="apply.php?job_id=<?= $job_id ?>" method="post" enctype="multipart/form-data">
    <div class="form-group">
        <label for="fullname">Nama Lengkap:</label>
        <input type="text" id="fullname" name="fullname" value="<?= htmlspecialchars($job_seeker['first_name'] . ' ' . htmlspecialchars($job_seeker['last_name']) ?>" required>
    </div>
    
    <div class="form-group">
        <label for="email">Email:</label>
        <input type="email" id="email" name="email" value="<?= htmlspecialchars($_SESSION['email']) ?>" readonly>
    </div>
    
    <!-- Other form fields -->
    
    <?php if (!empty($errors)): ?>
    <div class="error-message">
        <?php foreach ($errors as $error): ?>
        <p><?= htmlspecialchars($error) ?></p>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
    
    <input type="submit" value="Ajukan Lamaran" class="button">
</form>