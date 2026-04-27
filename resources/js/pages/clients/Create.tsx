import { Head } from '@inertiajs/react';
import type { BreadcrumbItem } from '@/types';
import { Users } from 'lucide-react';
import AppLayout from '@/layouts/app-layout';

import { useForm, router } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Header } from '@/components/header';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import React, { FormEvent, useState } from 'react';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Clients',
        href: '/clients/Create',
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

        post('/clients', {
            onSuccess: () => {
                setSuccessMessage('Client created successfully! Redirecting...');
            },
        });
    }
    return (
        <>
            <Head title="Clients" />
            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4 mx-4">
                <div className="flex pp-header justify-between">
                    <Header
                        icon = {<Users />}
                        title = "Create Client"
                        description = "Create a new client"
                    />
                </div>

                <div className="pp-row">
                    <form onSubmit={handleSubmit} className="space-y-4">
                        <div className="space-y-2">
                            <Label htmlFor="name">Client Name</Label>
                            <Input
                                id="name"
                                value={data.name}
                                onChange={e => setData('name', e.target.value)}
                                placeholder="Enter client name"
                                required
                            />
                            {errors.name && (
                                <p className="text-sm text-red-500">{errors.name}</p>
                            )}
                        </div>

                        <div className="space-y-2">
                            <Label htmlFor="code">Client Code</Label>
                            <Input
                                id="code"
                                value={data.code}
                                onChange={e => setData('code', e.target.value)}
                                placeholder="Enter client code"
                                required
                            />
                            {errors.code && (
                                <p className="text-sm text-red-500">{errors.code}</p>
                            )}
                        </div>

                        <div className="flex gap-2">
                            <Button type="submit" disabled={processing}>
                                {processing ? 'Creating...' : 'Create Client'}
                            </Button>
                            <Button
                                type="button"
                                variant="outline"
                                onClick={() => router.visit('/clients')}
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