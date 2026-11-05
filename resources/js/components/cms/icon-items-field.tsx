import { usePage } from '@inertiajs/react';
import type { LucideIcon } from 'lucide-react';
import {
    BadgeCheck,
    Building2,
    Calculator,
    CalendarCheck,
    ClipboardList,
    Hammer,
    HardHat,
    ListChecks,
    MessageSquareText,
    Receipt,
    Ruler,
    Shapes,
} from 'lucide-react';
import type { ReactNode } from 'react';
import { TextAreaField, TextField } from '@/components/cms/field';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import InputError from '@/components/input-error';
import type { IconItem } from '@/types';

/**
 * Previews for the icon keys the website draws. The website uses its own
 * icon set; these are close look-alikes so the manager can tell them apart.
 */
const ICON_PREVIEWS: Record<string, LucideIcon> = {
    'chat-circle-text': MessageSquareText,
    'seal-check': BadgeCheck,
    'clipboard-text': ClipboardList,
    buildings: Building2,
    ruler: Ruler,
    hammer: Hammer,
    calculator: Calculator,
    'hard-hat': HardHat,
    'list-checks': ListChecks,
    'calendar-check': CalendarCheck,
    receipt: Receipt,
};

function iconLabel(key: string): string {
    return key
        .split('-')
        .map((word) => word.charAt(0).toUpperCase() + word.slice(1))
        .join(' ');
}

export function IconPreview({ name }: { readonly name: string }): ReactNode {
    const Icon = ICON_PREVIEWS[name] ?? Shapes;

    return <Icon className="size-4" aria-hidden />;
}

type IconItemsFieldProps = {
    readonly label: string;
    readonly itemLabel: string;
    readonly value: IconItem[];
    readonly onChange: (value: IconItem[]) => void;
    readonly titleMax: number;
    readonly textMax: number;
    readonly error: (path: string) => string | undefined;
    readonly basePath: string;
    readonly hint?: ReactNode;
};

/**
 * A fixed number of cards, each with an icon, a title and a line of text
 * (reasons, pillars, steps). The layout on the site is built for this count.
 */
export function IconItemsField({
    label,
    itemLabel,
    value,
    onChange,
    titleMax,
    textMax,
    error,
    basePath,
    hint,
}: IconItemsFieldProps): ReactNode {
    const icons = usePage().props.cms?.icons ?? [];
    const update = (index: number, patch: Partial<IconItem>): void =>
        onChange(
            value.map((item, position) =>
                position === index ? { ...item, ...patch } : item,
            ),
        );

    return (
        <div className="grid gap-3">
            <div className="grid gap-1">
                <Label>{label}</Label>
                {hint && (
                    <p className="text-xs text-muted-foreground">{hint}</p>
                )}
            </div>
            <div className="grid gap-4 md:grid-cols-2">
                {value.map((item, index) => (
                    <div
                        key={index}
                        className="grid gap-3 rounded-lg border p-3"
                    >
                        <p className="text-sm font-medium">
                            {itemLabel} {index + 1}
                        </p>
                        <div className="grid gap-2">
                            <Label>Icon</Label>
                            <Select
                                value={item.icon}
                                onValueChange={(icon) =>
                                    update(index, { icon })
                                }
                            >
                                <SelectTrigger className="w-full">
                                    <SelectValue placeholder="Choose an icon" />
                                </SelectTrigger>
                                <SelectContent>
                                    {icons.map((icon) => (
                                        <SelectItem key={icon} value={icon}>
                                            <IconPreview name={icon} />
                                            {iconLabel(icon)}
                                        </SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>
                            <InputError
                                message={error(`${basePath}.${index}.icon`)}
                            />
                        </div>
                        <TextField
                            label="Title"
                            value={item.title}
                            max={titleMax}
                            required
                            error={error(`${basePath}.${index}.title`)}
                            onChange={(title) => update(index, { title })}
                        />
                        <TextAreaField
                            label="Text"
                            rows={2}
                            value={item.text}
                            max={textMax}
                            required
                            error={error(`${basePath}.${index}.text`)}
                            onChange={(text) => update(index, { text })}
                        />
                    </div>
                ))}
            </div>
        </div>
    );
}
