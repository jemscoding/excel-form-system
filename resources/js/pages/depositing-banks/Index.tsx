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
import { PaymentMethod } from '@/interfaces/interfaces';
import { PaymentMethodProps } from '@/props/props'
import { ClientTableConfig } from '@/tables/client';
import PaymentMethodController from '@/actions/App/Http/Controllers/PaymentMethodController';
import { PaymentMethodsTableConfig } from '@/tables/paymentmethod';


const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Payment Methods',
        href: '/payment-methods',
    },
]
Index.layout = (page: React.ReactNode) =>
    <AppLayout breadcrumbs={breadcrumbs}>
        {page}
    </AppLayout>;

export default function Index({ paymentMethods }: PaymentMethodProps) {
    const [showPayments, setShowPayments] = useState<PaymentMethod[]>([]);

    useEffect(() => {
        const timeoutId = setTimeout(() => {
            setShowPayments(paymentMethods);
        }, 1000);

        return () => clearTimeout(timeoutId);
    }, [paymentMethods]);


    const handleEdit = (paymentMethods: PaymentMethod) => {
        console.log(`Edit payment method with ID: ${paymentMethods.id}`);
        router.visit(PaymentMethodController.edit(paymentMethods.id));
    };

    const handleDelete = (paymentMethods: PaymentMethod) => {
        if (confirm('Are you sure you want to delete this client? ')) {
            console.log(`Delete payment method with ID: ${paymentMethods.id}`);
            router.delete(PaymentMethodController.destroy(paymentMethods.id), {
                onSuccess: () => {
                    console.log('Payment Method deleted successfully');
                },
                onError: (errors) => {
                    console.error('Error deleting payment method:', errors);
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
                        title="Payment Methods"
                        description="List of all payment methods"
                    />

                    {paymentMethods.length >= 1 && (
                    <Link href={PaymentMethodController.create()} className="flex items-center">
                        <Button className='cursor-pointer'>
                            <Plus /> Add Payment Method
                        </Button>
                    </Link>
                    )}

                </div>

                <div className="pp-row">
                    <TableList
                        data={paymentMethods}
                        columns={PaymentMethodsTableConfig.columns}
                        actions={PaymentMethodsTableConfig.actions}
                        indexLabel="#"
                        indexStartFrom={1}
                        showIndex={true}
                        onEdit={handleEdit}
                        onView={() => { }}
                        onDelete={handleDelete}
                        emptyTableMessage={{
                            icon: <Banknote />,
                            title: "No clients Found",
                            description: "Click Add Product to see them listed here.",
                            onActionClick: () => router.visit(PaymentMethodController.create()),
                            buttonText: "Payment Methods"
                        }}
                    />
                </div>
            </div>
        </>
    )
}