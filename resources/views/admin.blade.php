<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>ChatPilot Admin</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/admin/app.js'])
    <style>[x-cloak]{display:none!important}</style>
</head>
<body class="bg-gray-50 text-gray-900 min-h-screen" style="font-family: 'Inter', ui-sans-serif, system-ui, sans-serif;">

<div x-data="app" @login-success.window="onLogin($event.detail)">

    {{-- ===== LOGIN PAGE ===== --}}
    <template x-if="page === 'login'">
        <div x-data="loginPage">
            <div class="min-h-screen flex items-center justify-center bg-gray-50 px-4">
                <div class="w-full max-w-sm">
                    <div class="bg-white rounded-xl shadow-lg p-8">
                        <h1 class="text-2xl font-bold text-center mb-2">ChatPilot</h1>
                        <p class="text-gray-500 text-center text-sm mb-6">Sign in to your admin panel</p>
                        <div x-show="error" x-cloak class="mb-4 p-3 bg-red-50 border border-red-200 text-red-700 text-sm rounded-lg" x-text="error"></div>
                        <form @submit.prevent="submit()">
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                                <input type="email" x-model="email" required
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                       placeholder="admin@example.com">
                            </div>
                            <div class="mb-6">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                                <input type="password" x-model="password" required
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                       placeholder="••••••••">
                            </div>
                            <button type="submit" :disabled="loading"
                                    class="w-full py-2.5 bg-gray-900 text-white rounded-lg text-sm font-medium hover:bg-gray-800 disabled:opacity-50 transition-colors">
                                <span x-show="!loading">Sign In</span>
                                <span x-show="loading" x-cloak>Signing in...</span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </template>

    {{-- ===== AUTHENTICATED LAYOUT ===== --}}
    <template x-if="page !== 'login'">
        <div x-data="layoutComponent" x-init="init()" class="flex min-h-screen">
            {{-- Sidebar --}}
            <aside class="w-64 bg-gray-900 text-white flex-shrink-0 flex flex-col fixed top-0 left-0 bottom-0 z-40 transition-transform duration-200"
                   :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full md:translate-x-0'">
                <div class="p-4 border-b border-gray-700">
                    <h1 class="text-xl font-bold">ChatPilot</h1>
                    <p class="text-sm text-gray-400 mt-1" x-text="user?.name || ''"></p>
                </div>
                <nav class="flex-1 p-3 space-y-1">
                    <a href="#dashboard" x-show="user?.role === 'super_admin'" x-cloak
                       class="flex items-center px-3 py-2 rounded-lg text-sm transition-colors"
                       :class="page === 'dashboard' ? 'bg-gray-700 text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white'">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-4 0a1 1 0 01-1-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 01-1 1h-2z"/></svg>
                        Dashboard
                    </a>
                    <a href="#conversations"
                       class="flex items-center px-3 py-2 rounded-lg text-sm transition-colors"
                       :class="page === 'conversations' ? 'bg-gray-700 text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white'">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                        Conversations
                    </a>
                    <a href="#sites" x-show="user?.role === 'super_admin'" x-cloak
                       class="flex items-center px-3 py-2 rounded-lg text-sm transition-colors"
                       :class="page === 'sites' ? 'bg-gray-700 text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white'">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9"/></svg>
                        Sites
                    </a>
                    <a href="#users" x-show="user?.role === 'super_admin'" x-cloak
                       class="flex items-center px-3 py-2 rounded-lg text-sm transition-colors"
                       :class="page === 'users' ? 'bg-gray-700 text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white'">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                        Users
                    </a>
                </nav>
                <div class="p-3 border-t border-gray-700">
                    <button @click="logout()"
                            class="flex items-center w-full px-3 py-2 rounded-lg text-sm text-gray-300 hover:bg-gray-800 hover:text-white transition-colors">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                        Logout
                    </button>
                </div>
            </aside>

            {{-- Mobile overlay --}}
            <div x-show="sidebarOpen" @click="sidebarOpen = false" class="fixed inset-0 bg-black/50 z-30 md:hidden" x-cloak></div>

            {{-- Main content --}}
            <div class="flex-1 md:ml-64">
                {{-- Mobile top bar --}}
                <div class="md:hidden flex items-center justify-between p-4 bg-white border-b border-gray-200">
                    <button @click="sidebarOpen = !sidebarOpen" class="p-1">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                    </button>
                    <span class="font-semibold">ChatPilot</span>
                    <div class="w-6"></div>
                </div>

                <div class="p-4 md:p-6">
                    {{-- ===== DASHBOARD ===== --}}
                    <template x-if="page === 'dashboard' && user?.role === 'super_admin'">
                        <div x-data="dashboardPage" x-init="init()">
                            <div class="flex items-center justify-between mb-6">
                                <h2 class="text-2xl font-bold">Dashboard</h2>
                                <div class="flex gap-1 bg-white rounded-lg border border-gray-200 p-1">
                                    <template x-for="p in ['7d','30d','90d']" :key="p">
                                        <button @click="changePeriod(p)"
                                                :class="period === p ? 'bg-gray-900 text-white' : 'text-gray-600 hover:bg-gray-100'"
                                                class="px-3 py-1 rounded-md text-sm font-medium transition-colors" x-text="p"></button>
                                    </template>
                                </div>
                            </div>
                            <div x-show="error" x-cloak class="mb-4 p-3 bg-red-50 border border-red-200 text-red-700 text-sm rounded-lg" x-text="error"></div>
                            <div x-show="loading" class="text-center py-12 text-gray-500">Loading statistics...</div>
                            <template x-if="stats && !loading">
                                <div>
                                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-4 mb-8">
                                        <div class="bg-white rounded-xl border border-gray-200 p-5">
                                            <p class="text-sm text-gray-500 mb-1">Total Conversations</p>
                                            <p class="text-3xl font-bold" x-text="stats.total_conversations ?? 0"></p>
                                        </div>
                                        <div class="bg-white rounded-xl border border-gray-200 p-5">
                                            <p class="text-sm text-gray-500 mb-1">Total Messages</p>
                                            <p class="text-3xl font-bold" x-text="stats.total_messages ?? 0"></p>
                                        </div>
                                        <div class="bg-white rounded-xl border border-gray-200 p-5">
                                            <p class="text-sm text-gray-500 mb-1">AI Messages</p>
                                            <p class="text-3xl font-bold" x-text="stats.ai_messages ?? 0"></p>
                                        </div>
                                        <div class="bg-white rounded-xl border border-gray-200 p-5">
                                            <p class="text-sm text-gray-500 mb-1">Avg Response Time</p>
                                            <p class="text-3xl font-bold" x-text="stats.avg_response_time_seconds ? Math.round(stats.avg_response_time_seconds) + 's' : '-'"></p>
                                        </div>
                                        <div class="bg-white rounded-xl border border-gray-200 p-5">
                                            <p class="text-sm text-gray-500 mb-1">5xx Error Rate</p>
                                            <p class="text-3xl font-bold" x-text="(stats.server_error_rate_percent ?? 0) + '%'"></p>
                                        </div>
                                        <div class="bg-white rounded-xl border border-gray-200 p-5">
                                            <p class="text-sm text-gray-500 mb-1">Job Failure Rate</p>
                                            <p class="text-3xl font-bold" x-text="(stats.job_failure_rate_percent ?? 0) + '%'"></p>
                                        </div>
                                    </div>
                                    <div class="bg-white rounded-xl border border-gray-200 p-5">
                                        <h3 class="text-sm font-medium text-gray-700 mb-4">Daily Conversations</h3>
                                        <div class="flex items-end gap-1 h-40" x-show="stats.daily_conversations?.length">
                                            <template x-for="(day, i) in stats.daily_conversations" :key="i">
                                                <div class="flex-1 flex flex-col items-center gap-1">
                                                    <span class="text-xs text-gray-500" x-text="day.count"></span>
                                                    <div class="w-full bg-blue-500 rounded-t-sm" :style="'height:' + barHeight(day.count)"></div>
                                                    <span class="text-xs text-gray-400 truncate w-full text-center" x-text="day.date?.slice(5) || ''"></span>
                                                </div>
                                            </template>
                                        </div>
                                        <p x-show="!stats.daily_conversations?.length" class="text-gray-400 text-sm text-center py-8">No data for this period</p>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </template>

                    {{-- ===== CONVERSATIONS ===== --}}
                    <template x-if="page === 'conversations'">
                        <div x-data="conversationsPage" x-init="init()" @destroy.window="destroy()">
                            <div class="flex items-center justify-between mb-4">
                                <h2 class="text-2xl font-bold">Conversations</h2>
                                <select x-model="displayLanguage" @change="onLanguageChange()"
                                        class="px-3 py-1.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <option value="en">English</option>
                                    <option value="nl">Nederlands</option>
                                    <option value="de">Deutsch</option>
                                    <option value="fr">Français</option>
                                    <option value="es">Español</option>
                                    <option value="pt">Português</option>
                                    <option value="tr">Türkçe</option>
                                    <option value="zh">中文</option>
                                    <option value="ja">日本語</option>
                                    <option value="ko">한국어</option>
                                    <option value="ar">العربية</option>
                                    <option value="ru">Русский</option>
                                    <option value="hi">हिन्दी</option>
                                </select>
                            </div>
                            <div x-show="error" x-cloak class="mb-4 p-3 bg-red-50 border border-red-200 text-red-700 text-sm rounded-lg" x-text="error"></div>
                            <div class="flex bg-white rounded-xl border border-gray-200 overflow-hidden" style="height: calc(100vh - 160px);">
                                {{-- Conversation list --}}
                                <div class="w-80 border-r border-gray-200 flex flex-col flex-shrink-0"
                                     :class="activeId ? 'hidden md:flex' : 'flex'">
                                    <div class="p-3 border-b border-gray-200 space-y-2">
                                        <input type="text" x-model="search" @input="onSearchInput()"
                                               placeholder="Search visitors..."
                                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                                        <select x-model="statusFilter" @change="loadConversations()"
                                                class="w-full px-3 py-1.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                                            <option value="">All</option>
                                            <option value="active">Active</option>
                                            <option value="closed">Closed</option>
                                        </select>
                                    </div>
                                    <div class="flex-1 overflow-y-auto">
                                        <div x-show="loading" class="p-4 text-center text-gray-400 text-sm">Loading...</div>
                                        <div x-show="!loading && conversations.length === 0" class="p-4 text-center text-gray-400 text-sm">No conversations</div>
                                        <template x-for="conv in conversations" :key="conv.id">
                                            <div @click="selectConversation(conv.id)"
                                                 class="p-3 border-b border-gray-100 cursor-pointer hover:bg-gray-50 transition-colors"
                                                 :class="activeId === conv.id ? 'bg-blue-50' : ''">
                                                <div class="flex items-center justify-between mb-1">
                                                    <div class="flex items-center gap-1.5 min-w-0">
                                                        <span class="inline-block w-2 h-2 rounded-full flex-shrink-0"
                                                              :class="conv.visitor_online ? 'bg-green-500' : 'bg-gray-300'"></span>
                                                        <span class="font-medium text-sm truncate" x-text="conv.visitor_name || 'Visitor'"></span>
                                                    </div>
                                                    <span class="text-xs text-gray-400 flex-shrink-0" x-text="formatTime(conv.last_message_at || conv.updated_at)"></span>
                                                </div>
                                                <p class="text-xs text-gray-500 truncate" x-text="conv.last_message || ''"></p>
                                                <div class="flex items-center gap-2 mt-1">
                                                    <span class="text-xs px-1.5 py-0.5 rounded-full"
                                                          :class="conv.status === 'active' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500'"
                                                          x-text="conv.status || 'active'"></span>
                                                    <span x-show="conv.unread_count > 0" x-cloak
                                                          class="text-xs bg-blue-500 text-white px-1.5 py-0.5 rounded-full"
                                                          x-text="conv.unread_count"></span>
                                                </div>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                                {{-- Message panel --}}
                                <div class="flex-1 flex flex-col" :class="activeId ? 'flex' : 'hidden md:flex'">
                                    <template x-if="!activeId">
                                        <div class="flex-1 flex items-center justify-center text-gray-400 text-sm">Select a conversation</div>
                                    </template>
                                    <template x-if="activeId">
                                        <div class="flex flex-col h-full">
                                            <div class="p-3 border-b border-gray-200 flex items-center gap-3">
                                                <button @click="activeId = null" class="md:hidden p-1">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                                                </button>
                                                <div>
                                                    <div class="flex items-center gap-1.5">
                                                        <span class="inline-block w-2 h-2 rounded-full"
                                                              :class="getActiveConversation()?.visitor_online ? 'bg-green-500' : 'bg-gray-300'"></span>
                                                        <p class="font-medium text-sm" x-text="getActiveConversation()?.visitor_name || 'Visitor'"></p>
                                                    </div>
                                                    <p class="text-xs text-gray-400" x-text="'Language: ' + (getActiveConversation()?.metadata?.language || '-')"></p>
                                                </div>
                                            </div>
                                            <div class="flex items-center gap-2">
                                                <span class="text-xs px-2 py-0.5 rounded-full"
                                                      :class="{
                                                          'bg-green-100 text-green-700': getActiveConversation()?.status === 'active',
                                                          'bg-gray-100 text-gray-600': getActiveConversation()?.status === 'closed',
                                                          'bg-yellow-100 text-yellow-700': getActiveConversation()?.status === 'archived'
                                                      }"
                                                      x-text="getActiveConversation()?.status || 'active'"></span>
                                                <button x-show="getActiveConversation()?.status === 'active'"
                                                        @click="updateStatus('closed')"
                                                        class="text-xs px-2 py-1 bg-red-50 text-red-600 rounded hover:bg-red-100 transition-colors">
                                                    Close
                                                </button>
                                                <button x-show="getActiveConversation()?.status === 'closed'"
                                                        @click="updateStatus('active')"
                                                        class="text-xs px-2 py-1 bg-green-50 text-green-600 rounded hover:bg-green-100 transition-colors">
                                                    Reopen
                                                </button>
                                                <button x-show="getActiveConversation()?.status === 'closed'"
                                                        @click="deleteConversation()"
                                                        class="text-xs px-2 py-1 bg-red-50 text-red-600 rounded hover:bg-red-100 transition-colors"
                                                        title="Delete conversation">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                                </button>
                                            </div>
                                            <div data-messages class="flex-1 overflow-y-auto p-4 space-y-3">
                                                <div x-show="messagesLoading" class="text-center text-gray-400 text-sm py-4">Loading messages...</div>
                                                <template x-for="msg in messages" :key="msg.id">
                                                    <div>
                                                        <div x-show="msg.sender_type === 'system'" class="flex justify-center">
                                                            <div class="px-3 py-1.5 rounded-full text-xs text-gray-500 bg-gray-100 border border-dashed border-gray-300 italic">
                                                                <span x-text="getMessageContent(msg)"></span>
                                                            </div>
                                                        </div>
                                                        <div x-show="msg.sender_type !== 'system'" class="flex" :class="msg.sender_type === 'admin' || msg.sender_type === 'ai' ? 'justify-end' : 'justify-start'">
                                                            <div class="max-w-xs lg:max-w-md px-3 py-2 rounded-lg text-sm"
                                                                 :class="{
                                                                     'bg-blue-500 text-white': msg.sender_type === 'admin',
                                                                     'bg-purple-500 text-white': msg.sender_type === 'ai',
                                                                     'bg-gray-100 text-gray-900': msg.sender_type === 'visitor'
                                                                 }">
                                                                <span x-show="msg.sender_type === 'ai'" class="text-xs opacity-75 block mb-1">AI</span>
                                                                <p x-text="getMessageContent(msg)" class="whitespace-pre-wrap break-words"></p>
                                                                <p class="text-xs mt-1 opacity-60" x-text="formatTime(msg.created_at)"></p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </template>
                                            </div>
                                            <div class="p-3 border-t border-gray-200" x-show="getActiveConversation()?.status !== 'closed'">
                                                <form @submit.prevent="sendMessage()" class="flex gap-2">
                                                    <input type="text" x-model="messageText" maxlength="1000"
                                                           placeholder="Type a message..."
                                                           class="flex-1 px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                                                    <button type="submit" :disabled="sending || !messageText.trim()"
                                                            class="px-4 py-2 bg-gray-900 text-white rounded-lg text-sm font-medium hover:bg-gray-800 disabled:opacity-50 transition-colors">
                                                        Send
                                                    </button>
                                                </form>
                                            </div>
                                            <div class="p-3 border-t border-gray-200 text-center text-sm text-gray-400 italic" x-show="getActiveConversation()?.status === 'closed'">
                                                This conversation is closed.
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </template>

                    {{-- ===== USERS (super_admin only) ===== --}}
                    <template x-if="page === 'users' && user?.role === 'super_admin'">
                        <div x-data="usersPage" x-init="init()">
                            <div class="flex items-center justify-between mb-6">
                                <h2 class="text-2xl font-bold">Users</h2>
                                <button @click="openCreate()" class="px-4 py-2 bg-gray-900 text-white rounded-lg text-sm font-medium hover:bg-gray-800 transition-colors">
                                    + Add User
                                </button>
                            </div>

                            <div x-show="error" x-cloak class="mb-4 p-3 bg-red-50 border border-red-200 text-red-700 text-sm rounded-lg" x-text="error"></div>
                            <div x-show="loading" class="text-center py-12 text-gray-500">Loading users...</div>

                            {{-- Users table --}}
                            <template x-if="!loading && users.length">
                                <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                                    <table class="w-full">
                                        <thead class="bg-gray-50 border-b border-gray-200">
                                            <tr>
                                                <th class="text-left px-4 py-3 text-xs font-medium text-gray-500 uppercase">Name</th>
                                                <th class="text-left px-4 py-3 text-xs font-medium text-gray-500 uppercase">Email</th>
                                                <th class="text-left px-4 py-3 text-xs font-medium text-gray-500 uppercase">Role</th>
                                                <th class="text-left px-4 py-3 text-xs font-medium text-gray-500 uppercase">Sites</th>
                                                <th class="text-left px-4 py-3 text-xs font-medium text-gray-500 uppercase">Created</th>
                                                <th class="text-right px-4 py-3 text-xs font-medium text-gray-500 uppercase">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-100">
                                            <template x-for="u in users" :key="u.id">
                                                <tr class="hover:bg-gray-50">
                                                    <td class="px-4 py-3 text-sm font-medium" x-text="u.name"></td>
                                                    <td class="px-4 py-3 text-sm text-gray-600" x-text="u.email"></td>
                                                    <td class="px-4 py-3">
                                                        <span class="px-2 py-0.5 text-xs font-medium rounded-full"
                                                              :class="u.role === 'super_admin' ? 'bg-purple-100 text-purple-700' : 'bg-blue-100 text-blue-700'"
                                                              x-text="u.role === 'super_admin' ? 'Super Admin' : 'Admin'"></span>
                                                    </td>
                                                    <td class="px-4 py-3 text-sm text-gray-600 max-w-sm truncate" :title="userSitesLabel(u)" x-text="userSitesLabel(u)"></td>
                                                    <td class="px-4 py-3 text-sm text-gray-500" x-text="formatDate(u.created_at)"></td>
                                                    <td class="px-4 py-3 text-right">
                                                        <button @click="deleteUser(u)"
                                                                x-show="u.id !== $store.appState.user?.id"
                                                                :disabled="deletingId === u.id"
                                                                class="text-red-600 hover:text-red-800 text-sm font-medium disabled:opacity-50">
                                                            <span x-show="deletingId !== u.id">Delete</span>
                                                            <span x-show="deletingId === u.id" x-cloak>...</span>
                                                        </button>
                                                    </td>
                                                </tr>
                                            </template>
                                        </tbody>
                                    </table>
                                </div>
                            </template>

                            <template x-if="!loading && !users.length">
                                <div class="text-center py-12 text-gray-500">No users found.</div>
                            </template>

                            {{-- Create user modal --}}
                            <template x-if="showCreateForm">
                                <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50" @click.self="closeCreate()">
                                    <div class="bg-white rounded-xl shadow-xl w-full max-w-md p-6 mx-4">
                                        <h3 class="text-lg font-bold mb-4">Add New User</h3>
                                        <div x-show="formError" x-cloak class="mb-4 p-3 bg-red-50 border border-red-200 text-red-700 text-sm rounded-lg" x-text="formError"></div>
                                        <div class="space-y-4">
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 mb-1">Name</label>
                                                <input type="text" x-model="form.name" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                                                <input type="email" x-model="form.email" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                                                <input type="password" x-model="form.password" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 mb-1">Site Access</label>
                                                <div class="max-h-40 overflow-y-auto border border-gray-300 rounded-lg p-2 space-y-1">
                                                    <template x-for="site in sites" :key="site.id">
                                                        <label class="flex items-center gap-2 text-sm text-gray-700 cursor-pointer">
                                                            <input type="checkbox"
                                                                   :checked="form.site_ids.includes(site.id)"
                                                                   @change="toggleSite(site.id)"
                                                                   class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                                            <span x-text="site.name + ' (' + site.domain + ')'"></span>
                                                        </label>
                                                    </template>
                                                    <p x-show="!sites.length" class="text-xs text-gray-500">No sites available. Create a site first.</p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="flex justify-end gap-3 mt-6">
                                            <button @click="closeCreate()" class="px-4 py-2 text-sm text-gray-600 hover:text-gray-800">Cancel</button>
                                            <button @click="createUser()" :disabled="saving"
                                                    class="px-4 py-2 bg-gray-900 text-white rounded-lg text-sm font-medium hover:bg-gray-800 disabled:opacity-50 transition-colors">
                                                <span x-show="!saving">Create User</span>
                                                <span x-show="saving" x-cloak>Creating...</span>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </template>

                    {{-- ===== SITES ===== --}}
                    <template x-if="page === 'sites' && user?.role === 'super_admin'">
                        <div x-data="sitesPage" x-init="init()">
                            <div class="flex items-center justify-between mb-6">
                                <h2 class="text-2xl font-bold" x-show="!editing">Sites</h2>
                                <button x-show="!editing" @click="openCreate()"
                                        class="px-4 py-2 bg-gray-900 text-white rounded-lg text-sm font-medium hover:bg-gray-800 transition-colors">
                                    + New Site
                                </button>
                            </div>

                            <div x-show="error && !editing" x-cloak class="mb-4 p-3 bg-red-50 border border-red-200 text-red-700 text-sm rounded-lg" x-text="error"></div>
                            <div x-show="loading && !editing" class="text-center py-12 text-gray-500">Loading sites...</div>

                            {{-- Site list --}}
                            <div x-show="!editing && !loading" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                <template x-for="site in sites" :key="site.id">
                                    <div @click="openEdit(site)"
                                         class="bg-white rounded-xl border border-gray-200 p-5 cursor-pointer hover:shadow-md transition-shadow">
                                        <div class="flex items-center justify-between mb-2">
                                            <h3 class="font-semibold text-lg truncate" x-text="site.name"></h3>
                                            <span class="text-xs px-2 py-1 rounded-full"
                                                  :class="site.is_active !== false ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500'"
                                                  x-text="site.is_active !== false ? 'Active' : 'Inactive'"></span>
                                        </div>
                                        <p class="text-sm text-gray-500 truncate" x-text="site.domain"></p>
                                        <div class="flex items-center gap-2 mt-3">
                                            <span class="text-xs bg-gray-100 text-gray-600 px-2 py-1 rounded"
                                                  x-text="(site.ai_provider || 'none').toUpperCase()"></span>
                                            <span class="text-xs text-gray-400">API: sk_****...</span>
                                        </div>
                                    </div>
                                </template>
                                <div x-show="sites.length === 0" class="col-span-full text-center py-12 text-gray-400">
                                    No sites yet. Create your first site to get started.
                                </div>
                            </div>

                            {{-- Edit/Create form --}}
                            <div x-show="editing" x-cloak>
                                <div class="flex items-center gap-3 mb-6">
                                    <button @click="closeEdit()" class="p-1 hover:bg-gray-100 rounded-lg transition-colors">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                                    </button>
                                    <h2 class="text-2xl font-bold" x-text="editingSite ? 'Edit Site' : 'New Site'"></h2>
                                </div>

                                <div x-show="formError" x-cloak class="mb-4 p-3 bg-red-50 border border-red-200 text-red-700 text-sm rounded-lg" x-text="formError"></div>
                                <div x-show="formSuccess" x-cloak class="mb-4 p-3 bg-green-50 border border-green-200 text-green-700 text-sm rounded-lg" x-text="formSuccess"></div>

                                {{-- New API key display --}}
                                <div x-show="newApiKey" x-cloak class="mb-4 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                                    <p class="text-sm font-medium text-yellow-800 mb-2">Your API Key (shown once):</p>
                                    <div class="flex items-center gap-2">
                                        <code class="flex-1 text-sm bg-white p-2 rounded border border-yellow-300 break-all" x-text="newApiKey"></code>
                                        <button @click="copyToClipboard(newApiKey)"
                                                class="px-3 py-2 bg-yellow-600 text-white rounded-lg text-sm hover:bg-yellow-700 transition-colors">Copy</button>
                                    </div>
                                </div>

                                {{-- Tabs --}}
                                <div class="flex gap-1 bg-gray-100 rounded-lg p-1 mb-6 flex-wrap">
                                    <template x-for="tab in [
                                        {id:'general',label:'General'},
                                        {id:'ai',label:'AI'},
                                        {id:'language',label:'Language'},
                                        {id:'widget',label:'Widget'},
                                        {id:'ratelimit',label:'Rate Limit'},
                                        {id:'embed',label:'Embed'}
                                    ]" :key="tab.id">
                                        <button x-show="tab.id !== 'embed' || editingSite"
                                                @click="activeTab = tab.id"
                                                :class="activeTab === tab.id ? 'bg-white shadow-sm' : 'hover:bg-gray-200'"
                                                class="px-3 py-1.5 rounded-md text-sm font-medium transition-colors"
                                                x-text="tab.label"></button>
                                    </template>
                                </div>

                                <div class="bg-white rounded-xl border border-gray-200 p-6">

                                    {{-- General Tab --}}
                                    <div x-show="activeTab === 'general'" class="space-y-4">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">Site Name</label>
                                            <input type="text" x-model="form.name"
                                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                                                   placeholder="My Website">
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">Domain</label>
                                            <input type="text" x-model="form.domain"
                                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                                                   placeholder="example.com">
                                        </div>
                                        <div class="flex items-center gap-3">
                                            <label class="relative inline-flex items-center cursor-pointer">
                                                <input type="checkbox" x-model="form.is_active" class="sr-only peer">
                                                <div class="w-9 h-5 bg-gray-200 peer-focus:ring-2 peer-focus:ring-blue-500 rounded-full peer peer-checked:bg-blue-600 transition-colors after:content-[''] after:absolute after:top-0.5 after:left-0.5 after:bg-white after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:after:translate-x-4"></div>
                                            </label>
                                            <span class="text-sm text-gray-700">Active</span>
                                        </div>
                                        {{-- API Key section --}}
                                        <div x-show="editingSite" class="pt-4 border-t border-gray-200">
                                            <label class="block text-sm font-medium text-gray-700 mb-2">API Key</label>
                                            <div class="flex items-center gap-2">
                                                <code class="flex-1 text-sm bg-gray-50 p-2 rounded border border-gray-200 text-gray-500">sk_****...</code>
                                                <button x-show="!showApiKeyConfirm" @click="showApiKeyConfirm = true"
                                                        class="px-3 py-2 bg-red-50 text-red-700 border border-red-200 rounded-lg text-sm hover:bg-red-100 transition-colors">
                                                    Regenerate
                                                </button>
                                            </div>
                                            <div x-show="showApiKeyConfirm" x-cloak class="mt-2 p-3 bg-red-50 border border-red-200 rounded-lg">
                                                <p class="text-sm text-red-700 mb-2">This will invalidate the current key. Your widget will stop working until you update it.</p>
                                                <div class="flex gap-2">
                                                    <button @click="regenerateKey()" :disabled="regenerating"
                                                            class="px-3 py-1.5 bg-red-600 text-white rounded-lg text-sm hover:bg-red-700 disabled:opacity-50 transition-colors">
                                                        <span x-show="!regenerating">Confirm Regenerate</span>
                                                        <span x-show="regenerating" x-cloak>Regenerating...</span>
                                                    </button>
                                                    <button @click="showApiKeyConfirm = false"
                                                            class="px-3 py-1.5 bg-white border border-gray-300 rounded-lg text-sm hover:bg-gray-50 transition-colors">Cancel</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- AI Tab --}}
                                    <div x-show="activeTab === 'ai'" class="space-y-4">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">AI Provider</label>
                                            <select x-model="form.ai_provider"
                                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                                                <option value="none">None (manual only)</option>
                                                <option value="gemini">Google Gemini</option>
                                                <option value="openai">OpenAI</option>
                                                <option value="claude">Claude (Anthropic)</option>
                                            </select>
                                            <p class="text-xs text-gray-400 mt-1">Select the AI provider for automatic responses when admin is offline.</p>
                                        </div>
                                        <div x-show="form.ai_provider !== 'none'">
                                            <label class="block text-sm font-medium text-gray-700 mb-1">AI API Key</label>
                                            <div class="relative">
                                                <input :type="showAiKey ? 'text' : 'password'" x-model="form.ai_api_key"
                                                       class="w-full px-3 py-2 pr-10 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                                                       :placeholder="editingSite ? 'Leave empty to keep current key' : 'Enter API key'">
                                                <button type="button" @click="showAiKey = !showAiKey"
                                                        class="absolute right-2 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                                                    <svg x-show="!showAiKey" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                                    <svg x-show="showAiKey" x-cloak class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>
                                                </button>
                                            </div>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">System Prompt</label>
                                            <textarea x-model="form.ai_system_prompt" rows="5"
                                                      class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                                                      placeholder="Example:
