import { Head, Link } from '@inertiajs/react';
import { Eye, Pencil, Plus } from 'lucide-react';
import CreateTeamModal from '@/components/create-team-modal';
import Heading from '@/components/heading';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Tooltip,
    TooltipContent,
    TooltipProvider,
    TooltipTrigger,
} from '@/components/ui/tooltip';
import { useTranslation } from '@/hooks/use-translation';
import { edit, index } from '@/routes/teams';
import type { Team } from '@/types';

type Props = {
    teams: Team[];
};

export default function TeamsIndex({ teams }: Props) {
    const { __ } = useTranslation();

    return (
        <>
            <Head title={__('Teams')} />

            <h1 className="sr-only">{__('Teams')}</h1>

            <div className="flex flex-col space-y-6">
                <div className="flex items-center justify-between">
                    <Heading
                        variant="small"
                        title={__('Teams')}
                        description={__('Manage your teams and team memberships')}
                    />

                    <CreateTeamModal>
                        <Button data-test="teams-new-team-button">
                            <Plus /> {__('New team')}
                        </Button>
                    </CreateTeamModal>
                </div>

                <div className="space-y-3">
                    {teams.map((team) => (
                        <div
                            key={team.id}
                            data-test="team-row"
                            className="flex items-center justify-between rounded-lg border p-4"
                        >
                            <div className="flex items-center gap-4">
                                <div>
                                    <div className="flex items-center gap-2">
                                        <span className="font-medium">
                                            {team.name}
                                        </span>
                                        {team.isPersonal ? (
                                            <Badge variant="secondary">
                                                {__('Personal')}
                                            </Badge>
                                        ) : null}
                                    </div>
                                    <span className="text-sm text-muted-foreground">
                                        {team.roleLabel}
                                    </span>
                                </div>
                            </div>

                            <TooltipProvider>
                                <div className="flex items-center gap-2">
                                    {team.role === 'member' ? (
                                        <Tooltip>
                                            <TooltipTrigger asChild>
                                                <Button
                                                    variant="ghost"
                                                    size="sm"
                                                    data-test="team-view-button"
                                                    asChild
                                                >
                                                    <Link
                                                        href={edit(team.slug)}
                                                    >
                                                        <Eye className="h-4 w-4" />
                                                    </Link>
                                                </Button>
                                            </TooltipTrigger>
                                            <TooltipContent>
                                                <p>{__('View team')}</p>
                                            </TooltipContent>
                                        </Tooltip>
                                    ) : (
                                        <Tooltip>
                                            <TooltipTrigger asChild>
                                                <Button
                                                    variant="ghost"
                                                    size="sm"
                                                    data-test="team-edit-button"
                                                    asChild
                                                >
                                                    <Link
                                                        href={edit(team.slug)}
                                                    >
                                                        <Pencil className="h-4 w-4" />
                                                    </Link>
                                                </Button>
                                            </TooltipTrigger>
                                            <TooltipContent>
                                                <p>{__('Edit team')}</p>
                                            </TooltipContent>
                                        </Tooltip>
                                    )}
                                </div>
                            </TooltipProvider>
                        </div>
                    ))}

                    {teams.length === 0 ? (
                        <p className="py-8 text-center text-muted-foreground">
                            {__("You don't belong to any teams yet.")}
                        </p>
                    ) : null}
                </div>
            </div>
        </>
    );
}

TeamsIndex.layout = (page: Record<string, unknown>) => {
    const translations = page.translations as Record<string, string> | undefined;
    const t = (key: string) => translations?.[key] ?? key;
    return {
        breadcrumbs: [
            {
                title: t('Teams'),
                href: index(),
            },
        ],
    };
};
