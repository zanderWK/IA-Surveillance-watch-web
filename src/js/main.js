function setFocusToTextBox(id){
    document.getElementById(id).focus();
}

// send OTP
$(document).ready(function(){
    $("#submitOTP").on("submit", function(e){
        e.preventDefault();
        $('.spinner').removeClass('d-none'); // Show spinner

        $.ajax({
            url: $(this).attr("action"),
            type: 'POST', 
            data: $(this).serialize(),
            success: function(response){
                if(response.status === "success"){
                    $('#otp').val('');
                    window.location.href = response.body.url; 
                } else {
                    showToast('danger', response.body.body); 
                }
            },
            error: function(xhr){
                $('.spinner').addClass('d-none');
                showToast('danger', 'Invalid OTP please try again');
            }
        });
    });

   
});

function showToast(type, message) {
    var bgColor = type === 'danger' ? 'bg-danger' : 'bg-success'; 
    var toastHTML = `<div class="toast align-items-center text-white ${bgColor} border-0" role="alert" aria-live="assertive" aria-atomic="true">
                        <div class="d-flex">
                            <div class="toast-body">${message}</div>
                            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                        </div>
                    </div>`;
    $("#toasts").append(toastHTML).find('.toast').last().toast('show');
}


