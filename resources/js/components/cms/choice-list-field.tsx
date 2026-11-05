import type { ReactNode } from 'react';
import { CheckboxField } from '@/components/cms/field';
import InputError from '@/components/input-error';
import { Label } from '@/components/ui/label';

type Choice = { readonly id: number; readonly label: string };

/**
 * Pick one or more items from a short list (for example core services).
 */
export function ChoiceListField({
    label,
    choices,
    value,
    onChange,
    error,
    hint,
    disabledIds = [],
}: {
    readonly label: string;
    readonly choices: readonly Choice[];
    readonly value: number[];
    readonly onChange: (value: number[]) => void;
    readonly error?: string;
    readonly hint?: ReactNode;
    readonly disabledIds?: readonly number[];
}): ReactNode {
    return (
        <div className="grid gap-3">
            <div className="grid gap-1">
                <Label>{label}</Label>
                {hint && (
                    <p className="text-xs text-muted-foreground">{hint}</p>
                )}
            </div>
            <div className="grid gap-3 sm:grid-cols-2">
                {choices.map((choice) => (
                    <CheckboxField
                        key={choice.id}
                        label={choice.label}
                        disabled={disabledIds.includes(choice.id)}
                        checked={value.includes(choice.id)}
                        onChange={(checked) =>
                            onChange(
                                checked
                                    ? [...value, choice.id]
                                    : value.filter((id) => id !== choice.id),
                            )
                        }
                    />
                ))}
            </div>
            <InputError message={error} />
        </div>
    );
}
