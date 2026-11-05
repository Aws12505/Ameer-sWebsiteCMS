import { Head, Link } from '@inertiajs/react';
import type { ReactNode } from 'react';
import { PageBody, PageHeader } from '@/components/cms/layout';
import { StatusBadge } from '@/components/cms/status-badge';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { formatDateTime } from '@/lib/cms';
import { dashboard } from '@/routes';
import contactRequests from '@/routes/admin/contact-requests';
import heroSettings from '@/routes/admin/hero-settings';
import projects from '@/routes/admin/projects';
import reviews from '@/routes/admin/reviews';
import serviceCategories from '@/routes/admin/service-categories';
import services from '@/routes/admin/services';

type Props = {
    readonly stats: {
        readonly newRequests: number;
        readonly coreServices: number;
        readonly services: number;
        readonly projects: number;
        readonly featuredProjects: number;
        readonly reviews: number;
        readonly showReviews: boolean;
    };
    readonly latestRequests: readonly {
        readonly id: number;
        readonly name: string;
        readonly projectType: string | null;
        readonly status: string;
        readonly createdAt: string;
    }[];
};

export default function Dashboard({ stats, latestRequests }: Props): ReactNode {
    const tiles = [
        {
            label: 'New contact requests',
            value: stats.newRequests,
            href: contactRequests.index({ query: { status: 'new' } }),
        },
        {
            label: 'Core services live',
            value: stats.coreServices,
            href: serviceCategories.index(),
        },
        {
            label: 'Services live',
            value: stats.services,
            href: services.index(),
        },
        {
            label: 'Projects live',
            value: stats.projects,
            href: projects.index(),
            note: `${stats.featuredProjects} featured on Home`,
        },
        {
            label: 'Reviews live',
            value: stats.reviews,
            href: reviews.index(),
            note: stats.showReviews
                ? 'Reviews section is on'
                : 'Reviews section is off',
        },
    ];

    return (
        <>
            <Head title="Dashboard" />
            <PageBody>
                <PageHeader
                    title="Dashboard"
                    description="Everything you change here appears on the website after you save."
                />

                <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    {tiles.map((tile) => (
                        <Link
                            key={tile.label}
                            href={tile.href}
                            className="rounded-xl border bg-card p-4 transition-colors hover:bg-accent"
                        >
                            <p className="text-sm text-muted-foreground">
                                {tile.label}
                            </p>
                            <p className="mt-1 text-3xl font-semibold tabular-nums">
                                {tile.value}
                            </p>
                            {tile.note && (
                                <p className="mt-1 text-xs text-muted-foreground">
                                    {tile.note}
                                </p>
                            )}
                        </Link>
                    ))}
                    <Link
                        href={heroSettings.edit()}
                        className="rounded-xl border border-dashed p-4 text-sm text-muted-foreground transition-colors hover:bg-accent"
                    >
                        Change the home page background photos, video, or switch
                        the Reviews section on and off.
                    </Link>
                </div>

                <Card>
                    <CardHeader>
                        <CardTitle>Latest contact requests</CardTitle>
                    </CardHeader>
                    <CardContent>
                        {latestRequests.length ? (
                            <ul className="divide-y">
                                {latestRequests.map((request) => (
                                    <li key={request.id}>
                                        <Link
                                            href={contactRequests.show(
                                                request.id,
                                            )}
                                            className="flex flex-wrap items-center justify-between gap-2 py-3 hover:underline"
                                        >
                                            <span>
                                                <span className="font-medium">
                                                    {request.name}
                                                </span>
                                                {request.projectType && (
                                                    <span className="text-muted-foreground">
                                                        {' '}
                                                        · {request.projectType}
                                                    </span>
                                                )}
                                            </span>
                                            <span className="flex items-center gap-2 text-sm text-muted-foreground">
                                                {formatDateTime(
                                                    request.createdAt,
                                                )}
                                                <StatusBadge
                                                    status={request.status}
                                                />
                                            </span>
                                        </Link>
                                    </li>
                                ))}
                            </ul>
                        ) : (
                            <p className="text-sm text-muted-foreground">
                                No requests yet.
                            </p>
                        )}
                    </CardContent>
                </Card>
            </PageBody>
        </>
    );
}

Dashboard.layout = {
    breadcrumbs: [{ title: 'Dashboard', href: dashboard() }],
};
