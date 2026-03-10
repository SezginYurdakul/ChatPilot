export function conversationsPage() {
    return {
        conversations: [],
        activeId: null,
        messages: [],
        messageText: '',
        search: '',
        statusFilter: '',
        displayLanguage: localStorage.getItem('chatpilot_admin_display_language') || 'en',
        loading: true,
        messagesLoading: false,
        sending: false,
        error: '',
        _pollConvInterval: null,
        _pollMsgInterval: null,
        _searchTimeout: null,

        init() {
            this.loadConversations()
            this._pollConvInterval = setInterval(() => this.loadConversations(true), 5000)
        },

        destroy() {
            if (this._pollConvInterval) clearInterval(this._pollConvInterval)
            if (this._pollMsgInterval) clearInterval(this._pollMsgInterval)
            if (this._searchTimeout) clearTimeout(this._searchTimeout)
        },

        async loadConversations(silent = false) {
            if (!silent) this.loading = true
            try {
                let url = '/v1/admin/conversations'
                const params = new URLSearchParams()
                if (this.search) params.set('search', this.search)
                if (this.statusFilter) params.set('status', this.statusFilter)
                const q = params.toString()
                if (q) url += '?' + q
                const data = await window.__chatpilot_api.get(url)
                this.conversations = data.conversations || data.data || []
            } catch (err) {
                if (!silent) this.error = err.message
            } finally {
                if (!silent) this.loading = false
            }
        },

        selectConversation(id) {
            this.activeId = id
            this.loadMessages(id)
            if (this._pollMsgInterval) clearInterval(this._pollMsgInterval)
            this._pollMsgInterval = setInterval(() => {
                if (this.activeId) this.loadMessages(this.activeId, true)
            }, 5000)
        },

        async loadMessages(id, silent = false) {
            if (!id) return
            if (!silent) this.messagesLoading = true
            try {
                let url = `/v1/admin/conversations/${id}`
                if (this.displayLanguage) {
                    url += `?language=${encodeURIComponent(this.displayLanguage)}`
                }
                const data = await window.__chatpilot_api.get(url)
                this.messages = (data.messages || []).sort(
                    (a, b) => new Date(a.created_at) - new Date(b.created_at)
                )
                window.__chatpilot_api.post(`/v1/admin/conversations/${id}/read`).catch(() => {})
                if (!silent) {
                    this.$nextTick(() => this.scrollToBottom())
                }
            } catch (err) {
                if (!silent) this.error = err.message
            } finally {
                if (!silent) this.messagesLoading = false
            }
        },

        async sendMessage() {
            if (!this.messageText.trim() || !this.activeId || this.sending) return
            this.sending = true
            try {
                await window.__chatpilot_api.post(`/v1/admin/conversations/${this.activeId}/messages`, {
                    text: this.messageText.trim(),
                    language: this.displayLanguage,
                })
                this.messageText = ''
                await this.loadMessages(this.activeId, true)
                this.$nextTick(() => this.scrollToBottom())
            } catch (err) {
                this.error = err.message
            } finally {
                this.sending = false
            }
        },

        scrollToBottom() {
            const el = document.querySelector('[data-messages]')
            if (el) el.scrollTop = el.scrollHeight
        },

        onSearchInput() {
            if (this._searchTimeout) clearTimeout(this._searchTimeout)
            this._searchTimeout = setTimeout(() => this.loadConversations(), 300)
        },

        onLanguageChange() {
            localStorage.setItem('chatpilot_admin_display_language', this.displayLanguage)
            if (this.activeId) this.loadMessages(this.activeId)
        },

        getActiveConversation() {
            return this.conversations.find(c => c.id === this.activeId)
        },

        formatTime(dateStr) {
            if (!dateStr) return ''
            const d = new Date(dateStr)
            const now = new Date()
            if (d.toDateString() === now.toDateString()) {
                return d.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })
            }
            return d.toLocaleDateString([], { month: 'short', day: 'numeric' }) +
                   ' ' + d.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })
        },

        async updateStatus(status) {
            if (!this.activeId) return
            try {
                await window.__chatpilot_api.patch(`/v1/admin/conversations/${this.activeId}`, { status })
                await this.loadConversations(true)
                if (this.activeId) await this.loadMessages(this.activeId, true)
            } catch (err) {
                this.error = err.message
            }
        },

        async deleteConversation() {
            if (!this.activeId) return
            if (!confirm('Are you sure you want to delete this conversation?')) return
            try {
                await window.__chatpilot_api.delete(`/v1/admin/conversations/${this.activeId}`)
                this.activeId = null
                this.messages = []
                if (this._pollMsgInterval) clearInterval(this._pollMsgInterval)
                await this.loadConversations(true)
            } catch (err) {
                this.error = err.message
            }
        },

        getMessageContent(msg) {
            if (msg.translations && msg.translations[this.displayLanguage]) {
                return msg.translations[this.displayLanguage]
            }
            return msg.text || ''
        }
    }
}
