export function conversationsPage() {
    const CONVERSATION_POLL_MS = 30000
    const MESSAGE_POLL_MS = 20000

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
        _boundConversationIds: new Set(),
        _boundSiteIds: new Set(),

        init() {
            this.loadConversations()
            this._pollConvInterval = setInterval(() => this.loadConversations(true), CONVERSATION_POLL_MS)
        },

        destroy() {
            if (this._pollConvInterval) clearInterval(this._pollConvInterval)
            if (this._pollMsgInterval) clearInterval(this._pollMsgInterval)
            if (this._searchTimeout) clearTimeout(this._searchTimeout)
            this.leaveRealtimeChannels()
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
                this.bindRealtimeChannels()
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
            }, MESSAGE_POLL_MS)
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
        },

        bindRealtimeChannels() {
            if (!window.Echo) return

            const nextConversationIds = new Set(this.conversations.map(conv => String(conv.id)))
            const nextSiteIds = new Set(
                this.conversations
                    .map(conv => conv.site_id ? String(conv.site_id) : null)
                    .filter(Boolean)
            )

            for (const conversationId of this._boundConversationIds) {
                if (!nextConversationIds.has(conversationId)) {
                    window.Echo.leave(`conversation.${conversationId}`)
                    this._boundConversationIds.delete(conversationId)
                }
            }

            for (const siteId of this._boundSiteIds) {
                if (!nextSiteIds.has(siteId)) {
                    window.Echo.leave(`admin.site.${siteId}`)
                    this._boundSiteIds.delete(siteId)
                }
            }

            for (const conversationId of nextConversationIds) {
                if (this._boundConversationIds.has(conversationId)) continue

                window.Echo.channel(`conversation.${conversationId}`)
                    .listen('MessageSent', (event) => this.handleRealtimeMessage(event?.message))
                    .listen('MessageRead', () => this.handleRealtimeRead(conversationId))
                    .listen('VisitorStatusChanged', (event) => this.handleVisitorStatus(conversationId, event?.online))

                this._boundConversationIds.add(conversationId)
            }

            for (const siteId of nextSiteIds) {
                if (this._boundSiteIds.has(siteId)) continue

                window.Echo.channel(`admin.site.${siteId}`)
                    .listen('NewConversation', () => this.loadConversations(true))

                this._boundSiteIds.add(siteId)
            }
        },

        leaveRealtimeChannels() {
            if (!window.Echo) return

            for (const conversationId of this._boundConversationIds) {
                window.Echo.leave(`conversation.${conversationId}`)
            }

            for (const siteId of this._boundSiteIds) {
                window.Echo.leave(`admin.site.${siteId}`)
            }

            this._boundConversationIds.clear()
            this._boundSiteIds.clear()
        },

        handleRealtimeMessage(message) {
            if (!message?.id || !message?.conversation_id) return

            const normalized = this.normalizeMessage(message)
            const conversationId = String(normalized.conversation_id)

            this.upsertConversationFromMessage(conversationId, normalized)

            if (String(this.activeId) !== conversationId) {
                return
            }

            if (this.messages.some(existing => String(existing.id) === String(normalized.id))) {
                return
            }

            this.messages.push(normalized)
            this.messages.sort((a, b) => new Date(a.created_at) - new Date(b.created_at))

            if (normalized.sender_type === 'visitor') {
                window.__chatpilot_api.post(`/v1/admin/conversations/${conversationId}/read`).catch(() => {})
            }

            this.$nextTick(() => this.scrollToBottom())
        },

        handleRealtimeRead(conversationId) {
            const targetId = String(conversationId)
            const conversation = this.conversations.find(conv => String(conv.id) === targetId)
            if (conversation) {
                conversation.unread_count = 0
            }

            if (String(this.activeId) !== targetId) return

            const readAt = new Date().toISOString()
            this.messages = this.messages.map(message => {
                if (message.sender_type !== 'visitor') return message
                return { ...message, read_at: readAt }
            })
        },

        handleVisitorStatus(conversationId, online) {
            const conversation = this.conversations.find(conv => String(conv.id) === String(conversationId))
            if (conversation) {
                conversation.visitor_online = !!online
            }
        },

        upsertConversationFromMessage(conversationId, message) {
            const conversation = this.conversations.find(conv => String(conv.id) === conversationId)
            if (!conversation) {
                this.loadConversations(true)
                return
            }

            conversation.last_message = message.text || ''
            conversation.last_message_at = message.created_at

            if (message.sender_type === 'visitor' && String(this.activeId) !== conversationId) {
                conversation.unread_count = Number(conversation.unread_count || 0) + 1
            }

            this.promoteConversation(conversationId)
        },

        promoteConversation(conversationId) {
            const index = this.conversations.findIndex(conv => String(conv.id) === conversationId)
            if (index <= 0) return

            const [conversation] = this.conversations.splice(index, 1)
            this.conversations.unshift(conversation)
        },

        normalizeMessage(message) {
            return {
                ...message,
                sender_type: message.sender_type || message.sender,
                created_at: message.created_at || message.timestamp,
            }
        }
    }
}
