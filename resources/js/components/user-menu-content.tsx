import { Link, router } from '@inertiajs/react';
import { Check, LogOut, Settings } from 'lucide-react';
import {
    DropdownMenuGroup,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
} from '@/components/ui/dropdown-menu';
import { UserInfo } from '@/components/user-info';
import { useMobileNavigation } from '@/hooks/use-mobile-navigation';
import { logout } from '@/routes';
import { edit } from '@/routes/profile';
import { switchMethod } from '@/routes/teams';
import type { Team, User } from '@/types';

type Props = {
    user: User;
    teams?: Team[];
    currentTeam?: Team | null;
};

export function UserMenuContent({ user, teams = [], currentTeam }: Props) {
    const cleanup = useMobileNavigation();

    const handleLogout = () => {
        cleanup();
        router.flushAll();
    };

    const switchTeam = (team: Team) => {
        const previousTeamSlug = currentTeam?.slug;

        router.visit(switchMethod(team.slug), {
            onFinish: () => {
                if (!previousTeamSlug || typeof window === 'undefined') {
                    router.reload();
                    return;
                }

                const currentUrl = `${window.location.pathname}${window.location.search}${window.location.hash}`;
                const segment = `/${previousTeamSlug}`;

                if (currentUrl.includes(segment)) {
                    router.visit(currentUrl.replace(segment, `/${team.slug}`), {
                        replace: true,
                    });
                    return;
                }

                router.reload();
            },
        });
    };

    return (
        <>
            <DropdownMenuLabel className="p-0 font-normal">
                <div className="flex items-center gap-2 px-1 py-1.5 text-left text-sm">
                    <UserInfo user={user} showEmail={true} />
                </div>
            </DropdownMenuLabel>
            <DropdownMenuSeparator />
            {teams.length > 1 ? (
                <>
                    <DropdownMenuLabel className="text-xs text-muted-foreground">
                        Companies
                    </DropdownMenuLabel>
                    {teams.map((team) => (
                        <DropdownMenuItem
                            key={team.id}
                            className="cursor-pointer gap-2 p-2"
                            onSelect={() => switchTeam(team)}
                        >
                            {team.name}
                            {currentTeam?.id === team.id && (
                                <Check className="ml-auto h-4 w-4" />
                            )}
                        </DropdownMenuItem>
                    ))}
                    <DropdownMenuSeparator />
                </>
            ) : null}
            <DropdownMenuGroup>
                <DropdownMenuItem asChild>
                    <Link
                        className="block w-full cursor-pointer"
                        href={edit()}
                        prefetch
                        onClick={cleanup}
                    >
                        <Settings className="mr-2" />
                        Settings
                    </Link>
                </DropdownMenuItem>
            </DropdownMenuGroup>
            <DropdownMenuSeparator />
            <DropdownMenuItem asChild>
                <Link
                    className="block w-full cursor-pointer"
                    href={logout()}
                    as="button"
                    onClick={handleLogout}
                    data-test="logout-button"
                >
                    <LogOut className="mr-2" />
                    Log out
                </Link>
            </DropdownMenuItem>
        </>
    );
}
