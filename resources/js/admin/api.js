const BASE_URL = window.location.origin + '/api'

let token = null

async function request(method, path, body = null) {
    const headers = {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
    }
    if (token) {
        headers['Authorization'] = `Bearer ${token}`
    }

    const opts = { method, headers }
    if (body && method !== 'GET') {
        opts.body = JSON.stringify(body)
    }

    const res = await fetch(BASE_URL + path, opts)

    // Global 401 handler
    if (res.status === 401) {
        token = null
        localStorage.removeItem('chatpilot_token')
        window.location.hash = '#login'
        throw new Error('Unauthorized')
    }

    if (!res.ok) {
        const err = await res.json().catch(() => ({}))
        const error = new Error(err.message || `HTTP ${res.status}`)
        error.status = res.status
        error.data = err
        throw error
    }

    if (res.status === 204) return null
    return res.json()
}

export const api = {
    setToken(t) { token = t },
    getToken() { return token },
    get(path) { return request('GET', path) },
    post(path, body) { return request('POST', path, body) },
    patch(path, body) { return request('PATCH', path, body) },
    delete(path) { return request('DELETE', path) },
}
