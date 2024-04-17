<?php
// add header
include 'src/php/session.php';
include 'src/php/header.php';
require 'src/php/toast.php';

// Destroy session and send end-session via api to log out user:

$token = $_SESSION['token'];
$endpoint = 'logout';
$data = array(
    'session' => $token
);

$response = postData($endpoint, $data);

echo '<script>console.log(' . json_encode($response) . ')</script>';

if ($response['status'] == 200) {
    session_destroy();
    addToast('You have been logged out', 'success');
} else {
    addToast('An error occurred while logging out', 'error');
    session_destroy();
}



?>
<div class="container-fluid bg-main vh-100 bg-white">
    <div class="row display-flex justify-content-center align-content-center">
        <div class="col-md-12 col-xl-3 text-center p-2">
            <img class="img-fluid mb-5" src="src/img/logo.png" alt="logo" width="250px" height="auto">
           <div class="w-100 rounded-4 p-3 text-start bg-white shadow-lg">
            <h4>You have been logged out</h4>
            <span>To log back in please click the link in the email sent previously</span>
           </div>
        </div>
    </div>
</div>

<?php 
include 'src/php/footer.php';
?>
<script>
    $(".spinner").addClass("d-none");
</script>

