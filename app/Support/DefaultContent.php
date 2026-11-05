<?php

namespace App\Support;

use App\Enums\HeroMode;
use App\Models\Setting;

/**
 * The starting content of the one-record screens: the text the website
 * shows today (requirements section 10), stored the way the admin saves it.
 */
class DefaultContent
{
    /**
     * The starting content for a setting key.
     *
     * @return array<string, mixed>
     */
    public static function for(string $key): array
    {
        return match ($key) {
            Setting::Site => self::site(),
            Setting::Hero => self::hero(),
            Setting::HomePage => self::homePage(),
            Setting::ServicesPage => self::servicesPage(),
            Setting::ProjectsPage => self::projectsPage(),
            Setting::ContactPage => self::contactPage(),
            default => [],
        };
    }

    /**
     * @return array<string, mixed>
     */
    public static function site(): array
    {
        return [
            'name' => "Ameer's Construction and Remodeling",
            'description' => 'Renovation, remodeling and new construction for homes and businesses across Central Ohio.',
            'favicon' => null,
            'serviceArea' => 'Central Ohio',
            'phone' => ['display' => '(614) 483-0331'],
            'email' => 'homeameer62@gmail.com',
            'cta' => ['label' => 'Book an Appointment'],
            'footerBlurb' => 'Construction and remodeling for homes and businesses across Central Ohio.',
            'ctaBand' => [
                'heading' => 'Ready to start your project?',
                'text' => 'Tell us what you are planning. We will help you take the next step.',
            ],
            'notificationEmails' => [],
            'defaultShareImage' => null,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function hero(): array
    {
        return [
            'showReviews' => false,
            'heroMode' => HeroMode::Slider->value,
            'heroSlides' => [],
            'heroVideo' => null,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function homePage(): array
    {
        return [
            'hero' => [
                'headline' => ['Built around your vision.', 'Crafted to last.'],
                'subline' => 'Renovation, remodeling and new construction for homes and businesses across Central Ohio.',
                'secondaryButtonLabel' => 'View Our Projects',
            ],
            'why' => [
                'heading' => 'Why Choose Us',
                'intro' => 'What to expect from our team on every project.',
                'points' => [
                    ['icon' => 'chat-circle-text', 'title' => 'Clear Communication', 'text' => 'Keeping clients informed throughout every stage of the project.'],
                    ['icon' => 'seal-check', 'title' => 'Quality Workmanship', 'text' => 'Attention to detail and quality execution on every job.'],
                    ['icon' => 'clipboard-text', 'title' => 'Complete Coordination', 'text' => 'Managing projects from planning and estimating through completion.'],
                    ['icon' => 'buildings', 'title' => 'Residential and Commercial', 'text' => 'Experience across different project sizes and types.'],
                ],
            ],
            'about' => [
                'label' => 'About us',
                'heading' => 'More than a contractor. Your project partner.',
                'paragraphs' => [
                    "At Ameer's Construction and Remodeling, we bring together planning, craftsmanship, and project coordination to transform spaces into places that work better, look better, and feel like home.",
                    'From renovations and remodeling to new construction and commercial projects, we work closely with our clients from the initial estimate through completion.',
                ],
                'image' => null,
                'buttonLabel' => 'Explore our services',
                'pillars' => [
                    ['icon' => 'ruler', 'title' => 'Plan', 'text' => 'A clear scope and estimate before any work begins.'],
                    ['icon' => 'hammer', 'title' => 'Build', 'text' => 'Careful craftsmanship on every detail, large or small.'],
                    ['icon' => 'clipboard-text', 'title' => 'Manage', 'text' => 'One team coordinating trades, schedules and updates.'],
                ],
            ],
            'services' => [
                'label' => 'Our services',
                'heading' => 'Services built for every stage of a project',
                'intro' => 'Choose an area of work to see what it includes.',
                'buttonLabel' => 'View all services',
            ],
            'projects' => [
                'label' => 'Our projects',
                'heading' => 'Our work',
                'intro' => 'A look at projects we have helped bring to life across Central Ohio. Open any project to browse its gallery.',
                'buttonLabel' => 'View all projects',
            ],
            'process' => [
                'label' => 'How it works',
                'heading' => 'From idea to completion',
                'intro' => 'Four clear steps, with one team keeping you informed from the first call to the final walkthrough.',
                'steps' => [
                    ['icon' => 'chat-circle-text', 'title' => 'Tell us about your project', 'text' => 'Share your vision, goals, and project details.'],
                    ['icon' => 'calculator', 'title' => 'Estimate & planning', 'text' => 'We review your project and prepare a detailed estimate.'],
                    ['icon' => 'hard-hat', 'title' => 'Build', 'text' => 'Our team coordinates the work and brings the approved plan to life.'],
                    ['icon' => 'list-checks', 'title' => 'Final walkthrough', 'text' => 'We review the completed project and make sure everything meets expectations.'],
                ],
            ],
            'reviews' => ['label' => 'Reviews', 'heading' => 'What people say about us'],
            'seo' => ['title' => null, 'description' => null, 'image' => null],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function servicesPage(): array
    {
        return [
            'title' => 'Our Services',
            'intro' => 'Residential remodeling, home additions, commercial and exterior work from one team, with new construction through our partner PNE Homes.',
            'seo' => [
                'title' => 'Services',
                'description' => 'Residential remodeling, home additions, commercial construction and exterior improvements across Central Ohio, with new construction through PNE Homes.',
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function projectsPage(): array
    {
        return [
            'title' => 'Our Projects',
            'intro' => 'Browse completed work across Central Ohio. Open any project to see its gallery.',
            'seo' => [
                'title' => 'Projects',
                'description' => 'Browse completed restaurant, café, smoke shop and exterior projects across Central Ohio.',
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function contactPage(): array
    {
        return [
            'title' => 'Contact Us',
            'intro' => 'Tell us about your project and we will follow up with the next steps.',
            'form' => ['heading' => 'Book an Appointment', 'intro' => 'Tell us a little about your project.'],
            'success' => [
                'heading' => 'Thank you, we received your request',
                'text' => 'We will contact you soon to talk through your project and the next steps. If it is urgent, please call us.',
            ],
            'direct' => ['heading' => 'Reach us directly'],
            'next' => [
                'heading' => 'What happens next',
                'steps' => [
                    ['icon' => 'chat-circle-text', 'title' => 'We read your request', 'text' => 'and get back to you to talk it through.'],
                    ['icon' => 'calendar-check', 'title' => 'We plan a visit', 'text' => 'to see the space and understand what you need.'],
                    ['icon' => 'receipt', 'title' => 'You get a clear estimate', 'text' => 'before any work begins.'],
                ],
            ],
            'seo' => [
                'title' => 'Contact Us',
                'description' => 'Book an appointment to talk about your project. Call, email or send us the details.',
            ],
        ];
    }
}
