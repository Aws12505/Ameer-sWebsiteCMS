import { Head, router, setLayoutProps, usePage } from '@inertiajs/react';
import { Mail, Phone, Trash2 } from 'lucide-react';
import type { ReactNode } from 'react';
import { SelectField, TextAreaField } from '@/components/cms/field';
import {
    ConfirmButton,
    FormSection,
    PageBody,
    PageHeader,
    SaveBar,
} from '@/components/cms/layout';
import { Button } from '@/components/ui/button';
import { useCmsForm } from '@/hooks/use-cms-form';
import { formatDateTime } from '@/lib/cms';
import { LIMITS } from '@/lib/limits';
import contactRequests from '@/routes/admin/contact-requests';
import type { BreadcrumbItem, LastEdited, Option } from '@/types';

type ContactRequest = {
    readonly id: number;
    readonly createdAt: string;
    readonly name: string;
    readonly phone: string;
    readonly phoneHref: string;
    readonly email: string;
    readonly projectType: string | null;
    readonly address: string | null;
    readonly message: string;
    readonly status: string;
    readonly notes: string;
    readonly lastEdited: LastEdited;
};

type Props = {
    readonly request: ContactRequest;
    readonly statuses: readonly Option[];
};

export default function ContactRequestShow({
    request,
    statuses,
}: Props): ReactNode {
    const { auth } = usePage().props;
    const form = useCmsForm({ status: request.status, notes: request.notes });

    setLayoutProps<{ breadcrumbs: BreadcrumbItem[] }>({
        breadcrumbs: [
            { title: 'Contact requests', href: contactRequests.index() },
            { title: request.name, href: contactRequests.show(request.id) },
        ],
    });

    const details: readonly {
        readonly label: string;
        readonly value: ReactNode;
    }[] = [
        { label: 'Received', value: formatDateTime(request.createdAt) },
        {
            label: 'Phone',
            value: (
                <a href={request.phoneHref} className="underline">
                    {request.phone}
                </a>
            ),
        },
        {
            label: 'Email',
            value: (
                <a
                    href={`mailto:${request.email}`}
                    className="break-all underline"
                >
                    {request.email}
                </a>
            ),
        },
        { label: 'Project type', value: request.projectType ?? '—' },
        { label: 'Address or ZIP', value: request.address ?? '—' },
    ];

    return (
        <>
            <Head title={`Request from ${request.name}`} />
            <form
                onSubmit={(event) => {
                    event.preventDefault();
                    form.submit(contactRequests.update(request.id), {
                        preserveScroll: true,
                    });
                }}
            >
                <PageBody>
                    <PageHeader
                        title={`Request from ${request.name}`}
                        lastEdited={request.lastEdited}
                        actions={
                            <>
                                <Button variant="outline" asChild>
                                    <a href={request.phoneHref}>
                                        <Phone />
                                        Call
                                    </a>
                                </Button>
                                <Button variant="outline" asChild>
                                    <a href={`mailto:${request.email}`}>
                                        <Mail />
                                        Email
                                    </a>
                                </Button>
                            </>
                        }
                    />

                    <FormSection title="What they sent">
                        <dl className="grid gap-3 sm:grid-cols-[10rem_1fr]">
                            {details.map((detail) => (
                                <div key={detail.label} className="contents">
                                    <dt className="text-sm text-muted-foreground">
                                        {detail.label}
                                    </dt>
                                    <dd className="text-sm">{detail.value}</dd>
                                </div>
                            ))}
                        </dl>
                        <div className="grid gap-1">
                            <p className="text-sm text-muted-foreground">
                                Project details
                            </p>
                            <p className="text-sm whitespace-pre-line">
                                {request.message}
                            </p>
                        </div>
                    </FormSection>

                    <FormSection
                        title="Follow-up"
                        description="Only your team sees these. They are never sent to the visitor."
                    >
                        <SelectField
                            label="Status"
                            options={statuses}
                            value={form.data.status}
                            error={form.error('status')}
                            onChange={(value) => form.set('status', value)}
                        />
                        <TextAreaField
                            label="Internal notes"
                            allowLineBreaks
                            rows={5}
                            max={LIMITS.contactRequest.notes}
                            value={form.data.notes}
                            error={form.error('notes')}
                            onChange={(value) => form.set('notes', value)}
                        />
                    </FormSection>

                    <SaveBar
                        processing={form.processing}
                        isDirty={form.isDirty}
                    >
                        {auth.isAdmin && (
                            <ConfirmButton
                                title="Delete this request for good?"
                                description="It is personal data, so it is removed completely and cannot be restored."
                                confirmLabel="Delete"
                                variant="ghost"
                                onConfirm={() =>
                                    router.delete(
                                        contactRequests.destroy.url(request.id),
                                    )
                                }
                            >
                                <Trash2 />
                                Delete
                            </ConfirmButton>
                        )}
                    </SaveBar>
                </PageBody>
            </form>
        </>
    );
}
