import clsx from 'clsx';
import { ReactNode } from 'react';

export default function SectionHeader({
    eyebrow,
    title,
    copy,
    action,
    className,
}: {
    eyebrow?: string;
    title: ReactNode;
    copy?: ReactNode;
    action?: ReactNode;
    className?: string;
}) {
    return (
        <div className={clsx('mb-8 flex flex-col gap-5 md:flex-row md:items-end md:justify-between', className)}>
            <div className="max-w-3xl">
                {eyebrow ? <p className="artt-eyebrow mb-3">{eyebrow}</p> : null}
                <h1 className="artt-heading text-3xl leading-tight md:text-5xl">{title}</h1>
            </div>
            {(copy || action) && (
                <div className="max-w-xl space-y-4 text-sm leading-7 text-[var(--artt-muted)] md:text-base">
                    {copy ? <p>{copy}</p> : null}
                    {action}
                </div>
            )}
        </div>
    );
}
