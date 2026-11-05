import { ImageIcon, Trash2, Upload } from 'lucide-react';
import type { ReactNode } from 'react';
import { useId, useRef, useState } from 'react';
import { FieldShell, TextField } from '@/components/cms/field';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Spinner } from '@/components/ui/spinner';
import { uploadFile } from '@/lib/cms';
import type { ImageSize } from '@/lib/limits';
import { LIMITS } from '@/lib/limits';
import type { MediaField, UploadKind, UploadResult } from '@/types';

const ACCEPT: Record<UploadKind, string> = {
    image: 'image/jpeg,image/png,image/webp',
    favicon: 'image/png',
    video: 'video/mp4',
};

/**
 * A warning (never a block) when a photo is smaller than recommended.
 */
export function sizeWarning(
    result: UploadResult,
    recommended?: ImageSize,
): string | null {
    if (!recommended || result.width === null || result.height === null) {
        return null;
    }

    const tooSmall =
        result.width < recommended.width || result.height < recommended.height;

    return tooSmall
        ? `This photo is ${result.width} × ${result.height}. It may look blurry on large screens; ${recommendedText(recommended)} is recommended.`
        : null;
}

export function recommendedText(size: ImageSize): string {
    return size.height
        ? `${size.width} × ${size.height} or larger`
        : `${size.width} px on the long side or larger`;
}

type ImageFieldProps = {
    readonly label: string;
    readonly value: MediaField | null;
    readonly onChange: (value: MediaField | null) => void;
    readonly kind?: UploadKind;
    readonly recommended?: ImageSize;
    readonly withAlt?: boolean;
    readonly required?: boolean;
    readonly error?: string;
    readonly altError?: string;
    readonly hint?: ReactNode;
    readonly preview?: (value: MediaField) => ReactNode;
};

/**
 * Upload one photo (or video), with its required short description.
 */
export function ImageField({
    label,
    value,
    onChange,
    kind = 'image',
    recommended,
    withAlt = true,
    required,
    error,
    altError,
    hint,
    preview,
}: ImageFieldProps): ReactNode {
    const id = useId();
    const input = useRef<HTMLInputElement>(null);
    const [uploading, setUploading] = useState(false);
    const [uploadError, setUploadError] = useState<string | null>(null);
    const [warning, setWarning] = useState<string | null>(null);

    const upload = async (file: File): Promise<void> => {
        setUploading(true);
        setUploadError(null);
        setWarning(null);

        try {
            const result = await uploadFile(file, kind);
            setWarning(sizeWarning(result, recommended));
            onChange({
                path: result.path,
                url: result.url,
                alt: value?.alt ?? '',
            });
        } catch (exception) {
            setUploadError(
                exception instanceof Error
                    ? exception.message
                    : 'The upload failed. Please try again.',
            );
        } finally {
            setUploading(false);
        }
    };

    const recommendation = recommended && (
        <>Recommended: {recommendedText(recommended)}. </>
    );

    return (
        <FieldShell
            id={id}
            label={label}
            required={required}
            hint={
                <>
                    {recommendation}
                    {hint}
                </>
            }
            error={error}
        >
            <div className="flex flex-col gap-3 rounded-lg border border-dashed p-3 sm:flex-row sm:items-start">
                <div className="flex size-28 shrink-0 items-center justify-center overflow-hidden rounded-md bg-muted">
                    {value && kind === 'video' && (
                        <video
                            src={value.url}
                            className="size-full object-cover"
                            muted
                        />
                    )}
                    {value && kind !== 'video' && (
                        <img
                            src={value.url}
                            alt=""
                            className="size-full object-cover"
                        />
                    )}
                    {!value && (
                        <ImageIcon className="size-8 text-muted-foreground" />
                    )}
                </div>

                <div className="grid flex-1 gap-3">
                    <div className="flex flex-wrap gap-2">
                        <Button
                            type="button"
                            variant="outline"
                            size="sm"
                            disabled={uploading}
                            onClick={() => input.current?.click()}
                        >
                            {uploading ? <Spinner /> : <Upload />}
                            {value ? 'Replace' : 'Upload'}
                        </Button>
                        {value && (
                            <Button
                                type="button"
                                variant="ghost"
                                size="sm"
                                onClick={() => {
                                    setWarning(null);
                                    onChange(null);
                                }}
                            >
                                <Trash2 />
                                Remove
                            </Button>
                        )}
                        <input
                            id={id}
                            ref={input}
                            type="file"
                            accept={ACCEPT[kind]}
                            className="sr-only"
                            onChange={(event) => {
                                const file = event.target.files?.[0];

                                if (input.current) {
                                    // Clear the picker so choosing the same file again still uploads.
                                    input.current.value = '';
                                }

                                if (file) {
                                    void upload(file);
                                }
                            }}
                        />
                    </div>

                    {value && preview?.(value)}

                    {value && withAlt && (
                        <TextField
                            label="Short description of the photo"
                            value={value.alt}
                            max={LIMITS.alt}
                            required
                            error={altError}
                            placeholder="For example: Finished kitchen with white cabinets"
                            hint="Used by screen readers and search engines."
                            onChange={(alt) => onChange({ ...value, alt })}
                        />
                    )}

                    {warning && (
                        <p className="text-xs text-amber-700 dark:text-amber-400">
                            {warning}
                        </p>
                    )}
                    <InputError message={uploadError ?? undefined} />
                </div>
            </div>
        </FieldShell>
    );
}
