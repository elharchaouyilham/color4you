import { Link } from '@inertiajs/react';
import clsx from 'clsx';
import { ButtonHTMLAttributes, ReactNode } from 'react';

type ButtonVariant = 'primary' | 'secondary' | 'ghost' | 'danger' | 'success';

const variants: Record<ButtonVariant, string> = {
    primary:
        'bg-[linear-gradient(135deg,var(--artt-amber-2),var(--artt-amber))] text-[#130c03] shadow-[0_18px_54px_rgba(238,188,93,0.22)] hover:-translate-y-0.5',
    secondary:
        'artt-glass text-[var(--artt-fg)] hover:bg-white/10 hover:-translate-y-0.5',
    ghost:
        'text-[var(--artt-muted)] hover:bg-white/5 hover:text-[var(--artt-fg)]',
    danger:
        'bg-[var(--artt-danger)] text-[#260b06] hover:-translate-y-0.5',
    success:
        'bg-[var(--artt-success)] text-[#04150c] hover:-translate-y-0.5',
};

const sizes = {
    sm: 'px-3 py-2 text-xs',
    md: 'px-4 py-2.5 text-sm',
    lg: 'px-5 py-3 text-sm',
};

type SharedProps = {
    children: ReactNode;
    className?: string;
    variant?: ButtonVariant;
    size?: keyof typeof sizes;
};

type LinkButtonProps = SharedProps & {
    href: string;
    method?: 'get' | 'post' | 'put' | 'patch' | 'delete';
    as?: 'a' | 'button';
    disabled?: boolean;
    [key: string]: unknown;
};

type NativeButtonProps = SharedProps &
    ButtonHTMLAttributes<HTMLButtonElement> & {
        href?: undefined;
    };

export default function Button({
    children,
    className,
    variant = 'primary',
    size = 'md',
    ...props
}: LinkButtonProps | NativeButtonProps) {
    const classes = clsx(
        'inline-flex items-center justify-center gap-2 rounded-full font-bold transition duration-200 ease-[var(--artt-ease)] disabled:cursor-not-allowed disabled:opacity-50',
        sizes[size],
        variants[variant],
        className,
    );

    if ('href' in props && props.href) {
        return (
            <Link {...(props as any)} className={classes}>
                {children}
            </Link>
        );
    }

    return (
        <button {...(props as NativeButtonProps)} className={classes}>
            {children}
        </button>
    );
}
