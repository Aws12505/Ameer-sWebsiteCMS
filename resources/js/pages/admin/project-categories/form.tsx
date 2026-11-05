import { Head, setLayoutProps } from '@inertiajs/react';
import type { ReactNode } from 'react';
import { TextField } from '@/components/cms/field';
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
import { LIMITS } from '@/lib/limits';
import projectCategories from '@/routes/admin/project-categories';
import type { BreadcrumbItem, RecordMeta } from '@/types';

type CategoryForm = {
    slug: string;
    label: string;
    published: boolean;
};

type Props = {
    readonly item: RecordMeta | null;
    readonly defaults: CategoryForm;
};

export default function ProjectCategoryForm({
    item,
    defaults,
}: Props): ReactNode {
    const form = useCmsForm<CategoryForm>(defaults);
    const title = item ? defaults.label : 'New project category';

    setLayoutProps<{ breadcrumbs: BreadcrumbItem[] }>({
        breadcrumbs: [
            { title: 'Project categories', href: projectCategories.index() },
            {
                title,
                href: item
                    ? projectCategories.edit(item.id)
                    : projectCategories.create(),
            },
        ],
    });

    return (
        <>
            <Head title={title} />
            <form
                onSubmit={(event) => {
                    event.preventDefault();
                    form.submit(
                        item
                            ? projectCategories.update(item.id)
                            : projectCategories.store(),
                    );
                }}
            >
                <PageBody>
                    <PageHeader title={title} lastEdited={item?.lastEdited} />
                    <FormSection title="Category">
                        <TextField
                            label="Label"
                            required
                            max={LIMITS.projectCategory.label}
                            value={form.data.label}
                            error={form.error('label')}
                            onChange={(value) => {
                                form.set('label', value);

                                if (!item) {
                                    form.set('slug', slugify(value));
                                }
                            }}
                        />
                        <KeyField
                            value={form.data.slug}
                            locked={Boolean(item?.keyLocked)}
                            error={form.error('slug')}
                            usage={`It appears in the address: /projects?cat=${form.data.slug || 'key'}.`}
                            onChange={(value) => form.set('slug', value)}
                        />
                    </FormSection>
                    <PublishSection
                        itemName="category"
                        published={form.data.published}
                        destroy={
                            item ? projectCategories.destroy(item.id) : null
                        }
                        description="A category cannot be deleted while projects use it."
                        onChange={(checked) => form.set('published', checked)}
                    />
                    <SaveBar
                        processing={form.processing}
                        isDirty={form.isDirty}
                        label={item ? 'Save changes' : 'Create category'}
                    />
                </PageBody>
            </form>
        </>
    );
}
