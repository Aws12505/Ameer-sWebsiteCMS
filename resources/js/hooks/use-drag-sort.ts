import type { DragEvent } from 'react';
import { useState } from 'react';

type DragHandlers = {
    readonly draggable: true;
    readonly onDragStart: (event: DragEvent) => void;
    readonly onDragOver: (event: DragEvent) => void;
    readonly onDrop: (event: DragEvent) => void;
    readonly onDragEnd: () => void;
};

/**
 * Drag-and-drop re-ordering for a list, with plain HTML drag events.
 * Lists also show up/down buttons, which work on touch screens.
 */
export function useDragSort(onMove: (from: number, to: number) => void): {
    readonly draggingIndex: number | null;
    readonly overIndex: number | null;
    readonly dragProps: (index: number) => DragHandlers;
} {
    const [draggingIndex, setDraggingIndex] = useState<number | null>(null);
    const [overIndex, setOverIndex] = useState<number | null>(null);

    const reset = (): void => {
        setDraggingIndex(null);
        setOverIndex(null);
    };

    return {
        draggingIndex,
        overIndex,
        dragProps: (index: number): DragHandlers => ({
            draggable: true,
            onDragStart: (event: DragEvent): void => {
                // Firefox only starts a drag when some data is attached.
                event.dataTransfer.setData('text/plain', String(index));
                setDraggingIndex(index);
            },
            onDragOver: (event: DragEvent): void => {
                event.preventDefault();
                setOverIndex(index);
            },
            onDrop: (event: DragEvent): void => {
                event.preventDefault();

                if (draggingIndex !== null && draggingIndex !== index) {
                    onMove(draggingIndex, index);
                }

                reset();
            },
            onDragEnd: reset,
        }),
    };
}
