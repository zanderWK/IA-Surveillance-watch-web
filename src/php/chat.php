<?php

require 'api.php';

if(isset($_GET['get']) && $_GET['get'] == 'all-messages') {
    $sessionId = $_POST['session'];
    $messages = postData('all-messages', ['session' => $sessionId]);
    echo json_encode($messages);
}

if(isset($_GET['get']) && $_GET['get'] == 'latest-message') {
    $sessionId = $_POST['session'];
    $message = postData('latest-message', ['session' => $sessionId]);
    echo json_encode($message);
}

?>