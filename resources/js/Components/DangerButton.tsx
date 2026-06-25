import { ButtonHTMLAttributes } from 'react';
import Button from '@/Components/UI/Button';

export default function DangerButton({
    className = '',
    disabled,
    children,
    ...props
}: ButtonHTMLAttributes<HTMLButtonElement>) {
    return (
        <Button {...props} disabled={disabled} variant="danger" className={className}>
            {children}
        </Button>
    );
}
