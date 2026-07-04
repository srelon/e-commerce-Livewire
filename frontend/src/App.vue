<script setup lang="ts">
import { onMounted } from 'vue'
import { RouterView } from 'vue-router'
import { useLayoutStore } from '@/stores/layout'

const layout_store = useLayoutStore()

onMounted(() => {
    layout_store.fetch_layout()
})
</script>

<template>
    <RouterView />
</template>
