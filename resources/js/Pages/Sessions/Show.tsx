import Button from '@/Components/UI/Button';
import Card from '@/Components/UI/Card';
import SectionHeader from '@/Components/UI/SectionHeader';
import PublicLayout from '@/Layouts/PublicLayout';
import InputError from '@/Components/InputError';
import { Head, Link, useForm, usePage } from '@inertiajs/react';
import { PageProps } from '@/types';
import { FormEvent } from 'react';

type Session = {
    id: number;
    title: string;
    slug: string;
    description: string | null;
    starts_at: string;
    ends_at: string;
    capacity: number;
    registered_count: number;
    available_seats: number;
    price: string | null;
    status: string;
    trainer_name: string | null;
    trainer_specialty: string | null;
    category: { id: number; name: string; slug: string } | null;
    image_url: string | null;
};

export default function SessionsShow({
    session,
    relatedSessions,
}: {
    session: Session;
    relatedSessions: Array<{ id: number; title: string; slug: string; starts_at: string; available_seats: number; trainer_name: string | null }>;
}) {
    const { auth, flash } = usePage<PageProps>().props;
    const form = useForm({});

    const submit = (event: FormEvent) => {
        event.preventDefault();
        form.post(route('account.registrations.store', session.slug), {
            preserveScroll: true,
        });
    };

    return (
        <PublicLayout>
            <Head title={session.title} />

            <section className="artt-container grid gap-10 py-14 lg:grid-cols-[1.05fr_0.95fr] xl:gap-14">
                <Card padding="none" className="min-h-[320px]">
                    {session.image_url ? <img src={session.image_url} alt={session.title} className="h-full min-h-[320px] w-full object-cover" /> : null}
                </Card>

                <div className="space-y-6">
                    <div className="space-y-3">
                        <p className="artt-eyebrow">{session.category?.name ?? 'Atelier'}</p>
                        <h1 className="artt-heading text-4xl leading-tight md:text-6xl">{session.title}</h1>
                        <div className="text-sm text-[var(--artt-muted)]">{new Date(session.starts_at).toLocaleString()}</div>
                    </div>

                    <p className="leading-8 text-[var(--artt-muted)]">{session.description}</p>

                    <div className="grid gap-4 sm:grid-cols-3">
                        {[
                            ['Places restantes', session.available_seats],
                            ['Capacite', session.capacity],
                            ['Prix indicatif', session.price ?? '-'],
                        ].map(([label, value]) => (
                            <Card key={label as string} padding="sm">
                                <div className="text-sm text-[var(--artt-muted)]">{label}</div>
                                <div className="mt-2 text-2xl font-semibold text-[var(--artt-amber-2)]">{value}</div>
                            </Card>
                        ))}
                    </div>

                    <Card>
                        <div className="mb-2 text-lg font-semibold text-[var(--artt-fg)]">Encadrement</div>
                        <div className="text-sm text-[var(--artt-muted)]">Formateur: {session.trainer_name ?? 'A confirmer'}</div>
                        <div className="text-sm text-[var(--artt-muted)]">Specialite: {session.trainer_specialty ?? '-'}</div>
                    </Card>

                    <Card>
                        <div className="mb-4 text-lg font-semibold text-[var(--artt-fg)]">Inscription</div>
                        {flash.success ? (
                            <div className="mb-4 rounded-2xl bg-[rgba(126,215,168,0.12)] px-4 py-3 text-sm text-[var(--artt-success)]">{flash.success}</div>
                        ) : null}
                        {auth.user ? (
                            <form onSubmit={submit} className="space-y-4">
                                <InputError message={(form.errors as Record<string, string | undefined>).session} className="mt-2" />
                                <Button type="submit" disabled={form.processing || session.available_seats < 1}>
                                    S'inscrire a l'atelier
                                </Button>
                            </form>
                        ) : (
                            <div className="space-y-3 text-sm text-[var(--artt-muted)]">
                                <div>Connectez-vous pour reserver votre place.</div>
                                <Button href={route('login')}>Se connecter</Button>
                            </div>
                        )}
                    </Card>
                </div>
            </section>

            <section className="artt-container pb-16">
                <SectionHeader eyebrow="Autres ateliers" title="Explorer les prochaines sessions." />
                <div className="grid gap-6 md:grid-cols-3">
                    {relatedSessions.map((related) => (
                        <Link key={related.id} href={route('sessions.show', related.slug)}>
                            <Card interactive className="h-full">
                                <div className="text-xl font-semibold text-[var(--artt-fg)]">{related.title}</div>
                                <div className="mt-2 text-sm text-[var(--artt-muted)]">{new Date(related.starts_at).toLocaleString()}</div>
                                <div className="mt-2 text-sm text-[var(--artt-muted)]">Formateur: {related.trainer_name ?? 'A confirmer'}</div>
                                <div className="mt-4 text-sm font-medium text-[var(--artt-amber-2)]">{related.available_seats} places restantes</div>
                            </Card>
                        </Link>
                    ))}
                </div>
            </section>
        </PublicLayout>
    );
}
