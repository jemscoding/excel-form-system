// Routes
import { Head, Link, router } from '@inertiajs/react';
import AgentController from '@/actions/App/Http/Controllers/AgentController';

// Hooks
import { useEffect, useState } from 'react';

// Layouts
import AppLayout from '@/layouts/app-layout';
import { Button } from '@/components/ui/button';
import { Header } from '@/components/header';
import TableList from '@/components/table-list';
import type { BreadcrumbItem } from '@/types';
import { Users, Plus } from 'lucide-react';

// Data Setup
import { Agent } from '@/interfaces/interfaces';
import { AgentProps } from '@/props/props'
import { AgentTableConfig } from '@/tables/agent';


const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Agents',
        href: '/agents',
    },
]
Index.layout = (page: React.ReactNode) =>
    <AppLayout breadcrumbs={breadcrumbs}>
        {page}
    </AppLayout>;

export default function Index({ agents }: AgentProps) {
    const [showAgents, setShowAgents] = useState<Agent[]>([]);

    useEffect(() => {
        const timeoutId = setTimeout(() => {
            setShowAgents(agents);
        }, 1000);

        return () => clearTimeout(timeoutId);
    }, [agents]);


    const handleEdit = (agent: Agent) => {
        console.log(`Edit agent with ID: ${agent.id}`);
        router.visit(AgentController.edit(agent.id));
    };

    const handleDelete = (agent: Agent) => {
        if (confirm('Are you sure you want to delete this agent? ')) {
            console.log(`Delete client with ID: ${agent.id}`);
            router.delete(AgentController.destroy(agent.id), {
                onSuccess: () => {
                    console.log('Agent deleted successfully');
                },
                onError: (errors) => {
                    console.error('Error deleting agent:', errors);
                }
            });
        }
    };

    return (
        <>
            <Head title="Agents" />
            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4 mx-4">
                <div className="flex pp-header justify-between">
                    <Header
                        icon={<Users />}
                        title="Agents"
                        description="List of all agents"
                    />
                    {agents.length >= 1 && (
                    <Link href={AgentController.create()} className="flex items-center">
                        <Button className='cursor-pointer'>
                            <Plus /> Add Agent
                        </Button>
                    </Link>
                    )}
                </div>

                <div className="pp-row">
                    <TableList
                        data={agents}
                        columns={AgentTableConfig.columns}
                        actions={AgentTableConfig.actions}
                        indexLabel="#"
                        indexStartFrom={1}
                        showIndex={true}
                        onEdit={handleEdit}
                        onView={() => { }}
                        onDelete={handleDelete}
                        emptyTableMessage={{
                            icon: <Users />,
                            title: "No agents Found",
                            description: "Click Add Agents to see them listed here.",
                            onActionClick: () => router.visit(AgentController.create()),
                            buttonText: "Agent"
                        }}
                    />
                </div>
            </div>
        </>
    )
}