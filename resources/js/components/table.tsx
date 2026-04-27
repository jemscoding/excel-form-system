import { Eye, Pencil, Trash } from "lucide-react";
import { Column, Action } from "@/interfaces/interfaces";

interface TableProps {
    columns: Column[];
    data: any[];
    actions?: Action[];
    onView?: (item: any) => void;
    onEdit?: (item: any) => void;
    onDelete?: (item: any) => void;
}

export default function Table({ columns, data, actions, onView, onEdit, onDelete }: TableProps) {
    const handleActionClick = (action: Action, item: any) => {
        if (action.label === 'View' && onView) {
            onView(item);
        } else if (action.label === 'Edit' && onEdit) {
            onEdit(item);
        } else if (action.label === 'Delete' && onDelete) {
            onDelete(item);
        }
    };

    return (
        <div className="overflow-x-auto border-2 border-gray-200 rounded-xl">
            <table className="min-w-full divide-y divide-gray-200">
                <thead className="bg-gray-50">
                    <tr>
                        {columns.map((column) => (
                            <th
                                key={column.key}
                                className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                            >
                                {column.label}
                            </th>
                        ))}
                        {actions && actions.length > 0 && (
                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Actions
                            </th>
                        )}
                    </tr>
                </thead>
                <tbody className="bg-white divide-y divide-gray-200">
                    {data.map((item, index) => (
                        <tr key={item.id || index} className="hover:bg-gray-50">
                            {columns.map((column) => (
                                <td key={column.key} className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {column.render 
                                        ? column.render(item[column.key], item)
                                        : item[column.key] || '-'}
                                </td>
                            ))}
                            {actions && actions.length > 0 && (
                                <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <div className="flex gap-2">
                                        {actions.map((action) => (
                                            <button
                                                key={action.label}
                                                onClick={() => handleActionClick(action, item)}
                                                className="text-gray-600 hover:text-gray-900 transition-colors cursor-pointer"
                                                title={action.label}
                                            >
                                                {action.icon === 'Eye' && <Eye className="w-4 h-4" />}
                                                {action.icon === 'Pencil' && <Pencil className="w-4 h-4" />}
                                                {action.icon === 'Trash' && <Trash className="w-4 h-4" />}
                                            </button>
                                        ))}
                                    </div>
                                </td>
                            )}
                        </tr>
                    ))} 
                </tbody>
            </table>
        </div>
    );
}