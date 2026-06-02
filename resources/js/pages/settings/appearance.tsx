import { Head } from '@inertiajs/react';
import AppearanceTabs from '@/components/appearance-tabs';
import Heading from '@/components/heading';
import { useTranslation } from '@/hooks/use-translation';
import { edit as editAppearance } from '@/routes/appearance';

export default function Appearance() {
    const { __ } = useTranslation();

    return (
        <>
            <Head title={__('Appearance settings')} />

            <h1 className="sr-only">{__('Appearance settings')}</h1>

            <div className="space-y-6">
                <Heading
                    variant="small"
                    title={__('Appearance settings')}
                    description={__('Update the appearance settings for your account')}
                />
                <AppearanceTabs />
            </div>
        </>
    );
}

Appearance.layout = (page: Record<string, unknown>) => {
    const translations = page.translations as Record<string, string> | undefined;
    const t = (key: string) => translations?.[key] ?? key;
    return {
        breadcrumbs: [
            {
                title: t('Appearance settings'),
                href: editAppearance(),
            },
        ],
    };
};
