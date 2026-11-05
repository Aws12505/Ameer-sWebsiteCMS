import { Head, router, usePage } from '@inertiajs/react';
import { RotateCcw, Trash2 } from 'lucide-react';
import type { ReactNode } from 'react';
import { ConfirmButton, PageBody, PageHeader } from '@/components/cms/layout';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { formatDateTime } from '@/lib/cms';
import trash from '@/routes/admin/trash';

type TrashedItem = {
    readonly type: string;
    readonly typeLabel: string;
    readonly id: number;
    readonly title: string;
    readonly deletedAt: string;
    readonly purgesAt: string;
};

type Props = {
    readonly items: readonly TrashedItem[];
    readonly trashDays: number;
};

export default function Trash({ items, trashDays }: Props): ReactNode {
    const { auth } = usePage().props;

    return (
        <>
            <Head title="Trash" />
            <PageBody>
                <PageHeader
                    title="Trash"
                    description={`Deleted items stay here for ${trashDays} days, then they are removed for good. Restore puts an item back where it was (check that it is published).`}
                />
                {items.length ? (
                    <ul className="divide-y rounded-lg border bg-card">
                        {items.map((item) => (
                            <li
                                key={`${item.type}-${item.id}`}
                                className="flex flex-wrap items-center gap-3 p-3"
                            >
                                <div className="min-w-0 flex-1">
                                    <p className="font-medium">{item.title}</p>
                                    <p className="text-sm text-muted-foreground">
                                        Deleted {formatDateTime(item.deletedAt)}{' '}
                                        · removed for good{' '}
                                        {formatDateTime(item.purgesAt)}
                                    </p>
                                </div>
                                <Badge variant="secondary">
                                    {item.typeLabel}
                                </Badge>
                                <Button
                                    variant="outline"
                                    size="sm"
                                    onClick={() =>
                                        router.post(
                                            trash.restore.url({
                                                type: item.type,
                                                id: item.id,
                                            }),
                                            {},
                                            { preserveScroll: true },
                                        )
                                    }
                                >
                                    <RotateCcw />
                                    Restore
                                </Button>
                                {auth.isAdmin && (
                                    <ConfirmButton
                                        title={`Delete “${item.title}” for good?`}
                                        description="This cannot be undone."
                                        confirmLabel="Delete for good"
                                        variant="ghost"
                                        size="sm"
                                        onConfirm={() =>
                                            router.delete(
                                                trash.destroy.url({
                                                    type: item.type,
                                                    id: item.id,
                                                }),
                                                { preserveScroll: true },
                                            )
                                        }
                                    >
                                        <Trash2 />
                                        Delete for good
                                    </ConfirmButton>
                                )}
                            </li>
                        ))}
                    </ul>
                ) : (
                    <div className="rounded-lg border border-dashed p-8 text-center text-sm text-muted-foreground">
                        The trash is empty.
                    </div>
                )}
            </PageBody>
        </>
    );
}

Trash.layout = {
    breadcrumbs: [{ title: 'Trash', href: trash.index() }],
};
