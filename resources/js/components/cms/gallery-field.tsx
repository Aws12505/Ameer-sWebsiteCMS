import {
    ArrowDown,
    ArrowUp,
    GripVertical,
    ImagePlus,
    Trash2,
} from 'lucide-react';
import type { ReactNode } from 'react';
import { useId, useRef, useState } from 'react';
import { FieldShell, TextField } from '@/components/cms/field';
import { recommendedText, sizeWarning } from '@/components/cms/image-field';
import { Button } from '@/components/ui/button';
import { Spinner } from '@/components/ui/spinner';
import { useDragSort } from '@/hooks/use-drag-sort';
import { moveItem, uploadFile } from '@/lib/cms';
import type { ImageSize } from '@/lib/limits';
import { LIMITS } from '@/lib/limits';
import { cn } from '@/lib/utils';
import type { MediaField } from '@/types';

type GalleryFieldProps = {
    readonly label: string;
    readonly value: MediaField[];
    readonly onChange: (value: MediaField[]) => void;
    readonly max: number;
    readonly recommended?: ImageSize;
    readonly error?: string;
    readonly itemError: (
        index: number,
        field: 'path' | 'alt',
    ) => string | undefined;
    readonly hint?: ReactNode;
    readonly required?: boolean;
};

/**
 * An ordered list of photos, each with its short description.
 * Drag the handle (or use the arrows) to change the order.
 */
export function GalleryField({
    label,
    value,
    onChange,
    max,
    recommended,
    error,
    itemError,
    hint,
    required,
}: GalleryFieldProps): ReactNode {
    const id = useId();
    const input = useRef<HTMLInputElement>(null);
    const [uploading, setUploading] = useState(false);
    const [messages, setMessages] = useState<string[]>([]);
    const { dragProps, overIndex } = useDragSort((from, to) =>
        onChange(moveItem(value, from, to)),
    );

    const remaining = max - value.length;

    const upload = async (files: File[]): Promise<void> => {
        setUploading(true);
        setMessages([]);

        const accepted = files.slice(0, Math.max(remaining, 0));
        const results = await Promise.allSettled(
            accepted.map((file) => uploadFile(file, 'image')),
        );

        const added = results.flatMap((result) =>
            result.status === 'fulfilled'
                ? [{ path: result.value.path, url: result.value.url, alt: '' }]
                : [],
        );
        const notes = results.flatMap((result, index) => {
            const name = accepted[index].name;

            if (result.status === 'rejected') {
                return [
                    `${name}: ${result.reason instanceof Error ? result.reason.message : 'upload failed'}`,
                ];
            }

            const warning = sizeWarning(result.value, recommended);

            return warning ? [`${name}: ${warning}`] : [];
        });
        const skipped =
            files.length > accepted.length
                ? [
                      `Only ${max} photos are allowed; ${files.length - accepted.length} were not added.`,
                  ]
                : [];

        onChange([...value, ...added]);
        setMessages([...notes, ...skipped]);
        setUploading(false);
    };

    return (
        <FieldShell
            id={id}
            label={`${label} (${value.length} of ${max})`}
            required={required}
            hint={
                <>
                    {recommended && (
                        <>Recommended: {recommendedText(recommended)}. </>
                    )}
                    {hint}
                </>
            }
            error={error}
        >
            <div className="grid gap-3">
                {value.map((photo, index) => (
                    <div
                        key={photo.path}
                        {...dragProps(index)}
                        className={cn(
                            'flex flex-col gap-3 rounded-lg border bg-card p-3 sm:flex-row sm:items-start',
                            overIndex === index && 'ring-2 ring-ring',
                        )}
                    >
                        <div className="flex items-center gap-2">
                            <GripVertical
                                className="hidden size-4 shrink-0 cursor-grab text-muted-foreground sm:block"
                                aria-hidden
                            />
                            <img
                                src={photo.url}
                                alt=""
                                className="size-20 shrink-0 rounded-md bg-muted object-cover"
                            />
                        </div>
                        <div className="grid flex-1 gap-2">
                            <TextField
                                label={`Photo ${index + 1}: short description`}
                                value={photo.alt}
                                max={LIMITS.alt}
                                required
                                error={
                                    itemError(index, 'alt') ??
                                    itemError(index, 'path')
                                }
                                onChange={(alt) =>
                                    onChange(
                                        value.map((item, position) =>
                                            position === index
                                                ? { ...item, alt }
                                                : item,
                                        ),
                                    )
                                }
                            />
                        </div>
                        <div className="flex gap-1 sm:flex-col">
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
                                aria-label="Remove photo"
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
                    </div>
                ))}

                <div>
                    <Button
                        type="button"
                        variant="outline"
                        size="sm"
                        disabled={uploading || remaining < 1}
                        onClick={() => input.current?.click()}
                    >
                        {uploading ? <Spinner /> : <ImagePlus />}
                        Add photos
                    </Button>
                    <input
                        id={id}
                        ref={input}
                        type="file"
                        accept="image/jpeg,image/png,image/webp"
                        multiple
                        className="sr-only"
                        onChange={(event) => {
                            const files = Array.from(event.target.files ?? []);

                            if (input.current) {
                                // Clear the picker so choosing the same files again still uploads.
                                input.current.value = '';
                            }

                            if (files.length) {
                                void upload(files);
                            }
                        }}
                    />
                </div>

                {messages.map((message) => (
                    <p
                        key={message}
                        className="text-xs text-amber-700 dark:text-amber-400"
                    >
                        {message}
                    </p>
                ))}
            </div>
        </FieldShell>
    );
}
