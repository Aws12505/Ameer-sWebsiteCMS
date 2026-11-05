import { Head } from '@inertiajs/react';
import type { ReactNode } from 'react';
import { TextAreaField, TextField } from '@/components/cms/field';
import { IconItemsField } from '@/components/cms/icon-items-field';
import { ImageField } from '@/components/cms/image-field';
import {
    FormSection,
    PageBody,
    PageHeader,
    SaveBar,
} from '@/components/cms/layout';
import { SeoFields } from '@/components/cms/seo-fields';
import { StringListField } from '@/components/cms/string-list-field';
import { useCmsForm } from '@/hooks/use-cms-form';
import { IMAGE_SIZES, LIMITS } from '@/lib/limits';
import homePage from '@/routes/admin/pages/home';
import type { IconItem, LastEdited, MediaField } from '@/types';

type SectionText = {
    label: string;
    heading: string;
    intro: string;
    buttonLabel: string;
};

type HomePage = {
    hero: { headline: string[]; subline: string; secondaryButtonLabel: string };
    why: { heading: string; intro: string; points: IconItem[] };
    about: {
        label: string;
        heading: string;
        paragraphs: string[];
        image: MediaField | null;
        buttonLabel: string;
        pillars: IconItem[];
    };
    services: SectionText;
    projects: SectionText;
    process: {
        label: string;
        heading: string;
        intro: string;
        steps: IconItem[];
    };
    reviews: { label: string; heading: string };
    seo: {
        title: string | null;
        description: string | null;
        image: MediaField | null;
    };
};

type Props = {
    readonly page: HomePage;
    readonly lastEdited: LastEdited;
};

const SECTIONS = [
    { id: 'hero', label: 'Hero' },
    { id: 'why', label: '01 Why Choose Us' },
    { id: 'about', label: '02 About' },
    { id: 'services', label: '03 Services' },
    { id: 'projects', label: '04 Projects' },
    { id: 'process', label: '05 How it works' },
    { id: 'reviews', label: '06 Reviews' },
    { id: 'seo', label: 'Search' },
];

