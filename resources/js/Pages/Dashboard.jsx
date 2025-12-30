import { Page, Layout, LegacyCard, Text, BlockStack } from '@shopify/polaris';
import {
    BoxIcon,
    DeliveryIcon,
    ArrowLeftIcon,
    CurrencyIcon,
    CreditCardIcon,
    ProfileIcon
} from '@shopify/polaris-icons';

// Mapping functionality for icons can be added, for now using generic logic or simplified icons.

const StatCard = ({ title, value, icon: Icon }) => (
    <LegacyCard sectioned>
        <BlockStack gap="200">
            <Text variant="headingSm" as="h6" tone="subdued">
                {title}
            </Text>
            <div style={{ display: 'flex', alignItems: 'center', gap: '8px' }}>
                {Icon && (
                    <div style={{ width: '24px', color: '#5c5f62' }}>
                        <Icon />
                    </div>
                )}
                <Text variant="headingxl" as="h4">
                    {value}
                </Text>
            </div>
        </BlockStack>
    </LegacyCard>
);

export default function Dashboard({ data }) {
    // Helper to calculate safely
    const safeVal = (val) => val || 0;
    const t_parcel = safeVal(data.t_parcel);
    const t_delivered = safeVal(data.t_delivered);
    const t_return = safeVal(data.t_return);
    const t_transit = t_parcel - t_delivered - t_return;

    return (
        <Page title="Dashboard">
            <Layout>
                <Layout.Section>
                    <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fill, minmax(240px, 1fr))', gap: '16px' }}>
                        <StatCard title="Total Parcel" value={t_parcel} icon={BoxIcon} />
                        <StatCard title="Total Delivered" value={t_delivered} icon={DeliveryIcon} />
                        <StatCard title="Total Return" value={t_return} icon={ArrowLeftIcon} />
                        <StatCard title="Total Transit" value={t_transit} icon={DeliveryIcon} />

                        <StatCard title="Total Sales Amount" value={data.t_sale} icon={CurrencyIcon} />
                        <StatCard title="Total Delivery Fees Paid" value={data.t_delivery_fee} icon={CurrencyIcon} />
                        <StatCard title="Total Vat Amount" value={data.t_vat_amount} icon={CurrencyIcon} />
                        <StatCard title="Net Profit Amount" value={data.t_liquid_fragile} icon={CurrencyIcon} />

                        <StatCard title="Current Balance" value={data.t_balance_paid} icon={CreditCardIcon} />
                        <StatCard title="Opening Balance" value={data.t_balance_proc} icon={CurrencyIcon} />
                        <StatCard title="Payment Processing" value={data.t_balance_proc} icon={CurrencyIcon} />
                        <StatCard title="Paid Amount" value={data.t_balance_paid} icon={CurrencyIcon} />

                        <StatCard title="Total Shops" value={data.t_shop} icon={ProfileIcon} />
                        <StatCard title="Total Parcel Bank Items" value={data.t_parcel_bank} icon={BoxIcon} />
                        <StatCard title="Total Payment Request" value={data.t_request} icon={CurrencyIcon} />
                    </div>
                </Layout.Section>
            </Layout>
        </Page>
    );
}
