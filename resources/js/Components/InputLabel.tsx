import { LabelHTMLAttributes } from 'react';
import clsx from 'clsx';

export default function InputLabel({
    value,
    className = '',
    children,
    ...props
}: LabelHTMLAttributes<HTMLLabelElement> & { value?: string }) {
    return (
        <label
            {...props}
            className={clsx('block text-sm font-semibold text-[var(--artt-amber-2)]', className)}
        >
            {value ? value : children}
        </label>
    );
}
