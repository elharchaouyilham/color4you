import { ButtonHTMLAttributes } from 'react';
import Button from '@/Components/UI/Button';

export default function SecondaryButton({
    type = 'button',
    className = '',
    disabled,
    children,
    ...props
}: ButtonHTMLAttributes<HTMLButtonElement>) {
    return (
        <Button {...props} type={type} disabled={disabled} variant="secondary" className={className}>
            {children}
        </Button>
    );
}
