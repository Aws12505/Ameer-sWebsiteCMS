import { Lock } from 'lucide-react';
import type { ReactNode } from 'react';
import { TextField } from '@/components/cms/field';
import { LIMITS } from '@/lib/limits';

type KeyFieldProps = {
    readonly value: string;
    readonly onChange: (value: string) => void;
    readonly locked: boolean;
    readonly error?: string;
    readonly usage: string;
};

/**
 * The item's URL key. Created from the title, editable until the first
 * publish, then locked so links on the live site keep working.
 */
export function KeyField({
    value,
    onChange,
    locked,
    error,
    usage,
}: KeyFieldProps): ReactNode {
    return (
        <TextField
            label="Key"
            required
            disabled={locked}
            max={LIMITS.key}
            value={value}
            error={error}
            onChange={(next) => onChange(next.toLowerCase())}
            hint={
                locked ? (
                    <span className="inline-flex items-center gap-1">
                        <Lock className="size-3" aria-hidden />
                        Locked because this item has been published. {usage}
                    </span>
                ) : (
                    <>
                        Lowercase letters, numbers and hyphens. Made from the
                        title. {usage} It is locked after the first publish.
                    </>
                )
            }
        />
    );
}
