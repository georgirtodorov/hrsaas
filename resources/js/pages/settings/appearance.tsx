import { Head, router } from '@inertiajs/react';
import AppearanceTabs from '@/components/appearance-tabs';
import Heading from '@/components/heading';
import { useTranslation } from '@/hooks/use-translation';
import { edit as editAppearance } from '@/routes/appearance';
import { change as changeLocale } from '@/routes/locale';

export default function Appearance() {
    const { __, locale, supportedLocales } = useTranslation();

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

                <section className="space-y-4">
                    <h2 className="text-sm font-medium">{__('Language')}</h2>
                    <p className="text-muted-foreground text-xs">{__('Select your preferred language')}</p>
                    <div className="flex flex-wrap gap-4">
                        {supportedLocales?.map((loc: string) => (
                            <label
                                key={loc}
                                className="flex cursor-pointer items-center gap-2 rounded-lg border px-4 py-3 has-[:checked]:border-blue-500 has-[:checked]:bg-blue-50 dark:has-[:checked]:bg-blue-950"
                            >
                                <input
                                    type="radio"
                                    name="locale"
                                    value={loc}
                                    checked={locale === loc}
                                    onChange={() =>
                                        router.post(changeLocale(), { locale: loc })
                                    }
                                    className="accent-blue-500"
                                />
                                <span className="text-sm font-medium">
                                    {loc === 'en' ? 'English' : 'Български'}
                                </span>
                            </label>
                        ))}
                    </div>
                </section>

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
