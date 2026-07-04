<style>
    .filament-tree .dd-placeholder {
        border: 2px dashed var(--color-primary-500);
        border-radius: 0.5rem;
    }

    .filament-tree .dd-item:has(> .dd-list > .dd-placeholder) > .dd-handle {
        border-color: var(--color-primary-500);
        box-shadow: inset 0 0 0 2px var(--color-primary-500);
        background-color: color-mix(in oklab, var(--color-primary-500) 8%, transparent);
    }

    .filament-tree-page .fi-section-content {
        padding-bottom: 4rem;
    }

    .filament-tree-page .fi-color-info {
        color: white;
    }

    .menu-tree-bottom-actions {
        display: flex;
        justify-content: flex-start;
        gap: 0.75rem;
        margin-top: 1.5rem;
        padding-top: 1rem;
        border-top: 1px solid var(--color-gray-200);

        &:is(.dark *) {
            border-color: var(--color-gray-700);
        }
    }
</style>
