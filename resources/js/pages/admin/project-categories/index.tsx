import { Head, Link } from '@inertiajs/react';
import { Plus } from 'lucide-react';
import type { ReactNode } from 'react';
import { PageBody, PageHeader } from '@/components/cms/layout';
import { SortableList } from '@/components/cms/sortable-list';
import { Button } from '@/components/ui/button';
import projectCategories from '@/routes/admin/project-categories';

type CategoryRow = {
    readonly id: number;
    readonly label: string;
    readonly slug: string;
    readonly published: boolean;
    readonly projectsCount: number;
};

export default function ProjectCategoriesIndex({
    items,
}: {
    readonly items: CategoryRow[];
}): ReactNode {
    return (
        <>
            <Head title="Project categories" />
            <PageBody>
                <PageHeader
                    title="Project categories"
                    description="The filter chips on the Projects page and the label on each project card. The “All” chip is added automatically."
                    actions={
                        <Button asChild>
                            <Link href={projectCategories.create()}>
                                <Plus />
                                New category
                            </Link>
                        </Button>
                    }
                />
                <SortableList
                    items={items}
                    showImage={false}
                    reorder={projectCategories.reorder()}
                    publish={(item) => projectCategories.publish(item.id)}
                    editHref={(item) => projectCategories.edit(item.id)}
                    title={(item) => item.label}
                    details={(item) =>
                        `?cat=${item.slug} · ${item.projectsCount} projects`
                    }
                    empty="No categories yet."
                />
            </PageBody>
        </>
    );
}

ProjectCategoriesIndex.layout = {
    breadcrumbs: [
        { title: 'Project categories', href: projectCategories.index() },
    ],
};
