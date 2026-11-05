import { Link, usePage } from '@inertiajs/react';
import {
    BriefcaseBusiness,
    FileText,
    FolderKanban,
    Globe,
    House,
    Image,
    Inbox,
    LayoutGrid,
    MessageSquareQuote,
    Phone,
    Tags,
    Trash2,
    Users,
    Wrench,
} from 'lucide-react';
import AppLogo from '@/components/app-logo';
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
import contactRequests from '@/routes/admin/contact-requests';
import heroSettings from '@/routes/admin/hero-settings';
import contactPage from '@/routes/admin/pages/contact';
import homePage from '@/routes/admin/pages/home';
import pageText from '@/routes/admin/pages/text';
import projectCategories from '@/routes/admin/project-categories';
import projects from '@/routes/admin/projects';
import reviews from '@/routes/admin/reviews';
import serviceCategories from '@/routes/admin/service-categories';
import services from '@/routes/admin/services';
import siteSettings from '@/routes/admin/site-settings';
import trash from '@/routes/admin/trash';
import users from '@/routes/admin/users';
import type { NavGroup } from '@/types';

export function AppSidebar() {
    const { auth, cms } = usePage().props;

    const groups: NavGroup[] = [
        {
            label: 'Overview',
            items: [
                { title: 'Dashboard', href: dashboard(), icon: LayoutGrid },
                {
                    title: 'Contact requests',
                    href: contactRequests.index(),
                    icon: Inbox,
                    badge: cms?.newContactRequests,
                },
            ],
        },
        {
            label: 'Site',
            items: [
                {
                    title: 'Site settings',
                    href: siteSettings.edit(),
                    icon: Globe,
                },
                {
                    title: 'Hero & reviews switch',
                    href: heroSettings.edit(),
                    icon: Image,
                },
            ],
        },
        {
            label: 'Pages',
            items: [
                { title: 'Home page', href: homePage.edit(), icon: House },
                {
                    title: 'Services page',
                    href: pageText.edit('services'),
                    icon: FileText,
                },
                {
                    title: 'Projects page',
                    href: pageText.edit('projects'),
                    icon: FileText,
                },
                {
                    title: 'Contact page',
                    href: contactPage.edit(),
                    icon: Phone,
                },
            ],
        },
        {
            label: 'Content',
            items: [
                {
                    title: 'Core services',
                    href: serviceCategories.index(),
                    icon: BriefcaseBusiness,
                },
                { title: 'Services', href: services.index(), icon: Wrench },
                {
                    title: 'Project categories',
                    href: projectCategories.index(),
                    icon: Tags,
                },
                {
                    title: 'Projects',
                    href: projects.index(),
                    icon: FolderKanban,
                },
                {
                    title: 'Reviews',
                    href: reviews.index(),
                    icon: MessageSquareQuote,
                },
            ],
        },
        {
            label: 'Admin',
            items: [
                ...(auth.isAdmin
                    ? [{ title: 'Users', href: users.index(), icon: Users }]
                    : []),
                { title: 'Trash', href: trash.index(), icon: Trash2 },
            ],
        },
    ];

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

            <SidebarContent className="gap-4">
                {groups.map((group) => (
                    <NavMain
                        key={group.label}
                        label={group.label}
                        items={group.items}
                    />
                ))}
            </SidebarContent>

            <SidebarFooter>
                <NavUser />
            </SidebarFooter>
        </Sidebar>
    );
}
