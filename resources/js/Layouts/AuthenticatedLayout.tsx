import { PropsWithChildren, ReactNode } from 'react';
import { usePage } from '@inertiajs/react';
import Button from '@/Components/UI/Button';
import Card from '@/Components/UI/Card';
import CursorEffect from '@/Components/UI/CursorEffect';
import { PageProps } from '@/types';

export default function AuthenticatedLayout({
    header,
    children,
}: PropsWithChildren<{ header?: ReactNode }>) {
    const user = usePage<PageProps>().props.auth.user;

    return (
        <div className="artt-shell min-h-screen">
            <CursorEffect />
            <div className="artt-container py-6">
                <Card as="nav" className="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <div className="font-limelight text-3xl text-[var(--artt-amber-2)]">artt</div>
                        <div className="mt-1 text-sm text-[var(--artt-muted)]">
                            {user?.first_name} {user?.last_name}
                        </div>
                    </div>
                    <div className="flex flex-wrap gap-3">
                        <Button href={route('dashboard')} variant="secondary" size="sm">
                            Dashboard
                        </Button>
                        <Button href={route('profile.edit')} variant="secondary" size="sm">
                            Profile
                        </Button>
                        <Button href={route('logout')} method="post" as="button" size="sm">
                            Log Out
                        </Button>
                    </div>
                </Card>

                {header ? (
                    <div className="mb-6 text-2xl font-semibold text-[var(--artt-fg)]">
                        {header}
                    </div>
                ) : null}

                <main>{children}</main>
            </div>
        </div>
    );
}
