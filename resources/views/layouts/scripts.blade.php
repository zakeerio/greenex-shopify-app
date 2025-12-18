<script>
    $(document).ready(function() {
        // Get backend API URL from Laravel config
        const backendApiUrl = "{{ config('app.backend_api_url') }}";
        const backendApiKey = "{{ config('app.backend_api_key') }}";

        // Handle Authenticate Account button click

        $(function() {
            const token = $('meta[name="csrf-token"]').attr('content');

            // $("#AuthenticateAccount").on("click", function(e) {
            //     e.preventDefault();

            //     // Form fields
            //     let email = $("#email").val();
            //     let password = $("#password").val(); // optional if you want to keep
            //     let fulfillment = $("#fullfilment").val();
            //     let fragile = $("#Fragile").val();
            //     let apikey = $("#apikey").val(); // optional / ignore if not storing
            //     let insurance = $("#Insurance").val();
            //     let account_type = $("#accounttype").val();
            //     let auto_push_orders = $("#auto_push_orders").val();
            //     let price = $("#price").val();

            //     // Optional: simple validation
            //     if (!email) {
            //         alert("❌ Please enter email");
            //         return;
            //     }

            //     // External API signin to get token (replace with your API)
            //     $.ajax({
            //         url: backendApiUrl + "/signin",
            //         method: "POST",
            //         headers: {
            //             'Accept': 'application/json',
            //             'apiKey': backendApiKey
            //         },
            //         data: {
            //             email: email,
            //             password: password,
            //             api_key: apikey
            //         },
            //         success: function(apiResponse) {
            //             let apiToken = apiResponse.data.token;
            //             let user = apiResponse.data.user;

            //             // ✅ Save to Laravel DB
            //             $.ajax({
            //                 url: `{{ route('savesettings') }}`,
            //                 method: "POST",
            //                 headers: {
            //                     'X-CSRF-TOKEN': token
            //                 },
            //                 data: {
            //                     // shop_domain: user.hub.name ?? "default-shop", // shop_domain required
            //                     portal_user_id: user.id, // Shopify user id
            //                     email: email,
            //                     name: user.name,
            //                     phone: user.phone,
            //                     user_type: user.user_type,
            //                     hub_id: user.hub_id,
            //                     merchant_id: user.merchant?.id ?? null,
            //                     wallet_balance: user.merchant
            //                         ?.wallet_balance ?? 0,
            //                     api_token: apiToken,
            //                     api_response: JSON.stringify(apiResponse),
            //                     fulfillment_location: fulfillment,
            //                     fragile: fragile,
            //                     insurance: insurance,
            //                     account_type: account_type,
            //                     auto_push_cms: auto_push_orders,
            //                     price: price
            //                 },
            //                 success: function(res) {
            //                     alert(
            //                         "✅ Token & User Saved Successfully");
            //                     console.log(res);
            //                 },
            //                 error: function(err) {
            //                     console.error(err.responseText);
            //                     alert("❌ Error saving settings");
            //                 }
            //             });
            //         },
            //         error: function(err) {
            //             console.error(err.responseText);
            //             alert("❌ Authentication failed");
            //         }
            //     });
            // });

            /* ✅ SAVE SETTINGS */
            $(document).on("click", "#SaveAccountSettings", function(e) {
                e.preventDefault();

                var formdata = {
                    shop_domain: "{{ auth()->user()->name ?? 'default-shop' }}",
                    user_id: "{{ auth()->user()->id ?? '' }}",
                    fulfillment_location: $("#fullfilment").val(),
                    fragile: $("#Fragile").val(),
                    insurance: $("#Insurance").val(),
                    account_type: $("#accounttype").val(),
                    auto_push_orders: $("#auto_push_orders").val(),
                    price: $("#price").val(),
                };

                console.log(formdata);

                $.ajax({
                    url: "{{ route('updatesetting') }}",
                    type: "POST",
                    data: formdata,
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(res) {
                        alert("✅ " + res.message);
                    },
                    error: function(xhr) {
                        console.log(xhr.responseText);
                        alert("❌ Failed to save settings");
                    }
                });

                // $.ajax({
                //     url: "{{ route('updatesetting') }}",
                //     method: "POST",
                //     headers: {
                //         'X-CSRF-TOKEN': token
                //     },
                //     data: formdata,
                //     success: function(res) {
                //         alert("✅ " + res.message);
                //     },
                //     error: function(err) {
                //         alert("❌ Failed to save settings");
                //     }
                // });
            });
        });

        /* ✅ SEND SELECTED ORDERS TO EXTERNAL API */

        // $('#sendOrders').on('click', function() {

        //     let ordersData = [];

        //     $('tbody tr').each(function() {
        //         if ($(this).find('.order-checkbox').is(':checked')) {

        //             ordersData.push({
        //                 merchant_id: $(this).find('.merchant-id').val(),
        //                 total_price: parseFloat(
        //                     $(this).find('.price-input').text().replace(/,/g, '')
        //                 ),
        //                 line_items: JSON.parse($(this).find('.line-items').val()),
        //                 shipping_address: JSON.parse($(this).find('.shipping-address')
        //                     .val()),
        //                 phone: $(this).find('td:nth-child(5)').text(),
        //                 name: $(this).find('.name').val(),
        //                 note: $(this).find('.note').val(),
        //             });
        //         }
        //     });

        //     if (ordersData.length === 0) {
        //         alert('Please select at least one order!');
        //         return;
        //     }

        //     $.ajax({
        //         url: "/orders/send",
        //         method: "POST",
        //         data: {
        //             _token: $('meta[name="csrf-token"]').attr('content'),
        //             orders: ordersData
        //         },
        //         success: function(res) {
        //             alert('Orders sent successfully!');
        //             console.log(res);
        //         },
        //         error: function(err) {
        //             console.error(err);
        //             alert('Something went wrong!');
        //         }
        //     });
        // });


        // $('#sendOrders').on('click', function() {
        //     let ordersData = [];
        //     $('tbody tr').each(function() {
        //         if ($(this).find('.order-checkbox').is(':checked')) {
        //             ordersData.push({
        //                 merchant_id: $(this).find('.merchant-id').val(),
        //                 total_price: parseFloat($(this).find('.price-input').text()
        //                     .replace(/,/g, '')),
        //                 line_items: JSON.parse($(this).find('.line-items').val()),
        //                 shipping_address: JSON.parse($(this).find('.shipping-address')
        //                     .val()),
        //                 phone: $(this).find('td:nth-child(5)').text(),
        //                 name: $(this).find('.name').val(),
        //                 note: $(this).find('.note').val(),
        //             });
        //         }
        //     });

        //     console.log(ordersData);

        //     if (ordersData.length === 0) {
        //         alert('Please select at least one order!');
        //         return;
        //     }

        //     $.ajax({

        //         url: backendApiUrl + "/order/save", // EXTERNAL API
        //         method: "POST",
        //         headers: {
        //             'Accept': 'application/json',
        //             'apiKey': backendApiKey,
        //             'Authorization': 'Bearer 36|qmdDuugC0T6f7hIneFsI3PhiubmMvUrAcj3lqycebf1da235' // if needed
        //         },
        //         data: {
        //             _token: '{{ csrf_token() }}',
        //             orders: ordersData
        //         },
        //         success: function(res) {
        //             alert('Orders sent successfully!');
        //         },
        //         error: function(err) {
        //             console.error(err);
        //             alert('Something went wrong!');
        //         }
        //     });
        // });
    });


    $(document).ready(function() {
        // Select all checkboxes
        $('#checkbox-all-search').on('change', function() {
            $('.order-checkbox').prop('checked', this.checked);
            updateSelectedCount();
        });

        // Update count when individual checkbox changes
        $('.order-checkbox').on('change', function() {
            updateSelectedCount();
            $('#checkbox-all-search').prop('checked',
                $('.order-checkbox:checked').length === $('.order-checkbox').length
            );
        });



        // Update selected count display
        function updateSelectedCount() {
            const selectedCount = $('.order-checkbox:checked').length;
            $('#selectedCount').text(`${selectedCount} order${selectedCount !== 1 ? 's' : ''} selected`);
        }
             // Initialize count
            updateSelectedCount();

        // Send orders button click handler

        $('#sendOrders').on('click', function() {
            const ordersData = [];
            let hasError = false;

            $('.order-checkbox:checked').each(function() {
                try {
                    const orderData = JSON.parse($(this).data('order-data'));

                    // Get collect payment value
                    const row = $(this).closest('tr');
                    const collectPayment = row.find('.collect-payment').val();

                    // Add additional fields
                    ordersData.push({
                        merchant_id: orderData.merchant_id,
                        total_price: parseFloat(orderData.total_price),
                        line_items: orderData.line_items,
                        shipping_address: orderData.shipping_address,
                        phone: orderData.phone,
                        name: orderData.name,
                        note: orderData.note,
                        email: orderData.email,
                        order_number: orderData.order_number,
                        payment_type: orderData.payment_type,
                        collect_payment: collectPayment,
                        pieces: orderData.pieces,
                        weight: orderData.weight
                    });
                } catch (e) {
                    console.error('Error parsing order data:');
                    hasError = true;
                }
            });

            if (hasError) {
                alert('Error parsing order data. Please check console for details.');
                return;
            }

            if (ordersData.length === 0) {
                alert('Please select at least one order!');
                return;
            }

            // Show loading state
            const $button = $(this);
            const originalText = $button.text();
            $button.prop('disabled', true).text('Sending...');

            // Send AJAX request
            $.ajax({
                url: "{{ route('orders.send') }}",
                method: "POST",
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content'),
                    orders: ordersData
                },
                success: function(response) {
                    if (response.success) {
                        alert(`Successfully sent ${ordersData.length} order(s)!`);
                        console.log('Response:', response);

                        // Optional: Remove sent orders from table
                        $('.order-checkbox:checked').each(function() {
                            $(this).closest('tr').fadeOut(300, function() {
                                $(this).remove();
                                updateSelectedCount();
                            });
                        });
                    } else {
                        alert('Error: ' + (response.message || 'Failed to send orders'));
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error:', xhr.responseText);
                    let errorMsg = 'Failed to send orders. ';

                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMsg += xhr.responseJSON.message;
                    } else if (xhr.status === 422) {
                        errorMsg += 'Validation error.';
                    } else if (xhr.status === 401) {
                        errorMsg += 'Authentication required.';
                    } else if (xhr.status === 403) {
                        errorMsg += 'API token not configured.';
                    }

                    alert(errorMsg);
                },
                complete: function() {
                    $button.prop('disabled', false).text(originalText);
                }
            });
        });



        // Add this to your existing JavaScript
        function loadOrders() {
            $.ajax({
                url: "{{ route('orders.fetch') }}",
                method: "GET",
                data: {
                    limit: 100,
                    status: 'any',
                    // Add any other filters
                },
                beforeSend: function() {
                    // Show loading spinner
                    $('#ordersTable tbody').html(`
                <tr>
                    <td colspan="10" class="text-center py-8">
                        <div class="inline-block animate-spin rounded-full h-8 w-8 border-t-2 border-b-2 border-blue-500"></div>
                        <p class="mt-2 text-gray-600">Loading orders...</p>
                    </td>
                </tr>
            `);
                },
                success: function(response) {
                    if (response.success) {
                        // Rebuild table with new orders
                        buildOrdersTable(response.orders);
                        alert('Orders refreshed successfully!');
                    } else {
                        alert('Error loading orders: ' + (response.message || 'Unknown error'));
                    }
                },
                error: function(xhr) {
                    alert('Failed to load orders. Please try again.');
                    console.error('Load orders error:', xhr.responseText);
                }
            });
        }

        // NEW: Process selected orders with Guzzle
        $('#processSelected').on('click', function() {
            const selectedOrders = [];

            // Check if any checkboxes are selected
            const checkedBoxes = $('.order-checkbox:checked');
            if (checkedBoxes.length === 0) {
                alert('Please select at least one order!');
                return;
            }

            // Process each selected order
            checkedBoxes.each(function() {
                try {
                    // Parse the JSON string from data-order-data attribute
                    const orderDataJson = $(this).attr('data-order-data');
                    if (!orderDataJson) {
                        console.error('No order data found for checkbox');
                        return;
                    }

                    const orderData = JSON.parse(orderDataJson);
                    const row = $(this).closest('tr');
                    const collectPayment = row.find('.collect-payment').val();

                    // Log the order data for debugging
                    console.log('Processing order:', {
                        order_number: orderData.order_number,
                        collect_payment: collectPayment,
                        has_shipping_address: !!orderData.shipping_address,
                        shipping_address: orderData.shipping_address
                    });

                    selectedOrders.push({
                        order_id: orderData.id || orderData.merchant_id,
                        order_number: orderData.order_number,
                        collect_payment: collectPayment,
                        merchant_id: orderData.merchant_id,
                        total_price: parseFloat(orderData.total_price),
                        line_items: orderData.line_items,
                        shipping_address: orderData.shipping_address || {},
                        phone: orderData.phone || '',
                        name: orderData.name || '',
                        note: orderData.note || '',
                        email: orderData.email || '',
                        payment_type: orderData.payment_type || 'prepaid',
                        pieces: orderData.pieces || 0,
                        weight: orderData.weight || 0
                    });

                } catch (e) {
                    console.error('Error parsing order data:', e.message, e.stack);
                    console.log('Raw data:', $(this).attr('data-order-data'));
                }
            });

            if (selectedOrders.length === 0) {
                alert('No valid order data found in selected orders!');
                return;
            }

            // Log what we're sending
            console.log('Sending to server:', {
                count: selectedOrders.length,
                orders: selectedOrders.map(o => ({
                    order_number: o.order_number,
                    has_shipping: !!o.shipping_address,
                    phone: o.phone
                }))
            });

            // Show loading state
            const $button = $(this);
            const originalText = $button.text();
            $button.prop('disabled', true).text('Processing...');

            // Send to new endpoint
            $.ajax({
                url: "{{ route('orders.process-selected') }}",
                method: "POST",
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content'),
                    selected_orders: selectedOrders
                },
                success: function(response) {
                    console.log('Server response:', response);

                    if (response.success) {
                        const successCount = response.summary.successful;
                        const failedCount = response.summary.failed;

                        let message = `✅ Processed ${successCount} order(s) successfully.`;

                        // Add tracking info for successful orders
                        if (successCount > 0 && response.processed) {
                            response.processed.forEach(order => {
                                if (order.tracking_id) {
                                    message +=
                                        `\nOrder #${order.order_number}: Tracking ID: ${order.tracking_id}`;
                                }
                            });
                        }

                        if (failedCount > 0) {
                            message += `\n\n❌ ${failedCount} order(s) failed.`;

                            // Show detailed error modal
                            if (response.failed && response.failed.length > 0) {
                                showFailedOrdersModal(response.failed);
                            }
                        }

                        alert(message);

                        // Remove successfully processed orders
                        if (successCount > 0 && response.processed) {
                            $('.order-checkbox:checked').each(function() {
                                try {
                                    const orderData = JSON.parse($(this).attr(
                                        'data-order-data'));
                                    const wasProcessed = response.processed.some(
                                        o =>
                                        o.order_number === orderData
                                        .order_number
                                    );

                                    if (wasProcessed) {
                                        $(this).closest('tr').fadeOut(300,
                                        function() {
                                            $(this).remove();
                                            updateSelectedCount();
                                        });
                                    }
                                } catch (e) {
                                    console.error('Error checking processed order:',
                                        e);
                                }
                            });
                        }
                    } else {
                        alert('Error: ' + (response.message || 'Failed to process orders'));
                    }
                },
                error: function(xhr) {
                    console.error('Process error:', {
                        status: xhr.status,
                        response: xhr.responseText,
                        statusText: xhr.statusText
                    });

                    let errorMsg = 'Error processing orders. ';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMsg += xhr.responseJSON.message;
                    } else if (xhr.status === 422) {
                        errorMsg += 'Validation error. Check console for details.';
                    }

                    alert(errorMsg);
                },
                complete: function() {
                    $button.prop('disabled', false).text(originalText);
                }
            });
        });

        // Helper function to show failed orders modal
        function showFailedOrdersModal(failedOrders) {
            // Create or show modal
            let modal = $('#failedOrdersModal');
            if (!modal.length) {
                modal = $(`
            <div id="failedOrdersModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
                <div class="relative top-20 mx-auto p-5 border w-1/2 shadow-lg rounded-md bg-white">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-medium text-gray-900">Failed Orders</h3>
                        <button onclick="$('#failedOrdersModal').addClass('hidden')" class="text-gray-400 hover:text-gray-600">
                            ✕
                        </button>
                    </div>
                    <div class="max-h-96 overflow-y-auto">
                        <table class="min-w-full">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Order #</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Reason</th>
                                </tr>
                            </thead>
                            <tbody id="failedOrdersList" class="bg-white divide-y divide-gray-200">
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-4 flex justify-end">
                        <button onclick="$('#failedOrdersModal').addClass('hidden')"
                                class="px-4 py-2 bg-gray-600 text-white rounded hover:bg-gray-700">
                            Close
                        </button>
                    </div>
                </div>
            </div>
        `);
                $('body').append(modal);
            }

            // Populate the list
            const list = $('#failedOrdersList');
            list.empty();

            failedOrders.forEach(failed => {
                list.append(`
            <tr>
                <td class="px-4 py-2 text-sm font-medium text-gray-900">#${failed.order_number}</td>
                <td class="px-4 py-2 text-sm text-red-600">${failed.reason}</td>
            </tr>
        `);
            });

            // Show the modal
            $('#failedOrdersModal').removeClass('hidden');
        }

        // Also update your updateSelectedCount function to be more robust
        function updateSelectedCount() {
            try {
                const selectedCount = $('.order-checkbox:checked').length;
                $('#selectedCount').text(`${selectedCount} order${selectedCount !== 1 ? 's' : ''} selected`);
            } catch (e) {
                console.error('Error updating selected count:', e);
            }
        }

    });
</script>
