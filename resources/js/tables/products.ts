const formatProductCode = (code: any ,name: any) => {
    if (!code && !name) return 'N/A';
    if (!code) return name;
    if (!name) return `${code}`;
    return `${name} ${code}`;
}

export const ProductTableConfig = {
    columns: [
        {
            key: 'name',
            label: 'Product Name & Code',
            render : (value: any, item: any) => formatProductCode(item.code, item.name)
        },
    ],
    actions: ['view', 'edit', 'delete']
};