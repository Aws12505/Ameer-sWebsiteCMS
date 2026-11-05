import { Head, setLayoutProps } from '@inertiajs/react';
import type { ReactNode } from 'react';
import { SelectField, TextField } from '@/components/cms/field';
import {
    FormSection,
    PageBody,
    PageHeader,
    SaveBar,
} from '@/components/cms/layout';
import { useCmsForm } from '@/hooks/use-cms-form';
import users from '@/routes/admin/users';
import type { BreadcrumbItem, Option } from '@/types';

type Props = {
    readonly user: {
        readonly id: number;
        readonly name: string;
        readonly email: string;
        readonly role: string;
    } | null;
    readonly roles: readonly Option[];
    readonly passwordRules: string;
};

export default function UserForm({ user, roles }: Props): ReactNode {
    const form = useCmsForm({
        name: user?.name ?? '',
        email: user?.email ?? '',
        role: user?.role ?? 'manager',
        password: '',
    });
    const title = user ? user.name : 'New user';

    setLayoutProps<{ breadcrumbs: BreadcrumbItem[] }>({
        breadcrumbs: [
            { title: 'Users', href: users.index() },
            { title, href: user ? users.edit(user.id) : users.create() },
        ],
    });

    return (
        <>
            <Head title={title} />
            <form
                onSubmit={(event) => {
                    event.preventDefault();
                    form.submit(user ? users.update(user.id) : users.store());
                }}
            >
                <PageBody>
                    <PageHeader
                        title={title}
                        description="There is no public sign-up: share the login details with the person yourself. They can change their password and turn on two-step login under Settings."
                    />
                    <FormSection title="Login">
                        <TextField
                            label="Name"
                            required
                            autoComplete="off"
                            value={form.data.name}
                            error={form.error('name')}
                            onChange={(value) => form.set('name', value)}
                        />
                        <TextField
                            label="Email"
                            type="email"
                            required
                            autoComplete="off"
                            value={form.data.email}
                            error={form.error('email')}
                            onChange={(value) => form.set('email', value)}
                        />
                        <SelectField
                            label="Role"
                            required
                            options={roles}
                            value={form.data.role}
                            error={form.error('role')}
                            onChange={(value) => form.set('role', value)}
                        />
                        <TextField
                            label={
                                user
                                    ? 'New password (leave empty to keep the current one)'
                                    : 'Password'
                            }
                            type="password"
                            required={!user}
                            autoComplete="new-password"
                            value={form.data.password}
                            error={form.error('password')}
                            onChange={(value) => form.set('password', value)}
                        />
                    </FormSection>
                    <SaveBar
                        processing={form.processing}
                        isDirty={form.isDirty}
                        label={user ? 'Save changes' : 'Create user'}
                    />
                </PageBody>
            </form>
        </>
    );
}
