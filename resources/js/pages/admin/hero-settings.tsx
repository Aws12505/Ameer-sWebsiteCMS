import { Head } from '@inertiajs/react';
import type { ReactNode } from 'react';
import { CheckboxField, SelectField } from '@/components/cms/field';
import { GalleryField } from '@/components/cms/gallery-field';
import { ImageField } from '@/components/cms/image-field';
import {
    FormSection,
    PageBody,
    PageHeader,
    SaveBar,
} from '@/components/cms/layout';
import { useCmsForm } from '@/hooks/use-cms-form';
import { IMAGE_SIZES, LIMITS } from '@/lib/limits';
import heroSettings from '@/routes/admin/hero-settings';
import type { LastEdited, MediaField, Option } from '@/types';

type HeroMode = 'slider' | 'image' | 'video';

type HeroSettings = {
    showReviews: boolean;
    heroMode: HeroMode;
    heroSlides: MediaField[];
    heroVideo: { video: MediaField | null; poster: MediaField | null } | null;
};

type Props = {
    readonly settings: HeroSettings;
    readonly lastEdited: LastEdited;
};

const MODE_OPTIONS: Option[] = [
    { value: 'slider', label: 'Slider: photos fade into each other' },
    { value: 'image', label: 'Single photo: the first photo, still' },
    { value: 'video', label: 'Video: looping, muted' },
];

const MODE_HINTS: Record<HeroMode, string> = {
    slider: 'Needs at least 2 photos. The photo changes every 6 seconds.',
    image: 'Uses the first photo in the list below.',
    video: 'Needs a video and a poster picture. The photos are used if the video cannot play.',
};

function isHeroMode(value: string): value is HeroMode {
    return value in MODE_HINTS;
}

export default function HeroSettingsPage({
    settings,
    lastEdited,
}: Props): ReactNode {
    const form = useCmsForm<HeroSettings>(settings);
    const video = form.data.heroVideo ?? { video: null, poster: null };

    form.transform((data) => ({
        ...data,
        heroVideo:
            data.heroVideo?.video || data.heroVideo?.poster
                ? {
                      path: data.heroVideo.video?.path ?? '',
                      poster: data.heroVideo.poster?.path ?? '',
                  }
                : null,
    }));

    return (
        <>
            <Head title="Hero & reviews switch" />
            <form
                onSubmit={(event) => {
                    event.preventDefault();
                    form.submit(heroSettings.update(), {
                        preserveScroll: true,
                    });
                }}
            >
                <PageBody>
                    <PageHeader
                        title="Hero background & reviews switch"
                        description="The full-screen background behind the first section of the home page, and whether the Reviews section is shown."
                        lastEdited={lastEdited}
                    />

                    <FormSection
                        title="Reviews section"
                        description="Turn this on once real, approved customer reviews are published. When it is off, the section and its number are hidden."
                    >
                        <CheckboxField
                            label="Show the Reviews section on the home page"
                            checked={form.data.showReviews}
                            error={form.error('showReviews')}
                            description="The section is also hidden while there are no published reviews."
                            onChange={(checked) =>
                                form.set('showReviews', checked)
                            }
                        />
                    </FormSection>

                    <FormSection
                        title="Background"
                        description="Choose the subject in the centre of each photo: the same photo is cropped differently on phones and desktops."
                    >
                        <SelectField
                            label="Background type"
                            required
                            options={MODE_OPTIONS}
                            value={form.data.heroMode}
                            error={form.error('heroMode')}
                            hint={MODE_HINTS[form.data.heroMode]}
                            onChange={(value) => {
                                if (isHeroMode(value)) {
                                    form.set('heroMode', value);
                                }
                            }}
                        />
                        <GalleryField
                            label="Photos"
                            max={LIMITS.hero.maxSlides}
                            recommended={IMAGE_SIZES.heroSlide}
                            value={form.data.heroSlides}
                            error={form.error('heroSlides')}
                            itemError={(index, field) =>
                                form.error(`heroSlides.${index}.${field}`)
                            }
                            onChange={(value) => form.set('heroSlides', value)}
                        />
                    </FormSection>

                    {form.data.heroMode === 'video' && (
                        <FormSection title="Video">
                            <ImageField
                                label="Video file"
                                kind="video"
                                withAlt={false}
                                required
                                hint="MP4 (H.264), 1280 × 720 or larger, 10 to 30 seconds, under 10 MB. No sound needed."
                                value={video.video}
                                error={
                                    form.error('heroVideo') ??
                                    form.error('heroVideo.path')
                                }
                                onChange={(value) =>
                                    form.set('heroVideo', {
                                        ...video,
                                        video: value,
                                    })
                                }
                            />
                            <ImageField
                                label="Poster picture"
                                withAlt={false}
                                required
                                hint="Shown before the video starts and when it cannot play. Same shape as the video, JPG."
                                value={video.poster}
                                error={form.error('heroVideo.poster')}
                                onChange={(value) =>
                                    form.set('heroVideo', {
                                        ...video,
                                        poster: value,
                                    })
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

HeroSettingsPage.layout = {
    breadcrumbs: [
        { title: 'Hero & reviews switch', href: heroSettings.edit() },
    ],
};
