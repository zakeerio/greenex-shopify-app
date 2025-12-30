import {
    Page,
    Layout,
    LegacyCard,
    FormLayout,
    TextField,
    Select,
    Button,
    BlockStack,
    Box
} from '@shopify/polaris';
import { useForm } from '@inertiajs/react';

export default function SettingsIndex({ settings }) {
    const { data, setData, post, processing, errors } = useForm({
        email: settings?.email || '',
        password: '', // Password is not passed from backend for security
        apikey: settings?.api_token ? '********' : '', // Masked or empty
        fullfilment: settings?.fulfillment_location || '',
        fragile: settings?.fragile ? 'Yes' : 'No',
        insurance: settings?.insurance ? 'Yes' : 'No',
        account_type: settings?.account_type || 'live',
        auto_push_orders: settings?.auto_push_cms ? 'Yes' : 'No',
        price: settings?.price || '',
        user_id: settings?.user_id || '', // or handle in backend
    });

    const handleSubmit = () => {
        post('/settings/authenticate', {
            onSuccess: () => {
                console.log('Settings saved');
            },
        });
    };

    // Handler for Save Account Settings (if it's different)
    const handleSave = () => {
        // If "Save Account Settings" points to a different route, use it.
        // But based on analysis, authenticateAndSave seems comprehensive.
        // If specifically needing /settings/save, we can add another handler.
        post('/settings/save', { // 'store' route
            onSuccess: () => console.log('Settings saved'),
        });
    };

    return (
        <Page title="Settings">
            <Layout>
                <Layout.Section>
                    <LegacyCard sectioned>
                        <FormLayout>
                            <FormLayout.Group>
                                <TextField
                                    label="Email"
                                    value={data.email}
                                    onChange={(val) => setData('email', val)}
                                    autoComplete="email"
                                    error={errors.email}
                                />
                                <TextField
                                    label="Password"
                                    type="password"
                                    value={data.password}
                                    onChange={(val) => setData('password', val)}
                                    autoComplete="password"
                                    error={errors.password}
                                />
                            </FormLayout.Group>

                            <FormLayout.Group>
                                <Select
                                    label="Fulfillment Location"
                                    options={[
                                        { label: 'Select Location', value: '' },
                                        { label: 'Islamabad', value: 'islamabad' },
                                        { label: 'Lahore', value: 'lahore' },
                                        { label: 'Faislabad', value: 'faislabad' },
                                        { label: 'Rawalpindi', value: 'rawalpindi' },
                                        { label: 'Karachi', value: 'karachi' },
                                    ]}
                                    value={data.fullfilment}
                                    onChange={(val) => setData('fullfilment', val)}
                                    error={errors.fullfilment}
                                />
                                <Select
                                    label="Fragile"
                                    options={[
                                        { label: 'Select', value: '' },
                                        { label: 'Yes', value: 'Yes' },
                                        { label: 'No', value: 'No' },
                                    ]}
                                    value={data.fragile}
                                    onChange={(val) => setData('fragile', val)}
                                />
                            </FormLayout.Group>

                            <TextField
                                label="API Key"
                                value={data.apikey}
                                onChange={(val) => setData('apikey', val)}
                                autoComplete="off"
                                error={errors.apikey}
                            />

                            <FormLayout.Group>
                                <Select
                                    label="Insurance"
                                    options={[
                                        { label: 'Select', value: '' },
                                        { label: 'Yes', value: 'Yes' },
                                        { label: 'No', value: 'No' },
                                    ]}
                                    value={data.insurance}
                                    onChange={(val) => setData('insurance', val)}
                                />
                                <Select
                                    label="Account Type"
                                    options={[
                                        { label: 'Select', value: '' },
                                        { label: 'Live', value: 'live' },
                                        { label: 'Offline', value: 'offline' },
                                    ]}
                                    value={data.account_type}
                                    onChange={(val) => setData('account_type', val)}
                                />
                            </FormLayout.Group>

                            <FormLayout.Group>
                                <Select
                                    label="Order Push Automatically on CMS"
                                    options={[
                                        { label: 'Select', value: '' },
                                        { label: 'Yes', value: 'Yes' },
                                        { label: 'No', value: 'No' },
                                    ]}
                                    value={data.auto_push_orders}
                                    onChange={(val) => setData('auto_push_orders', val)}
                                />
                                <TextField
                                    label="Price"
                                    type="number"
                                    value={data.price}
                                    onChange={(val) => setData('price', val)}
                                    autoComplete="off"
                                    placeholder="$2999"
                                />
                            </FormLayout.Group>

                            <Box paddingBlockStart="400">
                                <BlockStack gap="400" inlineAlign="start">
                                    <div style={{ display: 'flex', gap: '10px' }}>
                                        <Button variant="primary" onClick={handleSubmit} loading={processing}>
                                            Authenticate Account
                                        </Button>
                                        <Button onClick={handleSave} loading={processing}>
                                            Save Account Settings
                                        </Button>
                                    </div>
                                </BlockStack>
                            </Box>

                        </FormLayout>
                    </LegacyCard>
                </Layout.Section>
            </Layout>
        </Page>
    );
}
