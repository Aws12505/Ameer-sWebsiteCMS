import { Head, usePage } from '@inertiajs/react';
import type { ReactNode } from 'react';
import { TextAreaField, TextField } from '@/components/cms/field';
import { ImageField } from '@/components/cms/image-field';
import {
    FormSection,
    PageBody,
    PageHeader,
    SaveBar,
} from '@/components/cms/layout';
import { StringListField } from '@/components/cms/string-list-field';
import { useCmsForm } from '@/hooks/use-cms-form';
import { IMAGE_SIZES, LIMITS } from '@/lib/limits';
import siteSettings from '@/routes/admin/site-settings';
import type { LastEdited, MediaField } from '@/types';

type SiteSettings = {
    name: string;
    description: string;
    favicon: MediaField | null;
    serviceArea: string;
    phone: { display: string };
    email: string;
    cta: { label: string };
    footerBlurb: string;
    ctaBand: { heading: string; text: string };
    notificationEmails: string[] | null;
    defaultShareImage: MediaField | null;
};

type Props = {
    readonly settings: SiteSettings;
    readonly lastEdited: LastEdited;
};

export default function SiteSettingsPage({
    settings,
    lastEdited,
}: Props): ReactNode {
    const { auth } = usePage().props;
    const form = useCmsForm<SiteSettings>(settings);

    form.transform((data) => ({
        ...data,
        favicon: data.favicon ? { path: data.favicon.path } : null,
        ...(auth.isAdmin ? {} : { notificationEmails: undefined }),
    }));

    return (
        <>
            <Head title="Site settings" />
            <form
                onSubmit={(event) => {
                    event.preventDefault();
                    form.submit(siteSettings.update(), {
                        preserveScroll: true,
                    });
                }}
            >
                <PageBody>
                    <PageHeader
                        title="Site settings"
                        description="The details used on every page: browser tab, header, footer, the red call-to-action band and the contact details."
                        lastEdited={lastEdited}
                    />

                    <FormSection
                        title="Site name and search"
                        description="Every page title ends with “| site name”. The description is shown in search results when a page has none of its own."
                    >
                        <TextField
                            label="Site name"
                            required
                            max={LIMITS.site.name}
                            value={form.data.name}
                            error={form.error('name')}
                            onChange={(value) => form.set('name', value)}
                        />
                        <TextAreaField
                            label="Site description"
                            required
                            rows={2}
                            max={LIMITS.site.description}
                            value={form.data.description}
                            error={form.error('description')}
                            onChange={(value) => form.set('description', value)}
                        />
                        <TextField
                            label="Service area"
                            required
                            max={LIMITS.site.serviceArea}
                            value={form.data.serviceArea}
                            error={form.error('serviceArea')}
                            hint="Used in page titles, the footer and the Contact page."
                            onChange={(value) => form.set('serviceArea', value)}
                        />
                        <ImageField
                            label="Browser tab icon (favicon)"
                            kind="favicon"
                            required
                            withAlt={false}
                            recommended={IMAGE_SIZES.favicon}
                            hint="One square PNG. Keep the artwork simple and centred; it is shown as small as 16 px. A transparent background is fine."
                            value={form.data.favicon}
                            error={form.errorUnder('favicon')}
                            onChange={(value) => form.set('favicon', value)}
                            preview={(value) => (
                                <div className="flex items-center gap-3 text-xs text-muted-foreground">
                                    <span className="flex items-center gap-2 rounded border bg-background px-2 py-1">
                                        <img
                                            src={value.url}
                                            alt=""
                                            className="size-4"
                                        />
                                        <span className="text-foreground">
                                            {form.data.name}
                                        </span>
                                    </span>
                                    Preview at 16 px, as in a browser tab
                                </div>
                            )}
                        />
                        <ImageField
                            label="Default share image (optional)"
                            recommended={IMAGE_SIZES.shareImage}
                            hint="Shown when a page is shared on social media and has no image of its own."
                            value={form.data.defaultShareImage}
                            error={form.error('defaultShareImage.path')}
                            altError={form.error('defaultShareImage.alt')}
                            onChange={(value) =>
                                form.set('defaultShareImage', value)
                            }
                        />
                    </FormSection>

                    <FormSection
                        title="Contact details"
                        description="Change the phone number once and it updates in the header, footer, CTA band, Contact page and service pages."
                    >
                        <TextField
                            label="Phone number"
                            type="tel"
                            required
                            max={LIMITS.site.phone}
                            value={form.data.phone.display}
                            error={form.error('phone.display')}
                            hint="Type it the way it should look, for example (614) 483-0331. The tap-to-call link is built for you."
                            onChange={(value) =>
                                form.set('phone.display', value)
                            }
                        />
                        <TextField
                            label="Email"
                            type="email"
                            required
                            max={LIMITS.site.email}
                            value={form.data.email}
                            error={form.error('email')}
                            onChange={(value) => form.set('email', value)}
                        />
                    </FormSection>

                    <FormSection title="Main button, footer and CTA band">
                        <TextField
                            label="Main button label"
                            required
                            max={LIMITS.site.ctaLabel}
                            value={form.data.cta.label}
                            error={form.error('cta.label')}
                            hint="The red button in the header, hero, service pages and CTA band. It always opens the Contact page."
                            onChange={(value) => form.set('cta.label', value)}
                        />
                        <TextField
                            label="Footer line"
                            required
                            max={LIMITS.site.footerBlurb}
                            value={form.data.footerBlurb}
                            error={form.error('footerBlurb')}
                            hint="One line under the logo in the footer."
                            onChange={(value) => form.set('footerBlurb', value)}
                        />
                        <TextField
                            label="CTA band heading"
                            required
                            max={LIMITS.site.ctaBandHeading}
                            value={form.data.ctaBand.heading}
                            error={form.error('ctaBand.heading')}
                            hint="The red band before the footer on Home, Services, service pages and Projects."
                            onChange={(value) =>
                                form.set('ctaBand.heading', value)
                            }
                        />
                        <TextField
                            label="CTA band line"
                            required
                            max={LIMITS.site.ctaBandText}
                            value={form.data.ctaBand.text}
                            error={form.error('ctaBand.text')}
                            onChange={(value) =>
                                form.set('ctaBand.text', value)
                            }
                        />
                    </FormSection>

                    {auth.isAdmin && form.data.notificationEmails !== null && (
                        <FormSection
                            title="Who receives contact requests"
                            description="Each request from the website is emailed to these addresses. Not shown on the website. Only an Admin can change this."
                        >
                            <StringListField
                                label="Email addresses"
                                itemLabel="Email"
                                min={0}
                                max={LIMITS.site.notificationEmails}
                                maxLength={LIMITS.site.email}
                                value={form.data.notificationEmails}
                                error={form.error('notificationEmails')}
                                itemError={(index) =>
                                    form.error(`notificationEmails.${index}`)
                                }
                                onChange={(value) =>
                                    form.set('notificationEmails', value)
                                }
                            />
                        </FormSection>
                    )}

                    <SaveBar
                        processing={form.processing}
                        isDirty={form.isDirty}
                    />
                </PageBody>
            </form>
        </>
    );
}

SiteSettingsPage.layout = {
    breadcrumbs: [{ title: 'Site settings', href: siteSettings.edit() }],
};
