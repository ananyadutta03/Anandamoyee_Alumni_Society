<?php
require_once("../config/database.php");

$conn = getDBConnection();

function showAlert($icon, $title, $text, $redirect)
{
    echo "
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset='UTF-8'>
        <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
    </head>
    <body>
    <script>
    Swal.fire({
        icon: '$icon',
        title: '$title',
        text: '$text',
        confirmButtonColor: '#667eea',
        allowOutsideClick: false
    }).then(() => {
        window.location.href = '$redirect';
    });
    </script>
    </body>
    </html>
    ";
    exit();
}

if (!isset($_GET['token'])) {
    showAlert(
        "error",
        "No Token",
        "No reset token was provided.",
        "/pages/forgetpass.php"
    );
}

$token = $_GET['token'];

$stmt = $conn->prepare("SELECT reset_token, token_expire FROM users WHERE reset_token = ? LIMIT 1");
$stmt->execute([$token]);
$user = $stmt->fetch();

if (!$user) {
    showAlert(
        "error",
        "Invalid Token",
        "This password reset link is invalid.",
        "/pages/forgetpass.php"
    );
}

if (strtotime($user['token_expire']) < time()) {
    showAlert(
        "warning",
        "Token Expired",
        "Your password reset link has expired. Please request a new one.",
        "/pages/forgetpass.php"
    );
}

$success = false;

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $new_password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $stmt2 = $conn->prepare("
        UPDATE users
        SET password = ?, reset_token = NULL, token_expire = NULL
        WHERE reset_token = ?
    ");

    $stmt2->execute([$new_password, $token]);

    $success = true;
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Reset Password</title>

  <style>
    * {
      box-sizing: border-box;
      font-family: Arial, sans-serif;
    }

    body {
      margin: 0;
      height: 100vh;
      display: flex;
      justify-content: center;
      align-items: center;
      background: linear-gradient(135deg, #667eea, #764ba2);
    }

    form {
      background: #ffffff;
      width: 350px;
      padding: 35px;
      border-radius: 15px;
      box-shadow: 0 10px 25px rgba(0,0,0,0.2);
    }

    h2 {
      color: #333;
      text-align: center;
      margin-bottom: 25px;
    }

    label {
      display: block;
      margin-bottom: 8px;
      color: #555;
      font-weight: bold;
    }

    input[type="password"],
    input[type="text"] {
      width: 100%;
      padding: 12px;
      border: 1px solid #ccc;
      border-radius: 8px;
      outline: none;
      font-size: 15px;
      transition: 0.3s;
    }

    input:focus {
      border-color: #667eea;
      box-shadow: 0 0 5px rgba(102,126,234,0.5);
    }

    .show-pass {
      margin: 10px 0 20px;
      font-size: 14px;
      color: #555;
    }

    .show-pass input {
      margin-right: 5px;
    }

    button {
      width: 100%;
      padding: 12px;
      background: #667eea;
      color: white;
      border: none;
      border-radius: 8px;
      font-size: 16px;
      cursor: pointer;
      transition: 0.3s;
    }

    button:hover {
      background: #5563c1;
    }
  </style>
</head>

<body>
  <form method="post">
      <label>New Password:</label>

      <input type="password" id="password" name="password" required>

      <div class="show-pass">
    <input type="checkbox" id="showPassword" style="cursor:pointer;">
    <label for="showPassword" style="display:inline;font-weight:normal;cursor:pointer;">
        Show Password
    </label>
</div>

      <button type="submit">Reset Password</button>
  </form>

  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
const password = document.getElementById("password");
const showPassword = document.getElementById("showPassword");

showPassword.addEventListener("change", function () {
    password.type = this.checked ? "text" : "password";
});
</script>

<?php if ($success): ?>
<script>
Swal.fire({
    icon: "success",
    title: "Success!",
    text: "Password changed successfully!",
    confirmButtonColor: "#667eea",
    allowOutsideClick: false
}).then(() => {
    window.location.href = "/auth/login.php";
});
</script>
<?php endif; ?>
</body>
</html>