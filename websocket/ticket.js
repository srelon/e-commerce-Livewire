const crypto = require('crypto')

const SECRET = process.env.WS_TICKET_SECRET

function verifyTicket(ticket) {
    if (!SECRET || !ticket) return null

    const [payload, signature] = ticket.split('.')
    if (!payload || !signature) return null

    const expected = crypto.createHmac('sha256', SECRET).update(payload).digest('hex')

    const signature_buffer = Buffer.from(signature)
    const expected_buffer = Buffer.from(expected)

    if (signature_buffer.length !== expected_buffer.length) return null
    if (!crypto.timingSafeEqual(signature_buffer, expected_buffer)) return null

    let data
    try {
        data = JSON.parse(Buffer.from(payload, 'base64').toString('utf8'))
    } catch {
        return null
    }

    if (!data.public_id || !data.expires_at) return null
    if (Date.now() / 1000 > data.expires_at) return null

    return { public_id: String(data.public_id) }
}

module.exports = { verifyTicket }
