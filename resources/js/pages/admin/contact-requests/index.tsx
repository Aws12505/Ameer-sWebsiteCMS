import { Head, Link, router } from '@inertiajs/react';
import { Download, Search } from 'lucide-react';
import type { ReactNode } from 'react';
import { useState } from 'react';
import { PageBody, PageHeader } from '@/components/cms/layout';
import { StatusBadge } from '@/components/cms/status-badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { formatDateTime } from '@/lib/cms';
import { cn } from '@/lib/utils';
import contactRequests from '@/routes/admin/contact-requests';
import type { Option } from '@/types';

type RequestRow = {
    readonly id: number;
    readonly createdAt: string;
    readonly name: string;
    readonly phone: string;
    readonly email: string;
    readonly projectType: string | null;
    readonly status: string;
};

type Paginated<TItem> = {
    readonly data: readonly TItem[];
    readonly links: readonly {
        readonly url: string | null;
        readonly label: string;
        readonly active: boolean;
    }[];
    readonly total: number;
    readonly last_page: number;
};

type Filters = { readonly search: string; readonly status: string };

type Props = {
    readonly requests: Paginated<RequestRow>;
    readonly filters: Filters;
    readonly statuses: readonly Option[];
};

const ANY_STATUS = 'any';

/** Laravel's pagination labels contain HTML entities for the arrows. */
function pageLabel(label: string): string {
    return label.replaceAll('&laquo;', '«').replaceAll('&raquo;', '»');
}

export default function ContactRequestsIndex({
    requests,
    filters,
    statuses,
}: Props): ReactNode {
    const [search, setSearch] = useState(filters.search);

    const apply = (next: Partial<Filters>): void => {
        const query = { ...filters, ...next };

        router.get(
            contactRequests.index.url({
                query: Object.fromEntries(
                    Object.entries(query).filter(([, value]) => value),
                ),
            }),
            {},
            { preserveState: true, replace: true },
        );
    };

    const exportQuery = Object.fromEntries(
        Object.entries(filters).filter(([, value]) => value),
    );

    return (
        <>
            <Head title="Contact requests" />
            <PageBody>
                <PageHeader
                    title="Contact requests"
                    description="Requests sent from the website's contact form, newest first. Each one is also emailed to the addresses in Site settings."
                    actions={
                        <Button variant="outline" asChild>
                            <a
                                href={contactRequests.export.url({
                                    query: exportQuery,
                                })}
                            >
                                <Download />
                                Export CSV
                            </a>
                        </Button>
                    }
                />

                <form
                    className="flex flex-col gap-2 sm:flex-row"
                    onSubmit={(event) => {
                        event.preventDefault();
                        apply({ search });
                    }}
                >
                    <div className="relative flex-1">
                        <Search
                            className="pointer-events-none absolute top-2.5 left-3 size-4 text-muted-foreground"
                            aria-hidden
                        />
                        <Input
                            className="pl-9"
                            placeholder="Search by name, phone or email"
                            aria-label="Search requests"
                            value={search}
                            onChange={(event) => setSearch(event.target.value)}
                        />
                    </div>
                    <Select
                        value={filters.status || ANY_STATUS}
                        onValueChange={(value) =>
                            apply({ status: value === ANY_STATUS ? '' : value })
                        }
                    >
                        <SelectTrigger className="sm:w-44" aria-label="Status">
                            <SelectValue />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value={ANY_STATUS}>
                                Any status
                            </SelectItem>
                            {statuses.map((status) => (
                                <SelectItem
                                    key={status.value}
                                    value={status.value}
                                >
                                    {status.label}
                                </SelectItem>
                            ))}
                        </SelectContent>
                    </Select>
                    <Button type="submit" variant="secondary">
                        Search
                    </Button>
                </form>

                {requests.data.length ? (
                    <div className="overflow-x-auto rounded-lg border bg-card">
                        <table className="w-full text-sm">
                            <thead className="border-b text-left text-muted-foreground">
                                <tr>
                                    <th className="p-3 font-medium">Date</th>
                                    <th className="p-3 font-medium">Name</th>
                                    <th className="hidden p-3 font-medium sm:table-cell">
                                        Phone
                                    </th>
                                    <th className="hidden p-3 font-medium md:table-cell">
                                        Project type
                                    </th>
                                    <th className="p-3 font-medium">Status</th>
                                </tr>
                            </thead>
                            <tbody className="divide-y">
                                {requests.data.map((request) => (
                                    <tr
                                        key={request.id}
                                        className={cn(
                                            'hover:bg-accent',
                                            request.status === 'new' &&
                                                'font-medium',
                                        )}
                                    >
                                        <td className="p-3 whitespace-nowrap">
                                            {formatDateTime(request.createdAt)}
                                        </td>
                                        <td className="p-3">
                                            <Link
                                                href={contactRequests.show(
                                                    request.id,
                                                )}
                                                className="hover:underline"
                                            >
                                                {request.name}
                                            </Link>
                                        </td>
                                        <td className="hidden p-3 whitespace-nowrap sm:table-cell">
                                            {request.phone}
                                        </td>
                                        <td className="hidden p-3 md:table-cell">
                                            {request.projectType ?? '—'}
                                        </td>
                                        <td className="p-3">
                                            <StatusBadge
                                                status={request.status}
                                            />
                                        </td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>
                ) : (
                    <div className="rounded-lg border border-dashed p-8 text-center text-sm text-muted-foreground">
                        No requests match.
                    </div>
                )}

                {requests.last_page > 1 && (
                    <nav className="flex flex-wrap gap-1" aria-label="Pages">
                        {requests.links.map((link) => (
                            <Button
                                key={link.label}
                                variant={link.active ? 'default' : 'ghost'}
                                size="sm"
                                disabled={!link.url}
                                asChild={Boolean(link.url)}
                            >
                                {link.url ? (
                                    <Link href={link.url} preserveScroll>
                                        {pageLabel(link.label)}
                                    </Link>
                                ) : (
                                    <span>{pageLabel(link.label)}</span>
                                )}
                            </Button>
                        ))}
                    </nav>
                )}
            </PageBody>
        </>
    );
}

ContactRequestsIndex.layout = {
    breadcrumbs: [{ title: 'Contact requests', href: contactRequests.index() }],
};
