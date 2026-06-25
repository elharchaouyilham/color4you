import React, { useState } from 'react';
import { Head, useForm } from '@inertiajs/react';
import AdminLayout from '@/Layouts/AdminLayout';
import { DataTable } from 'primereact/datatable';
import { Column } from 'primereact/column';
import { Button } from 'primereact/button';
import { Dialog } from 'primereact/dialog';
import { InputText } from 'primereact/inputtext';
import { InputTextarea } from 'primereact/inputtextarea';
import { Dropdown } from 'primereact/dropdown';
import { Checkbox } from 'primereact/checkbox';
import { Tag } from 'primereact/tag';

interface Category {
    id: number;
    name: string;
    slug: string;
    description: string | null;
    parent_id: number | null;
    parent?: { id: number; name: string } | null;
    type: 'product' | 'session';
    is_active: boolean;
}

interface ParentOption {
    id: number;
    name: string;
    type: string;
}

interface Props {
    categories: Category[];
    parentOptions: ParentOption[];
}

export default function CategoriesIndex({ categories, parentOptions }: Props) {
    const [dialogVisible, setDialogVisible] = useState(false);
    const [editMode, setEditMode] = useState(false);
    const [selectedCategory, setSelectedCategory] = useState<Category | null>(null);

    const { data, setData, post, put, delete: destroy, errors, reset, processing } = useForm({
        name: '',
        description: '',
        parent_id: null as number | null,
        type: 'product' as 'product' | 'session',
        is_active: true
    });

    const openCreateDialog = () => {
        reset();
        setEditMode(false);
        setSelectedCategory(null);
        setDialogVisible(true);
    };

    const openEditDialog = (category: Category) => {
        setEditMode(true);
        setSelectedCategory(category);
        setData({
            name: category.name,
            description: category.description || '',
            parent_id: category.parent_id,
            type: category.type,
            is_active: category.is_active
        });
        setDialogVisible(true);
    };

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        if (editMode && selectedCategory) {
            put(route('admin.categories.update', selectedCategory.id), {
                onSuccess: () => {
                    setDialogVisible(false);
                    reset();
                }
            });
        } else {
            post(route('admin.categories.store'), {
                onSuccess: () => {
                    setDialogVisible(false);
                    reset();
                }
            });
        }
    };

    const handleDelete = (category: Category) => {
        if (confirm(`Êtes-vous sûr de vouloir supprimer la catégorie "${category.name}" ?`)) {
            destroy(route('admin.categories.destroy', category.id));
        }
    };

    // Filter parent categories depending on the selected type
    const filteredParentOptions = parentOptions.filter(
        opt => opt.type === data.type && (!editMode || opt.id !== selectedCategory?.id)
    );

    const activeTemplate = (rowData: Category) => {
        return (
            <Tag
                value={rowData.is_active ? 'Actif' : 'Inactif'}
                severity={rowData.is_active ? 'success' : 'danger'}
            />
        );
    };

    const typeTemplate = (rowData: Category) => {
        return (
            <Tag
                value={rowData.type === 'product' ? 'Produit' : 'Session'}
                severity={rowData.type === 'product' ? 'info' : 'warning'}
            />
        );
    };

    const actionsTemplate = (rowData: Category) => {
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
        <AdminLayout title="Gestion des catégories">
            <Head title="Gérer les catégories" />

            <div className="mb-4 flex justify-between items-center">
                <p className="text-slate-600 text-sm">Gérez les catégories de produits et d'ateliers.</p>
                <Button
                    label="Ajouter une catégorie"
                    icon="pi pi-plus"
                    onClick={openCreateDialog}
                    className="p-button-sm bg-blue-600 border-none text-white hover:bg-blue-700"
                />
            </div>

            <div className="card">
                <DataTable value={categories} paginator rows={10} responsiveLayout="scroll">
                    <Column field="name" header="Nom" sortable />
                    <Column field="type" header="Type" body={typeTemplate} sortable />
                    <Column field="parent.name" header="Parent" sortable body={(row) => row.parent?.name || '-'} />
                    <Column field="description" header="Description" />
                    <Column field="is_active" header="Statut" body={activeTemplate} sortable />
                    <Column body={actionsTemplate} header="Actions" style={{ width: '8rem' }} />
                </DataTable>
            </div>

            {/* Create/Edit Dialog */}
            <Dialog
                header={editMode ? "Modifier la catégorie" : "Ajouter une catégorie"}
                visible={dialogVisible}
                style={{ width: '450px' }}
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
                    <div className="flex flex-col gap-1">
                        <label htmlFor="name" className="font-semibold text-slate-700">Nom</label>
                        <InputText
                            id="name"
                            value={data.name}
                            onChange={(e) => setData('name', e.target.value)}
                            className="w-full border border-slate-300 rounded p-2"
                            required
                        />
                        {errors.name && <span className="text-red-500 text-xs">{errors.name}</span>}
                    </div>

                    <div className="flex flex-col gap-1">
                        <label htmlFor="type" className="font-semibold text-slate-700">Type de catégorie</label>
                        <Dropdown
                            id="type"
                            value={data.type}
                            options={[
                                { label: 'Produits du catalogue', value: 'product' },
                                { label: 'Sessions de dessin / Ateliers', value: 'session' }
                            ]}
                            onChange={(e) => {
                                setData(prev => ({
                                    ...prev,
                                    type: e.value,
                                    parent_id: null // Reset parent if type changes
                                }));
                            }}
                            className="w-full border border-slate-300 rounded"
                            disabled={editMode}
                        />
                        {errors.type && <span className="text-red-500 text-xs">{errors.type}</span>}
                    </div>

                    <div className="flex flex-col gap-1">
                        <label htmlFor="parent_id" className="font-semibold text-slate-700">Catégorie parente (Optionnel)</label>
                        <Dropdown
                            id="parent_id"
                            value={data.parent_id}
                            options={filteredParentOptions}
                            optionLabel="name"
                            optionValue="id"
                            placeholder="Aucune"
                            onChange={(e) => setData('parent_id', e.value)}
                            showClear
                            className="w-full border border-slate-300 rounded"
                        />
                        {errors.parent_id && <span className="text-red-500 text-xs">{errors.parent_id}</span>}
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

                    <div className="flex items-center gap-2">
                        <Checkbox
                            id="is_active"
                            checked={data.is_active}
                            onChange={(e) => setData('is_active', e.checked || false)}
                        />
                        <label htmlFor="is_active" className="font-semibold text-slate-700 select-none">Catégorie active</label>
                        {errors.is_active && <span className="text-red-500 text-xs">{errors.is_active}</span>}
                    </div>
                </form>
            </Dialog>
        </AdminLayout>
    );
}
