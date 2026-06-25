import { Link } from '@inertiajs/react';
import { PropsWithChildren } from 'react';
import Card from '@/Components/UI/Card';
import CursorEffect from '@/Components/UI/CursorEffect';

export default function GuestLayout({ children }: PropsWithChildren) {
    return (
        <div className="artt-shell flex min-h-screen flex-col items-center justify-center px-4 py-8">
            <CursorEffect />
            <div className="mb-6">
                <Link href="/" className="font-limelight text-4xl text-[var(--artt-amber-2)]">
                    artt
                </Link>
            </div>

            <Card className="w-full max-w-md">
                {children}
            </Card>
        </div>
    );
}
