// Routes and Hooks
import { Head } from '@inertiajs/react';
import React, { FormEvent, useState } from 'react';
import { useForm, router } from '@inertiajs/react';

// Layout Components
import type { BreadcrumbItem } from '@/types';
import { Package } from 'lucide-react';
import AppLayout from '@/layouts/app-layout';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Header } from '@/components/header';

// Import the custom toast hook
import { useToast } from '@/hooks/use-toast';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Products',
        href: '/products',
    },
    {
        title: 'Create',
        href: '/products/create',
    }
];

Create.layout = (page: React.ReactNode) =>
    <AppLayout breadcrumbs={breadcrumbs}>
        {page}
    </AppLayout>;

export default function Create() {
    const { data, setData, post, processing, errors, reset } = useForm({
        name: '',
    });

    const [isSubmitting, setIsSubmitting] = useState(false);

    // Use the custom toast hook
    const { showLoading, showSuccess, showError, dismiss } = useToast();

    function handleSubmit(e: FormEvent<HTMLFormElement>) {
        e.preventDefault();

        // Prevent multiple submissions
        if (isSubmitting) return;
        setIsSubmitting(true);

        // Show loading toast (automatically dismisses any existing toast)
        showLoading('Creating product...');

        post('/products', {
            preserveScroll: true,
            onSuccess: () => {
                // Show success toast (automatically dismisses loading)
                showSuccess('Product created successfully!',
                    `${data.name} has been added.`
                );

                // Reset form
                reset();

                // Redirect after delay
                setTimeout(() => {
                    router.visit('/products');
                }, 2000);
            },
            onError: (errorErrors) => {
                // Get first error message only (don't show multiple toasts)
                const errorMessages = Object.values(errorErrors).flat();
                const firstError = errorMessages[0] || 'Please check the form for errors.';

                // Show single error toast (automatically dismisses loading)
                showError('Failed to create product', firstError);

                setIsSubmitting(false);
            },
            onFinish: () => {
                setIsSubmitting(false);
            },
        });
    }

    const handleCodeChange = (e: React.ChangeEvent<HTMLInputElement>) => {
        let value = e.target.value;

        // Allow digits only (0-9), no other characters
        let digits = value.replace(/\D/g, '');

        // Limit to 9 digits total
        if (digits.length > 9) {
            digits = digits.slice(0, 9);
        }

        // Store as-is without auto-padding while typing
        setData('code', digits);

    };

    return (
        <>
            <Head title="Create Product" />
            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4 mx-4">
                <div className="flex pp-header justify-between">
                    <Header
                        icon={<Package />}
                        title="Add Product"
                        description="Create a new product"
                    />
                </div>

                <div className="pp-row">
                    <form onSubmit={handleSubmit} className="space-y-4">
                        <div className="space-y-2">
                            <Label htmlFor="name">Product Name <span className="text-red-500">*</span></Label>
                            <Input
                                id="name"
                                value={data.name}
                                onChange={e => setData('name', e.target.value)}
                                placeholder="Enter product name"
                                required
                            />
                            {errors.name && (
                                <p className="text-sm text-red-500">{errors.name}</p>
                            )}
                        </div>


                        <div className="flex gap-2 pt-4">
                            <Button
                                type="submit"
                                disabled={processing || isSubmitting}
                                className="cursor-pointer"
                            >
                                {processing || isSubmitting ? 'Creating...' : 'Create Product'}
                            </Button>
                            <Button
                                type="button"
                                variant="outline"
                                onClick={() => router.visit('/products')}
                                className="cursor-pointer"
                            >
                                Cancel
                            </Button>
                        </div>
                    </form>
                </div>
            </div>
        </>
    );
}