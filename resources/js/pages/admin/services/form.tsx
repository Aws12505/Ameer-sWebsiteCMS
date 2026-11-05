import { Head, setLayoutProps } from '@inertiajs/react';
import type { ReactNode } from 'react';
import { ChoiceListField } from '@/components/cms/choice-list-field';
import { TextAreaField, TextField } from '@/components/cms/field';
import { ImageField } from '@/components/cms/image-field';
import { KeyField } from '@/components/cms/key-field';
import {
    FormSection,
    PageBody,
    PageHeader,
    SaveBar,
} from '@/components/cms/layout';
import { PublishSection } from '@/components/cms/record-form';
import { StringListField } from '@/components/cms/string-list-field';
import { useCmsForm } from '@/hooks/use-cms-form';
import { slugify } from '@/lib/cms';
import { IMAGE_SIZES, LIMITS } from '@/lib/limits';
import services from '@/routes/admin/services';
import type { BreadcrumbItem, MediaField, RecordMeta } from '@/types';

type ServiceForm = {
    slug: string;
    title: string;
    description: string;
    category_ids: number[];
    includes: string[];
    image: MediaField | null;
    published: boolean;
};

type Props = {
    readonly item: RecordMeta | null;
    readonly defaults: ServiceForm;
    readonly categories: readonly {
        readonly id: number;
        readonly title: string;
    }[];
};

export default function ServiceFormPage({
    item,
    defaults,
    categories,
}: Props): ReactNode {
    const form = useCmsForm<ServiceForm>(defaults);
    const title = item ? defaults.title : 'New service';

    setLayoutProps<{ breadcrumbs: BreadcrumbItem[] }>({
        breadcrumbs: [
            { title: 'Services', href: services.index() },
            { title, href: item ? services.edit(item.id) : services.create() },
        ],
    });

    return (
        <>
            <Head title={title} />
            <form
                onSubmit={(event) => {
                    event.preventDefault();
                    form.submit(
                        item ? services.update(item.id) : services.store(),
                        { preserveScroll: true },
                    );
                }}
            >
                <PageBody>
                    <PageHeader title={title} lastEdited={item?.lastEdited} />

                    <FormSection title="Service">
                        <TextField
                            label="Title"
                            required
                            max={LIMITS.service.title}
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
                            usage={`It is the link anchor, for example /services/residential#${form.data.slug || 'key'}.`}
                            onChange={(value) => form.set('slug', value)}
                        />
                        <TextAreaField
                            label="Description"
                            required
                            max={LIMITS.service.description}
                            value={form.data.description}
                            error={form.error('description')}
                            onChange={(value) => form.set('description', value)}
                        />
                        <ChoiceListField
                            label="Core services"
                            hint="Choose at least one. The service appears on each chosen core service's page."
                            choices={categories.map((category) => ({
                                id: category.id,
                                label: category.title,
                            }))}
                            value={form.data.category_ids}
                            error={form.errorUnder('category_ids')}
                            onChange={(value) =>
                                form.set('category_ids', value)
                            }
                        />
                        <StringListField
                            label="What's included"
                            itemLabel="Item"
                            hint="Three is normal."
                            min={LIMITS.service.minIncludes}
                            max={LIMITS.service.maxIncludes}
                            maxLength={LIMITS.service.include}
                            value={form.data.includes}
                            error={form.error('includes')}
                            itemError={(index) =>
                                form.error(`includes.${index}`)
                            }
                            onChange={(value) => form.set('includes', value)}
                        />
                        <ImageField
                            label="Photo"
                            required
                            recommended={IMAGE_SIZES.service}
                            value={form.data.image}
                            error={
                                form.error('image') ?? form.error('image.path')
                            }
                            altError={form.error('image.alt')}
                            onChange={(value) => form.set('image', value)}
                        />
                    </FormSection>

                    <PublishSection
                        itemName="service"
                        published={form.data.published}
                        destroy={item ? services.destroy(item.id) : null}
                        onChange={(checked) => form.set('published', checked)}
                    />

                    <SaveBar
                        processing={form.processing}
                        isDirty={form.isDirty}
                        label={item ? 'Save changes' : 'Create service'}
                    />
                </PageBody>
            </form>
        </>
    );
}
