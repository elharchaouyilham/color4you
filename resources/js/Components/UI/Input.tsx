import clsx from 'clsx';
import { forwardRef, InputHTMLAttributes, SelectHTMLAttributes, TextareaHTMLAttributes } from 'react';

export const Input = forwardRef<HTMLInputElement, InputHTMLAttributes<HTMLInputElement>>(function Input(
    { className, ...props },
    ref,
) {
    return <input {...props} ref={ref} className={clsx('artt-input text-sm', className)} />;
});

export function Select({ className, ...props }: SelectHTMLAttributes<HTMLSelectElement>) {
    return <select {...props} className={clsx('artt-input text-sm', className)} />;
}

export function Textarea({ className, ...props }: TextareaHTMLAttributes<HTMLTextAreaElement>) {
    return <textarea {...props} className={clsx('artt-input text-sm', className)} />;
}

export function Field({
    label,
    htmlFor,
    error,
    children,
}: {
    label: string;
    htmlFor?: string;
    error?: string;
    children: React.ReactNode;
}) {
    return (
        <div>
            <label htmlFor={htmlFor} className="mb-2 block text-sm font-semibold text-[var(--artt-amber-2)]">
                {label}
            </label>
            {children}
            {error ? <p className="mt-2 text-sm text-[var(--artt-danger)]">{error}</p> : null}
        </div>
    );
}
