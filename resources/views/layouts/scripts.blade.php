<script>
    document.addEventListener("DOMContentLoaded", function () {
        // Backend Config
        const backendApiUrl = "{{ config('app.backend_api_url') }}";
        const backendApiKey = "{{ config('app.backend_api_key') }}";
        const csrfToken = document
            .querySelector('meta[name="csrf-token"]')
            ?.getAttribute("content");

        // Helpers
        const $ = (selector) => document.querySelector(selector);
        const $$ = (selector) => document.querySelectorAll(selector);

        /* =========================================
           CHECKBOX LOGIC
           ========================================= */
        const checkboxAll = $("#checkbox-all-search");
        const selectedCountEl = $("#selectedCount");

        function updateSelectedCount() {
            const checkedBoxes = $$(".order-checkbox:checked");
            if (selectedCountEl) {
                selectedCountEl.textContent = `${checkedBoxes.length} order${
                    checkedBoxes.length !== 1 ? "s" : ""
                } selected`;
            }
            if (checkboxAll) {
                const allCheckboxes = $$(".order-checkbox:not(:disabled)");
                checkboxAll.checked =
                    allCheckboxes.length > 0 &&
                    checkedBoxes.length === allCheckboxes.length;
                // Indeterminate state
                checkboxAll.indeterminate =
                    checkedBoxes.length > 0 &&
                    checkedBoxes.length < allCheckboxes.length;
            }
        }

        if (checkboxAll) {
            checkboxAll.addEventListener("change", function () {
                const isChecked = this.checked;
                $$(".order-checkbox:not(:disabled)").forEach((cb) => {
                    cb.checked = isChecked;
                });
                updateSelectedCount();
            });
        }

        // Delegated event listener for order checkboxes (to handle dynamic rows)
        document.body.addEventListener("change", function (e) {
            if (e.target.classList.contains("order-checkbox")) {
                updateSelectedCount();
            }
        });

        // Initial count
        updateSelectedCount();

        /* =========================================
           VALIDATION LOGIC
           ========================================= */
        function disableInvalidCheckboxes() {
            $$(".order-checkbox").forEach((checkbox) => {
                try {
                    const orderDataJson =
                        checkbox.getAttribute("data-order-data");
                    if (!orderDataJson) return;

                    const orderData = JSON.parse(orderDataJson);
                    const row = checkbox.closest("tr");

                    // Check if order can be processed
                    const canBeProcessed =
                        orderData.can_be_processed !== false &&
                        !orderData.already_sent &&
                        !orderData.already_fulfilled;

                    if (!canBeProcessed) {
                        // Disable
                        checkbox.disabled = true;
                        checkbox.checked = false;
                        row.classList.add("opacity-50");

                        // Reason Badge
                        let reason = "";
                        let badgeClass = "bg-gray-100 text-gray-800"; // Default gray

                        if (orderData.already_fulfilled) {
                            reason = "Already Fulfilled";
                            badgeClass = "bg-red-100 text-red-800";
                        } else if (orderData.already_sent) {
                            reason = "Already Sent";
                            badgeClass = "bg-yellow-100 text-yellow-800";
                        } else if (!orderData.can_be_processed) {
                            reason = "Cannot Process";
                            badgeClass = "bg-gray-100 text-gray-800";
                        }

                        // Remove existing badges
                        row.querySelectorAll(".status-badge").forEach((el) =>
                            el.remove()
                        );

                        if (reason) {
                            const firstCell =
                                row.querySelector("td:first-child") ||
                                row.cells[0];
                            const badge = document.createElement("span");
                            badge.className = `status-badge ml-2 px-2 py-0.5 rounded text-xs font-medium ${badgeClass}`;
                            badge.textContent = reason;
                            firstCell.appendChild(badge);
                        }
                    } else {
                        // Enable
                        checkbox.disabled = false;
                        row.classList.remove("opacity-50");
                        row.querySelectorAll(".status-badge").forEach((el) =>
                            el.remove()
                        );
                    }
                } catch (e) {
                    console.error("Error validation checkbox:", e);
                }
            });
        }

        // Run validation on load
        setTimeout(disableInvalidCheckboxes, 100);

        /* =========================================
           PROCESS SELECTED ORDERS (NEW)
           ========================================= */
        const processBtn = $("#processSelected");
        if (processBtn) {
            processBtn.addEventListener("click", async function () {
                const selectedOrders = [];
                const checkedBoxes = $$(".order-checkbox:checked");

                if (checkedBoxes.length === 0) {
                    alert("Please select at least one order!");
                    return;
                }

                // Collect Data
                let hasInvalidOrder = false;
                const invalidOrders = [];

                checkedBoxes.forEach((cb) => {
                    try {
                        const orderDataJson =
                            cb.getAttribute("data-order-data");
                        if (!orderDataJson) return;

                        const orderData = JSON.parse(orderDataJson);
                        const row = cb.closest("tr");
                        const collectPayment =
                            row.querySelector(".collect-payment")?.value ||
                            "no";
                        const parcelDetails =
                            row.querySelector(".parcel_details")?.value || "";

                        // Validate again
                        if (orderData.already_sent) {
                            invalidOrders.push({
                                order_number: orderData.order_number,
                                reason: "Already sent",
                            });
                            return;
                        }

                        selectedOrders.push({
                            order_id: orderData.id || orderData.merchant_id,
                            order_number: orderData.order_number,
                            collect_payment: collectPayment,
                            merchant_id: orderData.merchant_id,
                            total_price: parseFloat(orderData.total_price),
                            line_items: orderData.line_items,
                            shipping_address: orderData.shipping_address || {},
                            phone: orderData.phone || "",
                            name: orderData.name || "",
                            note: orderData.note || "",
                            email: orderData.email || "",
                            payment_type: orderData.payment_type || "prepaid",
                            pieces: orderData.pieces || 0,
                            weight: orderData.weight || 0,
                            currency: orderData.currency || "USD",
                            total_tax: orderData.total_tax || 0,
                            discount_codes: orderData.discount_codes || [],
                            parcel_details: parcelDetails,
                        });
                    } catch (e) {
                        console.error("Error parsing selected order:", e);
                        hasInvalidOrder = true;
                    }
                });

                if (invalidOrders.length > 0 || hasInvalidOrder) {
                    alert(
                        `Cannot process ${invalidOrders.length} orders. Check console/badges.`
                    );
                    return;
                }

                if (
                    !confirm(
                        `Process ${selectedOrders.length} order(s)?\nThis will send orders to GreenEx.`
                    )
                ) {
                    return;
                }

                // UI Loading
                const originalText = processBtn.textContent;
                processBtn.disabled = true;
                processBtn.textContent = "Processing...";

                showProcessingModal(selectedOrders);

                try {
                    const response = await fetch(
                        "{{ route('orders.process-selected') }}",
                        {
                            method: "POST",
                            headers: {
                                "Content-Type": "application/json",
                                "X-CSRF-TOKEN": csrfToken,
                                Accept: "application/json",
                            },
                            body: JSON.stringify({
                                selected_orders: selectedOrders,
                            }),
                        }
                    );

                    const data = await response.json();

                    updateProcessingModal(data);

                    if (data.success && data.processed) {
                        // Mark rows as success
                        data.processed.forEach((po) => {
                            const rows = $$("tr");
                            // Find row by iterating (no clean data selector for tr typically unless added)
                            // But our checkbox has data-order-number.
                            // Actually the loop above uses cb.closest('tr').
                            // We can find by querySelector.
                            const cb = document.querySelector(
                                `.order-checkbox[data-order-number="${po.order_number}"]`
                            );
                            if (cb) {
                                const row = cb.closest("tr");
                                cb.disabled = true;
                                cb.checked = false; // uncheck processed
                                row.classList.add("bg-green-50");

                                const firstCell =
                                    row.querySelector("td:first-child") ||
                                    row.cells[0];
                                // Check if badge exists
                                if (!firstCell.querySelector(".badge-sent")) {
                                    const badge =
                                        document.createElement("span");
                                    badge.className =
                                        "badge-sent status-badge ml-2 px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800";
                                    badge.textContent = `✓ Sent | ${
                                        po.tracking_id || ""
                                    }`;
                                    firstCell.appendChild(badge);
                                }
                            }
                        });
                        updateSelectedCount();
                    } else {
                        // Error handled by modal
                        console.error("Processing failed partial/full", data);
                    }
                } catch (error) {
                    console.error("Fetch error:", error);
                    updateProcessingModal({
                        success: false,
                        message: error.message,
                    });
                    alert("Network or Server Error");
                } finally {
                    processBtn.disabled = false;
                    processBtn.textContent = originalText;
                }
            });
        }

        /* =========================================
           MODAL LOGIC (Vanilla)
           ========================================= */
        function showProcessingModal(selectedOrders) {
            let modal = $("#processingModal");
            if (!modal) {
                const modalHtml = `
            <div id="processingModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
                <div class="relative top-20 mx-auto p-5 border w-1/2 shadow-lg rounded-md bg-white">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-medium text-gray-900">Processing Orders</h3>
                        <button id="closeModalCross" class="text-gray-400 hover:text-gray-600">✕</button>
                    </div>
                    <div class="mb-4">
                        <div class="flex items-center">
                            <div class="animate-spin rounded-full h-5 w-5 border-t-2 border-b-2 border-blue-500 mr-3"></div>
                            <span id="processingStatus">Processing ${
                                selectedOrders.length
                            } orders...</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2.5 mt-2">
                            <div id="processingProgress" class="bg-blue-600 h-2.5 rounded-full" style="width: 0%"></div>
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
                                ${selectedOrders
                                    .map(
                                        (o) => `
                                    <tr data-on="${o.order_number}">
                                        <td class="px-4 py-2 text-sm font-medium text-gray-900">#${o.order_number}</td>
                                        <td class="px-4 py-2"><span class="px-2 py-1 rounded text-xs bg-blue-100 text-blue-800">Pending</span></td>
                                        <td class="px-4 py-2 text-sm text-gray-500">Waiting...</td>
                                    </tr>
                                `
                                    )
                                    .join("")}
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-4 flex justify-end">
                        <button id="closeProcessingModal" class="hidden px-4 py-2 bg-gray-600 text-white rounded hover:bg-gray-700">Close</button>
                    </div>
                </div>
            </div>`;
                document.body.insertAdjacentHTML("beforeend", modalHtml);
                modal = $("#processingModal"); // Re-select

                // Add listeners
                $("#closeModalCross").addEventListener("click", () =>
                    modal.classList.add("hidden")
                );
                $("#closeProcessingModal").addEventListener("click", () =>
                    modal.classList.add("hidden")
                );
            }

            modal.classList.remove("hidden");
            $("#processingProgress").style.width = "0%";
            $("#processingProgress").className =
                "bg-blue-600 h-2.5 rounded-full";
            $("#closeProcessingModal").classList.add("hidden");
            $(
                "#processingStatus"
            ).textContent = `Processing ${selectedOrders.length} orders...`;
        }

        function updateProcessingModal(data) {
            const modal = $("#processingModal");
            if (!modal) return;

            const statusEl = $("#processingStatus");
            const progressEl = $("#processingProgress");
            const closeBtn = $("#closeProcessingModal");

            if (data.success) {
                statusEl.textContent = `Completed: ${data.summary.successful} success, ${data.summary.failed} failed`;
                progressEl.style.width = "100%";
                progressEl.className = "bg-green-600 h-2.5 rounded-full";
            } else {
                statusEl.textContent = "Processing Failed";
                progressEl.style.width = "100%";
                progressEl.className = "bg-red-600 h-2.5 rounded-full";
            }

            if (data.processed) {
                data.processed.forEach((po) => {
                    const row = modal.querySelector(
                        `tr[data-on="${po.order_number}"]`
                    );
                    if (row) {
                        row.children[1].innerHTML =
                            '<span class="px-2 py-1 rounded text-xs bg-green-100 text-green-800">Success</span>';
                        row.children[2].innerHTML = `Tracking: ${po.tracking_id}`;
                    }
                });
            }

            if (data.failed) {
                data.failed.forEach((fo) => {
                    const row = modal.querySelector(
                        `tr[data-on="${fo.order_number}"]`
                    );
                    if (row) {
                        row.children[1].innerHTML =
                            '<span class="px-2 py-1 rounded text-xs bg-red-100 text-red-800">Failed</span>';
                        row.children[2].innerHTML = fo.reason;
                    }
                });
            }

            setTimeout(() => {
                closeBtn.classList.remove("hidden");
            }, 1000);
        }

        /* =========================================
           LOAD MORE
           ========================================= */
        let nextPageInfo = null;
        const loadMoreBtn = $("#loadMoreBtn");

        if (loadMoreBtn) {
            loadMoreBtn.addEventListener("click", async function () {
                if (!nextPageInfo) return;

                const originalText = this.textContent;
                this.disabled = true;
                this.textContent = "Loading...";

                try {
                    const res = await fetch(
                        `{{ route('orders.fetch') }}?limit=100&page_info=${nextPageInfo}`,
                        {
                            headers: {
                                Accept: "application/json",
                                "X-CSRF-TOKEN": csrfToken,
                            },
                        }
                    );
                    const data = await res.json();

                    if (data.success) {
                        buildOrdersTable(data.orders, true);
                        nextPageInfo = data.pagination?.next_page_info;

                        if (!nextPageInfo) {
                            this.style.display = "none";
                            const countEl = $("#ordersCount"); // Assuming this exists
                            if (countEl)
                                countEl.textContent = `All ${data.total} loaded`;
                        } else {
                            this.textContent = "Load More Orders";
                        }
                    } else {
                        alert("Failed to load");
                    }
                } catch (e) {
                    console.error(e);
                    alert("Error loading more");
                } finally {
                    this.disabled = false;
                    if (this.textContent === "Loading...")
                        this.textContent = originalText;
                }
            });
        }

        function loadOrders() {
            window.location.reload();
        }

        function buildOrdersTable(orders, append) {
            const tbody = $("#ordersTable tbody");
            if (!tbody) return;

            if (!append) tbody.innerHTML = "";

            const fragment = document.createDocumentFragment();

            orders.forEach((order) => {
                const tr = document.createElement("tr");
                tr.innerHTML = `
                    <td class="px-6 py-4 whitespace-nowrap">
                        <input type="checkbox"
                               class="order-checkbox"
                               data-order-data='${JSON.stringify(order).replace(
                                   /'/g,
                                   "\\'"
                               )}'
                               data-order-number="${order.order_number}">
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">${
                        order.order_number
                    }</td>
                    <td class="px-6 py-4 whitespace-nowrap">${order.name}</td>
                    <td class="px-6 py-4 whitespace-nowrap">${
                        order.customer
                            ? order.customer.first_name +
                              " " +
                              order.customer.last_name
                            : "N/A"
                    }</td>
                    <td class="px-6 py-4 whitespace-nowrap">$${parseFloat(
                        order.total_price
                    ).toFixed(2)}</td>
                    <td class="px-6 py-4 whitespace-nowrap">${
                        order.created_at
                    }</td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                            ${
                                order.fulfillment_status === "fulfilled"
                                    ? "bg-green-100 text-green-800"
                                    : order.fulfillment_status === "partial"
                                    ? "bg-yellow-100 text-yellow-800"
                                    : "bg-gray-100 text-gray-800"
                            }">
                            ${order.fulfillment_status || "unfulfilled"}
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
                `;
                fragment.appendChild(tr);
            });

            if (append) {
                tbody.appendChild(fragment);
            } else {
                tbody.prepend(fragment);
            }

            disableInvalidCheckboxes();
            updateSelectedCount();
        }
    });
</script>
