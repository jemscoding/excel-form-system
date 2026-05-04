import { Head, useForm } from '@inertiajs/react';
import { Header } from '@/components/header';
import { useState, useEffect } from 'react';
import { toast } from 'sonner';
import { useToast } from '@/hooks/use-toast';
import AppLayout from '@/layouts/app-layout';
import { Button } from '@/components/ui/button';
import {
    Select,
    SelectContent,
    SelectGroup,
    SelectItem,
    SelectLabel,
    SelectTrigger,
    SelectValue,
} from "@/components/ui/select";
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Download, Save, PhilippinePeso } from 'lucide-react';
import type { BreadcrumbItem } from '@/types';
import type { Agent, DepositingBank, PaymentMethod, Product, Client } from '@/interfaces/interfaces';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Excel Forms',
        href: '/excel-forms/Create',
    },
    {
        title: 'Create',
        href: '/excel-forms/Create',
    }
];

Create.layout = (page: React.ReactNode) =>
    <AppLayout breadcrumbs={breadcrumbs}>
        {page}
    </AppLayout>;

interface CreateProps {
    agents: Agent[];
    depositingBanks: DepositingBank[];
    paymentMethods: PaymentMethod[];
    products: Product[];
    clients: Client[];
    purposes: Array<{ id: number, name: string }>;
    status: Array<{ id: number; name: string }>;
}

