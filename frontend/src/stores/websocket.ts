import { defineStore } from 'pinia'
import { ref } from 'vue'

export const useWebsocketStore = defineStore('websocket', () => {
    const socket = ref<WebSocket | null>(null)
    const channels = ref(new Set<string>())

    function connect() {
        if (socket.value && socket.value.readyState !== WebSocket.CLOSED) {
            return
        }

        const url = import.meta.env.VITE_WS_URL ?? 'ws://127.0.0.1:6001'
        const ws = new WebSocket(url)
        socket.value = ws

        ws.onopen = () => {
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
