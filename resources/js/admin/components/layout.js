export function layoutComponent() {
    return {
        sidebarOpen: false,
        _heartbeatInterval: null,

        init() {
            this.startHeartbeat()
            window.addEventListener('beforeunload', () => this.goOffline())
        },

        logout() {
            if (window.__chatpilot_app && typeof window.__chatpilot_app.logout === 'function') {
                window.__chatpilot_app.logout()
            }
        },

        startHeartbeat() {
            this.sendHeartbeat()
            this._heartbeatInterval = setInterval(() => this.sendHeartbeat(), 15000)
        },

        stopHeartbeat() {
            if (this._heartbeatInterval) {
                clearInterval(this._heartbeatInterval)
                this._heartbeatInterval = null
            }
        },

        async sendHeartbeat() {
            try {
                await window.__chatpilot_api.post('/v1/admin/presence/heartbeat')
            } catch {
                // Ignore errors
            }
        },

        async goOffline() {
            try {
                const token = window.__chatpilot_api.getToken()
                if (!token) return
                // Use sendBeacon for reliability on page unload
                const blob = new Blob([JSON.stringify({})], { type: 'application/json' })
                navigator.sendBeacon(
                    window.location.origin + '/api/v1/admin/presence/offline',
                    blob
                )
            } catch {
                // Ignore
            }
        },

        destroy() {
            this.stopHeartbeat()
            this.goOffline()
        }
    }
}
