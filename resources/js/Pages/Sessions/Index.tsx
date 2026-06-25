import Button from '@/Components/UI/Button';
import Card from '@/Components/UI/Card';
import { Input, Select } from '@/Components/UI/Input';
import SectionHeader from '@/Components/UI/SectionHeader';
import PublicLayout from '@/Layouts/PublicLayout';
import { Head, Link, router } from '@inertiajs/react';
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
    category: { id: number; name: string; slug: string } | null;
    image_url: string | null;
};

type Pagination<T> = {
    data: T[];
    links: { url: string | null; label: string; active: boolean }[];
};

export default function SessionsIndex({
    filters,
    categories,
    sessions,
}: {
    filters: { search: string; category: string };
    categories: Array<{ id: number; name: string; slug: string }>;
    sessions: Pagination<Session>;
}) {
    return (
        <PublicLayout>
            <Head title="Ateliers" />

            <section className="artt-container py-14">
                <SectionHeader
                    eyebrow="Ateliers de dessin"
                    title="Sessions a venir, capacite restante et details pratiques."
                    copy="Trouvez un atelier, verifiez les places restantes, puis ouvrez la fiche pour vous inscrire."
                />

                <Card
                    as="form"
                    className="mb-8 grid gap-4 md:grid-cols-[minmax(0,1fr)_240px_auto]"
                    onSubmit={(event: FormEvent<HTMLFormElement>) => {
                        event.preventDefault();
                        const formData = new FormData(event.currentTarget);
                        router.get(route('sessions.index'), {
                            search: formData.get('search'),
                            category: formData.get('category'),
                        }, {
                            preserveState: true,
                            replace: true,
                        });
                    }}
                >
                    <Input type="search" name="search" defaultValue={filters.search} placeholder="Rechercher un atelier" />
                    <Select name="category" defaultValue={filters.category}>
                        <option value="">Toutes les categories</option>
                        {categories.map((category) => (
                            <option key={category.id} value={category.slug}>
                                {category.name}
                            </option>
                        ))}
                    </Select>
                    <Button type="submit" className="w-full md:w-auto">
                        Filtrer
                    </Button>
                </Card>

                <div className="grid gap-6 lg:grid-cols-2 2xl:grid-cols-3">
                    {sessions.data.map((session) => (
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
                                    <p className="line-clamp-3 text-sm leading-6 text-[var(--artt-muted)]">{session.description}</p>
                                    <div className="text-sm text-[var(--artt-muted)]">{new Date(session.starts_at).toLocaleString()}</div>
                                    <div className="flex flex-wrap gap-4 text-sm">
                                        <span className="text-[var(--artt-muted)]">Formateur: {session.trainer_name ?? 'A confirmer'}</span>
                                        <span className="font-medium text-[var(--artt-fg)]">{session.available_seats} places restantes</span>
                                    </div>
                                </div>
                            </Card>
                        </Link>
                    ))}
                </div>

                <div className="mt-8 flex flex-wrap gap-2">
                    {sessions.links.map((link) => (
                        <button
                            key={link.label}
                            type="button"
                            disabled={!link.url}
                            onClick={() => link.url && router.visit(link.url)}
                            className={`rounded-full px-4 py-2 text-sm font-semibold transition disabled:opacity-40 ${
                                link.active
                                    ? 'bg-[var(--artt-amber-2)] text-[#130c03]'
                                    : 'border border-[rgba(238,188,93,0.28)] text-[var(--artt-muted)] hover:text-[var(--artt-fg)]'
                            }`}
                            dangerouslySetInnerHTML={{ __html: link.label }}
                        />
                    ))}
                </div>
            </section>
        </PublicLayout>
    );
}
