import Alpine from 'alpinejs'
import { api } from './api.js'
import { loginPage } from './pages/login.js'
import { dashboardPage } from './pages/dashboard.js'
import { conversationsPage } from './pages/conversations.js'
import { sitesPage } from './pages/sites.js'
import { usersPage } from './pages/users.js'
import { layoutComponent } from './components/layout.js'

window.__chatpilot_api = api

Alpine.store('appState', {
    page: 'login',
    user: null,
})

Alpine.data('loginPage', loginPage)
Alpine.data('dashboardPage', dashboardPage)
Alpine.data('conversationsPage', conversationsPage)
Alpine.data('sitesPage', sitesPage)
Alpine.data('usersPage', usersPage)
Alpine.data('layoutComponent', layoutComponent)

Alpine.data('app', () => ({
    page: 'login',
    token: null,
    user: null,

    init() {
        this.token = localStorage.getItem('chatpilot_token')
        if (this.token) {
            api.setToken(this.token)
            this.loadUser()
        }

        window.__chatpilot_app = this

        window.addEventListener('hashchange', () => this.route())
        this.route()
        this.syncStore()
    },

    syncStore() {
        Alpine.store('appState').page = this.page
        Alpine.store('appState').user = this.user
    },

    defaultPage() {
        return this.user?.role === 'super_admin' ? 'dashboard' : 'conversations'
    },

    allowedPages() {
        if (!this.token) {
            return ['login']
        }

        if (this.user?.role === 'super_admin') {
            return ['dashboard', 'conversations', 'sites', 'users']
        }

        return ['conversations']
    },

    route() {
        const hash = window.location.hash.replace('#', '') || 'login'
        if (!this.token && hash !== 'login') {
            window.location.hash = '#login'
            return
        }
        if (this.token && hash === 'login') {
            window.location.hash = `#${this.defaultPage()}`
            return
        }

        const validPages = this.allowedPages()
        if (!validPages.includes(hash)) {
            window.location.hash = `#${this.defaultPage()}`
            return
        }

        this.page = hash
        this.syncStore()
    },

    async loadUser() {
        try {
            const data = await api.get('/v1/auth/user')
            this.user = data.user || data
            this.route()
            this.syncStore()
        } catch {
            this.logout()
        }
    },

    onLogin(detail) {
        this.token = detail.token
        this.user = detail.user
        localStorage.setItem('chatpilot_token', detail.token)
        api.setToken(detail.token)
        this.syncStore()
        window.location.hash = `#${this.defaultPage()}`
    },

    async logout() {
        // Mark admin offline before revoking auth token.
        await api.post('/v1/admin/presence/offline').catch(() => {})
        await api.post('/v1/auth/logout').catch(() => {})
        this.token = null
        this.user = null
        localStorage.removeItem('chatpilot_token')
        api.setToken(null)
        this.syncStore()
        window.location.hash = '#login'
    }
}))

Alpine.start()
