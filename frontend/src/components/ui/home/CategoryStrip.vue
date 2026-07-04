<template>
    <section class="cats">
        <div class="container">
            <div v-if="!layout_store.loaded" class="cats__skeleton-track">
                <div v-for="n in 8" :key="n" class="cats__skeleton-item">
                    <BaseSkeleton width="56px" height="56px" circle />
                    <BaseSkeleton width="70px" height="13px" />
                    <BaseSkeleton width="50px" height="12px" />
                </div>
            </div>
            <div v-else class="cats__wrapper">
                <div
                    class="cats__track"
                    :style="{
                        transform: `translateX(${offset}%)`,
                        transition: animated ? 'transform 0.6s ease' : 'none',
                    }"
                >
                    <CategoryItem
                        v-for="cat in categories"
                        :key="cat.id"
                        :name="cat.name"
                        :icon="to_storage_url(cat.icon)"
                        :count="cat.count"
                        :style="{ width: item_width }"
                    />
                </div>
            </div>
        </div>
    </section>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted, nextTick } from 'vue'
import CategoryItem from '@/components/ui/home/CategoryItem.vue'
import BaseSkeleton from '@/components/ui/base/BaseSkeleton.vue'
import { useLayoutStore, to_storage_url } from '@/stores/layout'

const VISIBLE = 8

const layout_store = useLayoutStore()
const categories = computed(() => layout_store.categories)

const total = computed(() => categories.value.length)
const max_index = computed(() => Math.max(0, total.value - VISIBLE))

const current = ref(0)
const animated = ref(true)

const offset = computed(() => total.value ? -(current.value * 100) / total.value : 0)

const track_width = computed(() => `${(total.value / VISIBLE) * 100}%`)
const item_width = computed(() => `${100 / total.value}%`)

let timer: ReturnType<typeof setInterval>

function advance() {
    if (current.value >= max_index.value) {
        animated.value = false
        current.value = 0
        nextTick(() => {
            requestAnimationFrame(() => {
                requestAnimationFrame(() => {
                    animated.value = true
                })
            })
        })
    } else {
        animated.value = true
        current.value++
    }
}

onMounted(() => {
    timer = setInterval(advance, 10000)
})

onUnmounted(() => {
    clearInterval(timer)
})
</script>

<style lang="scss" scoped>
.cats {
    padding: 40px 0;
    border-bottom: 1px solid $color-light;

    &__wrapper {
        overflow: hidden;
        width: 100%;
    }

    &__track {
        display: flex;
        width: v-bind(track_width);
    }

    &__skeleton-track {
        display: flex;
    }

    &__skeleton-item {
        flex: 1;
        display: flex;
        flex-direction: column;
        align-items: center;
        text-align: center;
        gap: 10px;
        padding: 16px 20px;
    }
}
</style>
