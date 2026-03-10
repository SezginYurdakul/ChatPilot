export function usersPage() {
    return {
        users: [],
        sites: [],
        loading: true,
        error: '',
        showCreateForm: false,
        form: { name: '', email: '', password: '', site_ids: [] },
        formError: '',
        saving: false,
        deletingId: null,

        init() {
            Promise.all([this.loadUsers(), this.loadSites()])
        },

        async loadUsers() {
            this.loading = true
            this.error = ''
            try {
                const data = await window.__chatpilot_api.get('/v1/admin/users')
                this.users = data.users || []
            } catch (err) {
                this.error = err.message
            } finally {
                this.loading = false
            }
        },

        async loadSites() {
            try {
                const data = await window.__chatpilot_api.get('/v1/admin/sites')
                this.sites = data.sites || []
            } catch (err) {
                this.error = err.message
            }
        },

        openCreate() {
            this.form = { name: '', email: '', password: '', site_ids: [] }
            this.formError = ''
            this.showCreateForm = true
        },

        closeCreate() {
            this.showCreateForm = false
            this.formError = ''
        },

        async createUser() {
            this.formError = ''
            if (!this.form.name.trim()) { this.formError = 'Name is required'; return }
            if (!this.form.email.trim()) { this.formError = 'Email is required'; return }
            if (!this.form.password || this.form.password.length < 6) { this.formError = 'Password must be at least 6 characters'; return }
            if (!this.form.site_ids.length) { this.formError = 'Select at least one site'; return }

            this.saving = true
            try {
                await window.__chatpilot_api.post('/v1/admin/users', {
                    name: this.form.name.trim(),
                    email: this.form.email.trim(),
                    password: this.form.password,
                    site_ids: this.form.site_ids,
                })
                this.showCreateForm = false
                this.loadUsers()
            } catch (err) {
                this.formError = err.data?.message || err.message || 'Failed to create user'
            } finally {
                this.saving = false
            }
        },

        async deleteUser(user) {
            if (!confirm(`Delete user "${user.name}"? This action cannot be undone.`)) return
            this.deletingId = user.id
            try {
                await window.__chatpilot_api.delete(`/v1/admin/users/${user.id}`)
                this.loadUsers()
            } catch (err) {
                this.error = err.data?.message || err.message || 'Failed to delete user'
            } finally {
                this.deletingId = null
            }
        },

        toggleSite(siteId) {
            if (this.form.site_ids.includes(siteId)) {
                this.form.site_ids = this.form.site_ids.filter((id) => id !== siteId)
                return
            }

            this.form.site_ids.push(siteId)
        },

        userSitesLabel(user) {
            if (user.role === 'super_admin') {
                return 'All sites'
            }

            const names = (user.sites || []).map((site) => site.name).filter(Boolean)
            return names.length ? names.join(', ') : '-'
        },

        formatDate(dateStr) {
            if (!dateStr) return '-'
            return new Date(dateStr).toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' })
        }
    }
}
