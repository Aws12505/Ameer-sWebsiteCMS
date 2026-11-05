import type { ReactNode } from 'react';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import { Spinner } from '@/components/ui/spinner';
import { formatDateTime } from '@/lib/cms';
import type { LastEdited } from '@/types';

/**
 * The title row of an admin screen.
 */
export function PageHeader({
    title,
    description,
    actions,
    lastEdited,
}: {
    readonly title: string;
    readonly description?: ReactNode;
    readonly actions?: ReactNode;
    readonly lastEdited?: LastEdited | null;
}): ReactNode {
    return (
        <div className="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
            <div className="space-y-1">
                <h1 className="text-xl font-semibold tracking-tight">
                    {title}
                </h1>
                {description && (
                    <p className="max-w-2xl text-sm text-muted-foreground">
                        {description}
                    </p>
                )}
                {lastEdited?.at && (
                    <p className="text-xs text-muted-foreground">
                        Last edited{' '}
                        {lastEdited.by ? `by ${lastEdited.by} ` : ''}
                        on {formatDateTime(lastEdited.at)}
                    </p>
                )}
            </div>
            {actions && <div className="flex flex-wrap gap-2">{actions}</div>}
        </div>
    );
}

/**
 * The page body: a column with comfortable spacing on phones and desktops.
 */
export function PageBody({
    children,
}: {
    readonly children: ReactNode;
}): ReactNode {
    return (
        <div className="mx-auto flex w-full max-w-5xl flex-col gap-6 p-4 md:p-6">
            {children}
        </div>
    );
}

/**
 * One titled card of form fields.
 */
export function FormSection({
    title,
    description,
    children,
    id,
}: {
    readonly title: string;
    readonly description?: ReactNode;
    readonly children: ReactNode;
    readonly id?: string;
}): ReactNode {
    return (
        <Card id={id} className="scroll-mt-20">
            <CardHeader>
                <CardTitle>{title}</CardTitle>
                {description && (
                    <CardDescription>{description}</CardDescription>
                )}
            </CardHeader>
            <CardContent className="grid gap-5">{children}</CardContent>
        </Card>
    );
}

/**
 * The save button, pinned to the bottom of the screen so it is always in reach.
 */
export function SaveBar({
    processing,
    isDirty,
    label = 'Save changes',
    children,
}: {
    readonly processing: boolean;
    readonly isDirty: boolean;
    readonly label?: string;
    readonly children?: ReactNode;
}): ReactNode {
    return (
        <div className="sticky bottom-0 z-20 -mx-4 -mb-4 border-t bg-background/95 backdrop-blur md:-mx-6 md:-mb-6">
            <div className="flex flex-wrap items-center justify-end gap-3 px-4 py-3 md:px-6">
                {isDirty && (
                    <span className="mr-auto text-sm text-muted-foreground">
                        You have unsaved changes.
                    </span>
                )}
                {children}
                <Button type="submit" disabled={processing}>
                    {processing && <Spinner />}
                    {label}
                </Button>
            </div>
        </div>
    );
}

/**
 * A button that asks "Are you sure?" before doing something.
 */
export function ConfirmButton({
    title,
    description,
    confirmLabel,
    onConfirm,
    children,
    variant = 'destructive',
    size,
    disabled,
}: {
    readonly title: string;
    readonly description: ReactNode;
    readonly confirmLabel: string;
    readonly onConfirm: () => void;
    readonly children: ReactNode;
    readonly variant?: 'destructive' | 'outline' | 'ghost';
    readonly size?: 'sm' | 'default' | 'icon';
    readonly disabled?: boolean;
}): ReactNode {
    return (
        <Dialog>
            <DialogTrigger asChild>
                <Button
                    type="button"
                    variant={variant}
                    size={size}
                    disabled={disabled}
                >
                    {children}
                </Button>
            </DialogTrigger>
            <DialogContent>
                <DialogTitle>{title}</DialogTitle>
                <DialogDescription>{description}</DialogDescription>
                <DialogFooter className="gap-2">
                    <DialogClose asChild>
                        <Button variant="secondary">Cancel</Button>
                    </DialogClose>
                    <DialogClose asChild>
                        <Button variant="destructive" onClick={onConfirm}>
                            {confirmLabel}
                        </Button>
                    </DialogClose>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    );
}
