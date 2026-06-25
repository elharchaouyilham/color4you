import { Link } from '@inertiajs/react';
import { PropsWithChildren } from 'react';
import CursorEffect from '@/Components/UI/CursorEffect';
import Navbar from '@/Components/UI/Navbar';

export default function PublicLayout({ children }: PropsWithChildren) {
    return (
        <div className="artt-shell">
            <CursorEffect />
            <Navbar />
            <main>{children}</main>

            <footer className="mt-10 border-t border-[rgba(238,188,93,0.18)]">
                <div className="artt-container flex flex-col gap-4 py-8 text-sm text-[var(--artt-muted)] md:flex-row md:items-center md:justify-between">
                    <div>
                        <div className="font-limelight text-xl text-[var(--artt-fg)]">Artt</div>
                        <div>Bibliotheque artistique, reservations et ateliers de dessin.</div>
                    </div>
                    <div className="flex flex-wrap gap-4">
                        <Link href={route('catalog.index')} className="transition hover:text-[var(--artt-fg)]">
                            Explorer le catalogue
                        </Link>
                        <Link href={route('sessions.index')} className="transition hover:text-[var(--artt-fg)]">
                            Voir les sessions
                        </Link>
                        <Link href={route('contact.create')} className="transition hover:text-[var(--artt-fg)]">
                            Nous contacter
                        </Link>
                    </div>
                </div>
            </footer>
        </div>
    );
}
