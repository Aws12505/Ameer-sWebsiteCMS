import { ArrowDown, ArrowUp, Plus, Trash2 } from 'lucide-react';
import type { ReactNode } from 'react';
import { TextAreaField, TextField } from '@/components/cms/field';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import { moveItem } from '@/lib/cms';

type StringListFieldProps = {
    readonly label: string;
    readonly itemLabel: string;
    readonly value: string[];
    readonly onChange: (value: string[]) => void;
    readonly min: number;
    readonly max: number;
    readonly maxLength: number;
    readonly multiline?: boolean;
    readonly error?: string;
    readonly itemError: (index: number) => string | undefined;
    readonly hint?: ReactNode;
};

/**
 * A short list of plain-text items (paragraphs, "What's included" bullets).
 */
export function StringListField({
    label,
    itemLabel,
    value,
    onChange,
    min,
    max,
    maxLength,
    multiline = false,
    error,
    itemError,
    hint,
}: StringListFieldProps): ReactNode {
    const Field = multiline ? TextAreaField : TextField;
    const update = (index: number, text: string): void =>
        onChange(
            value.map((item, position) => (position === index ? text : item)),
        );

    return (
        <div className="grid gap-3">
            <div className="grid gap-1">
                <Label>
                    {label} ({min === max ? min : `${min} to ${max}`})
                </Label>
                {hint && (
                    <p className="text-xs text-muted-foreground">{hint}</p>
                )}
            </div>

            {value.map((item, index) => (
                <div key={index} className="flex items-start gap-2">
                    <Field
                        className="flex-1"
                        label={`${itemLabel} ${index + 1}`}
                        value={item}
                        max={maxLength}
                        required={index < min}
                        error={itemError(index)}
                        onChange={(text) => update(index, text)}
                    />
                    {min !== max && (
                        <div className="mt-6 flex flex-col">
                            <Button
                                type="button"
                                variant="ghost"
                                size="icon"
                                aria-label="Move up"
                                disabled={!index}
                                onClick={() =>
                                    onChange(moveItem(value, index, index - 1))
                                }
                            >
                                <ArrowUp />
                            </Button>
                            <Button
                                type="button"
                                variant="ghost"
                                size="icon"
                                aria-label="Move down"
                                disabled={index === value.length - 1}
                                onClick={() =>
                                    onChange(moveItem(value, index, index + 1))
                                }
                            >
                                <ArrowDown />
                            </Button>
                            <Button
                                type="button"
                                variant="ghost"
                                size="icon"
                                aria-label={`Remove ${itemLabel.toLowerCase()}`}
                                disabled={value.length <= min}
                                onClick={() =>
                                    onChange(
                                        value.filter(
                                            (_, position) => position !== index,
                                        ),
                                    )
                                }
                            >
                                <Trash2 />
                            </Button>
                        </div>
                    )}
                </div>
            ))}

            {value.length < max && (
                <div>
                    <Button
                        type="button"
                        variant="outline"
                        size="sm"
                        onClick={() => onChange([...value, ''])}
                    >
                        <Plus />
                        Add {itemLabel.toLowerCase()}
                    </Button>
                </div>
            )}
            <InputError message={error} />
        </div>
    );
}
