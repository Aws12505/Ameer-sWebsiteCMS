/* eslint-disable ts-patterns/no-magic-numbers -- this file is the named-constant table; every number is named by its key. */

/**
 * Character limits and image sizes from the requirements (sections 3.6 and
 * 4 to 9). The server enforces the same limits; these drive the counters.
 */
export const LIMITS = {
    alt: 125,
    key: 40,
    seoTitle: 60,
    seoDescription: 160,
    site: {
        name: 60,
        description: 160,
        serviceArea: 40,
        phone: 20,
        email: 254,
        ctaLabel: 30,
        footerBlurb: 110,
        ctaBandHeading: 50,
        ctaBandText: 100,
        notificationEmails: 5,
    },
    hero: {
        headline: 40,
        subline: 120,
        buttonLabel: 30,
        maxSlides: 8,
    },
    home: {
        label: 24,
        buttonLabel: 30,
        whyHeading: 40,
        whyIntro: 70,
        pointTitle: 32,
        pointText: 90,
        aboutHeading: 70,
        aboutParagraph: 300,
        pillarTitle: 16,
        pillarText: 70,
        servicesHeading: 60,
        servicesIntro: 120,
        projectsHeading: 40,
        projectsIntro: 180,
        processHeading: 50,
        processIntro: 140,
        stepTitle: 32,
        stepText: 100,
        reviewsHeading: 50,
    },
    pageText: {
        title: 40,
        intro: 160,
    },
    contact: {
        title: 40,
        intro: 120,
        formHeading: 40,
        formIntro: 120,
        successHeading: 60,
        successText: 200,
        directHeading: 40,
        nextHeading: 40,
        stepTitle: 32,
        stepText: 70,
    },
    coreService: {
        title: 40,
        filterLabel: 20,
        summary: 160,
        paragraph: 400,
        minParagraphs: 2,
        maxParagraphs: 3,
        maxGallery: 8,
        externalUrl: 200,
    },
    service: {
        title: 40,
        description: 200,
        include: 60,
        minIncludes: 2,
        maxIncludes: 4,
    },
    projectCategory: {
        label: 20,
    },
    project: {
        title: 40,
        location: 40,
        description: 150,
        maxGallery: 19,
    },
    review: {
        name: 40,
        project: 60,
        quote: 200,
        maxRating: 5,
    },
    contactRequest: {
        notes: 5000,
    },
} as const;

/**
 * Recommended minimum image sizes (requirements 3.6). Smaller photos get a
 * warning, never a block.
 */
export const IMAGE_SIZES = {
    heroSlide: { width: 2400, height: 1350 },
    coreService: { width: 2000, height: 1400 },
    coreServiceGallery: { width: 1600, height: 0 },
    service: { width: 1600, height: 1000 },
    projectCover: { width: 1600, height: 1200 },
    projectGallery: { width: 1600, height: 1200 },
    about: { width: 1200, height: 1500 },
    reviewPhoto: { width: 232, height: 232 },
    shareImage: { width: 1200, height: 630 },
    favicon: { width: 512, height: 512 },
} as const;

export type ImageSize = {
    readonly width: number;
    readonly height: number;
};
