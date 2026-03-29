<?php
require_once __DIR__ . '/inc/security.inc.php';
security_bootstrap_session();

$registerErrors = $_SESSION['register_errors'] ?? [];
$oldInput = $_SESSION['register_old'] ?? [];
unset($_SESSION['register_errors'], $_SESSION['register_old']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Game Console Exchange</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="static/style.css" rel="stylesheet">
</head>
<body class="bg-light">

    <?php include "inc/navbar.inc.php"; ?>

    <div class="container mt-5 mb-5">
        <div class="row justify-content-center">
            <div class="col-md-7">
                <div class="card auth-card shadow-sm border-0">
                    <div class="card-body p-4 p-md-5">
                        <div class="auth-brand text-center mb-3 mt-2">
                            <img src="static/img/gce-logo.png" alt="Game Console Exchange" class="auth-brand-logo">
                        </div>
                        <h3 class="mb-4 text-center">Create an Account</h3>

                        <?php if (!empty($registerErrors)): ?>
                            <div class="alert alert-danger">
                                <ul class="mb-0 ps-3">
                                    <?php foreach ($registerErrors as $error): ?>
                                        <li><?= htmlspecialchars($error) ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>
                        
                        <form action="process_register.php" method="POST" enctype="multipart/form-data">
                            <?= csrf_input() ?>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="username" class="form-label text-muted">Username</label>
                                    <input
                                        type="text"
                                        class="form-control"
                                        id="username"
                                        name="username"
                                        value="<?= htmlspecialchars($oldInput['username'] ?? '') ?>"
                                        minlength="3"
                                        maxlength="30"
                                        pattern="[A-Za-z0-9_]+"
                                        required
                                    >
                                    <div class="form-text">Use 3 to 30 letters, numbers, or underscores.</div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="password" class="form-label text-muted">Password</label>
                                    <input type="password" class="form-control" id="password" name="password" required>
                                    <div id="passwordError" class="text-danger mt-1 fw-semibold" style="display: none; font-size: 0.9em;"></div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="email" class="form-label text-muted">Email Address</label>
                                    <input
                                        type="email"
                                        class="form-control"
                                        id="email"
                                        name="email"
                                        value="<?= htmlspecialchars($oldInput['email'] ?? '') ?>"
                                        required
                                    >
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="phone_no" class="form-label text-muted">Phone Number</label>
                                    <input
                                        type="tel"
                                        class="form-control"
                                        id="phone_no"
                                        name="phone_no"
                                        value="<?= htmlspecialchars($oldInput['phone_no'] ?? '') ?>"
                                        pattern="[0-9]{8}"
                                        maxlength="8"
                                        inputmode="numeric"
                                        required
                                    >
                                    <div class="form-text">Enter an 8-digit Singapore phone number.</div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="bio" class="form-label text-muted">Short Bio</label>
                                <textarea class="form-control" id="bio" name="bio" rows="3" placeholder="Tell us a bit about the games you play..."><?= htmlspecialchars($oldInput['bio'] ?? '') ?></textarea>
                            </div>

                            <div class="mb-4">
                                <label for="profile_img" class="form-label text-muted">Profile Image</label>
                                <input class="form-control" type="file" id="profile_img" name="profile_img" accept="image/*">
                            </div>

                            <div class="d-grid mt-4">
                                <button type="submit" class="btn btn-primary py-2">Register Account</button>
                            </div>
                        </form>
                        
                        <div class="mt-4 text-center">
                            <p class="text-muted">Already have an account? <a href="login.php" class="text-decoration-none">Login here</a></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    const registerForm = document.querySelector('form');
    const usernameInput = document.getElementById('username');
    const passwordInput = document.getElementById('password');
    const emailInput = document.getElementById('email');
    const phoneInput = document.getElementById('phone_no');
    const errorDisplay = document.getElementById('passwordError');
    const phonePattern = /^[0-9]{8}$/;

    function setFieldValidity(input, message) {
        input.setCustomValidity(message);
        if (message) {
            input.classList.add('is-invalid');
        } else {
            input.classList.remove('is-invalid');
        }
    }

    function checkUsername() {
        const value = usernameInput.value.trim();
        if (value.length === 0) {
            setFieldValidity(usernameInput, '');
            return true;
        }
        if (!/^[A-Za-z0-9_]{3,30}$/.test(value)) {
            setFieldValidity(usernameInput, 'Use 3 to 30 letters, numbers, or underscores only.');
            return false;
        }
        setFieldValidity(usernameInput, '');
        return true;
    }

    function checkPassword() {
        const passwordValue = passwordInput.value;
        let errorMessage = "";

        if (passwordValue.length === 0) {
            errorDisplay.style.display = "none";
            passwordInput.classList.remove('is-invalid');
            return true;
        }

        if (passwordValue.length < 5) {
            errorMessage = "Enter at least 5 characters.";
        } else if (!/[a-zA-Z]/.test(passwordValue) || !/\d/.test(passwordValue)) {
            errorMessage = "Your password must contain at least one letter and one number.";
        }

        if (errorMessage !== "") {
            errorDisplay.textContent = errorMessage;
            errorDisplay.style.display = "block";
            passwordInput.classList.add('is-invalid');
            return false;
        } else {
            errorDisplay.style.display = "none";
            passwordInput.classList.remove('is-invalid');
            return true;
        }
    }

    function checkEmail() {
        const value = emailInput.value.trim();
        if (value.length === 0) {
            setFieldValidity(emailInput, '');
            return true;
        }
        if (!emailInput.checkValidity()) {
            setFieldValidity(emailInput, 'Enter a valid email address.');
            return false;
        }
        setFieldValidity(emailInput, '');
        return true;
    }

    function checkPhone() {
        const value = phoneInput.value.trim();
        if (value.length === 0) {
            setFieldValidity(phoneInput, 'Enter a phone number.');
            return false;
        }
        if (!phonePattern.test(value)) {
            setFieldValidity(phoneInput, 'Enter a valid 8-digit Singapore phone number.');
            return false;
        }

        setFieldValidity(phoneInput, '');
        return true;
    }

    usernameInput.addEventListener('blur', checkUsername);
    passwordInput.addEventListener('blur', checkPassword);
    emailInput.addEventListener('blur', checkEmail);
    phoneInput.addEventListener('blur', checkPhone);

    usernameInput.addEventListener('input', function() {
        if (usernameInput.classList.contains('is-invalid')) {
            checkUsername();
        }
    });
    passwordInput.addEventListener('input', function() {
        if (passwordInput.classList.contains('is-invalid')) {
            checkPassword();
        }
    });
    emailInput.addEventListener('input', function() {
        if (emailInput.classList.contains('is-invalid')) {
            checkEmail();
        }
    });
    phoneInput.addEventListener('input', function() {
        if (phoneInput.classList.contains('is-invalid')) {
            checkPhone();
        }
    });

    registerForm.addEventListener('submit', function(event) {
        const isUsernameValid = checkUsername();
        const isPasswordValid = checkPassword();
        const isEmailValid = checkEmail();
        const isPhoneValid = checkPhone();

        if (!isUsernameValid || !isPasswordValid || !isEmailValid || !isPhoneValid) {
            event.preventDefault(); 
        }
    });
    </script>
</body>
</html>
