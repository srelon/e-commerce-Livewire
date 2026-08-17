<x-admin.manager-list-modal
    heading="News categories"
    create-label="New category"
    :form-title="$editing_id ? 'Edit category' : 'New category'"
>
    <x-admin.manager-table
        :columns="$listColumns"
        :items="$this->list"
        empty-message="No categories yet."
    />
</x-admin.manager-list-modal>
