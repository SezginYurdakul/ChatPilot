export function loginPage() {
    return {
        email: '',
        password: '',
        error: '',
        loading: false,

        async submit() {
            this.error = ''
            this.loading = true
            try {
                const data = await window.__chatpilot_api.post('/v1/auth/login', {
                    email: this.email,
                    password: this.password,
                })
                const token = data.token || data.access_token
                const user = data.user
                this.$dispatch('login-success', { token, user })
            } catch (err) {
                this.error = err.data?.message || err.message || 'Login failed'
            } finally {
                this.loading = false
            }
        }
    }
}
