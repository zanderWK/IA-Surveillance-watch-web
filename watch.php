<?php
include 'src/php/session.php';
include 'src/php/header.php';
require 'src/php/toast.php';
$env = parse_ini_file('src/php/.env');
$DashboardUrl = $env["DASHBOARDURL"];
$token = $_SESSION['token'];


$response = postData('get-live-streams', ['session' => $_SESSION['token']]);
if ($response['status'] == 200 && !empty($response['body'])) {
    $streams = $response['body']['links'];
} else {
    $streams = [
        // ['name' => 'Stream 1', 'url' => 'https://bitdash-a.akamaihd.net/content/sintel/hls/playlist.m3u8'],
        // ['name' => 'Stream 2', 'url' => 'https://bitdash-a.akamaihd.net/content/sintel/hls/playlist.m3u8'],
        // ['name' => 'Stream 3', 'url' => 'https://bitdash-a.akamaihd.net/content/sintel/hls/playlist.m3u8'],
        // ['name' => 'Stream 4', 'url' => 'https://bitdash-a.akamaihd.net/content/sintel/hls/playlist.m3u8']
    ];
}

$streamLength = count($streams);

if($streamLength == 0){
    addToast('No streams available', 'error');
}elseif($streamLength >= 2){
    $width ='vw-45';
    addToast('Streams starting', 'success');
}else{
    $width ='vw-95';
    addToast('Stream starting', 'success');
};

?>


<div class="container-fluid bg-black">
    
    <div class="row w-100 py-3 d-flex justify-content-center align-content-center">
        <div class="col-10">
            <img src="src/img/logoWhite.png"  width="100px" height="auto" alt="logo" class="img-fluid">
        </div>

        <div class="col-2 d-flex justify-content-end align-items-center">
        <a href="/logout.php" class="btn btn-link text-white shadow-none p-0">Log out <i class="fa-solid fa-arrow-right-from-bracket"></i></a>
        </div>
    </div>

 
    <div class="row">
        <div class="col-md-9 col-sm-12">
        <div id="streams-container">
    <?php foreach ($streams as $index => $stream): ?>
        <div class="stream-block <?php echo $width ?>"  >
            <div class="stream-name"><?php echo htmlspecialchars($stream['name']); ?></div>
            <video id="video-<?php echo $index; ?> " 
                   class="video-js vjs-default-skin stream-video vjs-fluid" 
                   controls 
                   preload="auto"   
                   
    data-setup='{"autoplay": true, "muted": true}'
                   >
                <!-- HLS source -->
                <source src="<?php echo htmlspecialchars($stream['url']); ?>" type="application/x-mpegURL">
            </video>
        </div>
    <?php endforeach; ?>
</div>

       </div>
        <div id="desktopChat" class="col-md-3 col-sm-12">
            <div id="chatBox" class="w-100 h-100 bg-white rounded-3 p-3 ">
                <h4>Chat</h4>
                <div id="desktop-chat-holder">
                    <div id="messageDiv">
              <div id="messages" class="w-100 chat rounded-3 " style=" box-shadow: inset 0px 0px 10px -5px rgba(0,0,0,0.9);">
              </div>
             
              <div class="message-box w-100 pt-3">
                <form id="messageBox" class="d-flex align-content-around flex-direction-row align-items-end">
                <textarea id="messageInput" name="message" rows="1" class="form-control messageInput" style="width:80%"  placeholder="Type a message..." ></textarea>
                <button type="submit" id="sendMessage" class="btn btn-primary chatButton" style="width:20%"><i class="fa-solid fa-paper-plane"></i></button>
                </form>
                </div>
              </div>
              </div>
            </div>
        </div>
    </div>
</div>

<button id="mobileChatButton" class="btn btn-primary rounded-pill shadow" data-bs-toggle="modal" data-bs-target="#chatModal">

<div class="position-relative w-100">
<i class="fa-regular fa-comment fs-2 position-relative"></i>
<span id="messagePill" class="position-absolute top-0 start-75 translate-middle badge rounded-pill bg-danger d-none">
  <span id="messageCount">
  </span>
    <span class="visually-hidden">unread messages</span>
  </span>
  
</div>
  
    </button>

    <div class="modal fade" id="chatModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Chat</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="mobile-chat-holder"></div>
        </div>
    </div>
<?php
include 'src/php/footer.php';
?>
<script src="https://cdn.socket.io/4.0.0/socket.io.min.js"></script>
<script>

    const streams = <?php echo json_encode($streams); ?>;
    // Initialize Socket.IO client
    const sessionId = '<?php echo  $_SESSION['token'] ?>';
    const socket = io('<?php echo $DashboardUrl ?>', {
        query: { 'sessionId': sessionId }
    });
</script>
<script src="src/js/chat.js"></script>