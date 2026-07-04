<template>
    <div class="store-location">
        <h2 class="store-location__heading">{{ title }}</h2>
        <ul v-if="layout_store.loaded" class="store-location__details">
            <li v-for="item in contact_info" :key="item.label">
                <svg viewBox="0 0 15 15" aria-hidden="true">
                    <path :d="item.icon ?? ''" />
                </svg>
                <div>
                    <strong>{{ item.label }}</strong>
                    <a v-if="item.href" :href="item.href">{{ item.value }}</a>
                    <span v-else>{{ item.value }}</span>
                </div>
            </li>
        </ul>
        <ul v-else class="store-location__details">
            <li v-for="n in 4" :key="n">
                <BaseSkeleton width="18px" height="18px" circle />
                <div class="store-location__skeleton-lines">
                    <BaseSkeleton width="80px" height="12px" />
                    <BaseSkeleton width="160px" height="14px" />
                </div>
            </li>
        </ul>
    </div>
</template>

<script setup lang="ts">
import BaseSkeleton from '@/components/ui/base/BaseSkeleton.vue'
import { useLayoutStore } from '@/stores/layout'
import { useContactList } from '@/composables/useContactList'

interface Props {
    title: string
}

defineProps<Props>()

const layout_store = useLayoutStore()
const contact_info = useContactList()
</script>

<style lang="scss" scoped>
.store-location {
    &__heading {
        font-size: clamp(18px, 1.8vw, 26px);
        font-weight: 700;
        color: $color-dark;
        margin-bottom: 10px;
    }

    &__details {
        display: flex;
        flex-direction: column;
        gap: 20px;
        margin-top: 24px;

        li {
            display: flex;
            align-items: flex-start;
            gap: 14px;
        }

        svg {
            width: 18px;
            height: 18px;
            flex-shrink: 0;
            margin-top: 2px;

            path {
                fill: $color-primary;
            }
        }

        div {
            display: flex;
            flex-direction: column;
            gap: 2px;
        }

        strong {
            font-size: 13px;
            font-weight: 700;
            color: $color-dark;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        span,
        a {
            font-size: 14px;
            color: $color-gray;
        }

        a:hover {
            color: $color-primary;
        }
    }
}
</style>
