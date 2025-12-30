import {
    Page,
    Layout,
    LegacyCard,
    IndexTable,
    useIndexResourceState,
    Text,
    Badge,
    Button,
    ButtonGroup,
    InlineStack,
    TextField,
    Select
} from '@shopify/polaris';
import { useState } from 'react';
import { router } from '@inertiajs/react';
import { RefreshIcon, ExportIcon } from '@shopify/polaris-icons';

export default function OrdersIndex({ orders, totalOrders, pagination }) {
    const resourceName = {
        singular: 'order',
        plural: 'orders',
    };

    const { selectedResources, allResourcesSelected, handleSelectionChange } =
        useIndexResourceState(orders);

    const [selectedPaymentMethods, setSelectedPaymentMethods] = useState({});

    // Bulk actions
    const promotedBulkActions = [
        {
            content: 'Process Selected (Guzzle)',
            onAction: () => handleProcessSelected(),
        },
    ];

    const handleProcessSelected = () => {
        if (selectedResources.length === 0) return;

        // Map selected resources to required format
        const selectedOrders = selectedResources.map(id => {
            const order = orders.find(o => o.id === id);
            return {
                order_id: order.id,
                order_number: order.order_number,
                collect_payment: selectedPaymentMethods[id] || (order.payment_type === 'COD' ? 'yes' : 'no')
            };
        });

        router.post('/orders/process-selected', { selected_orders: selectedOrders }, {
            onSuccess: () => {
                // Handle success (toast, refresh)
                console.log('Orders processing started');
                // shopify.toast.show('Orders processing started');
                // clear selection?
                handleSelectionChange("all", false);
            },
            preserveScroll: true
        });
    };

    const handleRefresh = () => {
        router.reload();
    };

    const handlePaymentChange = (id, value) => {
        setSelectedPaymentMethods(prev => ({ ...prev, [id]: value }));
    };

    const rowMarkup = orders.map(
        (
            { id, order_number, shipping_address, customer, contact_email, current_total_price, pieces, weight, payment_type, financial_status, can_be_processed, already_sent, already_fulfilled },
            index,
        ) => {
            const name = shipping_address?.name || customer?.name || 'N/A';
            const phone = shipping_address?.phone || customer?.phone || 'N/A';
            const displayPaymentType = payment_type || (financial_status === 'paid' ? 'PREPAID' : 'COD');

            // Payment collect selection state
            const collectPayment = selectedPaymentMethods[id] || (displayPaymentType === 'COD' ? 'yes' : 'no');

            return (
                <IndexTable.Row
                    id={id}
                    key={id}
                    selected={selectedResources.includes(id)}
                    position={index}
                >
                    <IndexTable.Cell>
                        <Text variant="bodyMd" fontWeight="bold" as="span">
                            #{order_number}
                        </Text>
                    </IndexTable.Cell>
                    <IndexTable.Cell>{name}</IndexTable.Cell>
                    <IndexTable.Cell>{contact_email}</IndexTable.Cell>
                    <IndexTable.Cell>{phone}</IndexTable.Cell>
                    <IndexTable.Cell>
                        <div style={{ width: '100px' }}>
                            <Select
                                options={[
                                    { label: 'Yes', value: 'yes' },
                                    { label: 'No', value: 'no' },
                                ]}
                                value={collectPayment}
                                onChange={(val) => handlePaymentChange(id, val)}
                                disabled={displayPaymentType !== 'COD'}
                            />
                        </div>
                    </IndexTable.Cell>
                    <IndexTable.Cell>
                        <Text as="span" numeric>{current_total_price}</Text>
                    </IndexTable.Cell>
                    <IndexTable.Cell>{pieces}</IndexTable.Cell>
                    <IndexTable.Cell>{weight} kg</IndexTable.Cell>
                    <IndexTable.Cell>
                        <Badge tone={displayPaymentType === 'COD' ? 'warning' : 'success'}>
                            {displayPaymentType}
                        </Badge>
                    </IndexTable.Cell>
                    <IndexTable.Cell>
                        <TextField
                            autoComplete="off"
                            placeholder='Details'
                            labelHidden
                            label="Parcel Details"
                        />
                    </IndexTable.Cell>
                </IndexTable.Row>
            );
        },
    );

    return (
        <Page
            title="Orders"
            primaryAction={{ content: 'Refresh Orders', icon: RefreshIcon, onAction: handleRefresh }}
        >
            <Layout>
                <Layout.Section>
                    <LegacyCard>
                        <IndexTable
                            resourceName={resourceName}
                            itemCount={orders.length}
                            selectedItemsCount={
                                allResourcesSelected ? 'All' : selectedResources.length
                            }
                            onSelectionChange={handleSelectionChange}
                            headings={[
                                { title: 'ID' },
                                { title: 'Name' },
                                { title: 'Email' },
                                { title: 'Phone' },
                                { title: 'Collect Payment' },
                                { title: 'Amount' },
                                { title: 'Pieces' },
                                { title: 'Weight' },
                                { title: 'Payment Type' },
                                { title: 'Parcel Details' },
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
