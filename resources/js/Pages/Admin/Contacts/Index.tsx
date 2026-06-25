import React from 'react';
import { Head, router } from '@inertiajs/react';
import AdminLayout from '@/Layouts/AdminLayout';
import { DataTable } from 'primereact/datatable';
import { Column } from 'primereact/column';
import { Button } from 'primereact/button';
import { Tag } from 'primereact/tag';

interface ContactMessage {
    id: number;
    name: string;
    email: string;
    phone: string | null;
    subject: string;
    message: string;
    status: string;
    created_at: string;
    read_at: string | null;
}

interface Props {
    contacts: ContactMessage[];
}

export default function ContactsIndex({ contacts }: Props) {
    const handleResolve = (id: number) => {
        router.post(route('admin.contacts.resolve', id));
    };

    const getStatusSeverity = (status: string) => {
        switch (status) {
            case 'new': return 'warning';
            case 'read': return 'info';
            case 'closed': return 'success';
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
    const senderTemplate = (rowData: ContactMessage) => {
        return (
            <div className="flex flex-col text-sm">
                <span className="font-semibold text-slate-900">{rowData.name}</span>
                <span className="text-slate-500 text-xs">{rowData.email}</span>
                {rowData.phone && <span className="text-slate-500 text-xs">{rowData.phone}</span>}
            </div>
        );
    };

    const messageTemplate = (rowData: ContactMessage) => {
        return (
            <div className="flex flex-col gap-1 max-w-md py-1">
                <span className="font-bold text-slate-800 text-sm">{rowData.subject}</span>
                <span className="text-slate-600 text-sm whitespace-pre-line leading-relaxed">{rowData.message}</span>
            </div>
        );
    };

    const statusTemplate = (rowData: ContactMessage) => {
        return <Tag value={rowData.status.toUpperCase()} severity={getStatusSeverity(rowData.status)} />;
    };

    const dateTemplate = (rowData: ContactMessage) => {
        return (
            <div className="flex flex-col text-xs text-slate-500">
                <span>Reçu: {formatDateTime(rowData.created_at)}</span>
                {rowData.read_at && <span className="text-slate-400">Lu: {formatDateTime(rowData.read_at)}</span>}
            </div>
        );
    };

    const actionsTemplate = (rowData: ContactMessage) => {
        if (rowData.status !== 'closed') {
            return (
                <Button
                    label="Marquer résolu"
                    icon="pi pi-check"
                    className="p-button-success p-button-sm w-full"
                    onClick={() => handleResolve(rowData.id)}
                />
            );
        }
        return <span className="text-slate-400 text-xs italic text-center block">Résolu</span>;
    };

    return (
        <AdminLayout title="Messages de contact des visiteurs">
            <Head title="Messages Clients" />

            <div className="mb-4">
                <p className="text-slate-600 text-sm">Consultez les messages envoyés via le formulaire de contact public et marquez-les comme résolus après traitement.</p>
            </div>

            <div className="card">
                <DataTable value={contacts} paginator rows={10} responsiveLayout="scroll" emptyMessage="Aucun message de contact.">
                    <Column body={senderTemplate} header="Expéditeur" sortable sortField="name" />
                    <Column body={messageTemplate} header="Message" />
                    <Column body={dateTemplate} header="Dates" sortable sortField="created_at" />
                    <Column body={statusTemplate} header="Statut" sortable sortField="status" />
                    <Column body={actionsTemplate} header="Actions" style={{ width: '10rem' }} />
                </DataTable>
            </div>
        </AdminLayout>
    );
}
