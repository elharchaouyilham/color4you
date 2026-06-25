import Button from '@/Components/UI/Button';
import Card from '@/Components/UI/Card';
import { Input, Select } from '@/Components/UI/Input';
import SectionHeader from '@/Components/UI/SectionHeader';
import PublicLayout from '@/Layouts/PublicLayout';
import { Head, Link, router } from '@inertiajs/react';
import { FormEvent } from 'react';

type Category = {
    id: number;
    name: string;
    slug: string;
    children?: Category[];
};

type Product = {
    id: number;
    name: string;
    slug: string;
    reference: string;
    description: string | null;
    price: string | null;
    available_quantity: number;
    image_url: string | null;
    category: {
        id: number | null;
        name: string | null;
        slug: string | null;
        parent_name: string | null;
    };
};

type Pagination<T> = {
    data: T[];
    links: { url: string | null; label: string; active: boolean }[];
};

export default function CatalogIndex({
    filters,
    categories,
    products,
}: {
    filters: { search: string; category: string };
    selectedCategory: Category | null;
    categories: Category[];
    products: Pagination<Product>;
}) {
    const submitFilters = (formData: FormData) => {
        router.get(route('catalog.index'), {
            search: formData.get('search'),
            category: formData.get('category'),
        }, {
            preserveState: true,
            replace: true,
        });
    };

    return (
        <PublicLayout>
            <Head title="Catalogue" />

            <section className="artt-container py-14">
                <SectionHeader
                    eyebrow="Catalogue"
                    title="Recherchez les ressources disponibles avant reservation."
                    copy="Filtrez par categorie, nom, reference ou description, puis ouvrez une fiche pour demander une reservation."
                />

                <Card
                    as="form"
                    className="mb-8 grid gap-4 md:grid-cols-[minmax(0,1fr)_260px_auto]"
                    onSubmit={(event: FormEvent<HTMLFormElement>) => {
                        event.preventDefault();
                        submitFilters(new FormData(event.currentTarget));
                    }}
                >
                    <Input
                        type="search"
                        name="search"
                        defaultValue={filters.search}
                        placeholder="Rechercher par nom, reference ou description"
                    />
                    <Select name="category" defaultValue={filters.category}>
                        <option value="">Toutes les categories</option>
                        {categories.map((category) => (
                            <optgroup key={category.id} label={category.name}>
                                <option value={category.slug}>{category.name}</option>
                                {category.children?.map((child) => (
                                    <option key={child.id} value={child.slug}>
                                        {child.name}
                                    </option>
                                ))}
                            </optgroup>
                        ))}
                    </Select>
                    <Button type="submit" className="w-full md:w-auto">
                        Filtrer
                    </Button>
                </Card>

                <div className="grid gap-6 md:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-4">
                    {products.data.map((product) => (
                        <Link key={product.id} href={route('catalog.show', product.slug)}>
                            <Card padding="none" interactive className="h-full">
                                <div className="aspect-[4/3] bg-[rgba(255,255,255,0.04)]">
                                    {product.image_url ? (
                                        <img src={product.image_url} alt={product.name} className="h-full w-full object-cover" />
                                    ) : null}
                                </div>
                                <div className="space-y-3 p-5">
                                    <div className="text-sm text-[var(--artt-amber-2)]">
                                        {product.category.parent_name ? `${product.category.parent_name} / ` : ''}
                                        {product.category.name}
                                    </div>
                                    <div className="text-xl font-semibold text-[var(--artt-fg)]">{product.name}</div>
                                    <p className="line-clamp-3 text-sm leading-6 text-[var(--artt-muted)]">{product.description}</p>
                                    <div className="flex items-center justify-between gap-3 text-sm">
                                        <span className="font-medium text-[var(--artt-fg)]">{product.available_quantity} disponibles</span>
                                        <span className="text-[var(--artt-muted)]">{product.reference}</span>
                                    </div>
                                </div>
                            </Card>
                        </Link>
                    ))}
                </div>

                <div className="mt-8 flex flex-wrap gap-2">
                    {products.links.map((link) => (
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
