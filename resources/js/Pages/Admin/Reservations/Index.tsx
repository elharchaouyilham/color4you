import React from 'react';
import { Head, router } from '@inertiajs/react';
import AdminLayout from '@/Layouts/AdminLayout';
import { DataTable } from 'primereact/datatable';
import { Column } from 'primereact/column';
import { Button } from 'primereact/button';
import { Tag } from 'primereact/tag';

interface Reservation {
    id: number;
    user: {
        id: number;
        name: string;
        email: string;
        phone: string | null;
    };
    products: Array<{
        id: number;
        name: string;
        reference: string;
        quantity: number;
        available_quantity: number;
    }>;
    status: string;
    reserved_at: string;
    pickup_due_at: string | null;
    picked_up_at: string | null;
    returned_at: string | null;
    cancelled_at: string | null;
}

interface Props {
    reservations: Reservation[];
}

export default function ReservationsIndex({ reservations }: Props) {
    const handleAction = (id: number, action: 'confirm' | 'reject' | 'pickup' | 'return') => {
        router.post(route(`admin.reservations.${action}`, id));
    };

    const getStatusSeverity = (status: string) => {
        switch (status) {
            case 'pending': return 'warning';
            case 'confirmed': return 'info';
            case 'picked_up': return 'success';
            case 'returned': return 'success';
            case 'rejected': return 'danger';
            case 'cancelled': return 'danger';
            case 'expired': return 'danger';
            default: return null;
        }
    };

    const formatDateTime = (isoString: string | null) => {
        if (!isoString) return '-';
        return new Date(isoString).toLocaleString('fr-FR', {
            dateStyle: 'medium',
            timeStyle: 'short'
        });
    };

    // Column Templates
    const clientTemplate = (rowData: Reservation) => {
        return (
            <div className="flex flex-col text-sm">
                <span className="font-semibold text-slate-900">{rowData.user.name}</span>
                <span className="text-slate-500 text-xs">{rowData.user.email}</span>
                {rowData.user.phone && <span className="text-slate-500 text-xs">{rowData.user.phone}</span>}
            </div>
        );
    };

    const productsTemplate = (rowData: Reservation) => {
        return (
            <div className="space-y-2 py-1">
                {rowData.products.map(p => (
                    <div key={p.id} className="flex flex-col text-sm border-b border-slate-100 last:border-0 pb-1.5 last:pb-0">
                        <span className="font-semibold text-slate-900">{p.name}</span>
                        <span className="text-slate-500 text-xs font-mono">Réf: {p.reference}</span>
                        <div className="flex gap-2 text-xs mt-1">
                            <span className="text-amber-700 font-semibold bg-amber-50 px-1.5 rounded">Qté: {p.quantity}</span>
                            <span className="text-emerald-700 bg-emerald-50 px-1.5 rounded">Dispo: {p.available_quantity}</span>
                        </div>
                    </div>
                ))}
            </div>
        );
    };

    const datesTemplate = (rowData: Reservation) => {
        return (
            <div className="flex flex-col text-xs text-slate-600 gap-0.5">
                <span><span className="font-semibold">Réservé:</span> {formatDateTime(rowData.reserved_at)}</span>
                {rowData.pickup_due_at && <span><span className="font-semibold text-amber-700">Délai retrait:</span> {formatDateTime(rowData.pickup_due_at)}</span>}
                {rowData.picked_up_at && <span><span className="font-semibold text-emerald-700">Retiré:</span> {formatDateTime(rowData.picked_up_at)}</span>}
                {rowData.returned_at && <span><span className="font-semibold text-blue-700">Retourné:</span> {formatDateTime(rowData.returned_at)}</span>}
                {rowData.cancelled_at && <span><span className="font-semibold text-red-700">Annulé:</span> {formatDateTime(rowData.cancelled_at)}</span>}
            </div>
        );
    };

    const statusTemplate = (rowData: Reservation) => {
        return <Tag value={rowData.status.toUpperCase()} severity={getStatusSeverity(rowData.status)} />;
    };

    const actionsTemplate = (rowData: Reservation) => {
        const hasInsufficientStock = rowData.products.some(p => p.quantity > p.available_quantity);

        return (
            <div className="flex flex-col gap-1.5 w-full">
                {rowData.status === 'pending' && (
                    <div className="flex gap-1.5">
                        <Button
                            label="Confirmer"
                            icon="pi pi-check"
                            className="p-button-success p-button-sm flex-1"
                            onClick={() => handleAction(rowData.id, 'confirm')}
                            disabled={hasInsufficientStock}
                            tooltip={hasInsufficientStock ? "Stock insuffisant" : undefined}
                        />
                        <Button
                            label="Rejeter"
                            icon="pi pi-times"
                            className="p-button-danger p-button-outlined p-button-sm flex-1"
                            onClick={() => handleAction(rowData.id, 'reject')}
                        />
                    </div>
                )}
                {rowData.status === 'confirmed' && (
                    <Button
                        label="Marquer Retiré"
                        icon="pi pi-directions"
                        className="p-button-info p-button-sm w-full"
                        onClick={() => handleAction(rowData.id, 'pickup')}
                    />
                )}
                {rowData.status === 'picked_up' && (
                    <Button
                        label="Marquer Retourné"
                        icon="pi pi-undo"
                        className="p-button-success p-button-sm w-full"
                        onClick={() => handleAction(rowData.id, 'return')}
                    />
                )}
                {['returned', 'rejected', 'cancelled', 'expired'].includes(rowData.status) && (
                    <span className="text-slate-400 text-xs italic text-center py-1">Terminé</span>
                )}
            </div>
        );
    };

    return (
        <AdminLayout title="Gestion des réservations de produits">
            <Head title="Gérer les réservations" />

            <div className="mb-4">
                <p className="text-slate-600 text-sm">Gérez et validez les demandes de réservation des clients, enregistrez les retraits de matériel et les retours.</p>
            </div>

            <div className="card">
                <DataTable value={reservations} paginator rows={10} responsiveLayout="scroll" emptyMessage="Aucune réservation enregistrée.">
                    <Column body={clientTemplate} header="Client" sortable sortField="user.name" />
                    <Column body={productsTemplate} header="Produits" />
                    <Column body={datesTemplate} header="Historique & Dates" />
                    <Column body={statusTemplate} header="Statut" sortable sortField="status" />
                    <Column body={actionsTemplate} header="Actions" style={{ width: '12rem' }} />
                </DataTable>
            </div>
        </AdminLayout>
    );
}
