import ClientLayout from '@/Layouts/ClientLayout';
import Card from '@/Components/UI/Card';
import ButtonLink from '@/Components/UI/Button';
import { Head, router } from '@inertiajs/react';
import { DataTable } from 'primereact/datatable';
import { Column } from 'primereact/column';
import { Button } from 'primereact/button';

type Reservation = {
    id: number;
    status: string;
    reserved_at: string | null;
    pickup_due_at: string | null;
    cancelled_at: string | null;
    can_cancel: boolean;
    products: Array<{
        id: number;
        name: string;
        slug: string;
        quantity: number;
        category_name: string | null;
    }>;
};

type Registration = {
    id: number;
    status: string;
    registered_at: string | null;
    cancelled_at: string | null;
    can_cancel: boolean;
    session: {
        title: string | null;
        slug: string | null;
        starts_at: string | null;
        category_name: string | null;
    };
};

export default function ClientDashboard({
    reservations,
    registrations,
}: {
    reservations: Reservation[];
    registrations: Registration[];
}) {
    // Templates for Reservations DataTable
    const productsTemplate = (rowData: Reservation) => (
        <div className="space-y-1">
            {rowData.products.map(p => (
                <div key={p.id} className="text-sm">
                    <span className="font-medium text-[var(--artt-fg)]">{p.name}</span>
                    <span className="ml-1 text-xs text-[var(--artt-muted)]">({p.category_name})</span>
                    <span className="ml-2 rounded-full border border-[rgba(238,188,93,0.22)] bg-white/5 px-2 py-0.5 font-mono text-xs font-semibold text-[var(--artt-amber-2)]">x{p.quantity}</span>
                </div>
            ))}
        </div>
    );

    const reservationStatusTemplate = (rowData: Reservation) => (
        <span className="capitalize text-slate-700">
            {rowData.status.replace('_', ' ')}
        </span>
    );

    const reservationDueTemplate = (rowData: Reservation) => (
        <span className="text-slate-700">
            {rowData.pickup_due_at ? new Date(rowData.pickup_due_at).toLocaleString() : '-'}
        </span>
    );

    const reservationActionTemplate = (rowData: Reservation) => {
        if (!rowData.can_cancel) return null;
        return (
            <Button
                type="button"
                severity="danger"
                outlined
                label="Annuler"
                onClick={() => router.post(route('account.reservations.cancel', rowData.id))}
                className="p-button-sm border-rose-300 text-rose-600 hover:bg-rose-50 px-3 py-1.5 text-xs font-medium rounded-md"
            />
        );
    };

    // Templates for Registrations DataTable
    const sessionTemplate = (rowData: Registration) => (
        <div>
            <div className="font-medium text-[var(--artt-fg)]">{rowData.session.title}</div>
            <div className="text-[var(--artt-muted)]">{rowData.session.category_name}</div>
        </div>
    );

    const sessionDateTemplate = (rowData: Registration) => (
        <span className="text-slate-700">
            {rowData.session.starts_at ? new Date(rowData.session.starts_at).toLocaleString() : '-'}
        </span>
    );

    const registrationStatusTemplate = (rowData: Registration) => (
        <span className="capitalize text-slate-700">{rowData.status}</span>
    );

    const registrationActionTemplate = (rowData: Registration) => {
        if (!rowData.can_cancel) return null;
        return (
            <Button
                type="button"
                severity="danger"
                outlined
                label="Annuler"
                onClick={() => router.post(route('account.registrations.cancel', rowData.id))}
                className="p-button-sm border-rose-300 text-rose-600 hover:bg-rose-50 px-3 py-1.5 text-xs font-medium rounded-md"
            />
        );
    };

    return (
        <ClientLayout title="Mon espace client">
            <Head title="Mon espace" />

            <div className="space-y-8">
                <section>
                    <div className="mb-4 flex items-center justify-between gap-4">
                        <div>
                            <h2 className="text-xl font-semibold text-[var(--artt-fg)]">Reservations produits</h2>
                            <p className="mt-1 text-sm text-[var(--artt-muted)]">Suivi des demandes en attente, confirmees ou annulees.</p>
                        </div>
                        <ButtonLink href={route('catalog.index')} variant="ghost" size="sm">
                            Nouvelle reservation
                        </ButtonLink>
                    </div>
                    <Card padding="none" className="overflow-hidden">
                        <DataTable
                            value={reservations}
                            className="p-datatable-sm"
                            responsiveLayout="scroll"
                            emptyMessage="Aucune reservation trouvee."
                        >
                            <Column header="Produits" body={productsTemplate} className="px-4 py-4" />
                            <Column header="Statut" body={reservationStatusTemplate} className="px-4 py-4" />
                            <Column header="Echeance" body={reservationDueTemplate} className="px-4 py-4" />
                            <Column body={reservationActionTemplate} className="px-4 py-4 text-right" />
                        </DataTable>
                    </Card>
                </section>

                <section>
                    <div className="mb-4 flex items-center justify-between gap-4">
                        <div>
                            <h2 className="text-xl font-semibold text-[var(--artt-fg)]">Inscriptions ateliers</h2>
                            <p className="mt-1 text-sm text-[var(--artt-muted)]">Historique des places reservees et annulations avant delai.</p>
                        </div>
                        <ButtonLink href={route('sessions.index')} variant="ghost" size="sm">
                            Voir les ateliers
                        </ButtonLink>
                    </div>
                    <Card padding="none" className="overflow-hidden">
                        <DataTable
                            value={registrations}
                            className="p-datatable-sm"
                            responsiveLayout="scroll"
                            emptyMessage="Aucune inscription trouvee."
                        >
                            <Column header="Session" body={sessionTemplate} className="px-4 py-4" />
                            <Column header="Date" body={sessionDateTemplate} className="px-4 py-4" />
                            <Column header="Statut" body={registrationStatusTemplate} className="px-4 py-4" />
                            <Column body={registrationActionTemplate} className="px-4 py-4 text-right" />
                        </DataTable>
                    </Card>
                </section>
            </div>
        </ClientLayout>
    );
}
