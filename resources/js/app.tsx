import { createInertiaApp } from '@inertiajs/react';
import { Toaster } from '@/components/ui/sonner';
import { TooltipProvider } from '@/components/ui/tooltip';
import { initializeTheme } from '@/hooks/use-appearance';
import AppLayout from '@/layouts/app-layout';
import AuthLayout from '@/layouts/auth-layout';
import SettingsLayout from '@/layouts/settings/layout';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

/** Page-name prefixes with their own layout; every other page uses AppLayout. */
const LAYOUTS_BY_PREFIX = [
    { prefix: 'auth/', layout: AuthLayout },
    { prefix: 'settings/', layout: [AppLayout, SettingsLayout] },
] as const;

void createInertiaApp({
    title: (title) => (title ? `${title} - ${appName}` : appName),
    layout: (name) =>
        LAYOUTS_BY_PREFIX.find(({ prefix }) => name.startsWith(prefix))
            ?.layout ?? AppLayout,
    strictMode: true,
    withApp(app) {
        return (
            <TooltipProvider delayDuration={0}>
                {app}
                {/* Top of the screen, so a toast never covers the pinned Save button. */}
                <Toaster position="top-center" />
            </TooltipProvider>
        );
    },
    progress: {
        color: '#4B5563',
    },
});

// This will set light / dark mode on load...
initializeTheme();
