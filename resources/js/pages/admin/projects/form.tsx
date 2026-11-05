import { Head, setLayoutProps } from '@inertiajs/react';
import type { ReactNode } from 'react';
import { ChoiceListField } from '@/components/cms/choice-list-field';
import {
    CheckboxField,
    SelectField,
    TextAreaField,
    TextField,
} from '@/components/cms/field';
import { GalleryField } from '@/components/cms/gallery-field';
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
import projects from '@/routes/admin/projects';
import type { BreadcrumbItem, MediaField, RecordMeta } from '@/types';

type ProjectForm = {
    slug: string;
    title: string;
    location: string;
    description: string;
    main_category_id: number | null;
    extra_category_ids: number[];
    featured: boolean;
    cover: MediaField | null;
    gallery: MediaField[];
    published: boolean;
};

type Props = {
    readonly item: RecordMeta | null;
    readonly defaults: ProjectForm;
    readonly categories: readonly {
        readonly id: number;
        readonly label: string;
    }[];
};

export default function ProjectFormPage({
    item,
    defaults,
    categories,
}: Props): ReactNode {
    const form = useCmsForm<ProjectForm>(defaults);
    const title = item ? defaults.title : 'New project';
    const mainId = form.data.main_category_id;

    setLayoutProps<{ breadcrumbs: BreadcrumbItem[] }>({
        breadcrumbs: [
            { title: 'Projects', href: projects.index() },
            { title, href: item ? projects.edit(item.id) : projects.create() },
        ],
    });

    return (
        <>
            <Head title={title} />
            <form
                onSubmit={(event) => {
                    event.preventDefault();
                    form.submit(
                        item ? projects.update(item.id) : projects.store(),
                        { preserveScroll: true },
                    );
                }}
            >
                <PageBody>
                    <PageHeader title={title} lastEdited={item?.lastEdited} />

                    <FormSection title="Project">
                        <TextField
                            label="Title"
                            required
                            max={LIMITS.project.title}
                            value={form.data.title}
                            error={form.error('title')}
                            onChange={(value) => {
                                form.set('title', value);

                                if (!item) {
                                    form.set('slug', slugify(value));
                                }
                            }}
                        />
                        <KeyField
                            value={form.data.slug}
                            locked={Boolean(item?.keyLocked)}
                            error={form.error('slug')}
                            usage="It identifies the project on the website."
                            onChange={(value) => form.set('slug', value)}
                        />
                        <TextField
                            label="Location"
                            required
                            max={LIMITS.project.location}
                            placeholder="Columbus, OH"
                            value={form.data.location}
                            error={form.error('location')}
                            hint="Write it as “City, ST”."
                            onChange={(value) => form.set('location', value)}
                        />
                        <TextAreaField
                            label="Description"
                            required
                            max={LIMITS.project.description}
                            value={form.data.description}
                            error={form.error('description')}
                            hint="Shown under the card and in the gallery pop-up."
                            onChange={(value) => form.set('description', value)}
                        />
                    </FormSection>

                    <FormSection title="Categories">
                        <SelectField
                            label="Main category"
                            required
                            hint="Shown on the card."
                            options={categories.map((category) => ({
                                value: String(category.id),
                                label: category.label,
                            }))}
                            value={mainId ? String(mainId) : null}
                            error={form.error('main_category_id')}
                            onChange={(value) => {
                                const id = Number(value);
                                form.set('main_category_id', id);
                                form.set(
                                    'extra_category_ids',
                                    form.data.extra_category_ids.filter(
                                        (extra) => extra !== id,
                                    ),
                                );
                            }}
                        />
                        <ChoiceListField
                            label="Also list under (optional)"
                            choices={categories.map((category) => ({
                                id: category.id,
                                label: category.label,
                            }))}
                            disabledIds={mainId ? [mainId] : []}
                            value={form.data.extra_category_ids}
                            error={form.errorUnder('extra_category_ids')}
                            onChange={(value) =>
                                form.set('extra_category_ids', value)
                            }
                        />
                    </FormSection>

                    <FormSection
                        title="Photos"
                        description="The cover is always the first photo in the gallery pop-up, so upload it only once."
                    >
                        <ImageField
                            label="Cover photo"
                            required
                            recommended={IMAGE_SIZES.projectCover}
                            value={form.data.cover}
                            error={
                                form.error('cover') ?? form.error('cover.path')
                            }
                            altError={form.error('cover.alt')}
                            onChange={(value) => form.set('cover', value)}
                        />
                        <GalleryField
                            label="More gallery photos"
                            max={LIMITS.project.maxGallery}
                            recommended={IMAGE_SIZES.projectGallery}
                            hint="Shown after the cover, in this order. Landscape works best."
                            value={form.data.gallery}
                            error={form.error('gallery')}
                            itemError={(index, field) =>
                                form.error(`gallery.${index}.${field}`)
                            }
                            onChange={(value) => form.set('gallery', value)}
                        />
                    </FormSection>

                    <FormSection title="Home page">
                        <CheckboxField
                            label="Featured on the home page"
                            checked={form.data.featured}
                            error={form.error('featured')}
                            description="At most 3 projects can be featured. They show in this list's order."
                            onChange={(checked) =>
                                form.set('featured', checked)
                            }
                        />
                    </FormSection>

                    <PublishSection
                        itemName="project"
                        published={form.data.published}
                        destroy={item ? projects.destroy(item.id) : null}
                        onChange={(checked) => form.set('published', checked)}
                    />

                    <SaveBar
                        processing={form.processing}
                        isDirty={form.isDirty}
                        label={item ? 'Save changes' : 'Create project'}
                    />
                </PageBody>
            </form>
        </>
    );
}
