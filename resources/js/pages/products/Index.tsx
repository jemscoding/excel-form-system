// Routes
import { Head, Link, router } from '@inertiajs/react';
import ProductController from '@/actions/App/Http/Controllers/ProductController';

// Hooks
import { useEffect, useState } from 'react';

// Layouts
import AppLayout from '@/layouts/app-layout';
import { Button } from '@/components/ui/button';
import { Header } from '@/components/header';
import TableList from '@/components/table-list';
import type { BreadcrumbItem } from '@/types';
import { Box } from 'lucide-react';

// Data Setup
import { Product } from '@/interfaces/interfaces';
import { ProductProps } from '@/props/props'
import { ProductTableConfig } from '@/tables/products';


const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Products',
        href: '/products',
    },
]
Index.layout = (page: React.ReactNode) =>
    <AppLayout breadcrumbs={breadcrumbs}>
        {page}
    </AppLayout>;

export default function Index({ products }: ProductProps) {
    const [showProducts, setShowProducts] = useState<Product[]>([]);

    useEffect(() => {
        const timeoutId = setTimeout(() => {
            setShowProducts(products);
        }, 1000);

        return () => clearTimeout(timeoutId);
    }, [products]);


    const handleEdit = (product: Product) => {
        console.log(`Edit product with ID: ${product.id}`);
        router.visit(ProductController.edit(product.id));
    };

    const handleDelete = (product: Product) => {
        if (confirm('Are you sure you want to delete this client? ')) {
            console.log(`Delete client with ID: ${product.id}`);
            router.delete(ProductController.destroy(product.id), {
                onSuccess: () => {
                    console.log('Product deleted successfully');
                },
                onError: (errors) => {
                    console.error('Error deleting client:', errors);
                }
            });
        }
    };

    return (
        <>
            <Head title="Product" />
            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4 mx-4">
                <div className="flex pp-header justify-between">
                    <Header
                        icon={<Box />}
                        title="Product"
                        description="List of all products"
                    />

                    {products.length >= 1 && (
                        <Link href={ProductController.create()} className="flex items-center">
                            <Button className='cursor-pointer'>
                                + Add Product
                            </Button>
                        </Link>
                    )}
                </div>

                <div className="pp-row">
                    <TableList
                        data={products}
                        columns={ProductTableConfig.columns}
                        actions={ProductTableConfig.actions}
                        indexLabel="#"
                        indexStartFrom={1}
                        showIndex={true}
                        onEdit={handleEdit}
                        onView={() => { }}
                        onDelete={handleDelete}
                        emptyTableMessage={{
                            icon: <Box />,
                            title: "No product Found",
                            description: "Click Add Product to see them listed here.",
                            onActionClick: () => router.visit(ProductController.create()),
                            buttonText: "Product"
                        }}
                    />
                </div>
            </div>
        </>
    )
}