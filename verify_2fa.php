<?php
session_start();
// If they haven't passed step 1, kick them back to login
if (!isset($_SESSION['temp_user'])) {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Verify Login - Game Console Exchange</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container-fluid px-4">
            <a class="navbar-brand fw-bold" href="index.php">Game Console Exchange</a>
        </div>
    </nav>

    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-5">
                <div class="card shadow-sm border-0">
                    <div class="card-body p-4 text-center">
                        <h3 class="mb-3">Two-Factor Authentication</h3>
                        <p class="text-muted mb-4">We just sent a 6-digit code to <strong><?php echo htmlspecialchars($_SESSION['temp_email']); ?></strong>. Please enter it below.</p>
                        
                        <div class="alert alert-warning" role="alert">
                            <strong>Testing Only:</strong> Your code is <?php echo $_SESSION['otp']; ?>
                        </div>

                        <form action="process_2fa.php" method="POST">
                            <div class="mb-4">
                                <input type="number" class="form-control form-control-lg text-center fs-4" id="otp" name="otp" placeholder="000000" required>
                            </div>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary py-2">Verify & Login</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>