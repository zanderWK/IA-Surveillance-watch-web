<?php
// add header
include 'src/php/session.php';
include 'src/php/header.php';
require 'src/php/toast.php';

$token = $_GET['token'];


?>
<div class="container-fluid bg-main vh-100 bg-white">
    <div class="row display-flex justify-content-center align-content-center">
        <div class="col-md-12 col-xl-3 text-center p-2">
            <img class="img-fluid mb-5" src="src/img/logo.png" alt="logo" width="250px" height="auto">
           <div class="w-100 rounded-4 p-3 text-start bg-white shadow-lg">
            <?php
            if(isset($token) && !empty($token)){

            echo '
            <h4>We have sent you an OTP</h4>
            <span>Enter OTP received</span>
            <form action="/src/php/otp.php"  id="submitOTP">
            <div class="form-group w-100 text-center">
                    
            <input type="number" class="form-control border-0 border-bottom bg-light my-3 text-center p-3" name="otp" id="otp" maxlength="6" placeholder="Enter OTP" onload=" document.getElementById( \'otp \').focus()"  required>
            <p class="small text-start">We have sent you an One Time Pin to your mobile number, if you have not received it, please contact us</p>        
            <button type="submit" class="btn btn-primary mt-3 w-100 border-rounded">Continue</button>
            </form>
            ';
            }else{
            echo '
            <h4> OTP not sent</h4>
            <span>Please use the latest link emailed to you.</span>
            ';
            }
            ?>
           </div>
        </div>
    </div>
</div>

<!-- footer -->
<?php

 $endpoint = '/request-otp';
 $data = array(
 'token' => $token
 );

 // Check if the OTP request was made in the last 3 minutes
if (isset($_SESSION['last_otp_request']) && (time() - $_SESSION['last_otp_request']) < 180) {
    addToast('Please wait 3 minutes before requesting a new OTP.', 'error');
} else {
   
    try {
        $response = postData($endpoint, $data);
        if ($response['status'] == 200) {
            $_SESSION['last_otp_request'] = time(); 
            // Handle successful OTP request
            addToast( "OTP sent successfully.", 'success');
        } else {
            // Handle failed OTP request
            addToast('Failed to send OTP. Please try again later.', 'error');
        }
    } catch (Exception $e) {
        // Handle exception during OTP request
        addToast('An error occurred', 'error');
    }
}
// add footer

include 'src/php/footer.php';
?>
<script>
    $(".spinner").addClass("d-none");
    </script>