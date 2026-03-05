export function dashboardPage() {
    return {
        stats: null,
        period: '7d',
        loading: true,
        error: '',

        init() {
            this.loadStats()
        },

        async loadStats() {
            this.loading = true
            this.error = ''
            try {
                const data = await window.__chatpilot_api.get(`/v1/admin/stats?period=${this.period}`)
                this.stats = data
            } catch (err) {
                this.error = err.message || 'Failed to load stats'
            } finally {
                this.loading = false
            }
        },

        changePeriod(p) {
            this.period = p
            this.loadStats()
        },

        maxDaily() {
            if (!this.stats?.daily_conversations?.length) return 1
            return Math.max(...this.stats.daily_conversations.map(d => d.count || 0), 1)
        },

        barHeight(count) {
            return Math.max((count / this.maxDaily()) * 120, 2) + 'px'
        }
    }
}
