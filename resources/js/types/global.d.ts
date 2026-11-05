import type { Auth } from '@/types/auth';
import type { CmsSharedProps } from '@/types/cms';

declare module 'react' {
    interface InputHTMLAttributes<T> {
        passwordrules?: string;
    }
}

declare module '@inertiajs/core' {
    export interface InertiaConfig {
        sharedPageProps: {
            name: string;
            auth: Auth;
            cms: CmsSharedProps | null;
            sidebarOpen: boolean;
            [key: string]: unknown;
        };
    }
}
