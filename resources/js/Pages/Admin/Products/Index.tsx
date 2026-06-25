import React, { useState } from 'react';
import { Head, useForm } from '@inertiajs/react';
import AdminLayout from '@/Layouts/AdminLayout';
import { DataTable } from 'primereact/datatable';
import { Column } from 'primereact/column';
import { Button } from 'primereact/button';
import { Dialog } from 'primereact/dialog';
import { InputText } from 'primereact/inputtext';
import { InputNumber } from 'primereact/inputnumber';
import { InputTextarea } from 'primereact/inputtextarea';
import { Dropdown } from 'primereact/dropdown';
import { Tag } from 'primereact/tag';

interface Product {
    id: number;
    category_id: number;
    category_name: string;
    reference: string;
    name: string;
    slug: string;
    description: string | null;
    price: string;
    stock_quantity: number;
    reserved_quantity: number;
    available_quantity: number;
    status: string;
    image_url: string | null;
    thumb_url: string | null;
}

interface CategoryOption {
    id: number;
    name: string;
}

interface Props {
    products: Product[];
    categories: CategoryOption[];
}

export default function ProductsIndex({ products, categories }: Props) {
    const [dialogVisible, setDialogVisible] = useState(false);
    const [editMode, setEditMode] = useState(false);
    const [selectedProduct, setSelectedProduct] = useState<Product | null>(null);

    const { data, setData, post, delete: destroy, errors, reset, processing } = useForm({
        category_id: '' as number | string,
        reference: '',
        name: '',
        description: '',
        price: 0 as number,
        stock_quantity: 0 as number,
        status: 'available',
        image: null as File | null,
        _method: 'POST'
    });

    const openCreateDialog = () => {
        reset();
        setEditMode(false);
        setSelectedProduct(null);
        setData(prev => ({ ...prev, _method: 'POST' }));
        setDialogVisible(true);
    };

    const openEditDialog = (product: Product) => {
        setEditMode(true);
        setSelectedProduct(product);
        setData({
            category_id: product.category_id,
            reference: product.reference,
            name: product.name,
            description: product.description || '',
            price: parseFloat(product.price),
            stock_quantity: product.stock_quantity,
            status: product.status,
            image: null,
            _method: 'PUT' // Set method to PUT so Laravel handles it as an update
        });
        setDialogVisible(true);
    };

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        if (editMode && selectedProduct) {
            // Post with PUT spoofing to support multipart form data uploads on updates
            post(route('admin.products.update_post', selectedProduct.slug), {
                forceFormData: true,
                onSuccess: () => {
                    setDialogVisible(false);
                    reset();
                }
            });
        } else {
            post(route('admin.products.store'), {
                forceFormData: true,
                onSuccess: () => {
                    setDialogVisible(false);
                    reset();
                }
            });
        }
    };

    const handleDelete = (product: Product) => {
        if (confirm(`Êtes-vous sûr de vouloir supprimer le produit "${product.name}" ?`)) {
            destroy(route('admin.products.destroy', product.slug));
        }
    };

    const getStatusSeverity = (status: string) => {
        switch (status) {
            case 'available': return 'success';
            case 'unavailable': return 'danger';
            case 'draft': return 'warning';
            case 'archived': return 'info';
            default: return null;
        }
    };

    // Column Templates
    const imageTemplate = (rowData: Product) => {
        if (rowData.thumb_url) {
            return (
                <img
                    src={rowData.thumb_url}
                    alt={rowData.name}
                    className="w-12 h-12 object-cover rounded border border-slate-200"
                />
            );
        }
        return (
            <div className="w-12 h-12 bg-slate-100 flex items-center justify-center rounded border border-slate-200 text-slate-400">
                <i className="pi pi-image" />
            </div>
        );
    };

    const priceTemplate = (rowData: Product) => {
        return <span className="font-mono">{parseFloat(rowData.price).toFixed(2)} €</span>;
    };

    const stockTemplate = (rowData: Product) => {
        return (
            <div className="flex flex-col text-sm font-mono">
                <span>Total: {rowData.stock_quantity}</span>
                <span className="text-amber-600 text-xs">Rés.: {rowData.reserved_quantity}</span>
                <span className="text-emerald-600 font-semibold">Dispo: {rowData.available_quantity}</span>
            </div>
        );
    };

    const statusTemplate = (rowData: Product) => {
        return <Tag value={rowData.status.toUpperCase()} severity={getStatusSeverity(rowData.status)} />;
    };

    const actionsTemplate = (rowData: Product) => {
        return (
            <div className="flex gap-2">
                <Button
                    icon="pi pi-pencil"
                    className="p-button-rounded p-button-text p-button-secondary"
                    onClick={() => openEditDialog(rowData)}
                />
                <Button
                    icon="pi pi-trash"
                    className="p-button-rounded p-button-text p-button-danger"
                    onClick={() => handleDelete(rowData)}
                />
            </div>
        );
    };

    return (
        <AdminLayout title="Gestion du catalogue de produits">
            <Head title="Gérer les produits" />

            <div className="mb-4 flex justify-between items-center">
                <p className="text-slate-600 text-sm">Gérez les produits du catalogue, les quantités de stock, et les images.</p>
                <Button
                    label="Ajouter un produit"
                    icon="pi pi-plus"
                    onClick={openCreateDialog}
                    className="p-button-sm bg-blue-600 border-none text-white hover:bg-blue-700"
                />
            </div>

            <div className="card">
                <DataTable value={products} paginator rows={10} responsiveLayout="scroll">
                    <Column body={imageTemplate} header="Image" />
                    <Column field="reference" header="Référence" sortable className="font-mono" />
                    <Column field="name" header="Nom" sortable />
                    <Column field="category_name" header="Catégorie" sortable />
                    <Column field="price" header="Prix" body={priceTemplate} sortable />
                    <Column field="stock_quantity" header="Stock" body={stockTemplate} sortable />
                    <Column field="status" header="Statut" body={statusTemplate} sortable />
                    <Column body={actionsTemplate} header="Actions" style={{ width: '8rem' }} />
                </DataTable>
            </div>

            {/* Create/Edit Dialog */}
            <Dialog
                header={editMode ? "Modifier le produit" : "Ajouter un produit"}
                visible={dialogVisible}
                style={{ width: '500px' }}
                onHide={() => setDialogVisible(false)}
                footer={
                    <div>
                        <Button label="Annuler" icon="pi pi-times" onClick={() => setDialogVisible(false)} className="p-button-text" />
                        <Button
                            label={editMode ? "Enregistrer" : "Créer"}
                            icon="pi pi-check"
                            onClick={handleSubmit}
                            loading={processing}
                            className="p-button-primary bg-blue-600 border-none text-white hover:bg-blue-700"
                        />
                    </div>
                }
            >
                <form onSubmit={handleSubmit} className="flex flex-col gap-4 pt-2">
                    <div className="grid grid-cols-2 gap-4">
                        <div className="flex flex-col gap-1">
                            <label htmlFor="reference" className="font-semibold text-slate-700">Référence</label>
                            <InputText
                                id="reference"
                                value={data.reference}
                                onChange={(e) => setData('reference', e.target.value)}
                                className="w-full border border-slate-300 rounded p-2 font-mono"
                                required
                            />
                            {errors.reference && <span className="text-red-500 text-xs">{errors.reference}</span>}
                        </div>

                        <div className="flex flex-col gap-1">
                            <label htmlFor="category_id" className="font-semibold text-slate-700">Catégorie</label>
                            <Dropdown
                                id="category_id"
                                value={data.category_id}
                                options={categories}
                                optionLabel="name"
                                optionValue="id"
                                placeholder="Sélectionner..."
                                onChange={(e) => setData('category_id', e.value)}
                                className="w-full border border-slate-300 rounded"
                                required
                            />
                            {errors.category_id && <span className="text-red-500 text-xs">{errors.category_id}</span>}
                        </div>
                    </div>

                    <div className="flex flex-col gap-1">
                        <label htmlFor="name" className="font-semibold text-slate-700">Nom du produit</label>
                        <InputText
                            id="name"
                            value={data.name}
                            onChange={(e) => setData('name', e.target.value)}
                            className="w-full border border-slate-300 rounded p-2"
                            required
                        />
                        {errors.name && <span className="text-red-500 text-xs">{errors.name}</span>}
                    </div>

                    <div className="grid grid-cols-2 gap-4">
                        <div className="flex flex-col gap-1">
                            <label htmlFor="price" className="font-semibold text-slate-700">Prix (€)</label>
                            <InputNumber
                                id="price"
                                value={data.price}
                                onValueChange={(e) => setData('price', e.value || 0)}
                                mode="decimal"
                                minFractionDigits={2}
                                maxFractionDigits={2}
                                inputClassName="w-full border border-slate-300 rounded p-2"
                                required
                            />
                            {errors.price && <span className="text-red-500 text-xs">{errors.price}</span>}
                        </div>

                        <div className="flex flex-col gap-1">
                            <label htmlFor="stock_quantity" className="font-semibold text-slate-700">Quantité en stock</label>
                            <InputNumber
                                id="stock_quantity"
                                value={data.stock_quantity}
                                onValueChange={(e) => setData('stock_quantity', e.value || 0)}
                                useGrouping={false}
                                inputClassName="w-full border border-slate-300 rounded p-2"
                                required
                            />
                            {errors.stock_quantity && <span className="text-red-500 text-xs">{errors.stock_quantity}</span>}
                        </div>
                    </div>

                    <div className="flex flex-col gap-1">
                        <label htmlFor="status" className="font-semibold text-slate-700">Statut</label>
                        <Dropdown
                            id="status"
                            value={data.status}
                            options={[
                                { label: 'Disponible', value: 'available' },
                                { label: 'Indisponible', value: 'unavailable' },
                                { label: 'Brouillon', value: 'draft' },
                                { label: 'Archivé', value: 'archived' }
                            ]}
                            onChange={(e) => setData('status', e.value)}
                            className="w-full border border-slate-300 rounded"
                        />
                        {errors.status && <span className="text-red-500 text-xs">{errors.status}</span>}
                    </div>

                    <div className="flex flex-col gap-1">
                        <label htmlFor="description" className="font-semibold text-slate-700">Description</label>
                        <InputTextarea
                            id="description"
                            value={data.description}
                            onChange={(e) => setData('description', e.target.value)}
                            rows={3}
                            className="w-full border border-slate-300 rounded p-2"
                        />
                        {errors.description && <span className="text-red-500 text-xs">{errors.description}</span>}
                    </div>

                    <div className="flex flex-col gap-1">
                        <label htmlFor="image" className="font-semibold text-slate-700">Image du produit</label>
                        <input
                            id="image"
                            type="file"
                            accept="image/*"
                            onChange={(e) => {
                                const files = e.target.files;
                                if (files && files.length > 0) {
                                    setData('image', files[0]);
                                }
                            }}
                            className="w-full border border-slate-300 rounded p-2 text-sm text-slate-500 file:mr-4 file:py-1 file:px-3 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100"
                        />
                        {errors.image && <span className="text-red-500 text-xs">{errors.image}</span>}
                        {editMode && selectedProduct?.image_url && !data.image && (
                            <span className="text-xs text-slate-400">Une image existe déjà. Laissez vide pour la conserver.</span>
                        )}
                    </div>
                </form>
            </Dialog>
        </AdminLayout>
    );
}
