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
import { CreditCard, Plus } from 'lucide-react';

// Data Setup
import { DepositingBank } from '@/interfaces/interfaces';
import { DepositingBankProps } from '@/props/props'
import { DepositingBankTableConfig } from '@/tables/depositingbank';
import DepositingBankController from '@/actions/App/Http/Controllers/DepositingBankController';


const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Depositing Banks',
        href: DepositingBankController.index(),
    },
]
Index.layout = (page: React.ReactNode) =>
    <AppLayout breadcrumbs={breadcrumbs}>
        {page}
    </AppLayout>;

export default function Index({ depositingBanks }: DepositingBankProps) {
    const [showPayments, setShowPayments] = useState<DepositingBank[]>([]);

    useEffect(() => {
        const timeoutId = setTimeout(() => {
            setShowPayments(depositingBanks);
        }, 1000);

        return () => clearTimeout(timeoutId);
    }, [depositingBanks]);


    const handleEdit = (depositingBanks: DepositingBank) => {
        console.log(`Edit payment method with ID: ${depositingBanks.id}`);
        router.visit(DepositingBankController.edit(depositingBanks.id));
    };

    const handleDelete = (depositingBanks: DepositingBank) => {
        if (confirm('Are you sure you want to delete this client? ')) {
            console.log(`Delete payment method with ID: ${depositingBanks.id}`);
            router.delete(DepositingBankController.destroy(depositingBanks.id), {
                onSuccess: () => {
                    console.log('Depositing Bank deleted successfully');
                },
                onError: (errors) => {
                    console.error('Error deleting deposiiting bank:', errors);
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
                        icon={<CreditCard />}
                        title="Depositing Banks"
                        description="List of all depositing banks"
                    />

                    {depositingBanks.length >= 1 && (
                    <Link href={DepositingBankController.create()} className="flex items-center">
                        <Button className='cursor-pointer'>
                            <Plus /> Add Depositing Bank
                        </Button>
                    </Link>
                    )}

                </div>

                <div className="pp-row">
                    <TableList
                        data={depositingBanks}
                        columns={DepositingBankTableConfig.columns}
                        actions={DepositingBankTableConfig.actions}
                        indexLabel="#"
                        indexStartFrom={1}
                        showIndex={true}
                        onEdit={handleEdit}
                        onView={() => { }}
                        onDelete={handleDelete}
                        emptyTableMessage={{
                            icon: <CreditCard />,
                            title: "No depositing bank Found",
                            description: "Click Add Depositing Bank to see them listed here.",
                            onActionClick: () => router.visit(DepositingBankController.create()),
                            buttonText: "Depositing Bank"
                        }}
                    />
                </div>
            </div>
        </>
    )
}