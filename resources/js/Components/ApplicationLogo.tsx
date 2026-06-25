import { SVGAttributes } from 'react';

export default function ApplicationLogo(props: SVGAttributes<SVGElement>) {
    return (
        <svg viewBox="0 0 316 316" xmlns="http://www.w3.org/2000/svg" {...props}>
            <path d="M305.8 81.125C300.2 74.8 289 74.8 283.4 81.125L158 206.525L32.6 81.125C27 74.8 15.8 74.8 10.2 81.125C4.6 87.45 4.6 98.65 10.2 104.975L146.8 241.575C153 247.775 163 247.775 169.2 241.575L305.8 104.975C311.4 98.65 311.4 87.45 305.8 81.125Z" fill="currentColor"/>
        </svg>
    );
}
