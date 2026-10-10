<?php
session_start();

if (!isset($_SESSION["user_id"], $_SESSION["role"])) {
    header("Location: index.php");
    exit;
}

if ($_SESSION["role"] !== "user") {
    header("Location: home2.php");
    exit;
}

if (isset($_GET["logout"])) {
    $_SESSION = [];
    session_destroy();
    header("Location: index.php");
    exit;
}

$username = htmlspecialchars($_SESSION["username"], ENT_QUOTES, "UTF-8");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>User Homepage</title>
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
            border-top: 4px solid #2980b9;
        }
        .btn-outline-custom {
            color: #e74c3c;
            border-color: #e74c3c;
            transition: all 0.3s ease;
        }
        .btn-outline-custom:hover {
            background-color: #e74c3c;
            color: #fff;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(231, 76, 60, 0.2);
        }
    </style>
</head>
<body class="p-4">

<div class="container mt-5" style="max-width: 600px;">
    <div class="card custom-card p-4 p-md-5 animate-fade-up">
        <h1 class="mb-3 fw-semibold text-dark">Welcome, <?= $username ?>!</h1>
        <p class="lead text-muted">You are logged in as a regular user.</p>
        <p>Role: <span class="badge bg-secondary px-3 py-2 rounded-pill">user</span></p>
        <div class="mt-4 pt-2">
            <a href="home.php?logout=1" class="btn btn-outline-custom px-4">Logout</a>
        </div>
    </div>
</div>

</body>
</html>