import { Column, Client, PaymentMethod, Product, Agent, DepositingBank } from '@/interfaces/interfaces';

export interface HeaderProps {
    icon: React.ReactNode
    title: string;
    description: string;
}


// TableList Props
export interface TableListProps {
    columns: Column[];
    data: any[];
    actions?: string[];
    onView?: (item: any) => void;
    onEdit?: (item: any) => void;
    onDelete?: (item: any) => void;
    showIndex?: boolean;
    indexLabel?: string;
    indexStartFrom?: number;
    emptyTableMessage?: {
        icon: React.ReactNode;
        title: string;
        description: string;
        buttonText?: string;
        onActionClick?: () => void;
    } | string;
};

// Data Props
export interface ClientProps {
    clients: Client[];
}

export interface PaymentMethodProps {
    paymentMethods: PaymentMethod[];
}

export interface ProductProps {
    products: Product[];
}

export interface AgentProps {
    agents: Agent[];
}

export interface DepositingBankProps {
    depositingBanks: DepositingBank[];
}