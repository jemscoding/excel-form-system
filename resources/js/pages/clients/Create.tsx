// Routes and Hooks
import { Head } from '@inertiajs/react';
import React, { FormEvent, useState } from 'react';
import { useForm, router } from '@inertiajs/react';

// Layout Components
import type { BreadcrumbItem } from '@/types';
import { Users } from 'lucide-react';
import AppLayout from '@/layouts/app-layout';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Header } from '@/components/header';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';


// Toast Notification Message
import { toast } from 'sonner';
import { useToast } from '@/hooks/use-toast';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Client',
        href: '/clients/Create',
    },
    {
        title: 'Create new client',
        href: '/clients/Create',
    }
]
Create.layout = (page: React.ReactNode) =>
    <AppLayout breadcrumbs={breadcrumbs}>
        {page}
    </AppLayout>;


export default function Create() {
    const { data, setData, post, processing, errors, reset } = useForm({
        name: '',
        code: '',
    });

    const {showLoading, showSuccess, showError, dismiss } = useToast();

    const [isSubmitting, setIsSubmitting] = useState(false);

    function handleSubmit(e: FormEvent<HTMLFormElement>) {
            e.preventDefault();
    
            // Prevent multiple submissions
            if (isSubmitting) return;
            setIsSubmitting(true);
    
            // Show loading toast (automatically dismisses any existing toast)
            showLoading('Creating client...');
    
            post('/clients', {
                preserveScroll: true,
                onSuccess: () => {
                    // Show success toast (automatically dismisses loading)
                    showSuccess('Client created successfully!', 
                        `${data.name} (AKPH-${data.code || 'No code'}) has been added.`
                    );
                    
                    // Reset form
                    reset();
                    
                    // Redirect after delay
                    setTimeout(() => {
                        router.visit('/clients');
                    }, 2000);
                },
                onError: (errorErrors) => {
                    // Get first error message only (don't show multiple toasts)
                    const errorMessages = Object.values(errorErrors).flat();
                    const firstError = errorMessages[0] || 'Please check the form for errors.';
                    
                    // Show single error toast (automatically dismisses loading)
                    showError('Failed to create client', firstError);
                    
                    setIsSubmitting(false);
                },
                onFinish: () => {
                    setIsSubmitting(false);
                },
            });
        }
    

    return (
        <>
            <Head title="Clients" />
            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4 mx-4">
                <div className="flex pp-header justify-between">
                    <Header
                        icon={<Users />}
                        title="Create Client"
                        description="Create a new client"
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