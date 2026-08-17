<div>
    <div class="mb-6">
        <x-admin.resource-header :title="$product ? $title : $page_title" :site-url="$site_url ?? null" />
    </div>

    <div
        x-data="{
            tab: ['details', 'reviews'].includes(new URLSearchParams(window.location.search).get('tab'))
                ? new URLSearchParams(window.location.search).get('tab')
                : 'details',
            setTab(value) {
                this.tab = value
                const url = new URL(window.location)
                url.searchParams.set('tab', value)
                window.history.replaceState({}, '', url)
            },
        }"
    >
        @if ($product)
            <div class="mb-6 flex gap-1 border-b border-base-300">
                <button
                    type="button"
                    class="cursor-pointer border-b-2 px-4 py-2 text-sm font-medium"
                    x-on:click="setTab('details')"
                    :class="tab === 'details' ? 'border-amber-600 text-amber-600' : 'border-transparent text-zinc-500 hover:text-zinc-800 dark:hover:text-white'"
                >Details</button>

                <button
                    type="button"
                    class="cursor-pointer border-b-2 px-4 py-2 text-sm font-medium"
                    x-on:click="setTab('reviews')"
                    :class="tab === 'reviews' ? 'border-amber-600 text-amber-600' : 'border-transparent text-zinc-500 hover:text-zinc-800 dark:hover:text-white'"
                >Reviews</button>
            </div>
        @endif

        <div x-show="tab === 'details'">
            @php $compact_header = true; @endphp
            @include('livewire.admin.partials.resource-form')
        </div>

        @if ($product)
            <div x-show="tab === 'reviews'">
                <livewire:components.catalog.products.reviews-panel :product="$product" wire:key="reviews-panel-{{ $product->id }}" />
            </div>
        @endif
    </div>

    @include('livewire.admin.partials.confirm-modal', ['name' => 'confirm-action-product-gallery'])
</div>
