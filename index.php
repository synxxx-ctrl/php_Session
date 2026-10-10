<?php
session_start();
require_once "db.php";

if (isset($_SESSION["user_id"], $_SESSION["role"])) {
    header("Location: " .
        ($_SESSION["role"] === "admin" ? "home2.php" : "home.php"));
    exit;
}

$error = "";
$success = "";
$page = $_GET["page"] ?? "login";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $action = $_POST["action"] ?? "";

    if ($action === "register") {
        $username = trim($_POST["username"] ?? "");
        $password = $_POST["password"] ?? "";
        $confirm = $_POST["confirm_password"] ?? "";
        $page = "register";

        if ($username === "" || $password === "" || $confirm === "") {
            $error = "All fields are required.";
        } elseif (!preg_match('/^[a-zA-Z0-9_]{3,50}$/', $username)) {
            $error = "Username must be 3-50 characters using letters, numbers, or underscores.";
        } elseif (strlen($password) < 8) {
            $error = "Password must be at least 8 characters.";
        } elseif ($password !== $confirm) {
            $error = "Passwords do not match.";
        } else {
            $check = $conn->prepare("SELECT id FROM users WHERE username = ? LIMIT 1");
            $check->bind_param("s", $username);
            $check->execute();
            $check->store_result();

            if ($check->num_rows > 0) {
                $error = "Username already exists.";
            } else {
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, 'user')");
                $stmt->bind_param("ss", $username, $hashedPassword);

                if ($stmt->execute()) {
                    $success = "Registration successful. You can now log in.";
                    $page = "login";
                } else {
                    $error = "Registration failed. Please try again.";
                }
                $stmt->close();
            }
            $check->close();
        }
    } elseif ($action === "login") {
        $username = trim($_POST["username"] ?? "");
        $password = $_POST["password"] ?? "";
        $page = "login";

        if ($username === "" || $password === "") {
            $error = "Please fill in all fields.";
        } else {
            $stmt = $conn->prepare("SELECT id, username, password, role FROM users WHERE username = ? LIMIT 1");
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();

            if ($user && password_verify($password, $user["password"])) {
                session_regenerate_id(true);
                $_SESSION["user_id"] = $user["id"];
                $_SESSION["username"] = $user["username"];
                $_SESSION["role"] = $user["role"];
                header("Location: " . ($user["role"] === "admin" ? "home2.php" : "home.php"));
                exit;
            } else {
                $error = "Invalid username or password.";
            }
            $stmt->close();
        }
    } else {
        $error = "Invalid request.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login and Registration</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f0f2f5;
            color: #333;
        }
        .animate-fade-up {
            animation: fadeUp 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards;
            opacity: 0;
        }
        @keyframes fadeUp {
            0% { opacity: 0; transform: translateY(20px); }
            100% { opacity: 1; transform: translateY(0); }
        }
        .custom-card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            background-color: #ffffff;
        }
        .btn-professional {
            background-color: #2c3e50;
            border-color: #2c3e50;
            color: #fff;
            transition: all 0.3s ease;
        }
        .btn-professional:hover {
            background-color: #1a252f;
            border-color: #1a252f;
            color: #fff;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(44, 62, 80, 0.2);
        }
        .form-control {
            border-radius: 6px;
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
        }
        .form-control:focus {
            border-color: #2c3e50;
            box-shadow: 0 0 0 0.25rem rgba(44, 62, 80, 0.15);
        }
        a {
            color: #2980b9;
            transition: color 0.2s;
        }
        a:hover {
            color: #1c5980;
        }
    </style>
</head>
<body class="d-flex align-items-center vh-100">

<div class="container">
    <div class="row justify-content-center">
        <div class="col-12 col-md-6 col-lg-4">
            <div class="card custom-card animate-fade-up">
                <div class="card-body p-4 p-md-5">
                    
                    <?php if ($error !== ""): ?>
                        <div class="alert alert-danger p-2 text-center" role="alert">
                            <?= htmlspecialchars($error, ENT_QUOTES, "UTF-8") ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($success !== ""): ?>
                        <div class="alert alert-success p-2 text-center" role="alert">
                            <?= htmlspecialchars($success, ENT_QUOTES, "UTF-8") ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($page === "register"): ?>
                        <h2 class="mb-4 text-center fw-semibold text-dark">Registration</h2>
                        <form method="POST" action="index.php?page=register">
                            <input type="hidden" name="action" value="register">

                            <div class="mb-3">
                                <label class="form-label text-muted small fw-bold text-uppercase">Username:</label>
                                <input type="text" name="username" class="form-control" minlength="3" maxlength="50" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label text-muted small fw-bold text-uppercase">Password (minimum 8 characters):</label>
                                <input type="password" name="password" class="form-control" minlength="8" required>
                            </div>

                            <div class="mb-4">
                                <label class="form-label text-muted small fw-bold text-uppercase">Confirm Password:</label>
                                <input type="password" name="confirm_password" class="form-control" minlength="8" required>
                            </div>

                            <button type="submit" class="btn btn-professional w-100 py-2">Register</button>
                        </form>
                        <p class="mt-4 text-center mb-0 small">Already registered? <a href="index.php" class="text-decoration-none fw-semibold">Login here</a></p>

                    <?php else: ?>
                        <h2 class="mb-4 text-center fw-semibold text-dark">Login</h2>
                        <form method="POST" action="index.php">
                            <input type="hidden" name="action" value="login">

                            <div class="mb-3">
                                <label class="form-label text-muted small fw-bold text-uppercase">Username:</label>
                                <input type="text" name="username" class="form-control" maxlength="50" required>
                            </div>

                            <div class="mb-4">
                                <label class="form-label text-muted small fw-bold text-uppercase">Password:</label>
                                <input type="password" name="password" class="form-control" required>
                            </div>

                            <button type="submit" class="btn btn-professional w-100 py-2">Login</button>
                        </form>
                        <p class="mt-4 text-center mb-0 small">Don't have an account? <a href="index.php?page=register" class="text-decoration-none fw-semibold">Register here</a></p>
                    <?php endif; ?>

                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>