export type MediaField = {
    path: string;
    url: string;
    alt: string;
};

export type LastEdited = {
    by: string | null;
    at: string | null;
};

export type RecordMeta = {
    id: number;
    keyLocked: boolean;
    lastEdited: LastEdited;
};

export type IconItem = {
    icon: string;
    title: string;
    text: string;
};

export type Option = {
    value: string;
    label: string;
};

export type CmsSharedProps = {
    newContactRequests: number;
    icons: string[];
};

export type UploadKind = 'image' | 'video' | 'favicon';

export type UploadResult = {
    path: string;
    url: string;
    width: number | null;
    height: number | null;
};
