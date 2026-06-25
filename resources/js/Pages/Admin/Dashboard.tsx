import React from 'react';
import { Head, Link } from '@inertiajs/react';
import AdminLayout from '@/Layouts/AdminLayout';
import Card from '@/Components/UI/Card';

interface Stats {
    total_products: number;
    total_reservations: number;
    pending_reservations: number;
    total_sessions: number;
    upcoming_sessions: number;
    total_trainers: number;
    new_contacts: number;
}

interface Props {
    stats: Stats;
}

export default function AdminDashboard({ stats }: Props) {
    const cards = [
        {
            title: 'Produits',
            value: stats.total_products,
            icon: 'pi pi-box',
            color: 'bg-blue-500',
            link: route('admin.products.index'),
            linkLabel: 'Gérer le catalogue'
        },
        {
            title: 'Réservations Totales',
            value: stats.total_reservations,
            icon: 'pi pi-shopping-bag',
            color: 'bg-emerald-500',
            link: route('admin.reservations.index'),
            linkLabel: 'Gérer les réservations'
        },
        {
            title: 'Réservations en attente',
            value: stats.pending_reservations,
            icon: 'pi pi-exclamation-circle',
            color: 'bg-amber-500',
            link: route('admin.reservations.index'),
            linkLabel: 'Confirmer ou rejeter'
        },
        {
            title: 'Sessions de dessin',
            value: stats.total_sessions,
            icon: 'pi pi-calendar',
            color: 'bg-purple-500',
            link: route('admin.sessions.index'),
            linkLabel: 'Gérer les sessions'
        },
        {
            title: 'Enseignants Actifs',
            value: stats.total_trainers,
            icon: 'pi pi-users',
            color: 'bg-pink-500',
            link: route('admin.sessions.index'), // Assigned in sessions
            linkLabel: 'Voir les ateliers'
        },
        {
            title: 'Messages Clients',
            value: stats.new_contacts,
            icon: 'pi pi-envelope',
            color: 'bg-cyan-500',
            link: route('admin.contacts.index'),
            linkLabel: 'Lire les messages'
        }
    ];

    return (
        <AdminLayout title="Tableau de bord administrateur">
            <Head title="Admin Dashboard" />

            <div className="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
                {cards.map((card, idx) => (
                    <Card key={idx} className="flex flex-col justify-between overflow-hidden" interactive>
                        <div className="flex items-center justify-between">
                            <div>
                                <span className="text-sm font-medium text-slate-500">{card.title}</span>
                                <div className="mt-1 text-3xl font-bold text-[var(--artt-fg)]">{card.value}</div>
                            </div>
                            <div className={`flex h-12 w-12 items-center justify-center rounded-full text-white ${card.color}`}>
                                <i className={`${card.icon} text-xl`} />
                            </div>
                        </div>
                        <div className="mt-4 border-t border-slate-100 pt-3">
                            <Link href={card.link} className="flex items-center gap-1 text-xs font-semibold text-[var(--artt-amber-2)] hover:text-[var(--artt-fg)]">
                                {card.linkLabel} <i className="pi pi-arrow-right text-[10px]" />
                            </Link>
                        </div>
                    </Card>
                ))}
            </div>
        </AdminLayout>
    );
}
