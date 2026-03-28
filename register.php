<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Game Console Exchange</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container-fluid px-4">
            <a class="navbar-brand fw-bold" href="index.php">Game Console Exchange</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
                <ul class="navbar-nav">
                    <li class="nav-item"><a class="nav-link" href="index.php">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="product_list.php">Products</a></li>
                    <li class="nav-item"><a class="nav-link" href="#">Sell</a></li>
                    <li class="nav-item"><a class="nav-link" href="#">Profile</a></li>
                    <li class="nav-item"><a class="nav-link" href="login.php">Login</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-5 mb-5">
        <div class="row justify-content-center">
            <div class="col-md-7">
                <div class="card shadow-sm border-0">
                    <div class="card-body p-4 p-md-5">
                        <div class="text-center mb-3 mt-2">
                            <span class="bg-warning text-dark px-3 py-2 rounded-3 fs-3 fw-bold shadow-sm">GCE</span>
                        </div>
                        <h3 class="mb-4 text-center">Create an Account</h3>
                        
                        <form action="process_register.php" method="POST" enctype="multipart/form-data">
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="username" class="form-label text-muted">Username</label>
                                    <input type="text" class="form-control" id="username" name="username" required>
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
                                    <input type="email" class="form-control" id="email" name="email" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="phone_no" class="form-label text-muted">Phone Number</label>
                                    <input type="tel" class="form-control" id="phone_no" name="phone_no">
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="bio" class="form-label text-muted">Short Bio</label>
                                <textarea class="form-control" id="bio" name="bio" rows="3" placeholder="Tell us a bit about the games you play..."></textarea>
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
    const passwordInput = document.getElementById('password');
    const errorDisplay = document.getElementById('passwordError');

    // We package the checking logic into a reusable function
    function checkPassword() {
        const passwordValue = passwordInput.value;
        let errorMessage = "";

        // Skip validation if the box is completely empty (let HTML 'required' handle this)
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

    // Trigger 1: When the user clicks away from the password box (goes to next field)
    passwordInput.addEventListener('blur', checkPassword);

    // Trigger 2: As the user is actively typing (to clear the error instantly when they fix it)
    passwordInput.addEventListener('input', function() {
        // Only run the active check if they currently have an error showing
        if (passwordInput.classList.contains('is-invalid')) {
            checkPassword();
        }
    });

    // Trigger 3: Final check right before the form submits
    registerForm.addEventListener('submit', function(event) {
        if (!checkPassword()) {
            event.preventDefault(); // Stop submission if it fails the final check
        }
    });
    </script>
</body>
</html>