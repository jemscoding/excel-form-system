import { toast } from 'sonner';
import { useRef } from 'react';

export const useToast = () => {
    const loadingToastId = useRef<string | number | null>(null);

    // Common options for all toasts
    const defaultOptions = {
        closeButton: true,
        position: 'top-right' as const,
        duration: 4000,
    };

    const dismissAll = () => {
        if (loadingToastId.current) {
            toast.dismiss(loadingToastId.current);
            loadingToastId.current = null;
        }
        toast.dismiss();
    };

    const showLoading = (message: string) => {
        dismissAll();
        loadingToastId.current = toast.loading(message, {
            ...defaultOptions,
            duration: Infinity,
        });
        return loadingToastId.current;
    };

    const showSuccess = (title: string, description?: string) => {
        dismissAll();
        toast.success(title, { 
            ...defaultOptions,
            description, 
        });
    };

    const showError = (title: string, description?: string) => {
        dismissAll();
        toast.error(title, { 
            ...defaultOptions,
            description, 
            duration: 5000,
        });
    };

    const showInfo = (title: string, description?: string) => {
        dismissAll();
        toast.info(title, { 
            ...defaultOptions,
            description, 
        });
    };

    const showWarning = (title: string, description?: string) => {
        dismissAll();
        toast.warning(title, { 
            ...defaultOptions,
            description, 
        });
    };

    const dismiss = () => {
        dismissAll();
    };

    return {
        showLoading,
        showSuccess,
        showError,
        showInfo,
        showWarning,
        dismiss,
    };
};