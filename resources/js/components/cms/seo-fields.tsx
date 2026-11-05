import type { ReactNode } from 'react';
import { TextAreaField, TextField } from '@/components/cms/field';
import { ImageField } from '@/components/cms/image-field';
import { FormSection } from '@/components/cms/layout';
import { IMAGE_SIZES, LIMITS } from '@/lib/limits';
import type { MediaField } from '@/types';

type SeoFieldsProps = {
    readonly title: string | null;
    readonly description: string | null;
    readonly image?: MediaField | null;
    readonly onChange: (
        field: 'title' | 'description' | 'image',
        value: unknown,
    ) => void;
    readonly error: (field: string) => string | undefined;
    readonly defaults: string;
    readonly withImage?: boolean;
};

/**
 * Search result title, description and share image for one page.
 */
export function SeoFields({
    title,
    description,
    image = null,
    onChange,
    error,
    defaults,
    withImage = false,
}: SeoFieldsProps): ReactNode {
    return (
        <FormSection
            title="Search and sharing (optional)"
            description={defaults}
        >
            <TextField
                label="Search title"
                max={LIMITS.seoTitle}
                value={title}
                error={error('title')}
                onChange={(value) => onChange('title', value)}
            />
            <TextAreaField
                label="Search description"
                rows={2}
                max={LIMITS.seoDescription}
                value={description}
                error={error('description')}
                onChange={(value) => onChange('description', value)}
            />
            {withImage && (
                <ImageField
                    label="Share image"
                    recommended={IMAGE_SIZES.shareImage}
                    value={image}
                    error={error('image.path')}
                    altError={error('image.alt')}
                    onChange={(value) => onChange('image', value)}
                />
            )}
        </FormSection>
    );
}
