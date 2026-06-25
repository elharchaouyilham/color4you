import Button from '@/Components/UI/Button';
import Card from '@/Components/UI/Card';
import { Field, Input, Textarea } from '@/Components/UI/Input';
import SectionHeader from '@/Components/UI/SectionHeader';
import PublicLayout from '@/Layouts/PublicLayout';
import { Head, useForm, usePage } from '@inertiajs/react';
import { FormEvent } from 'react';
import { PageProps } from '@/types';

export default function ContactIndex() {
    const { flash } = usePage<PageProps>().props;
    const form = useForm({
        name: '',
        email: '',
        phone: '',
        subject: '',
        message: '',
    });

    const submit = (event: FormEvent) => {
        event.preventDefault();
        form.post(route('contact.store'), {
            preserveScroll: true,
            onSuccess: () => form.reset(),
        });
    };

    return (
        <PublicLayout>
            <Head title="Contact" />

            <section className="artt-container max-w-5xl py-14">
                <SectionHeader
                    eyebrow="Contact"
                    title="Une question sur le catalogue, une reservation ou un atelier."
                    copy="Envoyez votre message ici. L'equipe conserve le meme flux de traitement cote serveur."
                />

                <Card as="form" onSubmit={submit} className="space-y-5">
                    {flash.success ? (
                        <div className="rounded-2xl bg-[rgba(126,215,168,0.12)] px-4 py-3 text-sm text-[var(--artt-success)]">{flash.success}</div>
                    ) : null}
                    <div className="grid gap-5 md:grid-cols-2">
                        <Field label="Nom complet" htmlFor="name" error={form.errors.name}>
                            <Input id="name" value={form.data.name} onChange={(event) => form.setData('name', event.target.value)} required />
                        </Field>
                        <Field label="Email" htmlFor="email" error={form.errors.email}>
                            <Input id="email" type="email" value={form.data.email} onChange={(event) => form.setData('email', event.target.value)} required />
                        </Field>
                    </div>
                    <div className="grid gap-5 md:grid-cols-[240px_minmax(0,1fr)]">
                        <Field label="Telephone" htmlFor="phone" error={form.errors.phone}>
                            <Input id="phone" value={form.data.phone} onChange={(event) => form.setData('phone', event.target.value)} />
                        </Field>
                        <Field label="Sujet" htmlFor="subject" error={form.errors.subject}>
                            <Input id="subject" value={form.data.subject} onChange={(event) => form.setData('subject', event.target.value)} required />
                        </Field>
                    </div>
                    <Field label="Message" htmlFor="message" error={form.errors.message}>
                        <Textarea id="message" rows={8} value={form.data.message} onChange={(event) => form.setData('message', event.target.value)} required />
                    </Field>
                    <Button type="submit" disabled={form.processing}>
                        Envoyer le message
                    </Button>
                </Card>
            </section>
        </PublicLayout>
    );
}
