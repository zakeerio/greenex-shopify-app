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


    });


    /* ✅ SELECT ALL CHECKBOXES */
    $(document).ready(function() {
        // Select all checkboxes
        $('#checkbox-all-search').on('change', function() {
            $('.order-checkbox:not(:disabled)').prop('checked', this.checked);
            updateSelectedCount();
        });

        // Update count when individual checkbox changes
        $('.order-checkbox').on('change', function() {
            updateSelectedCount();
            $('#checkbox-all-search').prop('checked',
                $('.order-checkbox:checked').length === $('.order-checkbox:not(:disabled)').length
            );
        });

        // Update selected count display
        function updateSelectedCount() {
            const selectedCount = $('.order-checkbox:checked').length;
            $('#selectedCount').text(`${selectedCount} order${selectedCount !== 1 ? 's' : ''} selected`);
        }

        // Initialize count
        updateSelectedCount();

        // Disable checkboxes for orders that cannot be processed
        function disableInvalidCheckboxes() {
            $('.order-checkbox').each(function() {
                try {
                    const orderDataJson = $(this).attr('data-order-data');
                    if (!orderDataJson) return;

                    const orderData = JSON.parse(orderDataJson);
                    const $checkbox = $(this);
                    const $row = $checkbox.closest('tr');

                    // Check if order can be processed
                    const canBeProcessed = orderData.can_be_processed !== false &&
                        !orderData.already_sent &&
                        !orderData.already_fulfilled;

                    if (!canBeProcessed) {
                        // Disable checkbox
                        $checkbox.prop('disabled', true);
                        $checkbox.prop('checked', false);

                        // Add visual indicators
                        $row.addClass('opacity-50');

                        // Add reason badge
                        let reason = '';
                        let badgeClass = 'badge-';

                        if (orderData.already_fulfilled) {
                            reason = 'Already Fulfilled';
                            badgeClass += 'danger';
                        } else if (orderData.already_sent) {
                            reason = 'Already Sent';
                            badgeClass += 'warning';
                        } else if (!orderData.can_be_processed) {
                            reason = 'Cannot Process';
                            badgeClass += 'secondary';
                        }

                        // Remove existing badge if any
                        $row.find('.status-badge').remove();

                        if (reason) {
                            $row.find('td:first').append(
                                `<span class="status-badge ms-2 badge ${badgeClass}">${reason}</span>`
                            );
                        }
                    } else {
                        // Enable checkbox if it can be processed
                        $checkbox.prop('disabled', false);
                        $row.removeClass('opacity-50');
                        $row.find('.status-badge').remove();
                    }
                } catch (e) {
                    console.error('Error processing checkbox:', e);
                }
            });
        }

        // Run on page load
        setTimeout(disableInvalidCheckboxes, 100);

        // NEW: Process selected orders with Guzzle - UPDATED
        $('#processSelected').on('click', function() {
            const selectedOrders = [];

            // Check if any checkboxes are selected
            const checkedBoxes = $('.order-checkbox:checked');
            if (checkedBoxes.length === 0) {
                alert('Please select at least one order!');
                return;
            }

            // Validate all selected orders can be processed
            let hasInvalidOrder = false;
            const invalidOrders = [];

            checkedBoxes.each(function() {
                try {
                    const orderDataJson = $(this).attr('data-order-data');
                    if (!orderDataJson) {
                        console.error('No order data found for checkbox');
                        hasInvalidOrder = true;
                        return;
                    }

                    const orderData = JSON.parse(orderDataJson);
                    const $row = $(this).closest('tr');
                    const collectPayment = $row.find('.collect-payment').val();
                    const parcel_details = $row.find('.parcel_details').val();


                    // Check if order can be processed
                    if (orderData.already_sent) {
                        invalidOrders.push({
                            order_number: orderData.order_number,
                            reason: 'Already sent to portal'
                        });
                        return;
                    }

                    if (orderData.already_fulfilled) {
                        invalidOrders.push({
                            order_number: orderData.order_number,
                            reason: 'Already fulfilled in Shopify'
                        });
                        return;
                    }

                    if (!orderData.can_be_processed) {
                        invalidOrders.push({
                            order_number: orderData.order_number,
                            reason: 'Cannot be processed'
                        });
                        return;
                    }

                    // Log the order data for debugging
                    console.log('Processing order:', {
                        order_number: orderData.order_number,
                        collect_payment: collectPayment,
                        can_be_processed: orderData.can_be_processed,
                        already_sent: orderData.already_sent,
                        already_fulfilled: orderData.already_fulfilled
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
                        weight: orderData.weight || 0,
                        currency: orderData.currency || 'USD',
                        total_tax: orderData.total_tax || 0,
                        discount_codes: orderData.discount_codes || [],
                        parcel_details: parcel_details
                    });

                } catch (e) {
                    console.error('Error parsing order data:', e.message, e.stack);
                    hasInvalidOrder = true;
                }
            });

            // Show invalid orders alert
            if (invalidOrders.length > 0) {
                let message = `Cannot process ${invalidOrders.length} order(s):\n\n`;
                invalidOrders.forEach(order => {
                    message += `❌ Order #${order.order_number}: ${order.reason}\n`;
                });

                message += '\nPlease unselect these orders and try again.';
                alert(message);
                return;
            }

            if (hasInvalidOrder) {
                alert('Some selected orders have invalid data. Please check console for details.');
                return;
            }

            if (selectedOrders.length === 0) {
                alert('No valid order data found in selected orders!');
                return;
            }

            // Log what we're sending
            console.log('Sending to server:', {
                count: selectedOrders.length,
                orders: selectedOrders.map(o => ({
                    order_number: o.order_number,
                    can_be_processed: true
                }))
            });

            // Show confirmation dialog with order count
            const confirmed = confirm(
                `Are you sure you want to process ${selectedOrders.length} order(s)?\n\nThis will:\n1. Send to portal\n2. Generate tracking ID\n3. Update Shopify`
            );

            if (!confirmed) return;

            // Show loading state
            const $button = $(this);
            const originalText = $button.text();
            $button.prop('disabled', true).text('Processing...');

            // Show processing modal
            showProcessingModal(selectedOrders);

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

                    // Update processing modal with results
                    updateProcessingModal(response);

                    if (response.success) {
                        const successCount = response.summary.successful;
                        const failedCount = response.summary.failed;

                        // Remove successfully processed orders from table
                        if (successCount > 0 && response.processed) {
                            response.processed.forEach(processedOrder => {
                                const $row = $(
                                    `[data-order-number="${processedOrder.order_number}"]`
                                );
                                if ($row.length) {
                                    // Update row with success status
                                    $row.find('.order-checkbox').prop('disabled',
                                        true);
                                    $row.addClass('table-success');

                                    // Add tracking info badge
                                    if (processedOrder.tracking_id) {
                                        $row.find('td:first').append(
                                            `<span class="badge badge-success ms-2">
                                            ✓ Sent | Tracking: ${processedOrder.tracking_id}
                                        </span>`
                                        );
                                    }
                                }
                            });
                        }

                        // Update selected count
                        updateSelectedCount();

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

                    // Update modal with error
                    updateProcessingModal({
                        success: false,
                        message: 'Server error occurred',
                        failed: []
                    });

                    let errorMsg = 'Error processing orders. ';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMsg += xhr.responseJSON.message;
                    } else if (xhr.status === 422) {
                        errorMsg += 'Validation error. Check console for details.';
                    }

                    setTimeout(() => {
                        alert(errorMsg);
                    }, 1000);
                },
                complete: function() {
                    $button.prop('disabled', false).text(originalText);
                }
            });
        });

        // Helper function to show processing modal
        function showProcessingModal(selectedOrders) {
            // Create modal if it doesn't exist
            let modal = $('#processingModal');
            if (!modal.length) {
                modal = $(`
            <div id="processingModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
                <div class="relative top-20 mx-auto p-5 border w-1/2 shadow-lg rounded-md bg-white">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-medium text-gray-900">Processing Orders</h3>
                        <button onclick="$('#processingModal').addClass('hidden')" class="text-gray-400 hover:text-gray-600">
                            ✕
                        </button>
                    </div>
                    <div class="mb-4">
                        <div class="flex items-center">
                            <div class="animate-spin rounded-full h-5 w-5 border-t-2 border-b-2 border-blue-500 mr-3"></div>
                            <span id="processingStatus">Processing ${selectedOrders.length} orders...</span>
                        </div>
                        <div class="progress mt-2">
                            <div id="processingProgress" class="progress-bar" style="width: 0%"></div>
                        </div>
                    </div>
                    <div class="max-h-96 overflow-y-auto">
                        <table class="min-w-full">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Order #</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Details</th>
                                </tr>
                            </thead>
                            <tbody id="processingOrdersList" class="bg-white divide-y divide-gray-200">
                                ${selectedOrders.map(order => `
                                    <tr data-order-number="${order.order_number}">
                                        <td class="px-4 py-2 text-sm font-medium text-gray-900">#${order.order_number}</td>
                                        <td class="px-4 py-2">
                                            <span class="badge badge-info">Pending</span>
                                        </td>
                                        <td class="px-4 py-2 text-sm text-gray-500">Waiting...</td>
                                    </tr>
                                `).join('')}
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-4 flex justify-end">
                        <button id="closeProcessingModal" onclick="$('#processingModal').addClass('hidden')"
                                class="px-4 py-2 bg-gray-600 text-white rounded hover:bg-gray-700 hidden">
                            Close
                        </button>
                    </div>
                </div>
            </div>
        `);
                $('body').append(modal);
            }

            // Show the modal
            $('#processingModal').removeClass('hidden');

            // Initialize progress
            $('#processingProgress').css('width', '0%');
            $('#closeProcessingModal').addClass('hidden');
        }

        // Update processing modal with results
        function updateProcessingModal(response) {
            const modal = $('#processingModal');
            if (!modal.length) return;

            // Update status text
            if (response.success) {
                $('#processingStatus').text(
                    `Processed ${response.summary.successful} orders, ${response.summary.failed} failed`);
                $('#processingProgress').css('width', '100%').addClass('bg-success');
            } else {
                $('#processingStatus').text('Processing failed');
                $('#processingProgress').css('width', '100%').addClass('bg-danger');
            }

            // Update individual order statuses
            if (response.processed) {
                response.processed.forEach(order => {
                    const $row = $(
                        `#processingOrdersList tr[data-order-number="${order.order_number}"]`);
                    if ($row.length) {
                        $row.find('td:nth-child(2)').html(
                            '<span class="badge badge-success">Success</span>');
                        $row.find('td:nth-child(3)').html(
                            `Tracking: ${order.tracking_id || 'N/A'}`
                        );
                    }
                });
            }

            if (response.failed) {
                response.failed.forEach(order => {
                    const $row = $(
                        `#processingOrdersList tr[data-order-number="${order.order_number}"]`);
                    if ($row.length) {
                        $row.find('td:nth-child(2)').html(
                            '<span class="badge badge-danger">Failed</span>');
                        $row.find('td:nth-child(3)').html(order.reason);
                    }
                });
            }

            // Show close button after 2 seconds
            setTimeout(() => {
                $('#closeProcessingModal').removeClass('hidden');
            }, 2000);
        }

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
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
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
                <td class="px-4 py-2">
                    <span class="badge badge-${failed.type === 'already_fulfilled' ? 'danger' : 'warning'}">
                        ${failed.type || 'error'}
                    </span>
                </td>
            </tr>
        `);
            });

            // Show the modal
            $('#failedOrdersModal').removeClass('hidden');
        }

        // Send orders button click handler (old method - keep for compatibility)
        $('#sendOrders').on('click', function() {
            const ordersData = [];
            let hasError = false;

            $('.order-checkbox:checked').each(function() {
                try {
                    const orderData = JSON.parse($(this).data('order-data'));

                    // Check if order can be sent
                    if (orderData.already_sent || orderData.already_fulfilled || !orderData
                        .can_be_processed) {
                        console.warn(`Order #${orderData.order_number} cannot be sent`);
                        return;
                    }

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
                alert('Please select at least one order that can be processed!');
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

                        // Update order status in table
                        $('.order-checkbox:checked').each(function() {
                            try {
                                const orderData = JSON.parse($(this).data(
                                    'order-data'));
                                const $row = $(this).closest('tr');

                                // Mark as already sent
                                $row.find('.order-checkbox').prop('disabled', true);
                                $row.addClass('table-success');
                                $row.find('td:first').append(
                                    '<span class="badge badge-success ms-2">✓ Sent</span>'
                                );
                            } catch (e) {
                                console.error('Error updating row:', e);
                            }
                        });

                        updateSelectedCount();
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

        // Add to your JavaScript file
        let nextPageInfo = null;
        let isLoadingMore = false;

        // Load more orders function
        function loadMoreOrders() {
            if (isLoadingMore || !nextPageInfo) return;

            isLoadingMore = true;
            $('#loadMoreBtn').prop('disabled', true).text('Loading...');

            $.ajax({
                url: "{{ route('orders.fetch') }}",
                method: "GET",
                data: {
                    limit: 100,
                    page_info: nextPageInfo,
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.success) {
                        // Append new orders to table
                        buildOrdersTable(response.orders, true); // append = true

                        // Update pagination info
                        nextPageInfo = response.pagination.next_page_info;

                        // Update load more button
                        if (nextPageInfo) {
                            $('#loadMoreBtn').prop('disabled', false).text('Load More Orders');
                        } else {
                            $('#loadMoreBtn').hide();
                            $('#ordersCount').text(`All ${response.total} orders loaded`);
                        }
                    }
                },
                error: function(xhr) {
                    console.error('Load more error:', xhr);
                    alert('Failed to load more orders');
                },
                complete: function() {
                    isLoadingMore = false;
                }
            });
        }

        // Update buildOrdersTable function to handle appending
        function buildOrdersTable(orders, append = false) {
            if (!append) {
                $('#ordersTable tbody').empty();
            }

            orders.forEach(order => {
                const row = `
            <tr>
                <td class="px-6 py-4 whitespace-nowrap">
                    <input type="checkbox"
                           class="order-checkbox"
                           data-order-data='${JSON.stringify(order).replace(/'/g, "\\'")}'
                           data-order-number="${order.order_number}">
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    ${order.order_number}
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    ${order.name}
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    ${order.customer ? (order.customer.first_name + ' ' + order.customer.last_name) : 'N/A'}
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    $${parseFloat(order.total_price).toFixed(2)}
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    ${order.created_at}
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                        ${order.fulfillment_status === 'fulfilled' ? 'bg-green-100 text-green-800' :
                          order.fulfillment_status === 'partial' ? 'bg-yellow-100 text-yellow-800' :
                          'bg-gray-100 text-gray-800'}">
                        ${order.fulfillment_status || 'unfulfilled'}
                    </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <select class="collect-payment border rounded px-2 py-1">
                        <option value="no">No</option>
                        <option value="yes">Yes</option>
                    </select>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    ${order.line_items.length} items
                </td>
            </tr>
        `;

                if (append) {
                    $('#ordersTable tbody').append(row);
                } else {
                    $('#ordersTable tbody').prepend(row);
                }
            });

            // Re-initialize checkboxes
            disableInvalidCheckboxes();
            updateSelectedCount();
        }

        // Add Load More button to your HTML
        // <button id="loadMoreBtn" class="mt-4 px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
        //     Load More Orders
        // </button>

        // Initialize on page load
        $(document).ready(function() {
            $('#loadMoreBtn').on('click', loadMoreOrders);
        });

    });
</script>
