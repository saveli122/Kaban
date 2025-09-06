<script setup>
import { ref, onMounted } from 'vue'
import { getBoardFull, createColumn } from '../api'
import KanbanColumn from './KanbanColumn.vue'
import AddItem from './AddItem.vue'

const props = defineProps({ boardId: { type: Number, required: true } })

const loading = ref(true)
const error = ref('')
// Дадим безопасное начальное значение, чтобы не упасть до первой загрузки
const board = ref({ id: props.boardId, columns: [] })

async function load() {
  try {
    loading.value = true
    error.value = ''
    board.value = await getBoardFull(props.boardId)
  } catch (e) {
    error.value = 'Не удалось загрузить доску'
  } finally {
    loading.value = false
  }
}

async function addColumn(name) {
  if (!name) return
  const pos = (board.value?.columns?.length || 0) + 1

  //  Оптимистично добавляем колонку в UI (без load())
  const tempId = Math.random()
  const newCol = { id: tempId, name, position: pos, tasks: [] }
  board.value.columns = [...board.value.columns, newCol]

  try {
    const created = await createColumn(props.boardId, name, pos) // axios → api.js
    // если API вернул объект колонки — подменим id
    const realId = created?.id ?? created?.data?.id
    if (realId) {
      const i = board.value.columns.findIndex(c => c.id === tempId)
      if (i !== -1) board.value.columns[i] = { ...board.value.columns[i], id: realId }
    }
  } catch {
    // откат при ошибке
    board.value.columns = board.value.columns.filter(c => c.id !== tempId)
    alert('Не удалось создать колонку')
  }
}

onMounted(load)
</script>

<template>
  <div v-if="loading" class="helper">Загрузка…</div>
  <div v-else-if="error" class="helper">{{ error }}</div>
  <div v-else>
    <div class="board">
      <!-- ❗ Убрали @changed="load" — больше не перезагружаем доску при каждом действии -->
      <KanbanColumn
                    v-for="col in board.columns"
                    :key="col.id"
                    :column="col"
                    :all-columns="board.columns"
                    @changed="load"
      />

      <div class="column">
        <h3 style="margin:0 0 8px 0">Новая колонка</h3>
        <AddItem
            placeholder="Название колонки"
            submit-label="Добавить колонку"
            @submit="addColumn"
        />
      </div>
    </div>
  </div>
</template>
