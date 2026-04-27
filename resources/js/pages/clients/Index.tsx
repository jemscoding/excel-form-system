// Routes
import { Head, Link, router } from '@inertiajs/react';
import ClientController from '@/actions/App/Http/Controllers/ClientController';

// Hooks
import { useEffect, useState } from 'react';

// Layouts
import AppLayout from '@/layouts/app-layout';
import { Button } from '@/components/ui/button';
import { Header } from '@/components/header';
import TableList from '@/components/table-list';
import type { BreadcrumbItem } from '@/types';
import { Users } from 'lucide-react';

// Data Setup
import { Client } from '@/interfaces/interfaces';
import { ClientProps } from '@/props/props'
import { ClientTableConfig } from '@/tables/client';


const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Clients',
        href: '/clients',
    },
]
Index.layout = (page: React.ReactNode) =>
    <AppLayout breadcrumbs={breadcrumbs}>
        {page}
    </AppLayout>;

export default function Index({ clients }: ClientProps) {
    const [showClients, setShowClients] = useState<Client[]>([]);

    useEffect(() => {
        const timeoutId = setTimeout(() => {
            setShowClients(clients);
        }, 1000);

        return () => clearTimeout(timeoutId);
    }, [clients]);


    const handleEdit = (id: number) => {
        console.log(`Edit client with ID: ${id}`);
        router.visit(ClientController.edit(id));
    };

    const handleDelete = (client: Client) => {
        if (confirm('Are you sure you want to delete this client? ')) {
            console.log(`Delete client with ID: ${id}`);
            router.delete(ClientController.destroy(id), {
                onSuccess: () => {
                    console.log('Client deleted successfully');
                },
                onError: (errors) => {
                    console.error('Error deleting client:', errors);
                }
            });
        }
    };

    return (
        <>
            <Head title="Clients" />
            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4 mx-4">
                <div className="flex pp-header justify-between">
                    <Header
                        icon={<Users />}
                        title="Clients"
                        description="List of all clients"
                    />

                    <Link href={ClientController.create()} className="flex items-center">
                        <Button className='cursor-pointer'>
                            + Add Client
                        </Button>
                    </Link>
                </div>

                <div className="pp-row">
                    <TableList
                        data={clients}
                        columns={ClientTableConfig.columns}
                        actions={ClientTableConfig.actions}
                        indexLabel="#"
                        indexStartFrom={1}
                        showIndex={true}
                        onEdit={handleEdit}
                        onView={() => { }}
                        onDelete={handleDelete}
                        emptyTableMessage={{
                            icon: <Users />,
                            title: "No clients Found",
                            description: "Click Add Product to see them listed here.",
                            onActionClick: () => router.visit('/clients/Create'),
                            buttonText: "Client"
                        }}
                    />
                </div>
            </div>
        </>
    )
}