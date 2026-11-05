import { router } from '@inertiajs/react';
import { Trash2 } from 'lucide-react';
import type { ReactNode } from 'react';
import { CheckboxField } from '@/components/cms/field';
import { ConfirmButton, FormSection } from '@/components/cms/layout';

type RouteTarget = { readonly url: string; readonly method: 'delete' };

/**
 * The "Published" switch and the "Move to trash" button of an edit screen.
 */
export function PublishSection({
    published,
    onChange,
    destroy,
    itemName,
    description,
}: {
    readonly published: boolean;
    readonly onChange: (published: boolean) => void;
    readonly destroy?: RouteTarget | null;
    readonly itemName: string;
    readonly description?: ReactNode;
}): ReactNode {
    return (
        <FormSection title="Publishing">
            <CheckboxField
                label="Published"
                checked={published}
                description={
                    description ??
                    'Only published items appear on the website. Switch it off to keep a draft.'
                }
                onChange={onChange}
            />
            {destroy && (
                <div>
                    <ConfirmButton
                        title={`Move this ${itemName} to the trash?`}
                        description="It disappears from the website straight away. You can restore it from the Trash for 30 days."
                        confirmLabel="Move to trash"
                        variant="outline"
                        size="sm"
                        onConfirm={() => router.delete(destroy.url)}
                    >
                        <Trash2 />
                        Move to trash
                    </ConfirmButton>
                </div>
            )}
        </FormSection>
    );
}
