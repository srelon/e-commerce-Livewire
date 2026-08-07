const { URL } = require('url')
const logger = require('./logger')
const channels = require('./channels')
const { verifyTicket } = require('./ticket')

function handleConnection(ws, req) {
    const { searchParams } = new URL(req.url, 'http://localhost')
    const ticket = verifyTicket(searchParams.get('ticket'))

    ws._channels = new Set()
    ws.public_id = ticket?.public_id ?? null

    logger.info({ ip: req.socket.remoteAddress, public_id: ws.public_id }, 'Client connected')

    ws.on('message', (data) => handleMessage(ws, data))
    ws.on('close', () => handleClose(ws))
    ws.on('error', (err) => logger.error({ err }, 'WebSocket error'))
}

function handleMessage(ws, data) {
    let msg

    try {
        msg = JSON.parse(data.toString())
    } catch (err) {
        logger.warn({ err }, 'Failed to parse client message')
        return
    }

    if (msg.type === 'subscribe' && msg.channel) {
        channels.subscribe(msg.channel, ws)
    } else if (msg.type === 'unsubscribe' && msg.channel) {
        channels.unsubscribe(msg.channel, ws)
    }
}

function handleClose(ws) {
    channels.unsubscribeAll(ws)
    logger.info('Client disconnected')
}

module.exports = { handleConnection }
