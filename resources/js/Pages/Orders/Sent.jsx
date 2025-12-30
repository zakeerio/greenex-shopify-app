import {
    Page,
    Layout,
    LegacyCard,
    IndexTable,
    Text,
    Badge,
    Button,
    Pagination
} from '@shopify/polaris';
import { router } from '@inertiajs/react';

export default function SentOrders({ sentOrders }) {
    const orders = sentOrders.data;

    // Pagination handlers
    const onPrevious = () => {
        if (sentOrders.prev_page_url) {
            router.visit(sentOrders.prev_page_url);
        }
    };

    const onNext = () => {
        if (sentOrders.next_page_url) {
            router.visit(sentOrders.next_page_url);
        }
    };

    const rowMarkup = orders.map(
        (
            { id, order_number, tracking_id, status, sent_at, portal_order_id, shopify_updated },
            index,
        ) => (
            <IndexTable.Row id={id} key={id} position={index}>
                <IndexTable.Cell>
                    <Text variant="bodyMd" fontWeight="bold" as="span">
                        #{order_number}
                    </Text>
                </IndexTable.Cell>
                <IndexTable.Cell>{tracking_id}</IndexTable.Cell>
                <IndexTable.Cell>{portal_order_id}</IndexTable.Cell>
                <IndexTable.Cell>
                    <Badge tone={status === 'confirmed' ? 'success' : 'info'}>
                        {status}
                    </Badge>
                </IndexTable.Cell>
                <IndexTable.Cell>
                    <Badge tone={shopify_updated ? 'success' : 'warning'}>
                        {shopify_updated ? 'Yes' : 'No'}
                    </Badge>
                </IndexTable.Cell>
                <IndexTable.Cell>
                    {new Date(sent_at).toLocaleString()}
                </IndexTable.Cell>
            </IndexTable.Row>
        ),
    );

    return (
        <Page title="Sent Orders History">
            <Layout>
                <Layout.Section>
                    <LegacyCard>
                        <IndexTable
                            resourceName={{ singular: 'sent order', plural: 'sent orders' }}
                            itemCount={orders.length}
                            headings={[
                                { title: 'Order Number' },
                                { title: 'Tracking ID' },
                                { title: 'Portal ID' },
                                { title: 'Status' },
                                { title: 'Shopify Updated' },
                                { title: 'Sent At' },
                            ]}
                            selectable={false}
                        >
                            {rowMarkup}
                        </IndexTable>
                        <div style={{ display: 'flex', justifyContent: 'center', padding: '16px' }}>
                            <Pagination
                                hasPrevious={!!sentOrders.prev_page_url}
                                onPrevious={onPrevious}
                                hasNext={!!sentOrders.next_page_url}
                                onNext={onNext}
                            />
                        </div>
                    </LegacyCard>
                </Layout.Section>
            </Layout>
        </Page>
    );
}
