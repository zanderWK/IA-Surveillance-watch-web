$(document).ready(function() {
    try {
        // Initialize HLS for each stream
        streams.forEach(function(stream, index) {
            var videoId = 'video-' + index;
            var videoElement = document.getElementById(videoId);
            if (videoElement && Hls.isSupported()) {
                initializeHls(stream.url, videoElement, index);
            } else {
                console.error(`Video element with ID ${videoId} not found or HLS not supported.`);
            }
        });
    } catch (e) {
        console.error('Error initializing streams:', e);
    }

    /**
     * Initialize HLS for a given video element and stream URL.
     */
    function initializeHls(url, videoElement, index) {
        var hls = new Hls({ maxBufferLength: 120 });
        window[`hlsInstance${index}`] = hls; // Store HLS instance in window object
        hls.loadSource(url);
        hls.attachMedia(videoElement);

        hls.on(Hls.Events.MANIFEST_PARSED, function() {
            if (videoElement.readyState > 4) { // 2 = HAVE_CURRENT_DATA
                videoElement.play();
            } else {
                videoElement.onloadeddata = function() {
                    videoElement.play();
                };
            }
        });

        hls.on(Hls.Events.ERROR, function(event, data) {
            handleHlsErrors(data, hls, stream, index);
        });
        // Initialize Video.js with the loading spinner disabled
        videojs(videoElement, {
            loadingSpinner: false,
            autoplay: true,
            preload: 'auto'
        });

        // Add error handling for Video.js
        videoElement.addEventListener('error', function(e) {
            handleVideoJsErrors(e, stream, index);
        });
    }

    /**
     * Handle HLS errors and try to recover stream.
     */
    function handleHlsErrors(data, hls, stream, index) {
   
            switch (data.type) {
                case Hls.ErrorTypes.NETWORK_ERROR:
                    console.error('Network error, trying to recover...');
                    hls.startLoad();
                    break;
                case Hls.ErrorTypes.MEDIA_ERROR:
                    console.error('Media error, trying to recover...');
                    hls.recoverMediaError();
                    break;
                default:
                    console.error('Unrecoverable error, restarting stream...');
                    restartStream(stream, index);
                    break;
            }
        
    }

    /**
     * Handle Video.js errors and restart stream if necessary.
     */
    function handleVideoJsErrors(e, stream, index) {
        const error = e.target.error;
        console.error('Video.js error:', error);

        // Reinitialize stream on any error
        console.error('Error detected, restarting stream...');
        restartStream(stream, index);
    }

    /**
     * Restart the stream for a given index.
     */
    function restartStream(stream, index) {
        var videoId = 'video-' + index;
        var videoElement = document.getElementById(videoId);

        if (videoElement && Hls.isSupported()) {
            initializeHls(stream.url, videoElement, index);
        } else {
            console.error(`Video element with ID ${videoId} not found or HLS not supported.`);
        }
    }

    // Move chat container based on window width
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

    // Clear count and set message pill to d-none and text HTML to empty
    $('#mobileChatButton').on('click', function() {
        $('#messagePill').addClass('d-none');
        $('#messageCount').html(0);
        scrollDown();
    });

    // Register socket event handlers
    socket.on('connect', function() {
        socket.emit('joinProjectRoom', { 'sessionId': sessionId });
        fetchAllMessages(sessionId);
    });

    socket.on('newMessage', function(data) {
        fetchLatestMessage(sessionId);
    });

    // Send message on form submit
    $('#messageBox').on('submit', function(e) {
        e.preventDefault();
        sendMessage(sessionId);
        $('#messageCount').text(-1);
    });

    // Handle Enter key press in message box
    $('#messageBox').on('keypress', function(event) {
        if (event.which === 13) { // 13 is the Enter key
            event.preventDefault(); // Prevent default submit
            $(this).submit(); // Submit the form
        }
    });

    /**
     * Send a message to the server.
     */
    function sendMessage(sessionId) {
        const text = $('#messageInput').val();
        if (text) {
            socket.emit('sendMessage', { text, session: sessionId });
            $('#messageInput').val("");
        }
    }

    /**
     * Set the message count and update the UI.
     */
    function setMessageCount(newValue, oldValue) {
        let val = parseInt(newValue) + parseInt(oldValue);
        if (val > 0) {
            $('#messagePill').removeClass('d-none');
            $('#messageCount').text(val);
        } else {
            $('#messagePill').addClass('d-none');
        }
    }

    /**
     * Fetch all messages for a session.
     */
    function fetchAllMessages(sessionId) {
        $.ajax({
            url: 'src/php/chat.php?get=all-messages',
            method: 'POST',
            data: { session: sessionId },
            success: function(response) {
                response = JSON.parse(response);
                displayMessages(response.body.messages.slice(0, -1));
                let newValue = response.body.messages.length;
                setMessageCount(newValue, "0");
            },
            error: function(error) {
                console.error('Error fetching all messages:', error);
            }
        });
    }

    /**
     * Fetch the latest message for a session.
     */
    function fetchLatestMessage(sessionId) {
        $.ajax({
            url: 'src/php/chat.php?get=latest-message',
            method: 'POST',
            data: { session: sessionId },
            success: function(response) {
                response = JSON.parse(response);
                displayMessage(response.body.messages);
                let newValue = 1;
                let oldValue = parseInt($('#messageCount').text());
                setMessageCount(newValue, oldValue);
            },
            error: function(error) {
                console.error('Error fetching latest message:', error);
            }
        });
    }

    /**
     * Create a message box HTML string.
     */
    const msgBox = (msg) => {
        return `
            <div class="w-100 rounded-3 bg-white shadow-sm p-2 px-3 mb-2">
                <div class="messageContent">${msg.message}</div>
                <div class="messageMeta d-flex justify-content-end border-top">
                    <span class="messageUser small">${msg.viewerName}</span>&nbsp;
                    <span class="small">@</span>&nbsp;
                    <span class="messageTime small">${new Date(msg.sent).toLocaleString('en-ZA')}</span>
                </div>
            </div>
        `;
    }

    /**
     * Display multiple messages.
     */
    function displayMessages(messages) {
        messages.forEach(msg => {
            $('#messages').append(msgBox(msg));
        });
    }

    /**
     * Display a single message.
     */
    function displayMessage(message) {
        $('#messages').append(msgBox(message));
    }

    // Setup auto-scroll for messages div
    const observer = new MutationObserver(function() {
        const messagesDiv = document.getElementById('messages');
        messagesDiv.scrollTop = messagesDiv.scrollHeight;
    });
    observer.observe(document.getElementById('messages'), { childList: true });

    /**
     * Scroll down to the bottom of the messages div.
     */
    function scrollDown() {
        var $messages = $('#messages');
        $messages.scrollTop($messages.prop("scrollHeight"));
    }

    // Hide spinner after initialization
    $(".spinner").addClass("d-none");

    // Global error flag
    let errorFlag = false;

    // Global error handler
    window.addEventListener('error', function(e) {
        console.log(e.filename);
    });

    window.onerror = function(message, source, lineno, colno, error) {
      
            if (!errorFlag) {
                errorFlag = true;
                showToast("bg-danger", "Stream terminated, trying to recover...");
                showToast("bg-success", "Refreshing...");
                setTimeout(function() {
                    window.location.reload();
                }, 5000);
            }
            streams.forEach(function(stream, index) {
                var videoId = 'video-' + index;
                var videoElement = document.getElementById(videoId);
                if (videoElement && Hls.isSupported()) {
                    initializeHls(stream.url, videoElement, index);
                } else {
                    console.error(`Video element with ID ${videoId} not found or HLS not supported.`);
                }
            });
            checkAllVideoPlayers();
        
        console.clear();
        return true; // Prevent the default browser error message
    };

    /**
     * Check all Video.js players on the page.
     */
    function checkAllVideoPlayers() {
        const allPlayers = videojs.players; // Get all players
        const playerStates = [];

        for (let playerId in allPlayers) {
            if (allPlayers.hasOwnProperty(playerId)) {
                const player = allPlayers[playerId];
                const isPlaying = !player.paused();
                playerStates.push({ playerId, isPlaying });
            }
        }

        return playerStates; // Optional: return the states if needed elsewhere
    }
});
