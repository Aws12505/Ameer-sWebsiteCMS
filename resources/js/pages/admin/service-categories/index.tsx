import { Head, Link } from '@inertiajs/react';
import { ExternalLink, House, Pin, Plus } from 'lucide-react';
import type { ReactNode } from 'react';
import { PageBody, PageHeader } from '@/components/cms/layout';
import { SortableList } from '@/components/cms/sortable-list';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import serviceCategories from '@/routes/admin/service-categories';

type CoreService = {
    readonly id: number;
    readonly title: string;
    readonly slug: string;
    readonly image: string | null;
    readonly published: boolean;
    readonly showOnHome: boolean;
    readonly isPinned: boolean;
    readonly externalUrl: string | null;
    readonly servicesCount: number;
};

type Props = {
    readonly items: CoreService[];
    readonly maxOnHome: number;
};

export default function CoreServicesIndex({
    items,
    maxOnHome,
}: Props): ReactNode {
    const onHome = items.filter((item) => item.showOnHome).length;

    return (
        <>
            <Head title="Core services" />
            <PageBody>
                <PageHeader
                    title="Core services"
                    description={`The big services: home cards, footer links, Services filter chips, their own pages and the contact form's project types. Drag to change the order everywhere. ${onHome} of ${maxOnHome} are shown on the home page.`}
                    actions={
                        <Button asChild>
                            <Link href={serviceCategories.create()}>
                                <Plus />
                                New core service
                            </Link>
                        </Button>
                    }
                />
                <SortableList
                    items={items}
                    reorder={serviceCategories.reorder()}
                    publish={(item) => serviceCategories.publish(item.id)}
                    editHref={(item) => serviceCategories.edit(item.id)}
                    title={(item) => item.title}
                    details={(item) =>
                        `/services/${item.slug} · ${item.servicesCount} services`
                    }
                    badges={(item) => (
                        <>
                            {item.isPinned && (
                                <Badge variant="secondary">
                                    <Pin />
                                    Pinned first
                                </Badge>
                            )}
                            {item.showOnHome && (
                                <Badge variant="secondary">
                                    <House />
                                    On home page
                                </Badge>
                            )}
                            {item.externalUrl && (
                                <Badge variant="outline">
                                    <ExternalLink />
                                    Opens {new URL(item.externalUrl).host}
                                </Badge>
                            )}
                        </>
                    )}
                    empty="No core services yet."
                />
            </PageBody>
        </>
    );
}

CoreServicesIndex.layout = {
    breadcrumbs: [{ title: 'Core services', href: serviceCategories.index() }],
};
