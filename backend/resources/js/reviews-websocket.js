const RECONNECT_BASE_MS = 3000
const RECONNECT_MAX_MS = 30000

export default function reviewsWebsocket({ url, channel }) {
    return {
        socket: null,
        reconnect_delay: RECONNECT_BASE_MS,

        init() {
            this.connect()
        },

        connect() {
            const ws = new WebSocket(url)
            this.socket = ws

            ws.onopen = () => {
                this.reconnect_delay = RECONNECT_BASE_MS
                ws.send(JSON.stringify({ type: 'subscribe', channel }))
            }

            ws.onmessage = (event) => {
                try {
                    const { event: name } = JSON.parse(event.data)

                    if (name && name.startsWith('review.')) {
                        this.$wire.handleWsReviewEvent()
                    }
                } catch {
                    // ignore malformed messages
                }
            }

            ws.onclose = () => {
                setTimeout(() => this.connect(), this.reconnect_delay)
                this.reconnect_delay = Math.min(this.reconnect_delay * 2, RECONNECT_MAX_MS)
            }

            ws.onerror = () => {
                ws.close()
            }
        },

        destroy() {
            this.socket?.close()
        },
    }
}
