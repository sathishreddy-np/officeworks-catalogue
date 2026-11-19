import { createInertiaApp } from '@inertiajs/react';
import StoreLayout from '@/components/catalogue/store-layout';
import { Toaster } from '@/components/ui/sonner';
import { TooltipProvider } from '@/components/ui/tooltip';
import { initializeTheme } from '@/hooks/use-appearance';
import AppLayout from '@/layouts/app-layout';
import AuthLayout from '@/layouts/auth-layout';
import SettingsLayout from '@/layouts/settings/layout';

// VITE_APP_NAME comes from the .env file at the time Vite starts.
const configuredName = import.meta.env.VITE_APP_NAME;
// Use that name when it has been set. Otherwise the browser tab says Officeworks.
const appName =
    configuredName && configuredName !== 'Laravel'
        ? configuredName
        : 'Officeworks';

void createInertiaApp({
    title: (title) => (title ? `${title} - ${appName}` : appName),
    layout: (name) => {
        switch (true) {
            case name === 'welcome':
                return null;
            // One store header for every catalogue page. Inertia keeps it mounted
            // so category changes do not reload the document or reset the bar.
            case name.startsWith('catalogue/'):
                return StoreLayout;
            case name.startsWith('auth/'):
                return AuthLayout;
            case name.startsWith('settings/'):
                return [AppLayout, SettingsLayout];
            default:
                return AppLayout;
        }
    },
    strictMode: true,
    withApp(app) {
        return (
            <TooltipProvider delayDuration={0}>
                {app}
                <Toaster />
            </TooltipProvider>
        );
    },
    progress: {
        // The thin bar at the top of the page during a visit. This is the store red.
        color: '#D71920',
    },
});

// This will set light / dark mode on load...
initializeTheme();
