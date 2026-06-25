import { Link, usePage } from '@inertiajs/react';
import { Menu, X } from 'lucide-react';
import { useState } from 'react';
import { PageProps } from '@/types';
import Button from './Button';

const navigation = [
    { label: 'Accueil', href: route('home') },
    { label: 'Catalogue', href: route('catalog.index') },
    { label: 'Ateliers', href: route('sessions.index') },
    { label: 'Contact', href: route('contact.create') },
];

export default function Navbar() {
    const { auth } = usePage<PageProps>().props;
    const [open, setOpen] = useState(false);

    return (
        <header className="relative z-20 py-5">
            <div className="artt-container">
                <div className="artt-glass rounded-full px-4 py-3 md:px-5">
                    <div className="flex items-center justify-between gap-4">
                        <div className="flex items-center gap-8">
                            <Link href={route('home')} className="flex items-center">
                                <span className="font-limelight text-2xl text-[var(--artt-fg)]">artt</span>
                            </Link>
                            <nav className="hidden items-center gap-6 text-sm font-semibold text-[var(--artt-muted)] lg:flex">
                                {navigation.map((item) => (
                                    <Link key={item.href} href={item.href} className="transition hover:text-[var(--artt-fg)]">
                                        {item.label}
                                    </Link>
                                ))}
                            </nav>
                        </div>

                        <div className="hidden items-center gap-3 md:flex">
                            {auth.user ? (
                                <>
                                    <Button href={route('account.dashboard')} variant="secondary" size="sm">
                                        Mon espace
                                    </Button>
                                    <Button href={route('logout')} method="post" as="button" size="sm">
                                        Deconnexion
                                    </Button>
                                </>
                            ) : (
                                <>
                                    <Button href={route('login')} variant="ghost" size="sm">
                                        Connexion
                                    </Button>
                                    <Button href={route('register')} size="sm">
                                        Creer un compte
                                    </Button>
                                </>
                            )}
                        </div>

                        <button
                            type="button"
                            className="grid h-10 w-10 place-items-center rounded-full text-[var(--artt-fg)] md:hidden"
                            onClick={() => setOpen((value) => !value)}
                            aria-label="Ouvrir le menu"
                        >
                            {open ? <X size={20} /> : <Menu size={20} />}
                        </button>
                    </div>

                    {open ? (
                        <div className="mt-4 grid gap-2 border-t border-[rgba(238,188,93,0.18)] pt-4 md:hidden">
                            {navigation.map((item) => (
                                <Link key={item.href} href={item.href} className="rounded-2xl px-3 py-2 text-sm text-[var(--artt-muted)]">
                                    {item.label}
                                </Link>
                            ))}
                            <div className="mt-2 grid gap-2">
                                {auth.user ? (
                                    <>
                                        <Button href={route('account.dashboard')} variant="secondary" size="sm">
                                            Mon espace
                                        </Button>
                                        <Button href={route('logout')} method="post" as="button" size="sm">
                                            Deconnexion
                                        </Button>
                                    </>
                                ) : (
                                    <>
                                        <Button href={route('login')} variant="secondary" size="sm">
                                            Connexion
                                        </Button>
                                        <Button href={route('register')} size="sm">
                                            Creer un compte
                                        </Button>
                                    </>
                                )}
                            </div>
                        </div>
                    ) : null}
                </div>
            </div>
        </header>
    );
}
