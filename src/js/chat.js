
$(document).ready(function() {
streams.forEach(function(stream, index) {
    var video = document.getElementById('video-' + index);
    if (Hls.isSupported()) {
        var hls = new Hls({ maxBufferLength: 0.5 });
        hls.loadSource(stream.url);
        hls.attachMedia(video);
        hls.on(Hls.Events.MANIFEST_PARSED, function() {
            video.play();
        });
        hls.on(Hls.Events.ERROR, function(event, data) {
            handleHlsErrors(data, hls);
        });
    }
});

$('#messageBox').on('keypress', function(event) {
if (event.which === 13) { // 13 is the Enter key
    event.preventDefault(); // Prevent default submit
    // Optional: Add any checks or processing before the form submission
    $(this).submit(); // Submit the form
}
});



function moveChat() {
    var $chatContainer = $('#messageDiv');
    if ($(window).width() >= 768) { // Bootstrap's MD breakpoint
        $('#desktop-chat-holder').append($chatContainer);
        $('#mobileChatButton').addClass('d-none');
        $('#chatModal').modal('hide');
        $('#desktopChat').removeClass('d-none');
    } else {
        $('#mobile-chat-holder').append($chatContainer);
        $('#mobileChatButton').removeClass('d-none');
        $('#desktopChat').addClass('d-none');
    }
}

// Initial move
moveChat();

// Re-apply on window resize
$(window).on('resize', moveChat);

// clear count and set message pill to d-none and text html to empty
$('#mobileChatButton').on('click', function() {
    $('#messagePill').addClass('d-none');
    $('#messageCount').html(0);
    scrollDown();
});

 // Register socket event handlers
 socket.on('connect', function() {
    console.log("Connected to Socket.IO server");
    socket.emit('joinProjectRoom', { 'sessionId': sessionId });
    fetchAllMessages(sessionId);

});

socket.on('newMessage', function(data) {
    fetchLatestMessage(sessionId);
});

$('#messageBox').on('submit', function(e) {
    e.preventDefault();
    sendMessage(sessionId);
    $('#messageCount').text(-1);
});

function sendMessage(sessionId) {
    const text = $('#messageInput').val();
    if (text) {
        socket.emit('sendMessage', { text, session: sessionId });
        $('#messageInput').val("");
    }
}

function setMessageCount(newValue,oldValue){
  
    let val = parseInt(newValue)+parseInt(oldValue);
    if(val>0){
    $('#messagePill').removeClass('d-none');
    $('#messageCount').text(val);
    }else{
        $('#messagePill').addClass('d-none');
    }
}

function fetchAllMessages(sessionId) {
    $.ajax({
        url: 'src/php/chat.php?get=all-messages',
        method: 'POST',
        data: { session: sessionId },
        success: function(response) {
            response = JSON.parse(response);
            displayMessages(response.body.messages);
            let newValue = response.body.messages.length;
            setMessageCount(newValue,"0");
        },
        error: function(error) {
            console.error('Error fetching all messages:', error);
        }
    });
}

function fetchLatestMessage(sessionId) {
    $.ajax({
        url: 'src/php/chat.php?get=latest-message',
        method: 'POST',
        data: { session: sessionId },
        success: function(response) {
            response = JSON.parse(response);
            displayMessage(response.body.messages);
            let newValue = 1;
            let oldvalue = parseInt($('#messageCount').text());
            setMessageCount(newValue,oldvalue);

        },
        error: function(error) {
            console.error('Error fetching latest message:', error);
        }
    });
}
const msgBox = (msg) => {
  return `<div class="w-100 rounded-3 bg-white shadow-sm p-2 px-3 mb-2">
                <div class="messageContent">${msg.message}</div>
              <div class="messageMeta d-flex justify-content-end border-top">
              <span class="messageUser small">${msg.viewerName}</span>&nbsp;
              <span class="small">@</span>&nbsp;
                <span class="messageTime small">${new Date(msg.sent).toLocaleString('en-ZA')}</span>
               
              </div>
            </div>
        `
}

function displayMessages(messages) {
    messages.forEach(msg => {
        $('#messages').append(msgBox(msg));
    });
}

function displayMessage(message) {
    $('#messages').append(msgBox(message));
}

// Setup auto-scroll
const observer = new MutationObserver(function() {
    const messagesDiv = document.getElementById('messages');
    messagesDiv.scrollTop = messagesDiv.scrollHeight;
});
observer.observe(document.getElementById('messages'), { childList: true });


function scrollDown(){
var $messages = $('#messages');
    $messages.scrollTop($messages.prop("scrollHeight"));
}

function handleHlsErrors(data, hls) {
if (data.fatal) {
    switch(data.type) {
        case Hls.ErrorTypes.NETWORK_ERROR:
            console.error("Network error encountered, trying to recover...");
            hls.startLoad();
            break;
        case Hls.ErrorTypes.MEDIA_ERROR:
            console.error("Media error encountered, trying to recover...");
            hls.recoverMediaError();
            break;
        default:
            hls.destroy();
            break;
    }
}
}

$(".spinner").addClass("d-none");

});