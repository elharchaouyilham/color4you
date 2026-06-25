import Button from '@/Components/UI/Button';
import Card from '@/Components/UI/Card';
import SectionHeader from '@/Components/UI/SectionHeader';
import PublicLayout from '@/Layouts/PublicLayout';
import { Head, Link } from '@inertiajs/react';

type ProductCard = {
    id: number;
    name: string;
    slug: string;
    reference: string;
    price: string | null;
    available_quantity: number;
    category: { id: number; name: string; slug: string } | null;
    image_url: string | null;
};

type SessionCard = {
    id: number;
    title: string;
    slug: string;
    starts_at: string;
    ends_at: string;
    available_seats: number;
    price: string | null;
    category: { id: number; name: string; slug: string } | null;
    trainer_name: string | null;
    image_url: string | null;
};

export default function Home({
    featuredProducts,
    upcomingSessions,
}: {
    featuredProducts: ProductCard[];
    upcomingSessions: SessionCard[];
}) {
    return (
        <PublicLayout>
            <Head title="Accueil" />

            <section className="relative -mt-[5.75rem] min-h-screen overflow-hidden pt-[5.75rem]">
                <div className="absolute inset-0 z-0 opacity-60">
                    <video
                        className="h-full w-full object-cover object-bottom opacity-80 saturate-[0.85] brightness-[0.72]"
                        muted
                        playsInline
                        autoPlay
                        loop
                        preload="auto"
                        src="https://plugin-assets.open-design.ai/plugins/innovation/hf_20260405_074625_a81f018a-956b-43fb-9aee-4d1508e30e6a-6993b9.mp4"
                    />
                    <video
                        className="absolute inset-0 h-full w-full object-cover object-bottom opacity-25 mix-blend-screen sepia saturate-[0.7] brightness-[0.42]"
                        muted
                        playsInline
                        autoPlay
                        loop
                        preload="auto"
                        src="https://plugin-assets.open-design.ai/plugins/liquid-glass-agency/hf_20260307_083826_e938b29f-a43a-41ec-a153-3d4730578ab8-b7258e.mp4"
                    />
                </div>
                <div className="absolute inset-0 z-0 bg-[radial-gradient(circle_at_50%_42%,rgba(238,188,93,0.18),transparent_22rem),linear-gradient(180deg,rgba(2,7,19,0.16),rgba(2,7,19,0.62)_56%,#020713_100%),linear-gradient(90deg,rgba(2,7,19,0.72),rgba(2,7,19,0.15),rgba(2,7,19,0.72))]" />

                <div className="artt-container relative z-10 grid min-h-[calc(100vh-5.75rem)] gap-10 py-14 lg:grid-cols-[1.15fr_0.85fr] lg:items-center lg:py-24">
                    <div className="space-y-7">
                    <p className="artt-eyebrow">Catalogue artistique et ateliers encadres</p>
                    <h1 className="artt-heading max-w-4xl text-5xl leading-[0.95] md:text-7xl xl:text-8xl">
                        Ressources artistiques, reservations et sessions dans un meme espace.
                    </h1>
                    <p className="max-w-2xl text-lg leading-8 text-[var(--artt-muted)]">
                        Artt regroupe livres, materiel et ateliers ouverts au public avec un espace client simple pour suivre vos demandes.
                    </p>
                    <div className="flex flex-wrap gap-3">
                        <Button href={route('catalog.index')} size="lg">
                            Parcourir le catalogue
                        </Button>
                        <Button href={route('sessions.index')} variant="secondary" size="lg">
                            Voir les ateliers
                        </Button>
                    </div>
                    </div>

                    <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-1 xl:grid-cols-2">
                        <Card>
                            <div className="text-sm text-[var(--artt-muted)]">Produits en vedette</div>
                            <div className="mt-3 text-4xl font-semibold text-[var(--artt-amber-2)]">{featuredProducts.length}</div>
                            <div className="mt-2 text-sm leading-6 text-[var(--artt-muted)]">Selection recente du catalogue disponible.</div>
                        </Card>
                        <Card>
                            <div className="text-sm text-[var(--artt-muted)]">Sessions a venir</div>
                            <div className="mt-3 text-4xl font-semibold text-[var(--artt-amber-2)]">{upcomingSessions.length}</div>
                            <div className="mt-2 text-sm leading-6 text-[var(--artt-muted)]">Ateliers ouverts avec capacite restante.</div>
                        </Card>
                    </div>
                </div>
            </section>

            <div className="artt-container artt-gold-rule" />

            <section className="artt-container py-16">
                <SectionHeader
                    eyebrow="Produits recents"
                    title="Une selection rapide de ressources disponibles."
                    action={
                        <Button href={route('catalog.index')} variant="ghost" size="sm">
                            Tout le catalogue
                        </Button>
                    }
                />
                <div className="grid gap-6 md:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-4">
                    {featuredProducts.map((product) => (
                        <Link key={product.id} href={route('catalog.show', product.slug)}>
                            <Card padding="none" interactive className="h-full">
                                <div className="aspect-[4/3] bg-[rgba(255,255,255,0.04)]">
                                    {product.image_url ? (
                                        <img src={product.image_url} alt={product.name} className="h-full w-full object-cover" />
                                    ) : null}
                                </div>
                                <div className="space-y-3 p-5">
                                    <div>
                                        <div className="text-sm text-[var(--artt-amber-2)]">{product.category?.name ?? 'Catalogue'}</div>
                                        <div className="mt-1 text-xl font-semibold text-[var(--artt-fg)]">{product.name}</div>
                                    </div>
                                    <div className="flex items-center justify-between gap-3 text-sm text-[var(--artt-muted)]">
                                        <span>{product.reference}</span>
                                        <span className="font-medium text-[var(--artt-fg)]">{product.available_quantity} en stock</span>
                                    </div>
                                </div>
                            </Card>
                        </Link>
                    ))}
                </div>
            </section>

            <section className="artt-container py-16">
                <SectionHeader
                    eyebrow="Ateliers ouverts"
                    title="Des sessions encadrees par des formateurs actifs."
                    action={
                        <Button href={route('sessions.index')} variant="ghost" size="sm">
                            Toutes les sessions
                        </Button>
                    }
                />
                <div className="grid gap-6 lg:grid-cols-2 2xl:grid-cols-3">
                    {upcomingSessions.map((session) => (
                        <Link key={session.id} href={route('sessions.show', session.slug)}>
                            <Card padding="none" interactive className="grid h-full overflow-hidden md:grid-cols-[220px_minmax(0,1fr)]">
                                <div className="aspect-[4/3] bg-[rgba(255,255,255,0.04)] md:aspect-auto">
                                    {session.image_url ? (
                                        <img src={session.image_url} alt={session.title} className="h-full w-full object-cover" />
                                    ) : null}
                                </div>
                                <div className="space-y-3 p-5">
                                    <div className="text-sm text-[var(--artt-amber-2)]">{session.category?.name ?? 'Atelier'}</div>
                                    <div className="text-2xl font-semibold text-[var(--artt-fg)]">{session.title}</div>
                                    <div className="text-sm text-[var(--artt-muted)]">{new Date(session.starts_at).toLocaleString()}</div>
                                    <div className="text-sm text-[var(--artt-muted)]">Formateur: {session.trainer_name ?? 'A confirmer'}</div>
                                    <div className="text-sm font-medium text-[var(--artt-fg)]">{session.available_seats} places restantes</div>
                                </div>
                            </Card>
                        </Link>
                    ))}
                </div>
            </section>
        </PublicLayout>
    );
}
