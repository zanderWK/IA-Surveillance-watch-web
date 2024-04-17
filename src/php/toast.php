<?php
// Start the session in toast.php if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Function to add toast messages
function addToast($message, $type = 'success') {
    if (!isset($_SESSION['toasts'])) {
        $_SESSION['toasts'] = [];
    }
    $_SESSION['toasts'][] = ['message' => $message, 'type' => $type];
}

// Function to render and clear toast messages
function renderToasts() {
    if (isset($_SESSION['toasts']) && count($_SESSION['toasts']) > 0) {
        echo '<div id="toasts" class="position-fixed bottom-0 end-0 p-3" style="z-index: 11; ">';
        foreach ($_SESSION['toasts'] as $toast) {
            $bgColor = $toast['type'] === 'success' ? 'bg-success' : 'bg-danger'; // Default to danger for error messages
            echo '<div class="toast align-items-center text-white ' . $bgColor . ' border-0" role="alert" aria-live="assertive" aria-atomic="true">
                    <div class="d-flex">
                        <div class="toast-body">' . $toast['message'] . '</div>
                        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                    </div>
                </div>';
        }
        echo '</div>';
        // Clear the toasts to avoid re-display
        unset($_SESSION['toasts']);
        // Include the JS code to activate the toasts
        echo '<script>
            var toastElList = [].slice.call(document.querySelectorAll(".toast"));
            var toastList = toastElList.map(function(toastEl) {
                return new bootstrap.Toast(toastEl);
            });
            toastList.forEach(toast => toast.show()); // This will show all the toasts
            </script>';
    }
}
