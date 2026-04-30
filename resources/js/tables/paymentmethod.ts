const formatPaymentMethodCode = (code: any, name: any) => {
    if (!code && !name) return 'N/A';
    if (!code) return name;
    if (!name) return `${code}`;
    return `${name} (${code})`;
};

export const PaymentMethodsTableConfig = {
    columns: [
        {
            key: 'name',
            label: 'Payment Method',
            render: (value: any, item: any) => formatPaymentMethodCode(item.code, item.name),
        },
    ],
    actions: ['view', 'edit', 'delete']
};