<template>
    <div class="delivery-step">
        <BaseRadioGroup
            v-model="delivery_id_str"
            name="delivery_method"
            :options="method_options"
        />

        <div v-if="selected_option?.requires_branch" class="delivery-step__row">
            <BaseSelect v-model="selected_city" label="City" @update:modelValue="local.branch_id = null">
                <option value="" disabled>Select city</option>
                <option v-for="city in cities" :key="city" :value="city">{{ city }}</option>
            </BaseSelect>
            <BaseSelect v-model="branch_id_str" label="Branch" :disabled="!selected_city">
                <option value="" disabled>Select branch</option>
                <option v-for="branch in branches_in_city" :key="branch.id" :value="String(branch.id)">
                    {{ branch.branch }}
                </option>
            </BaseSelect>
        </div>

        <BaseButton type="button" :disabled="!is_valid" @click="on_continue">Continue</BaseButton>
    </div>
</template>

<script setup lang="ts">
import { computed, ref, watch } from 'vue'
import BaseButton from '@/components/ui/base/BaseButton.vue'
import BaseSelect from '@/components/ui/base/BaseSelect.vue'
import BaseRadioGroup from '@/components/ui/base/BaseRadioGroup.vue'
import { useWizardStep } from '@/composables/useWizardStep'
import type { DeliveryData, DeliveryOption } from '@/types/shop'

const props = defineProps<{
    initial_data: DeliveryData
    options: DeliveryOption[]
}>()

const emit = defineEmits<{
    change: [data: DeliveryData]
    complete: [data: DeliveryData]
}>()

const { local, is_valid, on_continue } = useWizardStep<DeliveryData>(
    props.initial_data,
    emit,
    (data) => {
        const option = props.options.find((o) => o.id === data.delivery_id)
        return !!option && (!option.requires_branch || !!data.branch_id)
    }
)

const method_options = computed(() => props.options.map((option) => ({
    value: String(option.id),
    label: option.name,
})))

const selected_option = computed(() => props.options.find((option) => option.id === local.delivery_id))

const delivery_id_str = computed({
    get: () => local.delivery_id !== null ? String(local.delivery_id) : '',
    set: (value: string) => { local.delivery_id = value ? Number(value) : null },
})

const cities = computed(() => [...new Set(selected_option.value?.branches.map((branch) => branch.city) ?? [])])

const selected_city = ref('')

const branches_in_city = computed(() => selected_option.value?.branches.filter((branch) => branch.city === selected_city.value) ?? [])

const branch_id_str = computed({
    get: () => local.branch_id !== null ? String(local.branch_id) : '',
    set: (value: string) => { local.branch_id = value ? Number(value) : null },
})

watch(() => local.delivery_id, () => {
    selected_city.value = ''
    local.branch_id = null
})

watch(
    () => props.options,
    (options) => {
        if (!local.delivery_id && options.length) {
            local.delivery_id = options[0].id
            return
        }

        if (!selected_city.value && local.branch_id !== null) {
            const option = options.find((o) => o.id === local.delivery_id)
            const branch = option?.branches.find((b) => b.id === local.branch_id)
            if (branch) selected_city.value = branch.city
        }
    },
    { immediate: true }
)
</script>

<style lang="scss" scoped>
.delivery-step {
    display: flex;
    flex-direction: column;
    gap: 16px;

    &__row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 16px;
    }
}
</style>