Role: You are the customer support assistant for ChatPilot.
Language: Always reply in the visitor's language.
Scope: Only answer about our product, setup, pricing, and troubleshooting.
Rules: Do not invent facts; ask clarifying questions when needed.
Escalation: For billing or security issues, hand off to a human agent.
Style: Keep replies under 5 sentences and end with one helpful question."></textarea>
                                            <p class="text-xs text-gray-400 mt-1">Defines how the AI behaves. This prompt is saved always and used when AI provider is enabled.</p>
                                        </div>
                                        <div class="flex items-center gap-3">
                                            <label class="relative inline-flex items-center cursor-pointer">
                                                <input type="checkbox" x-model="form.settings.ai.respond_when_offline" class="sr-only peer">
                                                <div class="w-9 h-5 bg-gray-200 peer-focus:ring-2 peer-focus:ring-blue-500 rounded-full peer peer-checked:bg-blue-600 transition-colors after:content-[''] after:absolute after:top-0.5 after:left-0.5 after:bg-white after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:after:translate-x-4"></div>
                                            </label>
                                            <div>
                                                <span class="text-sm text-gray-700">Respond when offline</span>
                                                <p class="text-xs text-gray-400">AI automatically responds when no admin is online.</p>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Language Tab --}}
                                    <div x-show="activeTab === 'language'" class="space-y-4">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">Default Language</label>
                                            <select x-model="form.settings.language"
                                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                                                <option value="en">English</option>
                                                <option value="nl">Nederlands</option>
                                                <option value="de">Deutsch</option>
                                                <option value="fr">Français</option>
                                                <option value="es">Español</option>
                                                <option value="pt">Português</option>
                                                <option value="tr">Türkçe</option>
                                                <option value="zh">中文</option>
                                                <option value="ja">日本語</option>
                                                <option value="ko">한국어</option>
                                                <option value="ar">العربية</option>
                                                <option value="ru">Русский</option>
                                                <option value="hi">हिन्दी</option>
                                            </select>
                                            <p class="text-xs text-gray-400 mt-1">Fallback language when visitor's language cannot be determined.</p>
                                        </div>
                                        <div class="pt-4 border-t border-gray-200">
                                            <h4 class="text-sm font-medium text-gray-700 mb-2">Supported Languages</h4>
                                            <p class="text-xs text-gray-400 mb-3">Messages are automatically translated between these languages.</p>
                                            <div class="flex flex-wrap gap-2">
                                                <span class="text-xs bg-gray-100 text-gray-600 px-2 py-1 rounded-full">English (en)</span>
                                                <span class="text-xs bg-gray-100 text-gray-600 px-2 py-1 rounded-full">Nederlands (nl)</span>
                                                <span class="text-xs bg-gray-100 text-gray-600 px-2 py-1 rounded-full">Deutsch (de)</span>
                                                <span class="text-xs bg-gray-100 text-gray-600 px-2 py-1 rounded-full">Français (fr)</span>
                                                <span class="text-xs bg-gray-100 text-gray-600 px-2 py-1 rounded-full">Español (es)</span>
                                                <span class="text-xs bg-gray-100 text-gray-600 px-2 py-1 rounded-full">Português (pt)</span>
                                                <span class="text-xs bg-gray-100 text-gray-600 px-2 py-1 rounded-full">Türkçe (tr)</span>
                                                <span class="text-xs bg-gray-100 text-gray-600 px-2 py-1 rounded-full">中文 (zh)</span>
                                                <span class="text-xs bg-gray-100 text-gray-600 px-2 py-1 rounded-full">日本語 (ja)</span>
                                                <span class="text-xs bg-gray-100 text-gray-600 px-2 py-1 rounded-full">한국어 (ko)</span>
                                                <span class="text-xs bg-gray-100 text-gray-600 px-2 py-1 rounded-full">العربية (ar)</span>
                                                <span class="text-xs bg-gray-100 text-gray-600 px-2 py-1 rounded-full">Русский (ru)</span>
                                                <span class="text-xs bg-gray-100 text-gray-600 px-2 py-1 rounded-full">हिन्दी (hi)</span>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Widget Tab --}}
                                    <div x-show="activeTab === 'widget'" class="space-y-4">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">Theme</label>
                                            <select x-model="form.settings.widget.theme"
                                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                                                <option value="light">Light</option>
                                                <option value="dark">Dark</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">Position</label>
                                            <select x-model="form.settings.widget.position"
                                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                                                <option value="bottom-right">Bottom Right</option>
                                                <option value="bottom-left">Bottom Left</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">Greeting Message</label>
                                            <textarea x-model="form.settings.widget.greeting" rows="2"
                                                      class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                                                      placeholder="Hi! How can we help you?"></textarea>
                                            <p class="text-xs text-gray-400 mt-1">Shown to visitors when they open the chat widget.</p>
                                        </div>
                                    </div>

                                    {{-- Rate Limit Tab --}}
                                    <div x-show="activeTab === 'ratelimit'" class="space-y-4">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">Cooldown Between Messages (seconds)</label>
                                            <input type="number" x-model.number="form.settings.rate_limit.cooldown_seconds"
                                                   min="1" max="60"
                                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                                            <p class="text-xs text-gray-400 mt-1">Minimum seconds between visitor messages (1-60).</p>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">Max Messages Per Hour</label>
                                            <input type="number" x-model.number="form.settings.rate_limit.max_messages_per_hour"
                                                   min="1" max="1000"
                                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                                            <p class="text-xs text-gray-400 mt-1">Maximum messages a visitor can send per hour (1-1000).</p>
                                        </div>
                                    </div>

                                    {{-- Embed Tab --}}
                                    <div x-show="activeTab === 'embed'" class="space-y-4">
                                        <h4 class="text-sm font-medium text-gray-700 mb-2">Embed Code</h4>
                                        <p class="text-xs text-gray-400 mb-3">Add this code before &lt;/body&gt; on your website.</p>
                                        <div class="relative">
                                            <pre class="bg-gray-900 text-green-400 p-4 rounded-lg text-sm overflow-x-auto whitespace-pre-wrap"><code x-text="getEmbedSnippet()"></code></pre>
                                            <button @click="copyToClipboard(getEmbedSnippet())"
                                                    class="absolute top-2 right-2 px-2 py-1 bg-gray-700 text-gray-300 rounded text-xs hover:bg-gray-600 transition-colors">Copy</button>
                                        </div>
                                    </div>

                                    {{-- Save button --}}
                                    <div class="mt-6 pt-4 border-t border-gray-200 flex justify-end">
                                        <button @click="saveForm()" :disabled="saving"
                                                class="px-6 py-2.5 bg-gray-900 text-white rounded-lg text-sm font-medium hover:bg-gray-800 disabled:opacity-50 transition-colors">
                                            <span x-show="!saving" x-text="editingSite ? 'Save Changes' : 'Create Site'"></span>
                                            <span x-show="saving" x-cloak>Saving...</span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </template>

                </div>
            </div>
        </div>
    </template>

</div>

</body>
</html>
