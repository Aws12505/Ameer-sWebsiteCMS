import { Head, Link } from '@inertiajs/react';
import { Plus } from 'lucide-react';
import type { ReactNode } from 'react';
import { PageBody, PageHeader } from '@/components/cms/layout';
import { SortableList } from '@/components/cms/sortable-list';
import { Alert, AlertDescription } from '@/components/ui/alert';
import { Button } from '@/components/ui/button';
import heroSettings from '@/routes/admin/hero-settings';
import reviews from '@/routes/admin/reviews';

type ReviewRow = {
    readonly id: number;
    readonly name: string;
    readonly initials: string;
    readonly project: string;
    readonly rating: number;
    readonly image: string | null;
    readonly published: boolean;
};

type Props = {
    readonly items: ReviewRow[];
    readonly showReviews: boolean;
};

export default function ReviewsIndex({ items, showReviews }: Props): ReactNode {
    return (
        <>
            <Head title="Reviews" />
            <PageBody>
                <PageHeader
                    title="Reviews"
                    description="Customer reviews in a swipeable row on the home page. Only add real, approved reviews."
                    actions={
                        <Button asChild>
                            <Link href={reviews.create()}>
                                <Plus />
                                New review
                            </Link>
                        </Button>
                    }
                />
                {!showReviews && (
                    <Alert>
                        <AlertDescription>
                            The Reviews section is switched off, so these are
                            not on the website yet.{' '}
                            <Link
                                href={heroSettings.edit()}
                                className="underline"
                            >
                                Change the switch
                            </Link>
                        </AlertDescription>
                    </Alert>
                )}
                <SortableList
                    items={items}
                    reorder={reviews.reorder()}
                    publish={(item) => reviews.publish(item.id)}
                    editHref={(item) => reviews.edit(item.id)}
                    title={(item) => item.name}
                    details={(item) =>
                        `${'★'.repeat(item.rating)} · ${item.project}`
                    }
                    empty="No reviews yet."
                />
            </PageBody>
        </>
    );
}

ReviewsIndex.layout = {
    breadcrumbs: [{ title: 'Reviews', href: reviews.index() }],
};
