import { router } from '@inertiajs/react';
import type { InertiaLinkProps } from '@inertiajs/react';
import { Link } from '@inertiajs/react';
import { ArrowDown, ArrowUp, GripVertical, ImageIcon } from 'lucide-react';
import type { ReactNode } from 'react';
import { useState } from 'react';
import { Checkbox } from '@/components/ui/checkbox';
import { Button } from '@/components/ui/button';
import { useDragSort } from '@/hooks/use-drag-sort';
import { moveItem } from '@/lib/cms';
import { cn } from '@/lib/utils';

type RouteTarget = { readonly url: string; readonly method: 'put' | 'patch' };

export type SortableItem = {
    readonly id: number;
    readonly published?: boolean;
    readonly image?: string | null;
};

type SortableListProps<TItem extends SortableItem> = {
    readonly items: readonly TItem[];
    readonly reorder?: RouteTarget;
    readonly publish?: (item: TItem) => RouteTarget;
    readonly editHref: (item: TItem) => NonNullable<InertiaLinkProps['href']>;
    readonly title: (item: TItem) => ReactNode;
    readonly details?: (item: TItem) => ReactNode;
    readonly badges?: (item: TItem) => ReactNode;
    readonly showImage?: boolean;
    readonly empty: ReactNode;
};

/**
 * A list in the manager's order: drag a row (or use the arrows) to move
 * it, and switch it on or off with "Published". Saves straight away.
 */
export function SortableList<TItem extends SortableItem>({
    items,
    reorder,
    publish,
    editHref,
    title,
    details,
    badges,
    showImage = true,
    empty,
}: SortableListProps<TItem>): ReactNode {
    const [order, setOrder] = useState<readonly TItem[]>(items);
    const [synced, setSynced] = useState<readonly TItem[]>(items);

    if (synced !== items) {
        setSynced(items);
        setOrder(items);
    }

    const save = (next: readonly TItem[]): void => {
        setOrder(next);

        if (reorder) {
            router.visit(reorder.url, {
                method: reorder.method,
                data: { ids: next.map((item) => item.id) },
                preserveScroll: true,
                preserveState: true,
            });
        }
    };

    const move = (from: number, to: number): void =>
        save(moveItem([...order], from, to));
    const { dragProps, overIndex } = useDragSort(move);

    if (!order.length) {
        return (
            <div className="rounded-lg border border-dashed p-8 text-center text-sm text-muted-foreground">
                {empty}
            </div>
        );
    }

    return (
        <ul className="divide-y rounded-lg border bg-card">
            {order.map((item, index) => (
                <li
                    key={item.id}
                    {...(reorder ? dragProps(index) : {})}
                    className={cn(
                        'flex flex-wrap items-center gap-3 p-3 sm:flex-nowrap',
                        overIndex === index && 'bg-accent',
                    )}
                >
                    {reorder && (
                        <GripVertical
                            className="hidden size-4 shrink-0 cursor-grab text-muted-foreground sm:block"
                            aria-hidden
                        />
                    )}
                    {showImage && (
                        <div className="flex size-12 shrink-0 items-center justify-center overflow-hidden rounded-md bg-muted">
                            {item.image ? (
                                <img
                                    src={item.image}
                                    alt=""
                                    className="size-full object-cover"
                                />
                            ) : (
                                <ImageIcon className="size-5 text-muted-foreground" />
                            )}
                        </div>
                    )}
                    <div className="min-w-0 flex-1">
                        <Link
                            href={editHref(item)}
                            className="font-medium hover:underline"
                        >
                            {title(item)}
                        </Link>
                        {details && (
                            <div className="truncate text-sm text-muted-foreground">
                                {details(item)}
                            </div>
                        )}
                        {badges && (
                            <div className="mt-1 flex flex-wrap gap-1">
                                {badges(item)}
                            </div>
                        )}
                    </div>
                    <div className="flex items-center gap-1">
                        {publish && (
                            <label className="mr-2 flex items-center gap-2 text-sm">
                                <Checkbox
                                    checked={Boolean(item.published)}
                                    onCheckedChange={(state) =>
                                        router.visit(publish(item).url, {
                                            method: publish(item).method,
                                            data: { published: state === true },
                                            preserveScroll: true,
                                        })
                                    }
                                />
                                Published
                            </label>
                        )}
                        {reorder && (
                            <>
                                <Button
                                    variant="ghost"
                                    size="icon"
                                    aria-label="Move up"
                                    disabled={!index}
                                    onClick={() => move(index, index - 1)}
                                >
                                    <ArrowUp />
                                </Button>
                                <Button
                                    variant="ghost"
                                    size="icon"
                                    aria-label="Move down"
                                    disabled={index === order.length - 1}
                                    onClick={() => move(index, index + 1)}
                                >
                                    <ArrowDown />
                                </Button>
                            </>
                        )}
                    </div>
                </li>
            ))}
        </ul>
    );
}
