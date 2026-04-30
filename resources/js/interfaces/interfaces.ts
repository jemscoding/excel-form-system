// Table Interfaces for TableList
export interface Column {
    label: string;
    key: string;
    render?: (value: any, item: any) => React.ReactNode;
}

export interface Column {
    label: string;
    key: string;
    render?: (value: any, item: any) => React.ReactNode;
}

export interface Action {
    label: string;
    icon: string;
}

// Table Interfaces
export interface Client {
    id: number; 
    name: string;
    code: string;
}

export interface PaymentMethod {
    id: number;
    name: string;
    code: string;
}

export interface Product {
    id: number;
    name: string;
    code: string;
    price: number;
}

export interface Agent {
    id: number;
    name: string;
}

export interface DepositingBank {
    id: number;
    name: string;
}