import {
    Page,
    Layout,
    LegacyCard,
    IndexTable,
    useIndexResourceState,
    Text,
    Badge,
} from '@shopify/polaris';
import { router } from '@inertiajs/react';

export default function ShipmentsIndex({ shipments }) {
    const resourceName = {
        singular: 'shipment',
        plural: 'shipments',
    };

    const { selectedResources, allResourcesSelected, handleSelectionChange } =
        useIndexResourceState(shipments);

    const handlePrintSelected = () => {
        if (selectedResources.length === 0) {
            console.log('Please select at least one shipment');
            return;
        }

        // We need to submit a form to open in new tab for printing
        // Or we can use Inertia to post, but it handles response in SPA way.
        // For printing, we usually want a separate tab.
        // Since the backend 'bulkPrint' returns a view (which is likely a printable HTML page),
        // we should construct a form and submit it.

        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '/shipments/print'; // Adjust route if needed
        form.target = '_blank';

        // CSRF Token
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        if (csrfToken) {
            const csrfInput = document.createElement('input');
            csrfInput.type = 'hidden';
            csrfInput.name = '_token';
            csrfInput.value = csrfToken;
            form.appendChild(csrfInput);
        }

        selectedResources.forEach(id => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'selected_shipments[]';
            input.value = id;
            form.appendChild(input);
        });

        document.body.appendChild(form);
        form.submit();
        document.body.removeChild(form);
    };

    const promotedBulkActions = [
        {
            content: 'Print Selected',
            onAction: handlePrintSelected,
        },
    ];

    const statusColors = {
        1: 'critical', // Pending -> Red
        2: 'warning',  // Pickup Assign -> Yellow
        3: 'success', // Delivered -> Green
    };

    const rowMarkup = shipments.map(
        (
            { id, tracking_id, customer_name, customer_phone, cod_amount, weight, invoice_no, customer_address, deliveryType, status, statusName },
            index,
        ) => (
            <IndexTable.Row
                id={id}
                key={id}
                selected={selectedResources.includes(id)}
                position={index}
            >
                <IndexTable.Cell>
                    <Text variant="bodyMd" fontWeight="bold" as="span">
                        #{id}
                    </Text>
                </IndexTable.Cell>
                <IndexTable.Cell>
                    <Text color="interactive">
                        {tracking_id}
                    </Text>
                </IndexTable.Cell>
                <IndexTable.Cell>{customer_name}</IndexTable.Cell>
                <IndexTable.Cell>{customer_phone}</IndexTable.Cell>
                <IndexTable.Cell>
                    <Text fontWeight="semibold">Rs {new Intl.NumberFormat().format(cod_amount)}</Text>
                </IndexTable.Cell>
                <IndexTable.Cell>{weight}</IndexTable.Cell>
                <IndexTable.Cell>{invoice_no}</IndexTable.Cell>
                <IndexTable.Cell>
                    <div style={{ maxWidth: '200px', whiteSpace: 'normal' }}>
                        {customer_address}
                    </div>
                </IndexTable.Cell>
                <IndexTable.Cell>{deliveryType}</IndexTable.Cell>
                <IndexTable.Cell>
                    <Badge tone={statusColors[status] || 'info'}>
                        {statusName}
                    </Badge>
                </IndexTable.Cell>
                <IndexTable.Cell>
                    <Text color="subdued">Disabled</Text>
                </IndexTable.Cell>
            </IndexTable.Row>
        ),
    );

    return (
        <Page title="Shipments">
            <Layout>
                <Layout.Section>
                    <LegacyCard>
                        <IndexTable
                            resourceName={resourceName}
                            itemCount={shipments.length}
                            selectedItemsCount={
                                allResourcesSelected ? 'All' : selectedResources.length
                            }
                            onSelectionChange={handleSelectionChange}
                            headings={[
                                { title: 'ID' },
                                { title: 'Tracking ID' },
                                { title: 'Customer' },
                                { title: 'Phone' },
                                { title: 'COD Amount' },
                                { title: 'Weight' },
                                { title: 'Invoice' },
                                { title: 'Ship Address' },
                                { title: 'Delivery Type' },
                                { title: 'Status' },
                                { title: 'Actions' },
                            ]}
                            promotedBulkActions={promotedBulkActions}
                        >
                            {rowMarkup}
                        </IndexTable>
                    </LegacyCard>
                </Layout.Section>
            </Layout>
        </Page>
    );
}
