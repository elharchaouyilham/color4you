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

interface Session {
    id: number;
    trainer_profile_id: number;
    trainer_name: string;
    category_id: number;
    category_name: string;
    title: string;
    slug: string;
    description: string | null;
    starts_at: string;
    ends_at: string;
    capacity: number;
    registered_count: number;
    price: string;
    status: string;
    cover_url: string | null;
    trainer_response_note: string | null;
    trainer_responded_at: string | null;
}

interface TrainerOption {
    id: number;
    name: string;
}

interface CategoryOption {
    id: number;
    name: string;
}

interface Props {
    sessions: Session[];
    trainers: TrainerOption[];
    categories: CategoryOption[];
}

export default function SessionsIndex({ sessions, trainers, categories }: Props) {
    const [dialogVisible, setDialogVisible] = useState(false);
    const [editMode, setEditMode] = useState(false);
    const [selectedSession, setSelectedSession] = useState<Session | null>(null);

    const { data, setData, post, delete: destroy, errors, reset, processing } = useForm({
        trainer_profile_id: '' as number | string,
        category_id: '' as number | string,
        title: '',
        description: '',
        starts_at: '',
        ends_at: '',
        capacity: 10 as number,
        price: 0 as number,
        status: 'draft',
        image: null as File | null,
        _method: 'POST'
    });

    const openCreateDialog = () => {
        reset();
        setEditMode(false);
        setSelectedSession(null);
        setData(prev => ({
            ...prev,
            starts_at: '',
            ends_at: '',
            _method: 'POST'
        }));
        setDialogVisible(true);
    };

    const formatDateForInput = (isoString: string) => {
        if (!isoString) return '';
        const d = new Date(isoString);
        // Format to YYYY-MM-DDTHH:MM (local timezone compatible)
        const pad = (num: number) => String(num).padStart(2, '0');
        return `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}T${pad(d.getHours())}:${pad(d.getMinutes())}`;
    };

    const openEditDialog = (session: Session) => {
        setEditMode(true);
        setSelectedSession(session);
        setData({
            trainer_profile_id: session.trainer_profile_id,
            category_id: session.category_id,
            title: session.title,
            description: session.description || '',
            starts_at: formatDateForInput(session.starts_at),
            ends_at: formatDateForInput(session.ends_at),
            capacity: session.capacity,
            price: parseFloat(session.price),
            status: session.status,
            image: null,
            _method: 'PUT'
        });
        setDialogVisible(true);
    };

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        
        // Format dates correctly before submission
        const payload = { ...data };
        if (payload.starts_at) payload.starts_at = new Date(payload.starts_at).toISOString();
        if (payload.ends_at) payload.ends_at = new Date(payload.ends_at).toISOString();

        if (editMode && selectedSession) {
            post(route('admin.sessions.update_post', selectedSession.slug), {
                forceFormData: true,
                onSuccess: () => {
                    setDialogVisible(false);
                    reset();
                }
            });
        } else {
            post(route('admin.sessions.store'), {
                forceFormData: true,
                onSuccess: () => {
                    setDialogVisible(false);
                    reset();
                }
            });
        }
    };

    const handleDelete = (session: Session) => {
        if (confirm(`Êtes-vous sûr de vouloir supprimer la session "${session.title}" ?`)) {
            destroy(route('admin.sessions.destroy', session.slug));
        }
    };

    const getStatusSeverity = (status: string) => {
        switch (status) {
            case 'open': return 'success';
            case 'pending_trainer': return 'warning';
            case 'trainer_refused': return 'danger';
            case 'draft': return 'secondary';
            case 'full': return 'info';
            case 'completed': return 'success';
            case 'cancelled': return 'danger';
            default: return null;
        }
    };

    const formatDateTime = (isoString: string) => {
        return new Date(isoString).toLocaleString('fr-FR', {
            dateStyle: 'medium',
            timeStyle: 'short'
        });
    };

    // Column Templates
    const imageTemplate = (rowData: Session) => {
        if (rowData.cover_url) {
            return (
                <img
                    src={rowData.cover_url}
                    alt={rowData.title}
                    className="w-12 h-8 object-cover rounded border border-slate-200"
                />
            );
        }
        return (
            <div className="w-12 h-8 bg-slate-100 flex items-center justify-center rounded border border-slate-200 text-slate-400">
                <i className="pi pi-calendar" />
            </div>
        );
    };

    const dateTemplate = (rowData: Session) => {
        return (
            <div className="flex flex-col text-xs text-slate-700">
                <span><span className="font-semibold">Début:</span> {formatDateTime(rowData.starts_at)}</span>
                <span><span className="font-semibold">Fin:</span> {formatDateTime(rowData.ends_at)}</span>
            </div>
        );
    };

    const capacityTemplate = (rowData: Session) => {
        return (
            <span className="font-mono">
                {rowData.registered_count} / {rowData.capacity}
            </span>
        );
    };

    const priceTemplate = (rowData: Session) => {
        return <span className="font-mono">{parseFloat(rowData.price).toFixed(2)} €</span>;
    };

    const statusTemplate = (rowData: Session) => {
        return <Tag value={rowData.status.toUpperCase()} severity={getStatusSeverity(rowData.status)} />;
    };

    const actionsTemplate = (rowData: Session) => {
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
        <AdminLayout title="Gestion des ateliers de dessin">
            <Head title="Gérer les ateliers" />

            <div className="mb-4 flex justify-between items-center">
                <p className="text-slate-600 text-sm">Créez et configurez les ateliers, affectez des enseignants et gérez les inscriptions.</p>
                <Button
                    label="Planifier un atelier"
                    icon="pi pi-plus"
                    onClick={openCreateDialog}
                    className="p-button-sm bg-blue-600 border-none text-white hover:bg-blue-700"
                />
            </div>

            <div className="card">
                <DataTable value={sessions} paginator rows={10} responsiveLayout="scroll" emptyMessage="Aucun atelier planifié.">
                    <Column body={imageTemplate} header="Image" />
                    <Column field="title" header="Titre" sortable />
                    <Column field="category_name" header="Catégorie" sortable />
                    <Column field="trainer_name" header="Enseignant" sortable />
                    <Column body={dateTemplate} header="Horaires" />
                    <Column field="capacity" header="Inscriptions" body={capacityTemplate} sortable />
                    <Column field="price" header="Tarif" body={priceTemplate} sortable />
                    <Column field="status" header="Statut" body={statusTemplate} sortable />
                    <Column body={actionsTemplate} header="Actions" style={{ width: '8rem' }} />
                </DataTable>
            </div>

            {/* Create/Edit Dialog */}
            <Dialog
                header={editMode ? "Modifier la planification de l'atelier" : "Planifier un nouvel atelier"}
                visible={dialogVisible}
                style={{ width: '550px' }}
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
                        <label htmlFor="title" className="font-semibold text-slate-700">Titre de l'atelier</label>
                        <InputText
                            id="title"
                            value={data.title}
                            onChange={(e) => setData('title', e.target.value)}
                            className="w-full border border-slate-300 rounded p-2"
                            required
                        />
                        {errors.title && <span className="text-red-500 text-xs">{errors.title}</span>}
                    </div>

                    <div className="grid grid-cols-2 gap-4">
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

                        <div className="flex flex-col gap-1">
                            <label htmlFor="trainer_profile_id" className="font-semibold text-slate-700">Enseignant</label>
                            <Dropdown
                                id="trainer_profile_id"
                                value={data.trainer_profile_id}
                                options={trainers}
                                optionLabel="name"
                                optionValue="id"
                                placeholder="Sélectionner..."
                                onChange={(e) => setData('trainer_profile_id', e.value)}
                                className="w-full border border-slate-300 rounded"
                                required
                            />
                            {errors.trainer_profile_id && <span className="text-red-500 text-xs">{errors.trainer_profile_id}</span>}
                        </div>
                    </div>

                    <div className="grid grid-cols-2 gap-4">
                        <div className="flex flex-col gap-1">
                            <label htmlFor="starts_at" className="font-semibold text-slate-700">Date et heure de début</label>
                            <input
                                id="starts_at"
                                type="datetime-local"
                                value={data.starts_at}
                                onChange={(e) => setData('starts_at', e.target.value)}
                                className="w-full border border-slate-300 rounded p-2 text-sm text-slate-700 focus:ring-1 focus:ring-blue-500"
                                required
                            />
                            {errors.starts_at && <span className="text-red-500 text-xs">{errors.starts_at}</span>}
                        </div>

                        <div className="flex flex-col gap-1">
                            <label htmlFor="ends_at" className="font-semibold text-slate-700">Date et heure de fin</label>
                            <input
                                id="ends_at"
                                type="datetime-local"
                                value={data.ends_at}
                                onChange={(e) => setData('ends_at', e.target.value)}
                                className="w-full border border-slate-300 rounded p-2 text-sm text-slate-700 focus:ring-1 focus:ring-blue-500"
                                required
                            />
                            {errors.ends_at && <span className="text-red-500 text-xs">{errors.ends_at}</span>}
                        </div>
                    </div>

                    <div className="grid grid-cols-2 gap-4">
                        <div className="flex flex-col gap-1">
                            <label htmlFor="price" className="font-semibold text-slate-700">Tarif (€)</label>
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
                            <label htmlFor="capacity" className="font-semibold text-slate-700">Capacité max.</label>
                            <InputNumber
                                id="capacity"
                                value={data.capacity}
                                onValueChange={(e) => setData('capacity', e.value || 1)}
                                useGrouping={false}
                                inputClassName="w-full border border-slate-300 rounded p-2"
                                required
                            />
                            {errors.capacity && <span className="text-red-500 text-xs">{errors.capacity}</span>}
                        </div>
                    </div>

                    <div className="flex flex-col gap-1">
                        <label htmlFor="status" className="font-semibold text-slate-700">Statut</label>
                        <Dropdown
                            id="status"
                            value={data.status}
                            options={[
                                { label: 'Brouillon', value: 'draft' },
                                { label: 'En attente de l\'enseignant', value: 'pending_trainer' },
                                { label: 'Réfusé par l\'enseignant', value: 'trainer_refused' },
                                { label: 'Ouvert aux inscriptions', value: 'open' },
                                { label: 'Complet', value: 'full' },
                                { label: 'Terminé', value: 'completed' },
                                { label: 'Annulé', value: 'cancelled' }
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
                        <label htmlFor="image" className="font-semibold text-slate-700">Image de couverture</label>
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
                        {editMode && selectedSession?.cover_url && !data.image && (
                            <span className="text-xs text-slate-400">Une image existe déjà. Laissez vide pour la conserver.</span>
                        )}
                    </div>
                </form>
            </Dialog>
        </AdminLayout>
    );
}
