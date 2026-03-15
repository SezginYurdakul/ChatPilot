export function layoutComponent() {
    return {
        sidebarOpen: false,
        _heartbeatInterval: null,
        _beforeUnloadHandler: null,
        _initialized: false,
        _offlineSent: false,

        init() {
            if (this._initialized) {
                return
            }

            this._initialized = true
            this._offlineSent = false
            this.startHeartbeat()

            this._beforeUnloadHandler = () => this.goOffline()
            window.addEventListener('beforeunload', this._beforeUnloadHandler)
        },

        async logout() {
            this.stopHeartbeat()
            await this.goOffline()

            if (window.__chatpilot_app && typeof window.__chatpilot_app.logout === 'function') {
                window.__chatpilot_app.logout({ skipOffline: true })
            }
        },

        startHeartbeat() {
            if (this._heartbeatInterval) {
                return
            }

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
            const token = window.__chatpilot_api.getToken()
            if (!token) {
                return
            }

            try {
                await window.__chatpilot_api.post('/v1/admin/presence/heartbeat')
            } catch (error) {
                if (error.message === 'Unauthorized') {
                    this.stopHeartbeat()
                }
            }
        },

        async goOffline() {
            if (this._offlineSent) {
                return
            }

            try {
                const token = window.__chatpilot_api.getToken()
                if (!token) return

                this._offlineSent = true

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
                this._offlineSent = false
            }
        },

        destroy() {
            this.stopHeartbeat()
            if (this._beforeUnloadHandler) {
                window.removeEventListener('beforeunload', this._beforeUnloadHandler)
                this._beforeUnloadHandler = null
            }
        }
    }
}
