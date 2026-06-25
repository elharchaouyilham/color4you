import { Link } from '@inertiajs/react';
import clsx from 'clsx';

export type SidebarItem = {
    label: string;
    href: string;
    icon?: string;
    active: boolean;
};

export default function Sidebar({
    label,
    items,
}: {
    label?: string;
    items: SidebarItem[];
}) {
    return (
        <aside className="artt-glass rounded-[1.5rem] p-5 lg:sticky lg:top-6 lg:self-start">
            <Link href={route('home')} className="font-limelight text-3xl text-[var(--artt-amber-2)]">
                artt
            </Link>
            {label ? <div className="artt-eyebrow mt-3">{label}</div> : null}
            <nav className="mt-7 grid gap-2">
                {items.map((item) => (
                    <Link
                        key={item.href}
                        href={item.href}
                        className={clsx(
                            'flex items-center gap-3 rounded-2xl px-3 py-2.5 text-sm font-semibold transition',
                            item.active
                                ? 'bg-[linear-gradient(135deg,var(--artt-amber-2),var(--artt-amber))] text-[#130c03]'
                                : 'text-[var(--artt-muted)] hover:bg-white/5 hover:text-[var(--artt-fg)]',
                        )}
                    >
                        {item.icon ? <i className={clsx(item.icon, item.active ? 'text-[#130c03]' : 'text-[var(--artt-amber-2)]')} /> : null}
                        <span>{item.label}</span>
                    </Link>
                ))}
            </nav>
        </aside>
    );
}
