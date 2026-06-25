/// <reference types="vite/client" />

import { AxiosInstance } from 'axios';

export interface User {
    id: number;
    first_name: string;
    last_name: string;
    email: string;
    phone: string | null;
    email_verified_at: string | null;
    name: string;
}

export type PageProps<
    T extends Record<string, unknown> = Record<string, unknown>,
> = T & {
    auth: {
        user: User | null;
    };
    flash: {
        success?: string;
        error?: string;
    };
};

declare global {
    interface Window {
        axios: AxiosInstance;
    }

    function route(): {
        current: (pattern?: string) => boolean;
    };

    function route(name: string, params?: unknown, absolute?: boolean): string;
}
