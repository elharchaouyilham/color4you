import { usePage } from '@inertiajs/react';
import { PropsWithChildren, ReactNode } from 'react';
import { PageProps } from '@/types';
import Button from '@/Components/UI/Button';
import Card from '@/Components/UI/Card';
import CursorEffect from '@/Components/UI/CursorEffect';
import Sidebar, { SidebarItem } from '@/Components/UI/Sidebar';

export default function ClientLayout({
    title,
    children,
}: PropsWithChildren<{ title: ReactNode }>) {
    const { auth } = usePage<PageProps>().props;

    const menuItems: SidebarItem[] = [
        { label: 'Tableau de bord client', href: route('account.dashboard'), icon: 'pi pi-home', active: route().current('account.dashboard') },
        { label: 'Catalogue', href: route('catalog.index'), icon: 'pi pi-search', active: route().current('catalog.index') },
        { label: 'Ateliers', href: route('sessions.index'), icon: 'pi pi-calendar', active: route().current('sessions.index') },
        { label: 'Profil', href: route('profile.edit'), icon: 'pi pi-user', active: route().current('profile.edit') },
    ];

    return (
        <div className="artt-shell">
            <CursorEffect />
            <div className="artt-container grid min-h-screen gap-6 py-6 lg:grid-cols-[270px_minmax(0,1fr)]">
                <Sidebar label="Espace Client" items={menuItems} />

                <div className="space-y-6">
                    <Card as="header" className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <div className="text-sm text-[var(--artt-muted)]">Connecte en tant que</div>
                            <div className="text-lg font-semibold text-[var(--artt-fg)]">
                                {auth.user?.first_name} {auth.user?.last_name}
                            </div>
                        </div>
                        <div className="flex flex-wrap items-center gap-3">
                            <Button href={route('home')} variant="secondary" size="sm">
                                Retour au site
                            </Button>
                            <Button href={route('logout')} method="post" as="button" size="sm">
                                Deconnexion
                            </Button>
                        </div>
                    </Card>

                    <Card as="section">
                        <div className="mb-6 text-2xl font-semibold text-[var(--artt-fg)]">{title}</div>
                        {children}
                    </Card>
                </div>
            </div>
        </div>
    );
}
