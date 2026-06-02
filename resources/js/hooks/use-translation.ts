import { useCallback } from 'react';
import { usePage } from '@inertiajs/react';

export type TranslateFn = (key: string, replace?: Record<string, string | number>) => string;

export type UseTranslationReturn = {
    locale: string;
    __: TranslateFn;
};

export function useTranslation(): UseTranslationReturn {
    const { locale, translations } = usePage().props;

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

    return { locale, __ };
}
