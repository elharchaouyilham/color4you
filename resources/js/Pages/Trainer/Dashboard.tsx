import React, { useState, useRef } from 'react';
import { Head, router } from '@inertiajs/react';
import TrainerLayout from '@/Layouts/TrainerLayout';
import { DataTable } from 'primereact/datatable';
import { Column } from 'primereact/column';
import { Button } from 'primereact/button';
import { Dialog } from 'primereact/dialog';
import { Tag } from 'primereact/tag';
import { InputTextarea } from 'primereact/inputtextarea';
import axios from 'axios';

interface Session {
    id: number;
    title: string;
    slug: string;
    starts_at: string;
    ends_at: string;
    status: string;
    capacity: number;
    registered_count: number;
    category_name: string;
    trainer_response_note: string | null;
    trainer_responded_at: string | null;
}

interface Participant {
    id: number;
    status: string;
    registered_at: string;
    user: {
        id: number;
        name: string;
        email: string;
        phone: string | null;
    };
}

interface Props {
    sessions: Session[];
}

export default function TrainerDashboard({ sessions }: Props) {
    const [selectedSession, setSelectedSession] = useState<Session | null>(null);
    const [participants, setParticipants] = useState<Participant[]>([]);
    const [loadingParticipants, setLoadingParticipants] = useState(false);
    const [showParticipantsDialog, setShowParticipantsDialog] = useState(false);

    const [showRespondDialog, setShowRespondDialog] = useState(false);
    const [respondType, setRespondType] = useState<'confirm' | 'refuse'>('confirm');
    const [note, setNote] = useState('');
    const [submittingResponse, setSubmittingResponse] = useState(false);

    // Fetch participants list for modal
    const viewParticipants = async (session: Session) => {
        setSelectedSession(session);
        setLoadingParticipants(true);
        setShowParticipantsDialog(true);
        try {
            const response = await axios.get(route('trainer.sessions.participants', session.slug));
            setParticipants(response.data.participants);
        } catch (error) {
            console.error('Error fetching participants', error);
        } finally {
            setLoadingParticipants(false);
        }
    };

    // Open respond dialog
    const openRespondDialog = (session: Session, type: 'confirm' | 'refuse') => {
        setSelectedSession(session);
        setRespondType(type);
        setNote('');
        setShowRespondDialog(true);
    };

    // Submit accept/refuse proposal
    const submitResponse = () => {
        if (!selectedSession) return;
        setSubmittingResponse(true);

        router.post(route('trainer.sessions.respond', selectedSession.slug), {
            response: respondType,
            note: note
        }, {
            onSuccess: () => {
                setShowRespondDialog(false);
                setSelectedSession(null);
            },
            onFinish: () => {
                setSubmittingResponse(false);
            }
        });
    };

    // Update participant attendance status
    const updateAttendance = async (regId: number, status: string) => {
        try {
            await axios.post(route('trainer.registrations.attendance', regId), { status });
            // Update local participants array
            setParticipants(prev => prev.map(p => p.id === regId ? { ...p, status } : p));
        } catch (error) {
            console.error('Error updating attendance', error);
        }
    };

    const getStatusSeverity = (status: string) => {
        switch (status) {
            case 'open': return 'success';
            case 'pending_trainer': return 'warning';
            case 'trainer_refused': return 'danger';
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
    const dateTemplate = (rowData: Session) => {
        return <span>{formatDateTime(rowData.starts_at)}</span>;
    };

    const statusTemplate = (rowData: Session) => {
        return <Tag value={rowData.status.toUpperCase()} severity={getStatusSeverity(rowData.status)} />;
    };

    const capacityTemplate = (rowData: Session) => {
        return (
            <span className="font-mono">
                {rowData.registered_count} / {rowData.capacity}
            </span>
        );
    };

    const actionsTemplate = (rowData: Session) => {
        return (
            <div className="flex gap-2">
                {rowData.status === 'pending_trainer' && (
                    <>
                        <Button
                            icon="pi pi-check"
                            className="p-button-success p-button-sm"
                            label="Accepter"
                            onClick={() => openRespondDialog(rowData, 'confirm')}
                        />
                        <Button
                            icon="pi pi-times"
                            className="p-button-danger p-button-sm p-button-outlined"
                            label="Refuser"
                            onClick={() => openRespondDialog(rowData, 'refuse')}
                        />
                    </>
                )}
                {rowData.status !== 'pending_trainer' && rowData.status !== 'trainer_refused' && (
                    <Button
                        icon="pi pi-users"
                        className="p-button-info p-button-sm"
                        label="Participants"
                        onClick={() => viewParticipants(rowData)}
                    />
                )}
            </div>
        );
    };

    const participantAttendanceTemplate = (rowData: Participant) => {
        const statuses = [
            { label: 'Présent', value: 'attended', icon: 'pi pi-check-circle', class: 'p-button-success' },
            { label: 'Absent', value: 'absent', icon: 'pi pi-times-circle', class: 'p-button-danger' },
            { label: 'Enregistré', value: 'registered', icon: 'pi pi-info-circle', class: 'p-button-secondary' }
        ];

        return (
            <div className="flex gap-1">
                {statuses.map(s => {
                    const isActive = rowData.status === s.value;
                    return (
                        <Button
                            key={s.value}
                            icon={s.icon}
                            tooltip={s.label}
                            onClick={() => updateAttendance(rowData.id, s.value)}
                            className={`p-button-sm ${isActive ? s.class : 'p-button-text p-button-secondary'}`}
                        />
                    );
                })}
            </div>
        );
    };

    return (
        <TrainerLayout title="Tableau de bord de l'Enseignant">
            <Head title="Tableau de bord Enseignant" />

            <div className="card">
                <DataTable value={sessions} paginator rows={10} responsiveLayout="scroll" emptyMessage="Aucun atelier assigné.">
                    <Column field="title" header="Titre" sortable />
                    <Column field="category_name" header="Catégorie" sortable />
                    <Column field="starts_at" header="Date / Heure" body={dateTemplate} sortable />
                    <Column field="capacity" header="Inscrits" body={capacityTemplate} sortable />
                    <Column field="status" header="Statut" body={statusTemplate} sortable />
                    <Column body={actionsTemplate} header="Actions" style={{ minWidth: '12rem' }} />
                </DataTable>
            </div>

            {/* Proposal Response Dialog */}
            <Dialog
                header={`${respondType === 'confirm' ? 'Accepter' : 'Refuser'} la proposition d'atelier`}
                visible={showRespondDialog}
                style={{ width: '450px' }}
                onHide={() => setShowRespondDialog(false)}
                footer={
                    <div>
                        <Button label="Annuler" icon="pi pi-times" onClick={() => setShowRespondDialog(false)} className="p-button-text" />
                        <Button
                            label="Soumettre"
                            icon="pi pi-check"
                            onClick={submitResponse}
                            loading={submittingResponse}
                            className={respondType === 'confirm' ? 'p-button-success' : 'p-button-danger'}
                        />
                    </div>
                }
            >
                <div className="flex flex-col gap-2 pt-2">
                    <label htmlFor="note" className="font-semibold text-slate-700">Note ou message (Optionnel)</label>
                    <InputTextarea
                        id="note"
                        value={note}
                        onChange={(e) => setNote(e.target.value)}
                        rows={4}
                        placeholder={respondType === 'confirm' ? 'Ajoutez vos commentaires ou détails pour les participants...' : 'Précisez pourquoi vous refusez cet atelier...'}
                        className="w-full border border-slate-300 rounded p-2"
                    />
                </div>
            </Dialog>

            {/* Participants Dialog */}
            <Dialog
                header={`Participants - ${selectedSession?.title || ''}`}
                visible={showParticipantsDialog}
                style={{ width: '700px' }}
                onHide={() => setShowParticipantsDialog(false)}
            >
                <DataTable value={participants} loading={loadingParticipants} emptyMessage="Aucun participant inscrit.">
                    <Column field="user.name" header="Nom" sortable />
                    <Column field="user.email" header="Email" sortable />
                    <Column field="user.phone" header="Téléphone" />
                    <Column field="status" header="Statut" sortable body={(row) => (
                        <span className="capitalize font-semibold">{row.status}</span>
                    )} />
                    <Column body={participantAttendanceTemplate} header="Présence" />
                </DataTable>
            </Dialog>
        </TrainerLayout>
    );
}
