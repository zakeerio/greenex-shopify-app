<script>
    $(document).ready(function() {
        // Get backend API URL from Laravel config
        const backendApiUrl = "{{ config('app.backend_api_url') }}";
        const backendApiKey = "{{ config('app.backend_api_key') }}";

        // Handle Authenticate Account button click

        $(function() {
            const token = $('meta[name="csrf-token"]').attr('content');

            $("#AuthenticateAccount").on("click", function(e) {
                e.preventDefault();

                // Form fields
                let email = $("#email").val();
                let password = $("#password").val(); // optional if you want to keep
                let fulfillment = $("#fullfilment").val();
                let fragile = $("#Fragile").val();
                let apikey = $("#apikey").val(); // optional / ignore if not storing
                let insurance = $("#Insurance").val();
                let account_type = $("#accounttype").val();
                let auto_push_orders = $("#auto_push_orders").val();
                let price = $("#price").val();

                // Optional: simple validation
                if (!email) {
                    alert("❌ Please enter email");
                    return;
                }

                // External API signin to get token (replace with your API)
                $.ajax({
                    url: backendApiUrl + "/signin",
                    method: "POST",
                    headers: {
                        'Accept': 'application/json',
                        'apiKey': backendApiKey
                    },
                    data: {
                        email: email,
                        password: password,
                        api_key: apikey
                    },
                    success: function(apiResponse) {
                        let apiToken = apiResponse.data.token;
                        let user = apiResponse.data.user;

                        // ✅ Save to Laravel DB
                        $.ajax({
                            url: `{{ route('savesettings') }}`,
                            method: "POST",
                            headers: {
                                'X-CSRF-TOKEN': token
                            },
                            data: {
                                // shop_domain: user.hub.name ?? "default-shop", // shop_domain required
                                portal_user_id: user.id, // Shopify user id
                                email: email,
                                name: user.name,
                                phone: user.phone,
                                user_type: user.user_type,
                                hub_id: user.hub_id,
                                merchant_id: user.merchant?.id ?? null,
                                wallet_balance: user.merchant
                                    ?.wallet_balance ?? 0,
                                api_token: apiToken,
                                api_response: JSON.stringify(apiResponse),
                                fulfillment_location: fulfillment,
                                fragile: fragile,
                                insurance: insurance,
                                account_type: account_type,
                                auto_push_cms: auto_push_orders,
                                price: price
                            },
                            success: function(res) {
                                alert(
                                    "✅ Token & User Saved Successfully");
                                console.log(res);
                            },
                            error: function(err) {
                                console.error(err.responseText);
                                alert("❌ Error saving settings");
                            }
                        });
                    },
                    error: function(err) {
                        console.error(err.responseText);
                        alert("❌ Authentication failed");
                    }
                });
            });

            /* ✅ SAVE SETTINGS */
            $(document).on("click", "#SaveAccountSettings", function(e) {
                e.preventDefault();

                $.ajax({
                    url: "{{ route('updatesetting') }}",
                    method: "POST",
                    headers: {
                        'X-CSRF-TOKEN': token
                    },
                    data: {
                        fulfillment_location: $("#fullfilment").val(),
                        fragile : $("#Fragile").val(),
                        insurance : $("#Insurance").val(),
                        account_type : $("#accounttype").val(),
                        auto_push_orders : $("#auto_push_orders").val(),
                        price : $("#price").val(),
                    },
                    success: function(res) {
                        alert("✅ " + res.message);
                    },
                    error: function(err) {
                        alert("❌ Failed to save settings");
                    }
                });
            });
        });

        /* ✅ SEND SELECTED ORDERS TO EXTERNAL API */

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
