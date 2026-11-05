import { useForm } from '@inertiajs/react';
import type { InertiaFormProps } from '@inertiajs/react';
import type { FormDataType } from '@inertiajs/core';
import { getIn, setIn } from '@/lib/cms';

export type CmsForm<TForm extends FormDataType<TForm>> =
    InertiaFormProps<TForm> & {
        readonly value: <TValue>(path: string) => TValue;
        readonly set: (path: string, next: unknown) => void;
        readonly error: (path: string) => string | undefined;
        readonly errorUnder: (prefix: string) => string | undefined;
    };

/**
 * `useForm` with dot-path helpers for the nested content forms:
 * `form.value('why.points.0.title')`, `form.set('why.points.0.title', 'Hi')`
 * and `form.error('why.points.0.title')`.
 */
export function useCmsForm<TForm extends FormDataType<TForm>>(
    defaults: TForm,
): CmsForm<TForm> {
    const form = useForm<TForm>(defaults);
    // Laravel returns errors keyed by dot path ("about.image.alt"), wider than Inertia's typed keys.
    const errors = form.errors as Record<string, string | undefined>;

    return {
        ...form,
        // The caller names the type it stored at this path; the form state is untyped JSON.
        value: <TValue>(path: string): TValue =>
            getIn(form.data, path) as TValue,
        set: (path: string, next: unknown): void =>
            form.setData((previous: TForm) => setIn(previous, path, next)),
        error: (path: string): string | undefined => errors[path],
        errorUnder: (prefix: string): string | undefined =>
            Object.entries(errors).find(
                ([key]) => key === prefix || key.startsWith(`${prefix}.`),
            )?.[1],
    };
}
