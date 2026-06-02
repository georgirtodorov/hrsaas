import { useCallback } from 'react';
import { usePage } from '@inertiajs/react';

export type TranslateFn = (key: string, replace?: Record<string, string | number>) => string;

export type UseTranslationReturn = {
    locale: string;
    supportedLocales: string[];
    __: TranslateFn;
};

export function useTranslation(): UseTranslationReturn {
    const { locale, supportedLocales, translations } = usePage().props;
    const locales = (supportedLocales as string[]) ?? ['en', 'bg'];

    const __: TranslateFn = useCallback(
        (key: string, replace?: Record<string, string | number>) => {
            let translation = translations[key] ?? key;

            if (replace) {
                for (const [search, value] of Object.entries(replace)) {
                    translation = translation.replace(`:${search}`, String(value));
                }
            }

            return translation;
        },
        [translations],
    );

    return { locale, supportedLocales: locales, __ };
}
