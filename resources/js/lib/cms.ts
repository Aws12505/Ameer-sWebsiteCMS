import { store as uploadRoute } from '@/routes/admin/uploads';
import type { UploadKind, UploadResult } from '@/types';

/**
 * Read a nested value with a dot path ("why.points.0.title").
 */
export function getIn(source: unknown, path: string): unknown {
    return path
        .split('.')
        .reduce<unknown>(
            (value, key) =>
                value === null || value === undefined
                    ? undefined
                    : (value as Record<string, unknown>)[key],
            source,
        );
}

/**
 * Return a copy of the source with a nested value replaced.
 */
export function setIn<T>(source: T, path: string, value: unknown): T {
    const [key, ...rest] = path.split('.');
    const container = (source ?? {}) as Record<string, unknown>;
    const copy = (
        Array.isArray(container) ? [...container] : { ...container }
    ) as Record<string, unknown>;

    copy[key] =
        rest.length === 0
            ? value
            : setIn(container[key], rest.join('.'), value);

    return copy as T;
}

/** The longest URL key the CMS accepts (requirements 3.4). */
const MAX_KEY_LENGTH = 40;

/** HTTP status for "Payload Too Large", sent when a file exceeds the server limit. */
const HTTP_PAYLOAD_TOO_LARGE = 413;

/**
 * Combining accent marks left behind by NFKD normalisation.
 * Matches the accent in "é" after normalising ("e" + "́"); rejects "e".
 * Tester: https://regex101.com/?regex=%5B%5Cu0300-%5Cu036f%5D&testString=cafe%CC%81
 */
const ACCENT_MARKS = /[̀-ͯ]/g;

/**
 * An ampersand, spelled out as "and" in keys.
 * Matches the "&" in "Cafés & Bars"; rejects "Cafés and Bars".
 * Tester: https://regex101.com/?regex=%26&testString=Caf%C3%A9s%20%26%20Bars
 */
const AMPERSAND = /&/g;

/**
 * Runs of anything that is not a lowercase letter or digit.
 * Matches " " and "'s " in "ameer's home"; rejects "home-1".
 * Tester: https://regex101.com/?regex=%5B%5Ea-z0-9%5D%2B&testString=ameer%27s%20home
 */
const NON_KEY_CHARACTERS = /[^a-z0-9]+/g;

/**
 * Hyphens at the start or end of a key.
 * Matches both hyphens in "-decks-"; rejects the inner one in "new-construction".
 * Tester: https://regex101.com/?regex=%5E-%2B%7C-%2B%24&testString=-decks-
 */
const EDGE_HYPHENS = /^-+|-+$/g;

/**
 * Hyphens left at the end after trimming to the maximum length.
 * Matches "--" in "kitchen--"; rejects "kitchen".
 * Tester: https://regex101.com/?regex=-%2B%24&testString=kitchen--
 */
const TRAILING_HYPHENS = /-+$/g;

/**
 * Turn a title into a URL key: lowercase letters, numbers and hyphens.
 */
export function slugify(value: string): string {
    return value
        .normalize('NFKD')
        .replace(ACCENT_MARKS, '')
        .toLowerCase()
        .replace(AMPERSAND, ' and ')
        .replace(NON_KEY_CHARACTERS, '-')
        .replace(EDGE_HYPHENS, '')
        .slice(0, MAX_KEY_LENGTH)
        .replace(TRAILING_HYPHENS, '');
}

/**
 * Move one item of a list to another position.
 */
export function moveItem<T>(items: T[], from: number, to: number): T[] {
    if (to < 0 || to >= items.length || from === to) {
        return items;
    }

    const copy = [...items];
    const [item] = copy.splice(from, 1);
    copy.splice(to, 0, item);

    return copy;
}

/**
 * A readable date and time ("Oct 31, 2026, 3:15 PM").
 */
export function formatDateTime(value: string | null): string {
    if (!value) {
        return '';
    }

    return new Intl.DateTimeFormat('en-US', {
        dateStyle: 'medium',
        timeStyle: 'short',
    }).format(new Date(value));
}

/**
 * The XSRF-TOKEN cookie Laravel sets, capturing its value.
 * Matches "a=1; XSRF-TOKEN=abc%3D" (captures "abc%3D"); rejects "MY-XSRF-TOKEN=abc".
 * Tester: https://regex101.com/?regex=(%3F%3A%5E%7C%3B%5Cs*)XSRF-TOKEN%3D(%5B%5E%3B%5D*)&testString=a%3D1%3B%20XSRF-TOKEN%3Dabc%253D
 */
const XSRF_COOKIE = /(?:^|;\s*)XSRF-TOKEN=([^;]*)/;

function xsrfToken(): string {
    const match = document.cookie.match(XSRF_COOKIE);

    return match ? decodeURIComponent(match[1]) : '';
}

/**
 * Upload a photo or video to the media library. Throws with a
 * plain-English message when the server refuses the file.
 */
export async function uploadFile(
    file: File,
    kind: UploadKind,
): Promise<UploadResult> {
    const body = new FormData();
    body.append('file', file);
    body.append('kind', kind);

    const response = await fetch(uploadRoute.url(), {
        method: 'POST',
        body,
        credentials: 'same-origin',
        headers: {
            Accept: 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-XSRF-TOKEN': xsrfToken(),
        },
    });

    if (response.ok) {
        return (await response.json()) as UploadResult;
    }

    if (response.status === HTTP_PAYLOAD_TOO_LARGE) {
        throw new Error('This file is too large. The limit is 10 MB.');
    }

    const payload = (await response.json().catch(() => null)) as {
        message?: string;
        errors?: Record<string, string[]>;
    } | null;

    throw new Error(
        payload?.errors?.file?.[0] ??
            payload?.message ??
            'The upload failed. Please try again.',
    );
}
