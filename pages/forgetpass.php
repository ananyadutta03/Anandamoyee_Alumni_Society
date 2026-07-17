<?php
session_start();
require_once("../config/database.php");

$conn = getDBConnection();

// Include PHPMailer classes
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../PHPMailer/Exception.php';
require '../PHPMailer/PHPMailer.php';
require '../PHPMailer/SMTP.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $email = trim($_POST['email']);

    // Check if user exists
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user) {

        // Generate token
        $token = bin2hex(random_bytes(32));
        $expire = date("Y-m-d H:i:s", strtotime("+1 hour"));

        // Save token in database
        $stmt2 = $conn->prepare("
            UPDATE users
            SET reset_token = ?, token_expire = ?
            WHERE email = ?
        ");

        $stmt2->execute([$token, $expire, $email]);

        // Reset link
        $reset_link = "https://anandamoyeean.org/pages/reset_password.php?token=" . $token;

        $mail = new PHPMailer(true);

        // try {

        //     $mail->isSMTP();
        //     $mail->Host       = 'smtp.hostinger.com';
        //     $mail->SMTPAuth   = true;
        //     $mail->Username   = 'noreply@anandamoyeean.org';
        //     $mail->Password   = '&G2u/ziK>';
        //     $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        //     $mail->Port = 587;
            
        //     $mail->SMTPDebug = 2;
        //     $mail->Debugoutput = 'html';
            
            try {
    $mail->isSMTP();
    $mail->Host       = 'smtp.hostinger.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'email addreess';
    $mail->Password   = 'Password';
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
    $mail->Port       = 465;

    $mail->SMTPDebug  = 0; // change to 2 only while testing
    $mail->Debugoutput = 'html';

            $mail->setFrom('email address', 'Anandamoyeean Alumni Association');
            $mail->addAddress($email);

            $mail->isHTML(true);
            $mail->Subject = "Password Reset Request";

            $mail->Body = "
                <h3>Password Reset</h3>
                <p>Hello,</p>
                <p>You requested a password reset.</p>
                <p>
                    Click the link below to reset your password:
                </p>

                <p>
        <a href='$reset_link' style='
            display:inline-block;
            padding:10px 20px;
            background:#003366;
            color:#ffffff;
            text-decoration:none;
            border-radius:5px;
        '>
            Reset Password
        </a>
    </p>
                <p>
                    <a href='$reset_link'>$reset_link</a>
                </p>
                <p>This link will expire in 1 hour.</p>

                <p>
        If you did not request a password reset, please ignore this email.
        Your password will remain unchanged.
    </p>
            ";

            $mail->AltBody = "Reset your password using this link: $reset_link";

            $mail->send();

echo "
<div id='popup' style='
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100vh;
    background: white;
    display: flex;
    justify-content: center;
    align-items: center;
    text-align: center;
    z-index: 9999;
'>
    <div style='
        background: white;
        padding: 30px;
        border-radius: 10px;
        box-shadow: 0 0 15px rgba(0,0,0,0.3);
    '>
        <h3 style='color:green; margin-bottom:20px;'>✅ Success</h3>
        <p style='margin-bottom:25px;'>
        Password reset link has been sent to your email.</p>

        <a href='/'>
            <button style='
                padding:10px 20px;
                background:#007bff;
                color:white;
                border:none;
                border-radius:5px;
                cursor:pointer;
            '>
                Back to Homepage
            </button>
        </a>
    </div>
</div>
";

} catch (Exception $e) {

echo "
<div id='popup' style='
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    background: white;
    padding: 30px;
    border-radius: 10px;
    box-shadow: 0 0 15px rgba(0,0,0,0.3);
    text-align: center;
'>
    <h3 style='color:red;'>❌ Error</h3>
    <p>Mail could not be sent.</p>
    <a href='/'>
        <button>Back to Homepage</button>
    </a>
</div>
";
}

    } else {

        echo "<p style='color:red;'> No account found with this email.</p>";

    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password</title>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: "Segoe UI", sans-serif;
        }

        body {
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            background: #3a4b96;
        }

        .forgot-box {
            width: 380px;
            background: #ffffff;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }

        .forgot-box h2 {
            text-align: center;
            margin-bottom: 25px;
            color: #333;
            font-size: 28px;
        }

        .forgot-box label {
            font-size: 15px;
            color: #555;
            font-weight: 600;
        }

        .forgot-box input {
            width: 100%;
            padding: 14px;
            margin-top: 10px;
            border: 1px solid #ddd;
            border-radius: 8px;
            outline: none;
            font-size: 15px;
            transition: 0.3s;
        }

        .forgot-box input:focus {
            border-color: #667eea;
            box-shadow: 0 0 8px rgba(102,126,234,0.3);
        }

        .forgot-box button {
            width: 100%;
            padding: 14px;
            margin-top: 25px;
            border: none;
            border-radius: 8px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: 0.3s;
        }

        .forgot-box button:hover {
            opacity: 0.9;
            transform: translateY(-2px);
        }

        .back-login {
            text-align: center;
            margin-top: 20px;
        }

        .back-login a {
            color: #667eea;
            text-decoration: none;
            font-size: 14px;
        }

        .back-login a:hover {
            text-decoration: underline;
        }

        @media(max-width: 450px) {
            .forgot-box {
                width: 90%;
                padding: 30px;
            }
        }
    </style>
</head>

<body>

    <div class="forgot-box">

        <h2>Forgot Password</h2>

        <form method="POST">

            <label>Email Address</label>

            <input
                type="email"
                name="email"
                required
                placeholder="Enter your email"
            >

            <button type="submit">
                Send Reset Link
            </button>

        </form>

        <div class="back-login">
            <a href="/auth/login.php">Back to Login</a>
        </div>

    </div>

</body>
</html>