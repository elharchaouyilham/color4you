import Card from '@/Components/UI/Card';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head } from '@inertiajs/react';

export default function Dashboard() {
    return (
        <AuthenticatedLayout
            header={
                <h2 className="text-xl font-semibold leading-tight text-[var(--artt-fg)]">
                    Dashboard
                </h2>
            }
        >
            <Head title="Dashboard" />

            <Card>
                <div className="text-[var(--artt-fg)]">You're logged in!</div>
            </Card>
        </AuthenticatedLayout>
    );
}
