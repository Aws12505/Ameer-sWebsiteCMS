import { Head, Link, router, usePage } from '@inertiajs/react';
import { Plus, Trash2 } from 'lucide-react';
import type { ReactNode } from 'react';
import { ConfirmButton, PageBody, PageHeader } from '@/components/cms/layout';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import users from '@/routes/admin/users';

type UserRow = {
    readonly id: number;
    readonly name: string;
    readonly email: string;
    readonly role: string;
    readonly roleLabel: string;
    readonly twoFactor: boolean;
};

export default function UsersIndex({
    users: rows,
}: {
    readonly users: readonly UserRow[];
}): ReactNode {
    const { auth } = usePage().props;

    return (
        <>
            <Head title="Users" />
            <PageBody>
                <PageHeader
                    title="Users"
                    description="Who can log in to the CMS. Admins can do everything, including users and who receives contact requests. Managers edit all content and handle contact requests."
                    actions={
                        <Button asChild>
                            <Link href={users.create()}>
                                <Plus />
                                New user
                            </Link>
                        </Button>
                    }
                />
                <ul className="divide-y rounded-lg border bg-card">
                    {rows.map((user) => (
                        <li
                            key={user.id}
                            className="flex flex-wrap items-center gap-3 p-3"
                        >
                            <div className="min-w-0 flex-1">
                                <Link
                                    href={users.edit(user.id)}
                                    className="font-medium hover:underline"
                                >
                                    {user.name}
                                </Link>
                                <p className="truncate text-sm text-muted-foreground">
                                    {user.email}
                                </p>
                            </div>
                            <Badge
                                variant={
                                    user.role === 'admin'
                                        ? 'default'
                                        : 'secondary'
                                }
                            >
                                {user.roleLabel}
                            </Badge>
                            {user.twoFactor && (
                                <Badge variant="outline">Two-step login</Badge>
                            )}
                            {user.id !== auth.user.id && (
                                <ConfirmButton
                                    title={`Delete ${user.name}'s login?`}
                                    description="They will no longer be able to log in. Their past edits stay."
                                    confirmLabel="Delete login"
                                    variant="ghost"
                                    size="icon"
                                    onConfirm={() =>
                                        router.delete(
                                            users.destroy.url(user.id),
                                        )
                                    }
                                >
                                    <Trash2 />
                                </ConfirmButton>
                            )}
                        </li>
                    ))}
                </ul>
            </PageBody>
        </>
    );
}

UsersIndex.layout = {
    breadcrumbs: [{ title: 'Users', href: users.index() }],
};
