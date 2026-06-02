import { router } from '@inertiajs/react';
import { Check, Languages } from 'lucide-react';
import { useTranslation } from '@/hooks/use-translation';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import {
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
    useSidebar,
} from '@/components/ui/sidebar';
import { change as changeLocale } from '@/routes/locale';

const locales = [
    { value: 'en', label: 'English' },
    { value: 'bg', label: 'Български' },
];

export function LanguageSwitcher() {
    const { locale } = useTranslation();
    const { state } = useSidebar();

    function switchLocale(newLocale: string) {
        router.post(changeLocale().url, { locale: newLocale });
    }

    const current = locales.find((l) => l.value === locale) ?? locales[0];

    return (
        <SidebarMenu>
            <SidebarMenuItem>
                <DropdownMenu>
                    <DropdownMenuTrigger asChild>
                        <SidebarMenuButton
                            size="lg"
                            className="text-sidebar-accent-foreground data-[state=open]:bg-sidebar-accent"
                        >
                            <Languages className="size-4" />
                            <span>{state === 'expanded' ? current.label : ''}</span>
                        </SidebarMenuButton>
                    </DropdownMenuTrigger>
                    <DropdownMenuContent
                        className="w-(--radix-dropdown-menu-trigger-width) min-w-56 rounded-lg"
                        align="end"
                        side="bottom"
                    >
                        {locales.map((l) => (
                            <DropdownMenuItem
                                key={l.value}
                                onClick={() => switchLocale(l.value)}
                                className="flex items-center justify-between"
                            >
                                {l.label}
                                {locale === l.value && <Check className="size-4" />}
                            </DropdownMenuItem>
                        ))}
                    </DropdownMenuContent>
                </DropdownMenu>
            </SidebarMenuItem>
        </SidebarMenu>
    );
}
