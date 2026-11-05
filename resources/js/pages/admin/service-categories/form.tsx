import { Head, setLayoutProps } from '@inertiajs/react';
import type { ReactNode } from 'react';
import {
    CheckboxField,
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
import { StringListField } from '@/components/cms/string-list-field';
import { useCmsForm } from '@/hooks/use-cms-form';
import { slugify } from '@/lib/cms';
import { IMAGE_SIZES, LIMITS } from '@/lib/limits';
import serviceCategories from '@/routes/admin/service-categories';
import type { BreadcrumbItem, MediaField, RecordMeta } from '@/types';

type CoreServiceForm = {
    slug: string;
    title: string;
    filter_label: string;
    summary: string;
    image: MediaField | null;
    body: string[];
    gallery: MediaField[];
    external_url: string;
    show_on_home: boolean;
    is_pinned: boolean;
    seo_title: string;
    seo_description: string;
    seo_image: MediaField | null;
    published: boolean;
};

type Props = {
    readonly item: (RecordMeta & { readonly servicesCount: number }) | null;
    readonly defaults: CoreServiceForm;
};

export default function CoreServiceForm({ item, defaults }: Props): ReactNode {
    const form = useCmsForm<CoreServiceForm>(defaults);
    const isExternal = Boolean(form.data.external_url.trim());
    const title = item ? defaults.title : 'New core service';

    setLayoutProps<{ breadcrumbs: BreadcrumbItem[] }>({
        breadcrumbs: [
            { title: 'Core services', href: serviceCategories.index() },
            {
                title,
                href: item
                    ? serviceCategories.edit(item.id)
                    : serviceCategories.create(),
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
                            ? serviceCategories.update(item.id)
                            : serviceCategories.store(),
                        { preserveScroll: true },
                    );
                }}
            >
                <PageBody>
                    <PageHeader
                        title={title}
                        description="One record feeds the home card, the footer link, the Services filter chip, its own page and the contact form's project types."
                        lastEdited={item?.lastEdited}
                    />

                    <FormSection title="Card and filter">
                        <TextField
                            label="Title"
                            required
                            max={LIMITS.coreService.title}
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
                            usage={`It is the page address: /services/${form.data.slug || 'key'}.`}
                            onChange={(value) => form.set('slug', value)}
                        />
                        <TextField
                            label="Filter chip name"
                            required
                            max={LIMITS.coreService.filterLabel}
                            value={form.data.filter_label}
                            error={form.error('filter_label')}
                            hint="The short name on the Services page filter, for example “Residential”."
                            onChange={(value) =>
                                form.set('filter_label', value)
                            }
                        />
                        <TextAreaField
                            label="Summary"
                            required
                            rows={2}
                            max={LIMITS.coreService.summary}
                            value={form.data.summary}
                            error={form.error('summary')}
                            hint="Shown on the card, the strip under the filter, the top of the page and as the default search description."
                            onChange={(value) => form.set('summary', value)}
                        />
                        <ImageField
                            label="Photo"
                            required
                            recommended={IMAGE_SIZES.coreService}
                            hint="Landscape, subject in the centre. Used on the card and as the full-width photo at the top of the page."
                            value={form.data.image}
                            error={
                                form.error('image') ?? form.error('image.path')
                            }
                            altError={form.error('image.alt')}
                            onChange={(value) => form.set('image', value)}
                        />
                    </FormSection>

                    <FormSection title="Where it shows">
                        <CheckboxField
                            label="Show on the home page"
                            checked={form.data.show_on_home}
                            error={form.error('show_on_home')}
                            description="At most 4 core services can show as cards in the home page's Services section, in this list's order."
                            onChange={(checked) =>
                                form.set('show_on_home', checked)
                            }
                        />
                        <CheckboxField
                            label="Pin to the top"
                            checked={form.data.is_pinned}
                            description="Keeps this service first everywhere (used for New Construction)."
                            onChange={(checked) =>
                                form.set('is_pinned', checked)
                            }
                        />
                        <TextField
                            label="External link (optional)"
                            type="url"
                            max={LIMITS.coreService.externalUrl}
                            placeholder="https://"
                            value={form.data.external_url}
                            error={form.error('external_url')}
                            hint="Fill in only when another company handles this service (for example https://pnehomes.com/). Visitors will leave this website: the card and footer link open it in a new tab, and this service has no page of its own."
                            onChange={(value) =>
                                form.set('external_url', value)
                            }
                        />
                    </FormSection>

                    {!isExternal && (
                        <FormSection
                            title="Service page"
                            description="Shown under “How we approach …”. The services list, phone number and “Explore other services” are added automatically."
                        >
                            <StringListField
                                label="Paragraphs"
                                itemLabel="Paragraph"
                                multiline
                                hint="The first paragraph is shown larger."
                                min={LIMITS.coreService.minParagraphs}
                                max={LIMITS.coreService.maxParagraphs}
                                maxLength={LIMITS.coreService.paragraph}
                                value={form.data.body}
                                error={form.error('body')}
                                itemError={(index) =>
                                    form.error(`body.${index}`)
                                }
                                onChange={(value) => form.set('body', value)}
                            />
                            <GalleryField
                                label="Gallery (optional)"
                                max={LIMITS.coreService.maxGallery}
                                recommended={IMAGE_SIZES.coreServiceGallery}
                                hint="Four photos fill the layout exactly. Hidden when empty."
                                value={form.data.gallery}
                                error={form.error('gallery')}
                                itemError={(index, field) =>
                                    form.error(`gallery.${index}.${field}`)
                                }
                                onChange={(value) => form.set('gallery', value)}
                            />
                        </FormSection>
                    )}

                    {!isExternal && (
                        <FormSection
                            title="Search and sharing (optional)"
                            description="Empty values use the title, summary and photo."
                        >
                            <TextField
                                label="Search title"
                                max={LIMITS.seoTitle}
                                value={form.data.seo_title}
                                error={form.error('seo_title')}
                                onChange={(value) =>
                                    form.set('seo_title', value)
                                }
                            />
                            <TextAreaField
                                label="Search description"
                                rows={2}
                                max={LIMITS.seoDescription}
                                value={form.data.seo_description}
                                error={form.error('seo_description')}
                                onChange={(value) =>
                                    form.set('seo_description', value)
                                }
                            />
                            <ImageField
                                label="Share image"
                                recommended={IMAGE_SIZES.shareImage}
                                value={form.data.seo_image}
                                error={form.error('seo_image.path')}
                                altError={form.error('seo_image.alt')}
                                onChange={(value) =>
                                    form.set('seo_image', value)
                                }
                            />
                        </FormSection>
                    )}

                    <PublishSection
                        itemName="core service"
                        published={form.data.published}
                        description="Off: the card, footer link, filter chip, page and contact form option all disappear."
                        destroy={
                            item ? serviceCategories.destroy(item.id) : null
                        }
                        onChange={(checked) => form.set('published', checked)}
                    />

                    <SaveBar
                        processing={form.processing}
                        isDirty={form.isDirty}
                        label={item ? 'Save changes' : 'Create core service'}
                    />
                </PageBody>
            </form>
        </>
    );
}
