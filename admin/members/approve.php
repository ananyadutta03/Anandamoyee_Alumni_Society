<?php

require_once __DIR__ . '/../../config/init.php';
require_once __DIR__ . '/../../includes/auth_check.php';

requireAdmin();


if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

redirect(SITE_URL . '/admin/members/index.php');

}


if (!verifyCsrf($_POST['csrf_token'] ?? '')) {

setFlash('danger','Invalid request.');

redirect(SITE_URL . '/admin/members/index.php');

}



$id = intval($_POST['id'] ?? 0);

$action = $_POST['action'] ?? '';

$pdo = getDBConnection();



if($action === 'approve'){



// get user first

$stmt = $pdo->prepare("
SELECT name,email 
FROM users 
WHERE id=? 
AND role='user'
");

$stmt->execute([$id]);

$user = $stmt->fetch();



if($user){



$update = $pdo->prepare("
UPDATE users
SET status='approved'
WHERE id=?
AND role='user'
");


$update->execute([$id]);




// send approval email


require_once __DIR__ . '/../../includes/email_helper.php';



$body = "

<h2>Account Approved</h2>


<p>Hello {$user['name']},</p>


<p>
Your Anandamoyee Alumni account has been approved.
</p>


<p>
You can now login.
</p>


<a href='".SITE_URL."/auth/login.php'
style='
background:#003366;
color:white;
padding:10px 20px;
text-decoration:none;
border-radius:5px;
'>
Login Now
</a>


";


sendEmail(
$user['email'],
"Your Account Has Been Approved",
$body
);



}



setFlash(
'success',
'Member approved successfully.'
);



}



elseif($action === 'reject'){


$stmt=$pdo->prepare("
UPDATE users
SET status='rejected'
WHERE id=?
AND role='user'
");


$stmt->execute([$id]);



setFlash(
'warning',
'Member rejected.'
);



}



else{


setFlash(
'danger',
'Invalid action.'
);


}



redirect(
SITE_URL.'/admin/members/index.php'
);

?>