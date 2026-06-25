import { PropsWithChildren } from 'react';

export default function Modal({
    children,
    show = false,
    maxWidth = '2xl',
    closeable = true,
    onClose = () => {},
}: PropsWithChildren<{
    show: boolean;
    maxWidth?: 'sm' | 'md' | 'lg' | 'xl' | '2xl';
    closeable?: boolean;
    onClose?: () => void;
}>) {
    if (!show) return null;

    const close = () => {
        if (closeable) {
            onClose();
        }
    };

    const maxWidthClass = {
        sm: 'sm:max-w-sm',
        md: 'sm:max-w-md',
        lg: 'sm:max-w-lg',
        xl: 'sm:max-w-xl',
        '2xl': 'sm:max-w-2xl',
    }[maxWidth];

    return (
        <div className="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto px-4 py-6 sm:px-0">
            <div className="fixed inset-0 transform bg-black/70 backdrop-blur-sm transition-all" onClick={close} />

            <div
                className={`artt-glass z-50 overflow-hidden rounded-[1.5rem] shadow-xl transition-all sm:mx-auto sm:w-full ${maxWidthClass}`}
            >
                {children}
            </div>
        </div>
    );
}
