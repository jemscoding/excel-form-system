// Routes and Hooks
import { Head } from '@inertiajs/react';
import React, { FormEvent, useState } from 'react';
import { useForm, router } from '@inertiajs/react';

// Layout Components
import type { BreadcrumbItem } from '@/types';
import { Users, Banknote } from 'lucide-react';
import AppLayout from '@/layouts/app-layout';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Header } from '@/components/header';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';


// Toast Notification Message
import { toast } from 'sonner';

import PaymentMethodController from '@/actions/App/Http/Controllers/PaymentMethodController';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Payment Method',
        href: '/payment-methods/Create',
    },
     {
        title: 'Create',
        href: '/payment-methods/Create',
    },
]

Create.layout = (page: React.ReactNode) =>
    <AppLayout breadcrumbs={breadcrumbs}>
        {page}
    </AppLayout>;


export default function Create() {
    const { data, setData, post, processing, errors } = useForm({
        name: '',
        code: '',
    });

    const [successMessage, setSuccessMessage] = useState('');

    function handleSubmit(e: FormEvent<HTMLFormElement>) {
        e.preventDefault();

        const loadingToast = toast.loading('Creating Payment Method...');

        post('/payment-methods', {

            preserveScroll: true,
            onSuccess: () => {

                toast.dismiss(loadingToast);

                toast.success('Payment Method created successfully!', {
                    description:`${data.name} (${data.code}) has been added.`,
                    duration: 4000
                })
                setSuccessMessage('Payment Method created successfully! Redirecting...');

                setTimeout(() => {
                    router.visit('/payment-methods');            
                }, 2000)
            },
        });
    }

    return (
        <>
            <Head title="Payment Methods" />
            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4 mx-4">
                <div className="flex pp-header justify-between">
                    <Header
                        icon = {<Banknote />}
                        title = "Create Payment Method"
                        description = "Create a new payment method"
                    />
                </div>

                <div className="pp-row">
                    <form onSubmit={handleSubmit} className="space-y-4">
                        <div className="space-y-2">
                            <Label htmlFor="name">Payment Method Name</Label>
                            <Input
                                id="name"
                                value={data.name}
                                onChange={e => setData('name', e.target.value)}
                                placeholder="Enter payment method name"
                                required
                            />
                            {errors.name && (
                                <p className="text-sm text-red-500">{errors.name}</p>
                            )}
                        </div>

                        <div className="space-y-2">
                            <Label htmlFor="code">Payment Method Code</Label>
                            <Input
                                id="code"
                                value={data.code}
                                onChange={e => setData('code', e.target.value)}
                                placeholder="Enter payment method code"
                                required
                            />
                            {errors.code && (
                                <p className="text-sm text-red-500">{errors.code}</p>
                            )}
                        </div>

                        <div className="flex gap-2">
                            <Button type="submit" disabled={processing}>
                                {processing ? 'Creating...' : 'Create Payment Method'}
                            </Button>
                            <Button
                                type="button"
                                variant="outline"
                                onClick={() => router.visit(PaymentMethodController.index())}
                            >
                                Cancel
                            </Button>
                        </div>
                    </form>
                </div>
            </div>
        </>
    )
}