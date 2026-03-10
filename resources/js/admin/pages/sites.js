export const SUPPORTED_LANGUAGES = [
    { code: 'en', name: 'English' },
    { code: 'nl', name: 'Nederlands' },
    { code: 'de', name: 'Deutsch' },
    { code: 'fr', name: 'Français' },
    { code: 'es', name: 'Español' },
    { code: 'pt', name: 'Português' },
    { code: 'tr', name: 'Türkçe' },
    { code: 'zh', name: '中文' },
    { code: 'ja', name: '日本語' },
    { code: 'ko', name: '한국어' },
    { code: 'ar', name: 'العربية' },
    { code: 'ru', name: 'Русский' },
    { code: 'hi', name: 'हिन्दी' },
]

export function sitesPage() {
    return {
        sites: [],
        loading: true,
        error: '',
        editing: false,
        editingSite: null,
        activeTab: 'general',
        form: {},
        formError: '',
        formSuccess: '',
        saving: false,
        newApiKey: '',
        regenerating: false,
        showApiKeyConfirm: false,
        showAiKey: false,

        init() {
            this.loadSites()
        },

        async loadSites() {
            this.loading = true
            try {
                const data = await window.__chatpilot_api.get('/v1/admin/sites')
                this.sites = data.sites || []
            } catch (err) {
                this.error = err.message
            } finally {
                this.loading = false
            }
        },

        openCreate() {
            this.editingSite = null
            this.form = {
                name: '',
                domain: '',
                ai_provider: 'none',
                ai_api_key: '',
                ai_system_prompt: '',
                is_active: true,
                settings: {
                    language: 'en',
                    ai: { respond_when_offline: true },
                    widget: { theme: 'light', position: 'bottom-right', greeting: 'Hi! How can we help you?' },
                    rate_limit: { cooldown_seconds: 3, max_messages_per_hour: 50 },
                }
            }
            this.activeTab = 'general'
            this.formError = ''
            this.formSuccess = ''
            this.newApiKey = ''
            this.showApiKeyConfirm = false
            this.showAiKey = false
            this.editing = true
        },

        openEdit(site) {
            this.editingSite = site
            this.form = {
                name: site.name || '',
                domain: site.domain || '',
                ai_provider: site.ai_provider || 'none',
                ai_api_key: '',
                ai_system_prompt: site.ai_system_prompt || '',
                is_active: site.is_active !== false,
                settings: {
                    language: site.settings?.language || 'en',
                    ai: { respond_when_offline: site.settings?.ai?.respond_when_offline !== false },
                    widget: {
                        theme: site.settings?.widget?.theme || 'light',
                        position: site.settings?.widget?.position || 'bottom-right',
                        greeting: site.settings?.widget?.greeting || 'Hi! How can we help you?',
                    },
                    rate_limit: {
                        cooldown_seconds: site.settings?.rate_limit?.cooldown_seconds || 3,
                        max_messages_per_hour: site.settings?.rate_limit?.max_messages_per_hour || 50,
                    },
                }
            }
            this.activeTab = 'general'
            this.formError = ''
            this.formSuccess = ''
            this.newApiKey = ''
            this.showApiKeyConfirm = false
            this.showAiKey = false
            this.editing = true
        },

        closeEdit() {
            this.editing = false
            this.editingSite = null
            this.newApiKey = ''
        },

        validateForm() {
            if (!this.form || typeof this.form !== 'object') return 'Form data is missing'
            if (!(this.form.name || '').trim()) return 'Site name is required'
            if (!(this.form.domain || '').trim()) return 'Domain is required'
            if (this.form.ai_provider !== 'none' && !this.editingSite && !(this.form.ai_api_key || '').trim()) {
                return 'AI API key is required when a provider is selected'
            }
            const rl = this.form.settings?.rate_limit || {}
            if (rl.cooldown_seconds < 1 || rl.cooldown_seconds > 60) return 'Cooldown must be 1-60 seconds'
            if (rl.max_messages_per_hour < 1 || rl.max_messages_per_hour > 1000) return 'Max messages must be 1-1000'
            const validThemes = ['light', 'dark']
            if (!validThemes.includes(this.form.settings?.widget?.theme)) return 'Invalid theme'
            const validPositions = ['bottom-right', 'bottom-left']
            if (!validPositions.includes(this.form.settings?.widget?.position)) return 'Invalid position'
            const validLangs = SUPPORTED_LANGUAGES.map(l => l.code)
            if (!validLangs.includes(this.form.settings?.language)) return 'Invalid language'
            return null
        },

        async saveForm() {
            this.formError = ''
            this.formSuccess = ''

            if (!this.form || typeof this.form !== 'object') {
                this.formError = 'Form data could not be loaded. Please refresh the page.'

                return
            }

            const err = this.validateForm()
            if (err) {
                this.formError = err

                return
            }

            this.saving = true

            try {
                const payload = {
                    name: (this.form.name || '').trim(),
                    domain: (this.form.domain || '').trim(),
                    ai_provider: this.form.ai_provider,
                    ai_system_prompt: this.form.ai_system_prompt || null,
                    is_active: Boolean(this.form.is_active),
                    settings: this.form.settings || {},
                }

                if ((this.form.ai_api_key || '').trim()) {
                    payload.ai_api_key = this.form.ai_api_key.trim()
                }

                if (this.editingSite) {
                    await window.__chatpilot_api.patch(`/v1/admin/sites/${this.editingSite.id}`, payload)
                    this.formSuccess = 'Site updated successfully'
                } else {
                    const data = await window.__chatpilot_api.post('/v1/admin/sites', payload)
                    this.newApiKey = data?.api_key || ''
                    this.formSuccess = 'Site created successfully'
                    this.editingSite = data?.site || null
                }

                await this.loadSites()
            } catch (err) {
                this.formError = err.response?.data?.message || err.data?.message || err.message || 'Save failed'
            } finally {
                this.saving = false
            }
        },

        async regenerateKey() {
            if (!this.editingSite) return
            this.regenerating = true
            this.showApiKeyConfirm = false
            try {
                const data = await window.__chatpilot_api.post(`/v1/admin/sites/${this.editingSite.id}/regenerate-key`)
                this.newApiKey = data.api_key || ''
                this.formSuccess = 'API key regenerated. Update your widget script.'
            } catch (err) {
                this.formError = err.message
            } finally {
                this.regenerating = false
            }
        },

        copyToClipboard(text) {
            navigator.clipboard.writeText(text).then(() => {
                this.formSuccess = 'Copied to clipboard!'
                setTimeout(() => { if (this.formSuccess === 'Copied to clipboard!') this.formSuccess = '' }, 2000)
            })
        },

        getEmbedSnippet() {
            if (!this.editingSite) return ''
            const origin = window.location.origin
            const key = this.newApiKey || 'sk_YOUR_SITE_KEY'
            return '<script src="' + origin + '/chatpilot-widget.js"></script>\n<script>\n  ChatPilotWidget.init({\n    siteKey: \'' + key + '\',\n    apiUrl: \'' + origin + '\'\n  });\n</script>'
        }
    }
}
