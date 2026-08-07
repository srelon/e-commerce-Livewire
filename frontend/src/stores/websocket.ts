import { defineStore } from 'pinia'
import { ref } from 'vue'
import api from '@/plugins/axios'

export const useWebsocketStore = defineStore('websocket', () => {
    const socket = ref<WebSocket | null>(null)
    const channels = ref(new Set<string>())
    let connecting = false

    async function connect() {
        if (connecting || (socket.value && socket.value.readyState !== WebSocket.CLOSED)) {
            return
        }

        connecting = true

        const url = new URL(import.meta.env.VITE_WS_URL ?? 'ws://127.0.0.1:6001')

        try {
            const { data } = await api.post('broadcasting/auth', {}, { silent: true })
            url.searchParams.set('ticket', data.data.ticket)
        } catch {}

        const ws = new WebSocket(url.toString())
        socket.value = ws

        ws.onopen = () => {
            connecting = false
            channels.value.forEach((channel) => {
                ws.send(JSON.stringify({ type: 'subscribe', channel }))
            })
        }

        ws.onmessage = (event) => {
            try {
                const { event: name, data } = JSON.parse(event.data)
                window.dispatchEvent(new CustomEvent(`ws:${name}`, { detail: data }))
            } catch {
                // ignore malformed messages
            }
        }

        ws.onclose = () => {
            connecting = false
            socket.value = null
            setTimeout(connect, 3000)
        }

        ws.onerror = () => {
            ws.close()
        }
    }

    function subscribe(channel: string) {
        channels.value.add(channel)
        connect()

        if (socket.value?.readyState === WebSocket.OPEN) {
            socket.value.send(JSON.stringify({ type: 'subscribe', channel }))
        }
    }

    function unsubscribe(channel: string) {
        channels.value.delete(channel)

        if (socket.value?.readyState === WebSocket.OPEN) {
            socket.value.send(JSON.stringify({ type: 'unsubscribe', channel }))
        }
    }

    return {
        subscribe,
        unsubscribe,
    }
})
