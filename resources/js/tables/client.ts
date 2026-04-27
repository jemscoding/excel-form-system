
const formatClientCode = (code: string) => {
    return `AKPH-${code || ''}`;
};

export const ClientTableConfig = {
    columns: [
        {
            key: 'name',
            label: 'Client Name'
        },
        {

            key: 'code',
            label: 'Client Code',
            render: (value: any, item: any) =>  formatClientCode(value),
        }
    ],
    actions: ['view', 'edit', 'delete']
};