export default function HomePageEditor({ page, lastEdited }: Props): ReactNode {
    const form = useCmsForm<HomePage>(page);

    const text = (
        path: string,
        label: string,
        max: number,
        hint?: string,
    ): ReactNode => (
        <TextField
            label={label}
            required
            max={max}
            hint={hint}
            value={form.value<string>(path)}
            error={form.error(path)}
            onChange={(value) => form.set(path, value)}
        />
    );

    const longText = (path: string, label: string, max: number): ReactNode => (
        <TextAreaField
            label={label}
            required
            rows={2}
            max={max}
            value={form.value<string>(path)}
            error={form.error(path)}
            onChange={(value) => form.set(path, value)}
        />
    );

    return (
        <>
            <Head title="Home page" />
            <form
                onSubmit={(event) => {
                    event.preventDefault();
                    form.submit(homePage.update(), { preserveScroll: true });
                }}
            >
                <PageBody>
                    <PageHeader
                        title="Home page"
                        description="The text of each home page section. Photos and lists that come from elsewhere are noted in each section."
                        lastEdited={lastEdited}
                    />

                    <nav className="flex flex-wrap gap-2" aria-label="Sections">
                        {SECTIONS.map((section) => (
                            <a
                                key={section.id}
                                href={`#${section.id}`}
                                className="rounded-full border px-3 py-1 text-xs hover:bg-accent"
                            >
                                {section.label}
                            </a>
                        ))}
                    </nav>

                    <FormSection
                        id="hero"
                        title="Hero"
                        description="The first screen. The background photos or video are set in “Hero & reviews switch”; the main button label is in Site settings."
                    >
                        <TextField
                            label="Headline, line 1"
                            required
                            max={LIMITS.hero.headline}
                            value={form.data.hero.headline[0]}
                            error={
                                form.error('hero.headline.0') ??
                                form.error('hero.headline')
                            }
                            onChange={(value) =>
                                form.set('hero.headline.0', value)
                            }
                        />
                        <TextField
                            label="Headline, line 2 (optional)"
                            max={LIMITS.hero.headline}
                            value={form.data.hero.headline[1]}
                            error={form.error('hero.headline.1')}
                            hint="Each line stays whole; the line break falls between them."
                            onChange={(value) =>
                                form.set('hero.headline.1', value)
                            }
                        />
                        {longText(
                            'hero.subline',
                            'Short line',
                            LIMITS.hero.subline,
                        )}
                        {text(
                            'hero.secondaryButtonLabel',
                            'Second button label',
                            LIMITS.hero.buttonLabel,
                            'Opens the Projects page.',
                        )}
                    </FormSection>

                    <FormSection
                        id="why"
                        title="01 Why Choose Us"
                        description="The card that sits over the bottom edge of the hero."
                    >
                        {text('why.heading', 'Heading', LIMITS.home.whyHeading)}
                        {text('why.intro', 'Intro', LIMITS.home.whyIntro)}
                        <IconItemsField
                            label="Reasons (exactly 4)"
                            itemLabel="Reason"
                            basePath="why.points"
                            titleMax={LIMITS.home.pointTitle}
                            textMax={LIMITS.home.pointText}
                            value={form.data.why.points}
                            error={form.error}
                            onChange={(value) => form.set('why.points', value)}
                        />
                    </FormSection>

                    <FormSection id="about" title="02 About">
                        {text('about.label', 'Label', LIMITS.home.label)}
                        {text(
                            'about.heading',
                            'Heading',
                            LIMITS.home.aboutHeading,
                        )}
                        <StringListField
                            label="Paragraphs"
                            itemLabel="Paragraph"
                            multiline
                            min={2}
                            max={2}
                            maxLength={LIMITS.home.aboutParagraph}
                            value={form.data.about.paragraphs}
                            error={form.error('about.paragraphs')}
                            itemError={(index) =>
                                form.error(`about.paragraphs.${index}`)
                            }
                            onChange={(value) =>
                                form.set('about.paragraphs', value)
                            }
                        />
                        <ImageField
                            label="About photo"
                            required
                            recommended={IMAGE_SIZES.about}
                            hint="Portrait (tall) photo."
                            value={form.data.about.image}
                            error={
                                form.error('about.image') ??
                                form.error('about.image.path')
                            }
                            altError={form.error('about.image.alt')}
                            onChange={(value) => form.set('about.image', value)}
                        />
                        {text(
                            'about.buttonLabel',
                            'Button label',
                            LIMITS.home.buttonLabel,
                            'Opens the Services page.',
                        )}
                        <IconItemsField
                            label="Pillars (exactly 3)"
                            itemLabel="Pillar"
                            basePath="about.pillars"
                            hint="Shown when a visitor hovers the photo (always visible on touch screens)."
                            titleMax={LIMITS.home.pillarTitle}
                            textMax={LIMITS.home.pillarText}
                            value={form.data.about.pillars}
                            error={form.error}
                            onChange={(value) =>
                                form.set('about.pillars', value)
                            }
                        />
                    </FormSection>

                    <FormSection
                        id="services"
                        title="03 Services"
                        description="The cards are the core services with “Show on home page” switched on, in their order (Content → Core services)."
                    >
                        {text('services.label', 'Label', LIMITS.home.label)}
                        {text(
                            'services.heading',
                            'Heading',
                            LIMITS.home.servicesHeading,
                        )}
                        {longText(
                            'services.intro',
                            'Intro',
                            LIMITS.home.servicesIntro,
                        )}
                        {text(
                            'services.buttonLabel',
                            'Button label',
                            LIMITS.home.buttonLabel,
                            'Opens the Services page.',
                        )}
                    </FormSection>

                    <FormSection
                        id="projects"
                        title="04 Projects"
                        description="The cards are the projects marked Featured (up to 3), in their order (Content → Projects)."
                    >
                        {text('projects.label', 'Label', LIMITS.home.label)}
                        {text(
                            'projects.heading',
                            'Heading',
                            LIMITS.home.projectsHeading,
                        )}
                        {longText(
                            'projects.intro',
                            'Intro',
                            LIMITS.home.projectsIntro,
                        )}
                        {text(
                            'projects.buttonLabel',
                            'Button label',
                            LIMITS.home.buttonLabel,
                            'Opens the Projects page.',
                        )}
                    </FormSection>

                    <FormSection
                        id="process"
                        title="05 How it works"
                        description="A timeline of four steps. The step numbers are added automatically."
                    >
                        {text('process.label', 'Label', LIMITS.home.label)}
                        {text(
                            'process.heading',
                            'Heading',
                            LIMITS.home.processHeading,
                        )}
                        {longText(
                            'process.intro',
                            'Intro',
                            LIMITS.home.processIntro,
                        )}
                        <IconItemsField
                            label="Steps (exactly 4)"
                            itemLabel="Step"
                            basePath="process.steps"
                            titleMax={LIMITS.home.stepTitle}
                            textMax={LIMITS.home.stepText}
                            value={form.data.process.steps}
                            error={form.error}
                            onChange={(value) =>
                                form.set('process.steps', value)
                            }
                        />
                    </FormSection>

                    <FormSection
                        id="reviews"
                        title="06 Reviews"
                        description="The reviews come from Content → Reviews. Switch the section on or off in “Hero & reviews switch”."
                    >
                        {text('reviews.label', 'Label', LIMITS.home.label)}
                        {text(
                            'reviews.heading',
                            'Heading',
                            LIMITS.home.reviewsHeading,
                        )}
                    </FormSection>

                    <div id="seo" className="scroll-mt-20">
                        <SeoFields
                            withImage
                            defaults="Empty title means “site name | service area”. Empty image means the default share image."
                            title={form.data.seo.title}
                            description={form.data.seo.description}
                            image={form.data.seo.image}
                            error={(field) => form.error(`seo.${field}`)}
                            onChange={(field, value) =>
                                form.set(`seo.${field}`, value)
                            }
                        />
                    </div>

                    <SaveBar
                        processing={form.processing}
                        isDirty={form.isDirty}
                    />
                </PageBody>
            </form>
        </>
    );
}

HomePageEditor.layout = {
    breadcrumbs: [{ title: 'Home page', href: homePage.edit() }],
};
