<script setup>
import KanbanBoard from './components/KanbanBoard.vue'
</script>

<template>
  <div class="app-shell">
    <header class="app-header">
      <div class="brand">
        <div class="brand-badge"></div>
        <div>Kanban</div>
      </div>
      <div class="header-actions">
        <a class="btn-ghost" href="https://vuejs.org/guide/introduction.html" target="_blank" rel="noreferrer">Документация Vue</a>
        <a class="btn-ghost" href="https://laravel.com/docs" target="_blank" rel="noreferrer">Документация Laravel</a>
      </div>
    </header>

    <main class="board-wrap">
      <KanbanBoard :board-id="1" />
    </main>
  </div>
</template>