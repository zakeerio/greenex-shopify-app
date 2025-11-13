<script>
$(document).ready(function() {
    // Get backend API URL from Laravel config
    const backendApiUrl = "{{ config('app.backend_api_url') }}";
    const backendApiKey = "{{ config('app.backend_api_key') }}";

    $('#AuthenticateAccount').on('click', function(e) {
        e.preventDefault(); // Prevent normal form submit

        // Get form values
        let email = $('#email').val();
        let password = $('#password').val();
        let apiKey = $('#apikey').val();

        // CSRF token
        let token = $('meta[name="csrf-token"]').attr('content');

        // AJAX call to backend API
        $.ajax({
            url: backendApiUrl + "/signin", // example endpoint
            method: "POST",
            headers: {
                'X-CSRF-TOKEN': token,
                'Accept': 'application/json',
                'apiKey': backendApiKey
            },
            data: {

                email: email,
                password: password,
                api_key: apiKey,
            },
            success: function(response) {
                alert('✅ Account Loggedin successfully!');
                console.log(response);
            },
            error: function(xhr) {
                alert('❌ Error logging into account.');
                console.log(xhr.responseText);
            }
        });
    });
});
</script>
