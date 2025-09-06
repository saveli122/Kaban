<script setup>
import { ref } from 'vue'


const props = defineProps({
  placeholder: { type: String, default: '' },
  submitLabel: { type: String, default: 'Добавить' },
  withDescription: { type: Boolean, default: false },
})
const emit = defineEmits(['submit'])


const isOpen = ref(false)
const title = ref('')
const description = ref('')
const titleEl = ref(null)


function reset(){ title.value=''; description.value='' }
function open(){ isOpen.value = true; queueMicrotask(()=> titleEl.value?.focus()) }
function close(){ isOpen.value = false; reset() }
function toggle(){ isOpen.value ? close() : open() }


function submit() {
  const t = title.value.trim()
  if (!t) return
  if (props.withDescription) emit('submit', t, (description.value || '').trim())
  else emit('submit', t)
  close()
}


function onKeydown(e){ if(e.key === 'Escape') close() }
</script>


<template>
  <div class="add-popover" :class="{ 'is-open': isOpen }" @mouseenter="open" @mouseleave="!title && !description && (isOpen = false)">
    <button v-if="!isOpen" type="button" class="btn-ghost add-trigger" @click="toggle">+ {{ submitLabel }}</button>


    <div v-show="isOpen" class="add-panel" @keydown="onKeydown">
      <div class="add-panel-clip">
        <div class="add-panel-inner">
      <input ref="titleEl" class="input" :placeholder="placeholder" v-model="title" @keyup.enter="submit" />
      <textarea v-if="withDescription" class="textarea" placeholder="Описание (необязательно)" v-model="description"></textarea>
      <div class="add-actions">
        <button type="button" class="btn" @click="submit">{{ submitLabel }}</button>
        <button type="button" class="btn-ghost" @click="close">Отмена</button>
        <span class="helper" style="margin-left:auto">Enter — отправить, Esc — закрыть</span>
      </div>
    </div>
      </div>
    </div>
  </div>
</template>