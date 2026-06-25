import clsx from 'clsx';
import { ElementType } from 'react';

type CardProps = {
    as?: ElementType;
    padding?: 'none' | 'sm' | 'md' | 'lg';
    interactive?: boolean;
    className?: string;
    [key: string]: unknown;
};

const paddingClasses = {
    none: '',
    sm: 'p-4',
    md: 'p-5 md:p-6',
    lg: 'p-6 md:p-8',
};

export default function Card({
    as: Component = 'div',
    padding = 'md',
    interactive = false,
    className,
    ...props
}: CardProps) {
    return (
        <Component
            {...props}
            className={clsx(
                'artt-glass rounded-[1.5rem]',
                paddingClasses[padding],
                interactive && 'transition duration-300 ease-[var(--artt-ease)] hover:-translate-y-1 hover:bg-white/[0.06]',
                className,
            )}
        />
    );
}
