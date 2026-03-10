export function layoutComponent() {
    return {
        sidebarOpen: false,
        _heartbeatInterval: null,

        init() {
            this.startHeartbeat()
            window.addEventListener('beforeunload', () => this.goOffline())
        },

        async logout() {
            this.stopHeartbeat()
            await this.goOffline()

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
                // Keepalive fetch allows sending auth header during unload/navigation.
                await fetch(window.location.origin + '/api/v1/admin/presence/offline', {
                    method: 'POST',
                    keepalive: true,
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'Authorization': `Bearer ${token}`,
                    },
                    body: '{}',
                })
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