export default function Create({ agents, depositingBanks, paymentMethods, products, clients, purposes, status }: CreateProps) {
    const { data, setData, post, processing, errors, reset } = useForm({
        container_code: '',
        china_received_date: new Date().toISOString().split('T')[0],
        order_reference: '',
        client_id: '',
        client_name: '',
        client_code: '',
        product_id: '',
        pkgs: '',
        total_cbm: '',
        weight: '',
        applied_rate: '',
        soa_number: '',
        soa_code: '',
        initial_billing: '',
        withholding_tax: '',
        inbound_cost: '',
        service_fee: '',
        overweight: '',
        discount: '',
        others: '',
        depositing_bank_id: '',
        payment_reference_number: '',
        deposit_date: '',
        payment_method_id: '',
        status: '',
        purpose: 'FREIGHT PAYMENT',
        agent_id: '',
        receiving_bank: '',
    });


    // =======================HandleOnChange Functions==========================
    const handleClientNameChange = (value: string) => {
        const client = clients?.find(c => c.id.toString() === value);

        if (client) {
            setData('client_id', client.id.toString());
            setData('client_name', client.name);
            setData('client_code', client.code || '');
        }
    };

    const handleClientCodeChange = (value: string) => {
        // Find client by code
        const client = clients?.find(c => c.code === value);

        // If client is found, set client ID, name, and code
        if (client) {
            setData('client_id', client.id.toString());
            setData('client_name', client.name);
            setData('client_code', client.code);
        }
    };

    // Product
    const handleProductChange = (value: string) => {
        setData('product_id', value);
    };

    // Depositing Bank
    const handleDepositingBankChange = (value: string) => {
        setData('depositing_bank_id', value);
    };

    // PaymentMethod
    const handlePaymentMethodChange = (value: string) => {
        setData('payment_method_id', value);
    };

    // Agent
    const handleAgentChange = (value: string) => {
        setData('agent_id', value);
    };

    // Purpose
    const handlePurposeChange = (value: string) => {
        setData('purpose', value);
    }

    // ======================Calculations=================================

    // Actual Amount & Amount to be Paid
    const calculateAmountToBePaid = () => {
        const inbound = parseFloat(data.inbound_cost) || 0;
        const service = parseFloat(data.service_fee) || 0;
        const overweight = parseFloat(data.overweight) || 0;
        const others = parseFloat(data.others) || 0;
        const discount = parseFloat(data.discount) || 0;
        const tax = parseFloat(data.withholding_tax) || 0;

        return (inbound + service + overweight + others) - (discount + tax);
    };

    // Calculate initial billing to input
    useEffect(() => {
        const inbound = parseFloat(data.inbound_cost) || 0;
        const service = parseFloat(data.service_fee) || 0;
        const calculatedInitialBilling = inbound + service;

        setData('initial_billing', calculatedInitialBilling.toString());
    }, [data.inbound_cost, data.service_fee]);

    const [isSubmitting, setIsSubmitting] = useState(false);

    const { showLoading, showSuccess, showError, dismiss } = useToast();

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();

        showLoading('Adding entry to Excel...');

        post('/excel-forms', {
            preserveScroll: true,
            onSuccess: () => {
                dismiss();
                showSuccess('Entry added to Excel successfully!');
                reset();
            },
            onError: (errorErrors) => {
                // Get first error message only (don't show multiple toasts)
                const errorMessages = Object.values(errorErrors).flat();
                const firstError = errorMessages[0] || 'Please check the form for errors.';

                // Show single error toast (automatically dismisses loading)
                showError('Failed to create excel data', firstError);

                setIsSubmitting(false);
            },
        });
    };

    const handleDownload = () => {
        window.location.href = '/excel-forms/download';
        toast.success('Downloading Excel file...');
    };

    return (
        <>
            <Head title="Excel Form Entry" />
            <div className="p-6">
                <div className="mb-6 flex justify-between items-center">
                    <Header
                        title="Excel Form Entry"
                        description="Add a new entry to the Excel form"
                        icon={<Save size={24} />}
                    />
                    <Button onClick={handleDownload} variant="outline" className="flex items-center gap-2">
                        <Download className="w-4 h-4" />
                        Download Excel
                    </Button>
                </div>

                <form onSubmit={handleSubmit} className="space-y-6">
                    {/* Shipment Details */}
                    <div className="bg-white rounded-lg shadow p-6">
                        <h2 className="text-lg font-semibold mb-4 border-b pb-2">Shipment Details</h2>
                        <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <Label>Container Code *</Label>
                                <Input
                                    value={data.container_code}
                                    onChange={(e) => setData('container_code', e.target.value.toUpperCase())}
                                    placeholder="e.g., AF0135"
                                    required
                                />
                                {errors.container_code && <p className="text-red-500 text-sm mt-1">{errors.container_code}</p>}
                            </div>

                            <div>
                                <Label>China Received Date *</Label>
                                <Input
                                    type="date"
                                    value={data.china_received_date}
                                    onChange={(e) => setData('china_received_date', e.target.value)}
                                    required
                                />
                            </div>

                            <div>
                                <Label>Order Reference *</Label>
                                <Input
                                    value={data.order_reference}
                                    onChange={(e) => setData('order_reference', e.target.value)}
                                    placeholder="e.g., 73540202679284"
                                    required
                                />
                            </div>

                            {/* Client Name Selection using shadcn Select */}
                            <div>
                                <Label>Client Name *</Label>
                                <Select
                                    value={data.client_id}
                                    onValueChange={handleClientNameChange}
                                >
                                    <SelectTrigger className="w-full">
                                        <SelectValue placeholder="Select Client Name" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectGroup>
                                            {clients?.map((client) => (
                                                <SelectItem key={client.id} value={client.id.toString()}>
                                                    {client.name}
                                                </SelectItem>
                                            ))}
                                        </SelectGroup>
                                    </SelectContent>
                                </Select>
                                {errors.client_id && <p className="text-red-500 text-sm mt-1">{errors.client_id}</p>}
                            </div>

                            {/* Client Code Selection using shadcn Select */}
                            <div>
                                <Label>Client Code *</Label>
                                <Select
                                    value={data.client_code}
                                    onValueChange={handleClientCodeChange}
                                    disabled
                                >
                                    <SelectTrigger className="w-full">
                                        <SelectValue placeholder="Select Client Code" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectGroup>
                                            <SelectLabel>Client Codes</SelectLabel>
                                            {clients?.map((client) => (
                                                <SelectItem key={client.id} value={client.code}>
                                                    AKPH-{client.code}
                                                </SelectItem>
                                            ))}
                                        </SelectGroup>
                                    </SelectContent>
                                </Select>
                                {errors.client_code && <p className="text-red-500 text-sm mt-1">{errors.client_code}</p>}
                            </div>

                            {/* Order Lines/Product using shadcn Select */}
                            <div>
                                <Label>Order Lines/Product *</Label>
                                <Select
                                    value={data.product_id}
                                    onValueChange={handleProductChange}
                                >
                                    <SelectTrigger className="w-full">
                                        <SelectValue placeholder="Select Product" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectGroup>
                                            {products?.map((product) => (
                                                <SelectItem key={product.id} value={product.id.toString()}>
                                                    {product.name} {product.code}
                                                </SelectItem>
                                            ))}
                                        </SelectGroup>
                                    </SelectContent>
                                </Select>
                                {errors.product_id && <p className="text-red-500 text-sm mt-1">{errors.product_id}</p>}
                            </div>

                            <div>
                                <Label>Packages</Label>
                                <Input
                                    type="number"
                                    value={data.pkgs}
                                    onChange={(e) => setData('pkgs', e.target.value)}
                                    placeholder="Number of packages"
                                />
                            </div>

                            <div>
                                <Label>Total CBM</Label>
                                <Input
                                    type="number"
                                    value={data.total_cbm}
                                    onChange={(e) => setData('total_cbm', e.target.value)}
                                    placeholder="e.g., 1.5"
                                />
                            </div>

                            <div>
                                <Label>Weight (kg)</Label>
                                <Input
                                    type="number"
                                    value={data.weight}
                                    onChange={(e) => setData('weight', e.target.value)}
                                    placeholder="e.g., 8"
                                />
                            </div>

                            <div>
                                <Label>Applied Rate</Label>
                                <Input
                                    value={data.applied_rate}
                                    onChange={(e) => setData('applied_rate', e.target.value)}
                                    placeholder="e.g., 750/kgs"
                                />
                            </div>

                            <div>
                                <Label>SOA Number</Label>
                                <Input
                                    value={data.soa_number}
                                    onChange={(e) => {
                                        setData('soa_number', e.target.value);
                                        setData('soa_code', e.target.value);
                                    }}
                                    placeholder="e.g., SOA-AKPHJYL-072"
                                />
                            </div>

                            <div>
                                <Label>SOA Code (Autofill)</Label>
                                <Input
                                    value={data.soa_code}
                                    onChange={(e) => {
                                        setData('soa_code', e.target.value);

                                        setData('soa_number', e.target.value);
                                    }}
                                    placeholder="e.g., SOA-AKPHJYL-072"
                                    disabled
                                />
                            </div>
                        </div>
                    </div>

                    {/* Financial Details */}
                    <div className="bg-white rounded-lg shadow p-6">
                        <h2 className="text-lg font-semibold mb-4 border-b pb-2">Financial Details</h2>

                        {/* Summary Card */}
                        {calculateAmountToBePaid() >= 1 && (
                            <div className="my-4 p-4 bg-blue-50 rounded-lg">
                                <p className="text-sm text-gray-600">Actual Payment: </p>
                                <p className="text-2xl font-bold text-blue-600">
                                    ₱{calculateAmountToBePaid().toLocaleString(undefined, { minimumFractionDigits: 2 })}
                                </p>
                            </div>
                        )}

                        <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <Label>Initial Billing (Calculated)</Label>
                                <div className="relative">
                                    <PhilippinePeso className="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-500" />
                                    <Input
                                        type="number"
                                        value={data.initial_billing}
                                        readOnly
                                        className="pl-9"
                                        placeholder="0.00"
                                    />
                                </div>
                            </div>

                            <div>
                                <Label>Inbound Cost *</Label>
                                <div className="relative">
                                    <PhilippinePeso className="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-500" />
                                    <Input
                                        type="number"
                                        value={data.inbound_cost}
                                        onChange={(e) => setData('inbound_cost', e.target.value)}
                                        className="pl-9"
                                        placeholder="0.00"
                                        required
                                    />
                                </div>
                            </div>

                            <div>
                                <Label>Service Fee <span className='text-red-600'>*</span></Label>
                                <div className="relative">
                                    <PhilippinePeso className="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-500" />
                                    <Input
                                        type="number"
                                        value={data.service_fee}
                                        onChange={(e) => setData('service_fee', e.target.value)}
                                        className="pl-9"
                                        placeholder="0.00"
                                        required
                                    />
                                </div>
                            </div>

                            <div>
                                <Label>Withholding Tax</Label>
                                <div className="relative">
                                    <PhilippinePeso className="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-500" />
                                    <Input
                                        type="number"
                                        value={data.withholding_tax}
                                        onChange={(e) => setData('withholding_tax', e.target.value)}
                                        className="pl-9"
                                        placeholder="0.00"
                                    />
                                </div>
                            </div>

                            <div>
                                <Label>Overweight Charge</Label>
                                <div className="relative">
                                    <PhilippinePeso className="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-500" />
                                    <Input
                                        type="number"
                                        value={data.overweight}
                                        onChange={(e) => setData('overweight', e.target.value)}
                                        className="pl-9"
                                        placeholder="0.00"
                                    />
                                </div>
                            </div>

                            <div>
                                <Label>Discount</Label>
                                <div className="relative">
                                    <PhilippinePeso className="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-500" />
                                    <Input
                                        type="number"
                                        value={data.discount}
                                        onChange={(e) => setData('discount', e.target.value)}
                                        className="pl-9"
                                        placeholder="0.00"
                                    />
                                </div>
                            </div>

                            <div>
                                <Label>Others</Label>
                                <div className="relative">
                                    <PhilippinePeso className="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-500" />
                                    <Input
                                        type="number"
                                        value={data.others}
                                        onChange={(e) => setData('others', e.target.value)}
                                        className="pl-9"
                                        placeholder="0.00"
                                    />
                                </div>
                            </div>
                        </div>
                    </div>


                    {/* Payment Details */}
                    <div className="bg-white rounded-lg shadow p-6">
                        <h2 className="text-lg font-semibold mb-4 border-b pb-2">Payment Details</h2>
                        <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <Label>Payment Reference Number *</Label>
                                <Input
                                    value={data.payment_reference_number}
                                    onChange={(e) => setData('payment_reference_number', e.target.value)}
                                    placeholder="e.g., 1735783286237"
                                    required
                                />
                            </div>

                            <div>
                                <Label>Depositing Bank *</Label>
                                <Select
                                    value={data.depositing_bank_id}
                                    onValueChange={handleDepositingBankChange}
                                >
                                    <SelectTrigger className="w-full">
                                        <SelectValue placeholder="Select Depositing Bank" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectGroup>
                                            {depositingBanks?.map((bank) => (
                                                <SelectItem key={bank.id} value={bank.id.toString()}>
                                                    {bank.name}
                                                </SelectItem>
                                            ))}
                                        </SelectGroup>
                                    </SelectContent>
                                </Select>
                                {errors.depositing_bank_id && <p className="text-red-500 text-sm mt-1">{errors.depositing_bank_id}</p>}
                            </div>

                            <div>
                                <Label>Receiving Bank</Label>
                                <Select
                                    value={data.payment_method_id}
                                    onValueChange={handlePaymentMethodChange}
                                >
                                    <SelectTrigger className="w-full">
                                        <SelectValue placeholder="Select Receiving Bank" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectGroup>
                                            {paymentMethods?.map((method) => (
                                                <SelectItem key={method.id} value={method.id.toString()}>
                                                    {method.name} ({method.code})
                                                </SelectItem>
                                            ))}
                                        </SelectGroup>
                                    </SelectContent>
                                </Select>
                                {errors.payment_method_id && <p className="text-red-500 text-sm mt-1">{errors.payment_method_id}</p>}
                            </div>

                            <div>
                                <Label>Deposit Date</Label>
                                <Input
                                    type="date"
                                    value={data.deposit_date}
                                    onChange={(e) => setData('deposit_date', e.target.value)}
                                />
                            </div>

                            <div>
                                <Label>Agent</Label>
                                <Select
                                    value={data.agent_id}
                                    onValueChange={handleAgentChange}
                                >
                                    <SelectTrigger className="w-full">
                                        <SelectValue placeholder="Select Agent" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectGroup>
                                            {agents?.map((agent) => (
                                                <SelectItem key={agent.id} value={agent.id.toString()}>
                                                    {agent.name}
                                                </SelectItem>
                                            ))}
                                        </SelectGroup>
                                    </SelectContent>
                                </Select>
                                {errors.agent_id && <p className="text-red-500 text-sm mt-1">{errors.agent_id}</p>}
                            </div>

                            <div>
                                <Label>Purpose</Label>
                                <Select
                                    value={data.purpose}
                                    onValueChange={handlePurposeChange}
                                >
                                    <SelectTrigger className='w-full'>
                                        <SelectValue placeholder="Select Purpose" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectGroup>
                                            {purposes?.map((purpose) => (
                                                <SelectItem key={purpose.id} value={purpose.name}>
                                                    {purpose.name}
                                                </SelectItem>
                                            ))}
                                        </SelectGroup>
                                    </SelectContent>
                                </Select>
                            </div>

                            <div>
                                <Label>Status</Label>
                                <Select
                                    value={data.status}
                                    onValueChange={(value) => setData('status', value)}
                                >
                                    <SelectTrigger className='w-full'>
                                        <SelectValue placeholder="Select Status" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectGroup>
                                            {status?.map((status) => (
                                                <SelectItem key={status.id} value={status.name}>
                                                    {status.name}
                                                </SelectItem>
                                            ))}
                                        </SelectGroup>
                                    </SelectContent>
                                </Select>
                            </div>
                        </div>
                    </div>

                    {/* Action Buttons */}
                    <div className="flex gap-3 justify-end">
                        <Button type="button" variant="outline" onClick={() => reset()}>
                            Clear Form
                        </Button>
                        <Button type="submit" disabled={processing} className="flex items-center gap-2">
                            <Save className="w-4 h-4" />
                            {processing ? 'Adding...' : 'Add to Excel'}
                        </Button>
                    </div>
                </form>
            </div>
        </>
    );
}