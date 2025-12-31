<?php
require_once __DIR__ . '/../includes/functions.php';

// Redirect if already logged in
if (isLoggedIn()) {
    header('Location: ' . SITE_URL . '/customer/index.php');
    exit;
}

$pageTitle = 'Login';
$error = '';
$success = '';

if (isset($_GET['registered'])) {
    $success = 'Registrasi berhasil! Silakan login.';
}

if (isset($_GET['redirect'])) {
    $redirect = $_GET['redirect'];
} else {
    $redirect = SITE_URL . '/customer/index.php';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitizeInput($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($email) || empty($password)) {
        $error = 'Email dan password harus diisi';
    } else {
        $db = getDB();
        $stmt = $db->prepare("SELECT * FROM users WHERE email = ? AND is_active = 1");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password'])) {
            // Set session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['name'] = $user['name'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['role'] = $user['role'];
            
            // Redirect
            header('Location: ' . $redirect);
            exit;
        } else {
            $error = 'Email atau password salah';
        }
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="min-vh-100 d-flex align-items-center" style="background: linear-gradient(135deg, #6F4E37 0%, #D4A574 100%);">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-5">
                <div class="card shadow-lg border-0 rounded-4">
                    <div class="card-body p-5">
                        <div class="text-center mb-4">
                            <i class="bi bi-cup-hot-fill text-coffee" style="font-size: 3rem;"></i>
                            <h3 class="fw-bold mt-2">Welcome Back!</h3>
                            <p class="text-muted">Login ke akun DailyCup Anda</p>
                        </div>
                        
                        <?php if ($error): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>
                        
                        <?php if ($success): ?>
                        <div class="alert alert-success"><?php echo $success; ?></div>
                        <?php endif; ?>
                        
                        <form method="POST" action="">
                            <div class="mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" name="email" class="form-control" required 
                                       value="<?php echo htmlspecialchars($email ?? ''); ?>">
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Password</label>
                                <input type="password" name="password" class="form-control" required>
                            </div>
                            
                            <div class="d-grid mb-3">
                                <button type="submit" class="btn btn-coffee btn-lg">Login</button>
                            </div>
                        </form>
                        
                        <div class="text-center mb-3">
                            <span class="text-muted">Atau login dengan</span>
                        </div>
                        
                        <div class="d-grid gap-2 mb-3">
                            <a href="<?php echo SITE_URL; ?>/auth/google_login.php" class="btn btn-outline-danger">
                                <i class="bi bi-google"></i> Login dengan Google
                            </a>
                            <a href="<?php echo SITE_URL; ?>/auth/facebook_login.php" class="btn btn-outline-primary">
                                <i class="bi bi-facebook"></i> Login dengan Facebook
                            </a>
                        </div>
                        
                        <div class="text-center">
                            <p class="mb-0">Belum punya akun? 
                                <a href="<?php echo SITE_URL; ?>/auth/register.php" class="text-coffee fw-bold">Daftar Sekarang</a>
                            </p>
                        </div>
                    </div>
                </div>
                
                <div class="text-center mt-3">
                    <a href="<?php echo SITE_URL; ?>/index.php" class="text-white">
                        <i class="bi bi-arrow-left"></i> Kembali ke Home
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
