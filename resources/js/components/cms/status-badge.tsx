import type { ReactNode } from 'react';
import { Badge } from '@/components/ui/badge';

type Status = 'new' | 'in_progress' | 'done';

const STATUS_LABELS: Record<Status, string> = {
    new: 'New',
    in_progress: 'In progress',
    done: 'Done',
};

const STATUS_VARIANTS: Record<Status, 'default' | 'secondary' | 'outline'> = {
    new: 'default',
    in_progress: 'secondary',
    done: 'outline',
};

function isStatus(value: string): value is Status {
    return value in STATUS_LABELS;
}

/**
 * The New / In progress / Done label of a contact request.
 */
export function StatusBadge({
    status,
}: {
    readonly status: string;
}): ReactNode {
    if (!isStatus(status)) {
        return null;
    }

    return (
        <Badge variant={STATUS_VARIANTS[status]}>{STATUS_LABELS[status]}</Badge>
    );
}
