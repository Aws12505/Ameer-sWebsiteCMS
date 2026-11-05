import type { ReactNode } from 'react';
import { useId } from 'react';
import InputError from '@/components/input-error';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { cn } from '@/lib/utils';
import type { Option } from '@/types';

/**
 * Any line break, replaced by a space because fields are single-line plain text.
 * Matches the break in "first\nsecond" → "first second"; rejects "first second".
 * Tester: https://regex101.com/?regex=%5Cr%3F%5Cn%7C%5Cr&flags=g&testString=first%0Asecond
 */
const LINE_BREAKS = /\r?\n|\r/g;

/** Visible lines of a text box before it scrolls. */
const DEFAULT_TEXTAREA_ROWS = 3;

type ShellProps = {
    readonly id: string;
    readonly label: string;
    readonly hint?: ReactNode;
    readonly error?: string;
    readonly required?: boolean;
    readonly length?: number;
    readonly max?: number;
    readonly children: ReactNode;
    readonly className?: string;
};

/**
 * Label, character counter, hint and error around one input.
 */
export function FieldShell({
    id,
    label,
    hint,
    error,
    required,
    length,
    max,
    children,
    className,
}: ShellProps): ReactNode {
    return (
        <div className={cn('grid gap-2', className)}>
            <div className="flex items-baseline justify-between gap-2">
                <Label htmlFor={id}>
                    {label}
                    {required && (
                        <span className="text-destructive" aria-hidden>
                            {' '}
                            *
                        </span>
                    )}
                </Label>
                {max !== undefined && length !== undefined && (
                    <span
                        className={cn(
                            'text-xs text-muted-foreground tabular-nums',
                            length > max && 'text-destructive',
                        )}
                        aria-live="polite"
                    >
                        {length} / {max}
                    </span>
                )}
            </div>
            {children}
            {hint && <p className="text-xs text-muted-foreground">{hint}</p>}
            <InputError message={error} />
        </div>
    );
}

type TextFieldProps = {
    readonly label: string;
    readonly value: string | null | undefined;
    readonly onChange: (value: string) => void;
    readonly max?: number;
    readonly error?: string;
    readonly hint?: ReactNode;
    readonly required?: boolean;
    readonly disabled?: boolean;
    readonly placeholder?: string;
    readonly type?: 'text' | 'email' | 'url' | 'tel' | 'password';
    readonly className?: string;
    readonly autoComplete?: string;
};

/**
 * A single-line text input with a character counter.
 */
export function TextField({
    label,
    value,
    onChange,
    max,
    error,
    hint,
    required,
    disabled,
    placeholder,
    type = 'text',
    className,
    autoComplete,
}: TextFieldProps): ReactNode {
    const id = useId();
    const text = value ?? '';

    return (
        <FieldShell
            id={id}
            label={label}
            hint={hint}
            error={error}
            required={required}
            length={text.length}
            max={max}
            className={className}
        >
            <Input
                id={id}
                type={type}
                value={text}
                maxLength={max}
                disabled={disabled}
                placeholder={placeholder}
                autoComplete={autoComplete}
                aria-invalid={Boolean(error)}
                onChange={(event) => onChange(event.target.value)}
            />
        </FieldShell>
    );
}

type TextAreaFieldProps = Omit<TextFieldProps, 'type' | 'autoComplete'> & {
    readonly rows?: number;
    readonly allowLineBreaks?: boolean;
};

/**
 * A longer text box. Plain text: line breaks are turned into spaces
 * unless the field explicitly allows them (internal notes).
 */
export function TextAreaField({
    label,
    value,
    onChange,
    max,
    error,
    hint,
    required,
    disabled,
    placeholder,
    className,
    rows = DEFAULT_TEXTAREA_ROWS,
    allowLineBreaks = false,
}: TextAreaFieldProps): ReactNode {
    const id = useId();
    const text = value ?? '';

    return (
        <FieldShell
            id={id}
            label={label}
            hint={hint}
            error={error}
            required={required}
            length={text.length}
            max={max}
            className={className}
        >
            <textarea
                id={id}
                rows={rows}
                value={text}
                maxLength={max}
                disabled={disabled}
                placeholder={placeholder}
                aria-invalid={Boolean(error)}
                className="w-full min-w-0 rounded-md border border-input bg-transparent px-3 py-2 text-base shadow-xs transition-[color,box-shadow] outline-none placeholder:text-muted-foreground focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50 disabled:cursor-not-allowed disabled:opacity-50 aria-invalid:border-destructive md:text-sm dark:bg-input/30"
                onChange={(event) =>
                    onChange(
                        allowLineBreaks
                            ? event.target.value
                            : event.target.value.replace(LINE_BREAKS, ' '),
                    )
                }
            />
        </FieldShell>
    );
}

type CheckboxFieldProps = {
    readonly label: string;
    readonly checked: boolean;
    readonly onChange: (checked: boolean) => void;
    readonly description?: ReactNode;
    readonly error?: string;
    readonly disabled?: boolean;
};

/**
 * An on/off switch with a short explanation.
 */
export function CheckboxField({
    label,
    checked,
    onChange,
    description,
    error,
    disabled,
}: CheckboxFieldProps): ReactNode {
    const id = useId();

    return (
        <div className="grid gap-1">
            <div className="flex items-start gap-3">
                <Checkbox
                    id={id}
                    checked={checked}
                    disabled={disabled}
                    className="mt-0.5"
                    onCheckedChange={(state) => onChange(state === true)}
                />
                <div className="grid gap-1">
                    <Label htmlFor={id}>{label}</Label>
                    {description && (
                        <p className="text-xs text-muted-foreground">
                            {description}
                        </p>
                    )}
                </div>
            </div>
            <InputError message={error} />
        </div>
    );
}

type SelectFieldProps = {
    readonly label: string;
    readonly value: string | null | undefined;
    readonly onChange: (value: string) => void;
    readonly options: readonly Option[];
    readonly error?: string;
    readonly hint?: ReactNode;
    readonly required?: boolean;
    readonly placeholder?: string;
    readonly className?: string;
};

/**
 * A drop-down choice.
 */
export function SelectField({
    label,
    value,
    onChange,
    options,
    error,
    hint,
    required,
    placeholder = 'Choose…',
    className,
}: SelectFieldProps): ReactNode {
    const id = useId();

    return (
        <FieldShell
            id={id}
            label={label}
            hint={hint}
            error={error}
            required={required}
            className={className}
        >
            <Select value={value ?? undefined} onValueChange={onChange}>
                <SelectTrigger
                    id={id}
                    className="w-full"
                    aria-invalid={Boolean(error)}
                >
                    <SelectValue placeholder={placeholder} />
                </SelectTrigger>
                <SelectContent>
                    {options.map((option) => (
                        <SelectItem key={option.value} value={option.value}>
                            {option.label}
                        </SelectItem>
                    ))}
                </SelectContent>
            </Select>
        </FieldShell>
    );
}
