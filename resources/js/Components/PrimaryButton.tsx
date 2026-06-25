import { ButtonHTMLAttributes } from 'react';
import Button from '@/Components/UI/Button';

export default function PrimaryButton({
    className = '',
    disabled,
    children,
    ...props
}: ButtonHTMLAttributes<HTMLButtonElement>) {
    return (
        <Button {...props} disabled={disabled} className={className}>
            {children}
        </Button>
    );
}
