<x-admin.manager-list-modal
    heading="News authors"
    create-label="New author"
    :form-title="$editing_id ? 'Edit author' : 'New author'"
>
    <x-admin.manager-table
        :columns="$listColumns"
        :items="$this->list"
        empty-message="No authors yet."
    />
</x-admin.manager-list-modal>
