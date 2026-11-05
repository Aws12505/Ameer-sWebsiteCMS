import { Head, Link } from '@inertiajs/react';
import { Plus, Star } from 'lucide-react';
import type { ReactNode } from 'react';
import { PageBody, PageHeader } from '@/components/cms/layout';
import { SortableList } from '@/components/cms/sortable-list';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import projects from '@/routes/admin/projects';

type ProjectRow = {
    readonly id: number;
    readonly title: string;
    readonly slug: string;
    readonly location: string;
    readonly image: string | null;
    readonly published: boolean;
    readonly featured: boolean;
    readonly mainCategory: string | null;
    readonly photosCount: number;
};

type Props = {
    readonly items: ProjectRow[];
    readonly maxFeatured: number;
};

export default function ProjectsIndex({
    items,
    maxFeatured,
}: Props): ReactNode {
    const featured = items.filter((item) => item.featured).length;

    return (
        <>
            <Head title="Projects" />
            <PageBody>
                <PageHeader
                    title="Projects"
                    description={`Each project opens as a photo gallery from its card. New projects are added at the top. ${featured} of ${maxFeatured} are featured on the home page.`}
                    actions={
                        <Button asChild>
                            <Link href={projects.create()}>
                                <Plus />
                                New project
                            </Link>
                        </Button>
                    }
                />
                <SortableList
                    items={items}
                    reorder={projects.reorder()}
                    publish={(item) => projects.publish(item.id)}
                    editHref={(item) => projects.edit(item.id)}
                    title={(item) => item.title}
                    details={(item) =>
                        [
                            item.mainCategory,
                            item.location,
                            `${item.photosCount} photos`,
                        ]
                            .filter(Boolean)
                            .join(' · ')
                    }
                    badges={(item) =>
                        item.featured && (
                            <Badge variant="secondary">
                                <Star />
                                Featured on home page
                            </Badge>
                        )
                    }
                    empty="No projects yet."
                />
            </PageBody>
        </>
    );
}

ProjectsIndex.layout = {
    breadcrumbs: [{ title: 'Projects', href: projects.index() }],
};
