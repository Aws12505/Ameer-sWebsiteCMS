import { Head, setLayoutProps } from '@inertiajs/react';
import type { ReactNode } from 'react';
import { SelectField, TextAreaField, TextField } from '@/components/cms/field';
import { ImageField } from '@/components/cms/image-field';
import { KeyField } from '@/components/cms/key-field';
import {
    FormSection,
    PageBody,
    PageHeader,
    SaveBar,
} from '@/components/cms/layout';
import { PublishSection } from '@/components/cms/record-form';
import { useCmsForm } from '@/hooks/use-cms-form';
import { slugify } from '@/lib/cms';
import { IMAGE_SIZES, LIMITS } from '@/lib/limits';
import reviews from '@/routes/admin/reviews';
import type { BreadcrumbItem, MediaField, RecordMeta } from '@/types';

type ReviewForm = {
    slug: string;
    name: string;
    project: string;
    rating: number;
    quote: string;
    photo: MediaField | null;
    published: boolean;
};

type Props = {
    readonly item: RecordMeta | null;
    readonly defaults: ReviewForm;
};

const RATING_OPTIONS = Array.from(
    { length: LIMITS.review.maxRating },
    (_, index) => {
        const stars = LIMITS.review.maxRating - index;

        return {
            value: String(stars),
            label: `${'★'.repeat(stars)} (${stars})`,
        };
    },
);

export default function ReviewFormPage({ item, defaults }: Props): ReactNode {
    const form = useCmsForm<ReviewForm>(defaults);
    const title = item ? defaults.name : 'New review';

    setLayoutProps<{ breadcrumbs: BreadcrumbItem[] }>({
        breadcrumbs: [
            { title: 'Reviews', href: reviews.index() },
            { title, href: item ? reviews.edit(item.id) : reviews.create() },
        ],
    });

    form.transform((data) => ({
        ...data,
        photo: data.photo ? { path: data.photo.path } : null,
    }));

    return (
        <>
            <Head title={title} />
            <form
                onSubmit={(event) => {
                    event.preventDefault();
                    form.submit(
                        item ? reviews.update(item.id) : reviews.store(),
                        { preserveScroll: true },
                    );
                }}
            >
                <PageBody>
                    <PageHeader
                        title={title}
                        description="Only real reviews, with the customer's permission to show them."
                        lastEdited={item?.lastEdited}
                    />
                    <FormSection title="Review">
                        <TextField
                            label="Customer name"
                            required
                            max={LIMITS.review.name}
                            value={form.data.name}
                            error={form.error('name')}
                            hint="Their initials are shown when there is no photo."
                            onChange={(value) => {
                                form.set('name', value);

                                if (!item) {
                                    form.set('slug', slugify(value));
                                }
                            }}
                        />
                        <KeyField
                            value={form.data.slug}
                            locked={Boolean(item?.keyLocked)}
                            error={form.error('slug')}
                            usage=""
                            onChange={(value) => form.set('slug', value)}
                        />
                        <TextField
                            label="Project line"
                            required
                            max={LIMITS.review.project}
                            placeholder="Bathroom remodel, Lewis Center, OH"
                            value={form.data.project}
                            error={form.error('project')}
                            onChange={(value) => form.set('project', value)}
                        />
                        <SelectField
                            label="Rating"
                            required
                            options={RATING_OPTIONS}
                            value={String(form.data.rating)}
                            error={form.error('rating')}
                            onChange={(value) =>
                                form.set('rating', Number(value))
                            }
                        />
                        <TextAreaField
                            label="Quote"
                            required
                            max={LIMITS.review.quote}
                            value={form.data.quote}
                            error={form.error('quote')}
                            hint="Shown in three lines or fewer."
                            onChange={(value) => form.set('quote', value)}
                        />
                        <ImageField
                            label="Photo (optional)"
                            withAlt={false}
                            recommended={IMAGE_SIZES.reviewPhoto}
                            hint="Square, face in the centre."
                            value={form.data.photo}
                            error={form.errorUnder('photo')}
                            onChange={(value) => form.set('photo', value)}
                        />
                    </FormSection>
                    <PublishSection
                        itemName="review"
                        published={form.data.published}
                        destroy={item ? reviews.destroy(item.id) : null}
                        onChange={(checked) => form.set('published', checked)}
                    />
                    <SaveBar
                        processing={form.processing}
                        isDirty={form.isDirty}
                        label={item ? 'Save changes' : 'Create review'}
                    />
                </PageBody>
            </form>
        </>
    );
}
