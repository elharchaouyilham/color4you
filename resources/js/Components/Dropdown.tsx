import { useState, createContext, useContext, PropsWithChildren, Dispatch, SetStateAction } from 'react';
import { Link, InertiaLinkProps } from '@inertiajs/react';

const DropDownContext = createContext<{
    open: boolean;
    setOpen: Dispatch<SetStateAction<boolean>>;
    toggleOpen: () => void;
} | null>(null);

export default function Dropdown({ children }: PropsWithChildren) {
    const [open, setOpen] = useState(false);

    const toggleOpen = () => {
        setOpen((previousState) => !previousState);
    };

    return (
        <DropDownContext.Provider value={{ open, setOpen, toggleOpen }}>
            <div className="relative">{children}</div>
        </DropDownContext.Provider>
    );
}

const Trigger = ({ children }: PropsWithChildren) => {
    const context = useContext(DropDownContext);
    if (!context) throw new Error('Trigger must be used within a Dropdown');

    return (
        <>
            <div onClick={context.toggleOpen}>{children}</div>

            {context.open && (
                <div
                    className="fixed inset-0 z-40"
                    onClick={() => context.setOpen(false)}
                />
            )}
        </>
    );
};

const Content = ({
    align = 'right',
    width = '48',
    contentClasses = 'py-1 bg-white',
    children,
}: PropsWithChildren<{
    align?: 'left' | 'right';
    width?: '48';
    contentClasses?: string;
}>) => {
    const context = useContext(DropDownContext);
    if (!context) throw new Error('Content must be used within a Dropdown');

    const alignmentClasses =
        align === 'left'
            ? 'ltr:origin-top-left rtl:origin-top-right start-0'
            : 'ltr:origin-top-right rtl:origin-top-left end-0';

    const widthClasses = width === '48' ? 'w-48' : '';

    if (!context.open) return null;

    return (
        <div
            className={`absolute z-50 mt-2 rounded-md shadow-lg ${alignmentClasses} ${widthClasses}`}
            onClick={() => context.setOpen(false)}
        >
            <div
                className={
                    `rounded-md ring-1 ring-black ring-opacity-5 ` + contentClasses
                }
            >
                {children}
            </div>
        </div>
    );
};

const DropdownLink = ({ className = '', children, ...props }: InertiaLinkProps) => {
    return (
        <Link
            {...props}
            className={
                'block w-full px-4 py-2 text-start text-sm leading-5 text-gray-700 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 transition duration-150 ease-in-out ' +
                className
            }
        >
            {children}
        </Link>
    );
};

Dropdown.Trigger = Trigger;
Dropdown.Content = Content;
Dropdown.Link = DropdownLink;
