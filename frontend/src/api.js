import axios from 'axios'

export const api = axios.create({
    baseURL: import.meta.env.VITE_API_URL || '/api',
})

export async function getBoardFull(boardId) {
    return api.get(`/boards/${boardId}/full`).then(r => r.data)
}

export async function createColumn(board_id, name, position = 0) {
    return api.post('/columns', { board_id, name, position }).then(r => r.data)
}

export async function updateColumn(id, payload) {
    return api.patch(`/columns/${id}`, payload).then(r => r.data)
}

export async function deleteColumn(id) {
    return api.delete(`/columns/${id}`).then(r => r.data)
}

export async function createTask(column_id, title, description = '', position = 0) {
    return api.post('/tasks', { column_id, title, description, position }).then(r => r.data)
}

export async function updateTask(id, payload) {
    return api.patch(`/tasks/${id}`, payload).then(r => r.data)
}

export async function deleteTask(id) {
    return api.delete(`/tasks/${id}`).then(r => r.data)
}

export async function moveTask(id, column_id, position = 0) {
    return api.post(`/tasks/${id}/move`, { column_id, position }).then(r => r.data)
}