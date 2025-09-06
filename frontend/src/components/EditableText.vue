<script setup>
import { ref, watch, onMounted } from 'vue'


const props = defineProps({
  modelValue: { type: String, default: '' },
  placeholder: { type: String, default: '' },
})
const emit = defineEmits(['update:modelValue','save','cancel'])


const local = ref(props.modelValue)
const inputEl = ref(null)
watch(() => props.modelValue, v => local.value = v)


onMounted(() => inputEl.value?.focus())


function onKey(e){
  if (e.key === 'Enter') emit('save')
  if (e.key === 'Escape') emit('cancel')
}
</script>


<template>
  <div class="edit-row">
    <input
        ref="inputEl"
        class="input"
        :placeholder="placeholder"
        :value="local"
        @input="e=>emit('update:modelValue', e.target.value)"
        @keydown="onKey"
    />
    <button type="button" class="btn btn-compact" @click="$emit('save')">OK</button>
    <button type="button" class="btn-ghost btn-compact" @click="$emit('cancel')">Отмена</button>
  </div>
</template>