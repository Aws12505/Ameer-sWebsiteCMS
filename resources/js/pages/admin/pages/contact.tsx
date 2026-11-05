import { Head } from '@inertiajs/react';
import type { ReactNode } from 'react';
import { TextAreaField, TextField } from '@/components/cms/field';
import { IconItemsField } from '@/components/cms/icon-items-field';
import {
    FormSection,
    PageBody,
    PageHeader,
    SaveBar,
} from '@/components/cms/layout';
import { SeoFields } from '@/components/cms/seo-fields';
import { useCmsForm } from '@/hooks/use-cms-form';
import { LIMITS } from '@/lib/limits';
import contactPage from '@/routes/admin/pages/contact';
import type { IconItem, LastEdited } from '@/types';

type ContactPage = {
    title: string;
    intro: string;
    form: { heading: string; intro: string };
    success: { heading: string; text: string };
    direct: { heading: string };
    next: { heading: string; steps: IconItem[] };
    seo: { title: string | null; description: string | null };
};

type Props = {
    readonly page: ContactPage;
    readonly lastEdited: LastEdited;
};

export default function ContactPageEditor({
    page,
    lastEdited,
}: Props): ReactNode {
    const form = useCmsForm<ContactPage>(page);

    const field = (
        path: string,
        label: string,
        max: number,
        long = false,
    ): ReactNode => {
        const Field = long ? TextAreaField : TextField;

        return (
            <Field
                label={label}
                required
                max={max}
                value={form.value<string>(path)}
                error={form.error(path)}
                onChange={(value) => form.set(path, value)}
            />
        );
    };

    return (
        <>
            <Head title="Contact page" />
            <form
                onSubmit={(event) => {
                    event.preventDefault();
                    form.submit(contactPage.update(), { preserveScroll: true });
                }}
            >
                <PageBody>
                    <PageHeader
                        title="Contact page"
                        description="The phone, email and service area come from Site settings. The form field labels are fixed."
                        lastEdited={lastEdited}
                    />
                    <FormSection title="Top of the page">
                        {field('title', 'Title', LIMITS.contact.title)}
                        {field('intro', 'Intro', LIMITS.contact.intro, true)}
                    </FormSection>
                    <FormSection title="Request form">
                        {field(
                            'form.heading',
                            'Form heading',
                            LIMITS.contact.formHeading,
                        )}
                        {field(
                            'form.intro',
                            'Form intro',
                            LIMITS.contact.formIntro,
                            true,
                        )}
                        {field(
                            'success.heading',
                            'Thank-you heading',
                            LIMITS.contact.successHeading,
                        )}
                        {field(
                            'success.text',
                            'Thank-you text',
                            LIMITS.contact.successText,
                            true,
                        )}
                    </FormSection>
                    <FormSection title="Contact details and next steps">
                        {field(
                            'direct.heading',
                            '“Reach us directly” heading',
                            LIMITS.contact.directHeading,
                        )}
                        {field(
                            'next.heading',
                            '“What happens next” heading',
                            LIMITS.contact.nextHeading,
                        )}
                        <IconItemsField
                            label="Next steps (exactly 3)"
                            itemLabel="Step"
                            basePath="next.steps"
                            hint="Read as one sentence: the bold title, then the text. For example: “We read your request and get back to you to talk it through.”"
                            titleMax={LIMITS.contact.stepTitle}
                            textMax={LIMITS.contact.stepText}
                            value={form.data.next.steps}
                            error={form.error}
                            onChange={(value) => form.set('next.steps', value)}
                        />
                    </FormSection>
                    <SeoFields
                        defaults="The title shows as “Search title | site name” in the browser tab and search results."
                        title={form.data.seo.title}
                        description={form.data.seo.description}
                        error={(name) => form.error(`seo.${name}`)}
                        onChange={(name, value) =>
                            form.set(`seo.${name}`, value)
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

ContactPageEditor.layout = {
    breadcrumbs: [{ title: 'Contact page', href: contactPage.edit() }],
};
