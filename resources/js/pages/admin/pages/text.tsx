import { Head, setLayoutProps } from '@inertiajs/react';
import type { ReactNode } from 'react';
import { TextAreaField, TextField } from '@/components/cms/field';
import {
    FormSection,
    PageBody,
    PageHeader,
    SaveBar,
} from '@/components/cms/layout';
import { SeoFields } from '@/components/cms/seo-fields';
import { useCmsForm } from '@/hooks/use-cms-form';
import { LIMITS } from '@/lib/limits';
import pageText from '@/routes/admin/pages/text';
import type { BreadcrumbItem, LastEdited } from '@/types';

type PageText = {
    title: string;
    intro: string;
    seo: { title: string | null; description: string | null };
};

type Props = {
    readonly pageKey: 'services' | 'projects';
    readonly title: string;
    readonly page: PageText;
    readonly lastEdited: LastEdited;
};

/**
 * The Services and Projects page texts.
 */
export default function PageTextEditor({
    pageKey,
    title,
    page,
    lastEdited,
}: Props): ReactNode {
    const form = useCmsForm<PageText>(page);

    setLayoutProps<{ breadcrumbs: BreadcrumbItem[] }>({
        breadcrumbs: [{ title, href: pageText.edit(pageKey) }],
    });

    return (
        <>
            <Head title={title} />
            <form
                onSubmit={(event) => {
                    event.preventDefault();
                    form.submit(pageText.update(pageKey), {
                        preserveScroll: true,
                    });
                }}
            >
                <PageBody>
                    <PageHeader
                        title={title}
                        description="The heading and intro at the top of the page. The list below it comes from the Content screens."
                        lastEdited={lastEdited}
                    />
                    <FormSection title="Top of the page">
                        <TextField
                            label="Title"
                            required
                            max={LIMITS.pageText.title}
                            value={form.data.title}
                            error={form.error('title')}
                            onChange={(value) => form.set('title', value)}
                        />
                        <TextAreaField
                            label="Intro"
                            required
                            rows={2}
                            max={LIMITS.pageText.intro}
                            value={form.data.intro}
                            error={form.error('intro')}
                            onChange={(value) => form.set('intro', value)}
                        />
                    </FormSection>
                    <SeoFields
                        defaults="The title shows as “Search title | site name” in the browser tab and search results."
                        title={form.data.seo.title}
                        description={form.data.seo.description}
                        error={(field) => form.error(`seo.${field}`)}
                        onChange={(field, value) =>
                            form.set(`seo.${field}`, value)
                        }
                    />
                    <SaveBar
                        processing={form.processing}
                        isDirty={form.isDirty}
                    />
                </PageBody>
            </form>
        </>
    );
}
