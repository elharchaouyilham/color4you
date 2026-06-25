import Button from '@/Components/UI/Button';
import Card from '@/Components/UI/Card';
import { Field, Input } from '@/Components/UI/Input';
import SectionHeader from '@/Components/UI/SectionHeader';
import InputError from '@/Components/InputError';
import PublicLayout from '@/Layouts/PublicLayout';
import { Head, Link, useForm, usePage } from '@inertiajs/react';
import { FormEvent } from 'react';
import { PageProps } from '@/types';

type Product = {
    id: number;
    name: string;
    slug: string;
    reference: string;
    description: string | null;
    price: string | null;
    available_quantity: number;
    stock_quantity: number;
    reserved_quantity: number;
    status: string;
    image_url: string | null;
    category: {
        id: number | null;
        name: string | null;
        slug: string | null;
        parent_name: string | null;
    };
};

export default function CatalogShow({
    product,
    relatedProducts,
}: {
    product: Product;
    relatedProducts: Array<{ id: number; name: string; slug: string; price: string | null; available_quantity: number; image_url: string | null }>;
}) {
    const { auth, flash } = usePage<PageProps>().props;
    const form = useForm({ quantity: '1' });

    const submit = (event: FormEvent) => {
        event.preventDefault();
        form.post(route('account.reservations.store', product.slug), {
            preserveScroll: true,
        });
    };

    return (
        <PublicLayout>
            <Head title={product.name} />

            <section className="artt-container grid gap-10 py-14 lg:grid-cols-[1.05fr_0.95fr] xl:gap-14">
                <Card padding="none" className="min-h-[320px]">
                    {product.image_url ? (
                        <img src={product.image_url} alt={product.name} className="h-full min-h-[320px] w-full object-cover" />
                    ) : null}
                </Card>

                <div className="space-y-6">
                    <div className="space-y-3">
                        <p className="artt-eyebrow">
                            {product.category.parent_name ? `${product.category.parent_name} / ` : ''}
                            {product.category.name}
                        </p>
                        <h1 className="artt-heading text-4xl leading-tight md:text-6xl">{product.name}</h1>
                        <div className="text-sm text-[var(--artt-muted)]">Reference: {product.reference}</div>
                    </div>

                    <p className="leading-8 text-[var(--artt-muted)]">{product.description}</p>

                    <div className="grid gap-4 sm:grid-cols-3">
                        {[
                            ['Disponible', product.available_quantity],
                            ['Stock total', product.stock_quantity],
                            ['Prix indicatif', product.price ?? '-'],
                        ].map(([label, value]) => (
                            <Card key={label as string} padding="sm">
                                <div className="text-sm text-[var(--artt-muted)]">{label}</div>
                                <div className="mt-2 text-2xl font-semibold text-[var(--artt-amber-2)]">{value}</div>
                            </Card>
                        ))}
                    </div>

                    <Card>
                        <div className="mb-4 text-lg font-semibold text-[var(--artt-fg)]">Demande de reservation</div>
                        {flash.success ? (
                            <div className="mb-4 rounded-2xl bg-[rgba(126,215,168,0.12)] px-4 py-3 text-sm text-[var(--artt-success)]">{flash.success}</div>
                        ) : null}
                        {auth.user ? (
                            <form onSubmit={submit} className="space-y-4">
                                <Field label="Quantite" htmlFor="quantity">
                                    <Input
                                        id="quantity"
                                        type="number"
                                        min={1}
                                        max={product.available_quantity}
                                        value={form.data.quantity}
                                        onChange={(event) => form.setData('quantity', event.target.value)}
                                        required
                                    />
                                    <InputError message={form.errors.quantity} className="mt-2" />
                                </Field>
                                <Button type="submit" disabled={form.processing}>
                                    Envoyer la demande
                                </Button>
                            </form>
                        ) : (
                            <div className="space-y-3 text-sm text-[var(--artt-muted)]">
                                <div>Connectez-vous pour demander une reservation.</div>
                                <Button href={route('login')}>Se connecter</Button>
                            </div>
                        )}
                    </Card>
                </div>
            </section>

            <section className="artt-container pb-16">
                <SectionHeader eyebrow="Produits similaires" title="Continuer l'exploration du catalogue." />
                <div className="grid gap-6 md:grid-cols-2 xl:grid-cols-4">
                    {relatedProducts.map((related) => (
                        <Link key={related.id} href={route('catalog.show', related.slug)}>
                            <Card padding="none" interactive className="h-full">
                                <div className="aspect-[4/3] bg-[rgba(255,255,255,0.04)]">
                                    {related.image_url ? <img src={related.image_url} alt={related.name} className="h-full w-full object-cover" /> : null}
                                </div>
                                <div className="p-4">
                                    <div className="font-semibold text-[var(--artt-fg)]">{related.name}</div>
                                    <div className="mt-2 text-sm text-[var(--artt-muted)]">{related.available_quantity} disponibles</div>
                                </div>
                            </Card>
                        </Link>
                    ))}
                </div>
            </section>
        </PublicLayout>
    );
}
