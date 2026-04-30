import { Link } from '@inertiajs/react';
import { Box, Banknote, LayoutGrid, UserCircle, UsersRound } from 'lucide-react';
import AppLogo from '@/components/app-logo';
import { NavFooter } from '@/components/nav-footer';
import { NavMain } from '@/components/nav-main';
import { NavUser } from '@/components/nav-user';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { dashboard } from '@/routes';
import type { NavItem } from '@/types';

import ProductController from '@/actions/App/Http/Controllers/ProductController';
import ClientController from '@/actions/App/Http/Controllers/ClientController';
import AgentController from '@/actions/App/Http/Controllers/AgentController';
import PaymentMethodController from '@/actions/App/Http/Controllers/PaymentMethodController';

// Controller Routes

const mainNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: dashboard(),
        icon: LayoutGrid,
    },
    {
        title: 'Clients',
        href:  ClientController.index(),
        icon: UserCircle,
    },
    {
        title: 'Agents',
        href:  AgentController.index(),
        icon: UsersRound,
    },
    {
        title: 'Payment Methods',
        href:  PaymentMethodController.index(),
        icon: Banknote,
    },
    {
        title: 'Products',
        href: ProductController.index(),
        icon: Box,
    }
];


export function AppSidebar() {
    return (
        <Sidebar collapsible="icon" variant="inset">
            <SidebarHeader>
                <SidebarMenu>
                    <SidebarMenuItem>
                        <SidebarMenuButton size="lg" asChild>
                            <Link href={dashboard()} prefetch>
                                <AppLogo />
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                </SidebarMenu>
            </SidebarHeader>

            <SidebarContent>
                <NavMain items={mainNavItems} />
            </SidebarContent>

            <SidebarFooter>
                <NavUser />
            </SidebarFooter>
        </Sidebar>
    );
}
