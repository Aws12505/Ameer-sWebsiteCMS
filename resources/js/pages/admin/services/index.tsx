import { Head, Link, router } from '@inertiajs/react';
import { Plus } from 'lucide-react';
import type { ReactNode } from 'react';
import { PageBody, PageHeader } from '@/components/cms/layout';
import { SortableList } from '@/components/cms/sortable-list';
import { Button } from '@/components/ui/button';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import services from '@/routes/admin/services';

type ServiceRow = {
    readonly id: number;
    readonly title: string;
    readonly slug: string;
    readonly image: string | null;
    readonly published: boolean;
    readonly categories: readonly string[];
};

type Props = {
    readonly items: ServiceRow[];
    readonly categories: readonly {
        readonly id: number;
        readonly title: string;
    }[];
    readonly filter: { readonly category: number | null };
};

const ALL = 'all';

export default function ServicesIndex({
    items,
    categories,
    filter,
}: Props): ReactNode {
    const selected = filter.category ? String(filter.category) : ALL;

    return (
        <>
            <Head title="Services" />
            <PageBody>
                <PageHeader
                    title="Services"
                    description="The detailed services on the Services page and on each core service's page. Drag to change the order; filter by core service to order a smaller group."
                    actions={
                        <Button asChild>
                            <Link
                                href={services.create({
                                    query: filter.category
                                        ? { category: filter.category }
                                        : {},
                                })}
                            >
                                <Plus />
                                New service
                            </Link>
                        </Button>
                    }
                />
                <div className="flex flex-wrap items-center gap-2">
                    <span className="text-sm text-muted-foreground">Show</span>
                    <Select
                        value={selected}
                        onValueChange={(value) =>
                            router.get(
                                services.index.url({
                                    query:
                                        value === ALL
                                            ? {}
                                            : { category: value },
                                }),
                                {},
                                { preserveState: true },
                            )
                        }
                    >
                        <SelectTrigger className="w-64">
                            <SelectValue />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value={ALL}>All services</SelectItem>
                            {categories.map((category) => (
                                <SelectItem
                                    key={category.id}
                                    value={String(category.id)}
                                >
                                    Only {category.title}
                                </SelectItem>
                            ))}
                        </SelectContent>
                    </Select>
                    <span className="text-sm text-muted-foreground">
                        {items.length} services
                    </span>
                </div>
                <SortableList
                    items={items}
                    reorder={services.reorder()}
                    publish={(item) => services.publish(item.id)}
                    editHref={(item) => services.edit(item.id)}
                    title={(item) => item.title}
                    details={(item) =>
                        item.categories.join(', ') || 'No core service'
                    }
                    empty="No services here yet."
                />
            </PageBody>
        </>
    );
}

ServicesIndex.layout = {
    breadcrumbs: [{ title: 'Services', href: services.index() }],
};
