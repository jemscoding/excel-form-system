export interface Client {
    id: number; 
    name: string;
    code: string;
}

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