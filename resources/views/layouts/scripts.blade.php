<script>
    $(document).ready(function() {
        // Get backend API URL from Laravel config
        const backendApiUrl = "{{ config('app.backend_api_url') }}";
        const backendApiKey = "{{ config('app.backend_api_key') }}";

        // Handle Authenticate Account button click
        // $("#AuthenticateAccount").on('click', function(e) {
        //     e.preventDefault(); // Prevent normal form submit

        //     // Get form values
        //     let email = $('#email').val();
        //     let password = $('#password').val();
        //     let apiKey = $('#apikey').val();

        //     // CSRF token
        //     let token = $('meta[name="csrf-token"]').attr('content');

        //     // AJAX call to backend API
        //     $.ajax({
        //         url: backendApiUrl + "/signin", // example endpoint
        //         method: "POST",
        //         headers: {
        //             'X-CSRF-TOKEN': token,
        //             'Accept': 'application/json',
        //             'apiKey': backendApiKey
        //         },
        //         data: {

        //             email: email,
        //             password: password,
        //             api_key: apiKey,
        //         },
        //         success: function(response) {
        //             alert('✅ Account Loggedin successfully!');
        //             console.log(response);
        //         },
        //         error: function(xhr) {
        //             alert('❌ Error logging into account.');
        //             console.log(xhr.responseText);
        //         }
        //     });
        // });

        // const token = $('meta[name="csrf-token"]').attr('content');

        /* ✅ AUTHENTICATE ACCOUNT */
        // $("#AuthenticateAccount").on("click", function () {

        //     $.ajax({
        //         url: "/settings/authenticate",
        //         method: "POST",
        //         headers: { 'X-CSRF-TOKEN': token },
        //         data: {
        //             email: $("#email").val(),
        //             password: $("#password").val(),
        //             apikey: $("#apikey").val(),
        //         },
        //         success: function (res) {
        //             alert("✅ " + res.message);
        //         },
        //         error: function (err) {
        //             alert("❌ Authentication Failed");
        //         }
        //     });
        // });

        $(function() {

            const token = $('meta[name="csrf-token"]').attr('content');

            $("#AuthenticateAccount").on("click", function(e) {
                e.preventDefault();

                let email = $("#email").val();
                let password = $("#password").val();
                let apiKey = $("#apikey").val();

                if (!email || !password || !apiKey) {
                    alert("❌ Please fill all fields");
                    return;
                }

                // 🔹 STEP 1: Authenticate from External API
                $.ajax({
                    url: backendApiUrl + "/signin", // EXTERNAL API
                    method: "POST",
                    headers: {
                        'Accept': 'application/json',
                        'apiKey': backendApiKey
                    },
                    data: {
                        email: email,
                        password: password,
                        api_key: apiKey
                    },

                    success: function(apiResponse) {

                        console.log("✅ API Auth Success:", apiResponse);

                        // 🔹 STEP 2: If API Auth Success → Save in Laravel DB
                        $.ajax({
                            url: "/settings/authenticate", // YOUR LARAVEL ROUTE
                            method: "POST",
                            headers: {
                                'X-CSRF-TOKEN': token
                            },
                            data: {
                                email: email,
                                password: password, // or hashed from backend
                                apikey: apiKey,
                                api_response: apiResponse // optional
                            },

                            success: function(res) {
                                alert(
                                    "✅ Account Authenticated & Saved Successfully!");
                                console.log("Saved:", res);
                            },

                            error: function(err) {
                                alert(
                                    "❌ Auth success but DB save failed");
                                console.error(err.responseText);
                            }
                        });
                    },

                    error: function(xhr) {
                        alert("❌ Authentication Failed. Data NOT Saved");
                        console.error(xhr.responseText);
                    }
                });

            });

        });



        /* ✅ SAVE SETTINGS */
        $("#SettingForm").on("submit", function(e) {
            e.preventDefault();

            $.ajax({
                url: "/settings/save",
                method: "POST",
                headers: {
                    'X-CSRF-TOKEN': token
                },
                data: {
                    email: $("#email").val(),
                    password: $("#password").val(),
                    apikey: $("#apikey").val(),
                    Fullfillment: $("#Fullfillment").val(),
                    Firgile: $("#Firgile").val(),
                    Insurance: $("#Insurance").val(),
                    accounttype: $("#accounttype").val(),
                    cms: $("#cms").val(),
                    price: $("#price").val(),
                },
                success: function(res) {
                    alert("✅ " + res.message);
                },
                error: function(err) {
                    alert("❌ Failed to save settings");
                }
            });
        });



        $('#sendOrders').on('click', function() {
            let ordersData = [];
            $('tbody tr').each(function() {
                if ($(this).find('.order-checkbox').is(':checked')) {
                    ordersData.push({
                        merchant_id: $(this).find('.merchant-id').val(),
                        total_price: parseFloat($(this).find('.price-input').text()
                            .replace(/,/g, '')),
                        line_items: JSON.parse($(this).find('.line-items').val()),
                        shipping_address: JSON.parse($(this).find('.shipping-address')
                            .val()),
                        phone: $(this).find('td:nth-child(5)').text(),
                        name: $(this).find('.name').val(),
                        note: $(this).find('.note').val(),
                    });
                }
            });

            console.log(ordersData);

            if (ordersData.length === 0) {
                alert('Please select at least one order!');
                return;
            }

            $.ajax({

                url: backendApiUrl + "/order/save", // EXTERNAL API
                method: "POST",
                headers: {
                    'Accept': 'application/json',
                    'apiKey': backendApiKey,
                    'Authorization': 'Bearer 36|qmdDuugC0T6f7hIneFsI3PhiubmMvUrAcj3lqycebf1da235' // if needed
                },
                data: {
                    _token: '{{ csrf_token() }}',
                    orders: ordersData
                },
                success: function(res) {
                    alert('Orders sent successfully!');
                },
                error: function(err) {
                    console.error(err);
                    alert('Something went wrong!');
                }
            });
        });
    });
</script>
