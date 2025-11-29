<!-- Comprehensive Environment Configuration Tab -->
<div x-show="activeTab === 'env-config'" x-cloak>
    <div class="space-y-4">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-sm font-semibold">Environment Configuration</h2>
                <p class="text-[10px] text-muted-foreground mt-0.5">
                    Manage all system settings without editing .env file directly
                </p>
            </div>
            <div class="flex items-center gap-2">
                <button
                    type="button"
                    class="btn btn-sm btn-ghost border"
                    @click="syncFromEnv()"
                    :disabled="isSyncingEnv"
                    title="Sync configuration from .env file to database">
                    <svg x-show="isSyncingEnv" class="h-3 w-3 md:mr-1 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" d="M4 12a8 8 0 0 1 8-8" stroke="currentColor" stroke-width="4" stroke-linecap="round"></path>
                    </svg>
                    <svg x-show="!isSyncingEnv" class="h-3 w-3 md:mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                    </svg>
                    <span class="hidden md:inline text-xs">Sync from .env</span>
                </button>
                <button
                    type="button"
                    class="btn btn-sm btn-primary"
                    @click="saveEnvConfig()"
                    :disabled="isSavingEnv">
                    <svg x-show="isSavingEnv" class="h-3 w-3 mr-1 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" d="M4 12a8 8 0 0 1 8-8" stroke="currentColor" stroke-width="4" stroke-linecap="round"></path>
                    </svg>
                    <span x-text="isSavingEnv ? 'Saving...' : 'Save All Changes'"></span>
                </button>
            </div>
        </div>

        <!-- Accordion Sections -->
        <div class="space-y-2" x-data="{ activeSection: 'database' }">

            <!-- 1. Database Configuration -->
            <div class="card">
                <button
                    type="button"
                    class="w-full p-3 flex items-center justify-between hover:bg-muted/5 transition-colors"
                    @click="activeSection = activeSection === 'database' ? null : 'database'">
                    <div class="flex items-center gap-2">
                        <svg class="h-4 w-4 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"></path>
                        </svg>
                        <div class="text-left">
                            <h3 class="text-xs font-semibold">Database Configuration</h3>
                            <p class="text-[10px] text-muted-foreground">MySQL connection settings</p>
                        </div>
                    </div>
                    <svg class="h-4 w-4 text-muted-foreground transition-transform" :class="{ 'rotate-180': activeSection === 'database' }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>
                <div x-show="activeSection === 'database'" x-collapse class="border-t p-3">
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block text-[10px] font-medium mb-1">Host</label>
                            <input type="text" class="input input-sm w-full" x-model="envConfig.DB_HOST" placeholder="localhost">
                        </div>
                        <div>
                            <label class="block text-[10px] font-medium mb-1">Port</label>
                            <input type="number" class="input input-sm w-full" x-model="envConfig.DB_PORT" placeholder="3306">
                        </div>
                        <div>
                            <label class="block text-[10px] font-medium mb-1">Database Name</label>
                            <input type="text" class="input input-sm w-full" x-model="envConfig.DB_NAME" placeholder="gassstea_srp">
                        </div>
                        <div>
                            <label class="block text-[10px] font-medium mb-1">Username</label>
                            <input type="text" class="input input-sm w-full" x-model="envConfig.DB_USER" placeholder="gassstea_srp">
                        </div>
                        <div class="col-span-2">
                            <label class="block text-[10px] font-medium mb-1">Password</label>
                            <input type="password" class="input input-sm w-full" x-model="envConfig.DB_PASS" placeholder="••••••••">
                        </div>
                    </div>
                    <button type="button" class="btn btn-ghost border btn-sm text-[10px] mt-2" @click="testDatabaseConnection()" :disabled="isTestingDb">
                        <svg x-show="isTestingDb" class="h-3 w-3 mr-1 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" d="M4 12a8 8 0 0 1 8-8" stroke="currentColor" stroke-width="4" stroke-linecap="round"></path>
                        </svg>
                        <span x-text="isTestingDb ? 'Testing...' : 'Test Connection'"></span>
                    </button>
                </div>
            </div>

            <!-- 2. Domain Configuration -->
            <div class="card">
                <button
                    type="button"
                    class="w-full p-3 flex items-center justify-between hover:bg-muted/5 transition-colors"
                    @click="activeSection = activeSection === 'domains' ? null : 'domains'">
                    <div class="flex items-center gap-2">
                        <svg class="h-4 w-4 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"></path>
                        </svg>
                        <div class="text-left">
                            <h3 class="text-xs font-semibold">Domain Configuration</h3>
                            <p class="text-[10px] text-muted-foreground">Brand and tracking domain URLs</p>
                        </div>
                    </div>
                    <svg class="h-4 w-4 text-muted-foreground transition-transform" :class="{ 'rotate-180': activeSection === 'domains' }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>
                <div x-show="activeSection === 'domains'" x-collapse class="border-t p-3">
                    <div class="space-y-3">
                        <!-- Brand Domain -->
                        <div>
                            <h4 class="text-[11px] font-semibold mb-1.5 text-primary">Brand Domain</h4>
                            <div class="grid grid-cols-2 gap-2">
                                <div>
                                    <label class="block text-[10px] font-medium mb-1">Main URL</label>
                                    <input type="url" class="input input-sm w-full" x-model="envConfig.APP_URL" placeholder="https://trackng.app">
                                </div>
                                <div>
                                    <label class="block text-[10px] font-medium mb-1">Panel URL</label>
                                    <input type="url" class="input input-sm w-full" x-model="envConfig.APP_PANEL_URL" placeholder="https://panel.trackng.app">
                                </div>
                            </div>
                        </div>
                        <!-- Tracking Domain -->
                        <div>
                            <h4 class="text-[11px] font-semibold mb-1.5 text-primary">Tracking Domain</h4>
                            <div class="grid grid-cols-2 gap-2">
                                <div>
                                    <label class="block text-[10px] font-medium mb-1">Primary Domain</label>
                                    <input type="text" class="input input-sm w-full" x-model="envConfig.TRACKING_PRIMARY_DOMAIN" placeholder="qvtrk.com">
                                </div>
                                <div>
                                    <label class="block text-[10px] font-medium mb-1">Tracking URL</label>
                                    <input type="url" class="input input-sm w-full" x-model="envConfig.TRACKING_DOMAIN" placeholder="https://qvtrk.com">
                                </div>
                                <div>
                                    <label class="block text-[10px] font-medium mb-1">Redirect URL</label>
                                    <input type="url" class="input input-sm w-full" x-model="envConfig.TRACKING_REDIRECT_URL" placeholder="https://t.qvtrk.com">
                                </div>
                                <div>
                                    <label class="block text-[10px] font-medium mb-1">Decision API</label>
                                    <input type="url" class="input input-sm w-full" x-model="envConfig.TRACKING_DECISION_API" placeholder="https://api.qvtrk.com">
                                </div>
                                <div class="col-span-2">
                                    <label class="block text-[10px] font-medium mb-1">Postback URL</label>
                                    <input type="url" class="input input-sm w-full" x-model="envConfig.TRACKING_POSTBACK_URL" placeholder="https://postback.qvtrk.com">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 3. API Keys Configuration -->
            <div class="card">
                <button
                    type="button"
                    class="w-full p-3 flex items-center justify-between hover:bg-muted/5 transition-colors"
                    @click="activeSection = activeSection === 'apikeys' ? null : 'apikeys'">
                    <div class="flex items-center gap-2">
                        <svg class="h-4 w-4 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path>
                        </svg>
                        <div class="text-left">
                            <h3 class="text-xs font-semibold">API Keys & Authentication</h3>
                            <p class="text-[10px] text-muted-foreground">Secure API access keys</p>
                        </div>
                    </div>
                    <svg class="h-4 w-4 text-muted-foreground transition-transform" :class="{ 'rotate-180': activeSection === 'apikeys' }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>
                <div x-show="activeSection === 'apikeys'" x-collapse class="border-t p-3">
                    <div class="space-y-2">
                        <div>
                            <label class="block text-[10px] font-medium mb-1">Internal API Key</label>
                            <div class="flex gap-1">
                                <input :type="showInternalKey ? 'text' : 'password'" class="input input-sm flex-1 font-mono text-[10px]" x-model="envConfig.API_KEY_INTERNAL" placeholder="32 character key">
                                <button type="button" class="btn btn-sm btn-ghost" @click="showInternalKey = !showInternalKey">
                                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path x-show="!showInternalKey" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                        <path x-show="showInternalKey" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"></path>
                                    </svg>
                                </button>
                                <button type="button" class="btn btn-sm btn-ghost" @click="generateApiKey('internal')">
                                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                    </svg>
                                </button>
                            </div>
                        </div>
                        <div>
                            <label class="block text-[10px] font-medium mb-1">External API Key</label>
                            <div class="flex gap-1">
                                <input :type="showExternalKey ? 'text' : 'password'" class="input input-sm flex-1 font-mono text-[10px]" x-model="envConfig.API_KEY_EXTERNAL" placeholder="32 character key">
                                <button type="button" class="btn btn-sm btn-ghost" @click="showExternalKey = !showExternalKey">
                                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path x-show="!showExternalKey" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                        <path x-show="showExternalKey" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"></path>
                                    </svg>
                                </button>
                                <button type="button" class="btn btn-sm btn-ghost" @click="generateApiKey('external')">
                                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                    </svg>
                                </button>
                            </div>
                        </div>
                        <div>
                            <label class="block text-[10px] font-medium mb-1">SRP Decision API</label>
                            <input type="url" class="input input-sm w-full" x-model="envConfig.SRP_API_URL" placeholder="https://api.qvtrk.com/decision.php">
                        </div>
                        <div>
                            <label class="block text-[10px] font-medium mb-1">SRP API Key</label>
                            <div class="flex gap-1">
                                <input :type="showSrpKey ? 'text' : 'password'" class="input input-sm flex-1 font-mono text-[10px]" x-model="envConfig.SRP_API_KEY" placeholder="32 character key">
                                <button type="button" class="btn btn-sm btn-ghost" @click="showSrpKey = !showSrpKey">
                                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path x-show="!showSrpKey" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                        <path x-show="showSrpKey" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"></path>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 4. Application Settings -->
            <div class="card">
                <button
                    type="button"
                    class="w-full p-3 flex items-center justify-between hover:bg-muted/5 transition-colors"
                    @click="activeSection = activeSection === 'application' ? null : 'application'">
                    <div class="flex items-center gap-2">
                        <svg class="h-4 w-4 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                        <div class="text-left">
                            <h3 class="text-xs font-semibold">Application Settings</h3>
                            <p class="text-[10px] text-muted-foreground">General configuration</p>
                        </div>
                    </div>
                    <svg class="h-4 w-4 text-muted-foreground transition-transform" :class="{ 'rotate-180': activeSection === 'application' }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>
                <div x-show="activeSection === 'application'" x-collapse class="border-t p-3">
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block text-[10px] font-medium mb-1">App Name</label>
                            <input type="text" class="input input-sm w-full" x-model="envConfig.APP_NAME" placeholder="Smart Redirect Platform">
                        </div>
                        <div>
                            <label class="block text-[10px] font-medium mb-1">Timezone</label>
                            <select class="input input-sm w-full" x-model="envConfig.APP_TIMEZONE">
                                <option value="UTC">UTC</option>
                                <option value="Asia/Jakarta">Asia/Jakarta</option>
                                <option value="Asia/Singapore">Asia/Singapore</option>
                                <option value="America/New_York">America/New_York</option>
                                <option value="Europe/London">Europe/London</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-[10px] font-medium mb-1">Environment</label>
                            <select class="input input-sm w-full" x-model="envConfig.APP_ENV">
                                <option value="development">Development</option>
                                <option value="staging">Staging</option>
                                <option value="production">Production</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-[10px] font-medium mb-1">SRP Environment</label>
                            <select class="input input-sm w-full" x-model="envConfig.SRP_ENV">
                                <option value="development">Development</option>
                                <option value="staging">Staging</option>
                                <option value="production">Production</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-[10px] font-medium mb-1">Debug Mode</label>
                            <select class="input input-sm w-full" x-model="envConfig.APP_DEBUG">
                                <option value="false">Disabled</option>
                                <option value="true">Enabled</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-[10px] font-medium mb-1">Maintenance Mode</label>
                            <select class="input input-sm w-full" x-model="envConfig.MAINTENANCE_MODE">
                                <option value="false">Disabled</option>
                                <option value="true">Enabled</option>
                            </select>
                        </div>
                    </div>
                    <div class="mt-2">
                        <label class="block text-[10px] font-medium mb-1">Maintenance Message</label>
                        <input type="text" class="input input-sm w-full" x-model="envConfig.MAINTENANCE_MESSAGE" placeholder="System under maintenance. Please try again later.">
                    </div>
                </div>
            </div>

            <!-- 5. Session & Security -->
            <div class="card">
                <button
                    type="button"
                    class="w-full p-3 flex items-center justify-between hover:bg-muted/5 transition-colors"
                    @click="activeSection = activeSection === 'security' ? null : 'security'">
                    <div class="flex items-center gap-2">
                        <svg class="h-4 w-4 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                        </svg>
                        <div class="text-left">
                            <h3 class="text-xs font-semibold">Session & Security</h3>
                            <p class="text-[10px] text-muted-foreground">Security and session configuration</p>
                        </div>
                    </div>
                    <svg class="h-4 w-4 text-muted-foreground transition-transform" :class="{ 'rotate-180': activeSection === 'security' }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>
                <div x-show="activeSection === 'security'" x-collapse class="border-t p-3">
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block text-[10px] font-medium mb-1">Session Lifetime (seconds)</label>
                            <input type="number" class="input input-sm w-full" x-model="envConfig.SESSION_LIFETIME" placeholder="7200">
                        </div>
                        <div>
                            <label class="block text-[10px] font-medium mb-1">Session Name</label>
                            <input type="text" class="input input-sm w-full" x-model="envConfig.SESSION_NAME" placeholder="SRP_SESSION">
                        </div>
                        <div>
                            <label class="block text-[10px] font-medium mb-1">Rate Limit Attempts</label>
                            <input type="number" class="input input-sm w-full" x-model="envConfig.RATE_LIMIT_ATTEMPTS" placeholder="5">
                        </div>
                        <div>
                            <label class="block text-[10px] font-medium mb-1">Rate Limit Window (seconds)</label>
                            <input type="number" class="input input-sm w-full" x-model="envConfig.RATE_LIMIT_WINDOW" placeholder="900">
                        </div>
                        <div>
                            <label class="block text-[10px] font-medium mb-1">Secure Cookies</label>
                            <select class="input input-sm w-full" x-model="envConfig.SECURE_COOKIES">
                                <option value="true">Enabled (HTTPS)</option>
                                <option value="false">Disabled</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-[10px] font-medium mb-1">HTTP Only</label>
                            <select class="input input-sm w-full" x-model="envConfig.HTTP_ONLY">
                                <option value="true">Enabled</option>
                                <option value="false">Disabled</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-[10px] font-medium mb-1">Same Site Policy</label>
                            <select class="input input-sm w-full" x-model="envConfig.SAME_SITE">
                                <option value="Strict">Strict</option>
                                <option value="Lax">Lax</option>
                                <option value="None">None</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-[10px] font-medium mb-1">Trust CloudFlare Headers</label>
                            <select class="input input-sm w-full" x-model="envConfig.TRUST_CF_HEADERS">
                                <option value="true">Yes</option>
                                <option value="false">No</option>
                            </select>
                        </div>
                    </div>
                    <div class="mt-2">
                        <label class="block text-[10px] font-medium mb-1">Session Secret</label>
                        <div class="flex gap-1">
                            <input type="text" class="input input-sm flex-1 font-mono text-[10px]" x-model="envConfig.SESSION_SECRET" placeholder="Random secret key">
                            <button type="button" class="btn btn-sm btn-ghost" @click="generateSessionSecret()">
                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 6. Feature Flags -->
            <div class="card">
                <button
                    type="button"
                    class="w-full p-3 flex items-center justify-between hover:bg-muted/5 transition-colors"
                    @click="activeSection = activeSection === 'features' ? null : 'features'">
                    <div class="flex items-center gap-2">
                        <svg class="h-4 w-4 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <div class="text-left">
                            <h3 class="text-xs font-semibold">Feature Flags</h3>
                            <p class="text-[10px] text-muted-foreground">Enable or disable features</p>
                        </div>
                    </div>
                    <svg class="h-4 w-4 text-muted-foreground transition-transform" :class="{ 'rotate-180': activeSection === 'features' }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>
                <div x-show="activeSection === 'features'" x-collapse class="border-t p-3">
                    <div class="space-y-3">
                        <!-- Brand Domain Features -->
                        <div>
                            <h4 class="text-[11px] font-semibold mb-1.5">Brand Domain</h4>
                            <div class="space-y-1">
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="checkbox" class="checkbox checkbox-xs" x-model="envConfig.BRAND_ENABLE_LANDING_PAGE" :checked="envConfig.BRAND_ENABLE_LANDING_PAGE === 'true'" @change="envConfig.BRAND_ENABLE_LANDING_PAGE = $event.target.checked ? 'true' : 'false'">
                                    <span class="text-[10px]">Enable Landing Page</span>
                                </label>
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="checkbox" class="checkbox checkbox-xs" x-model="envConfig.BRAND_ENABLE_DOCUMENTATION" :checked="envConfig.BRAND_ENABLE_DOCUMENTATION === 'true'" @change="envConfig.BRAND_ENABLE_DOCUMENTATION = $event.target.checked ? 'true' : 'false'">
                                    <span class="text-[10px]">Enable Documentation</span>
                                </label>
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="checkbox" class="checkbox checkbox-xs" x-model="envConfig.BRAND_ENABLE_API_DOCS" :checked="envConfig.BRAND_ENABLE_API_DOCS === 'true'" @change="envConfig.BRAND_ENABLE_API_DOCS = $event.target.checked ? 'true' : 'false'">
                                    <span class="text-[10px]">Enable API Documentation</span>
                                </label>
                            </div>
                        </div>
                        <!-- Tracking Domain Features -->
                        <div>
                            <h4 class="text-[11px] font-semibold mb-1.5">Tracking Domain</h4>
                            <div class="space-y-1">
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="checkbox" class="checkbox checkbox-xs" x-model="envConfig.TRACKING_ENABLE_VPN_CHECK" :checked="envConfig.TRACKING_ENABLE_VPN_CHECK === 'true'" @change="envConfig.TRACKING_ENABLE_VPN_CHECK = $event.target.checked ? 'true' : 'false'">
                                    <span class="text-[10px]">Enable VPN Detection</span>
                                </label>
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="checkbox" class="checkbox checkbox-xs" x-model="envConfig.TRACKING_ENABLE_GEO_FILTER" :checked="envConfig.TRACKING_ENABLE_GEO_FILTER === 'true'" @change="envConfig.TRACKING_ENABLE_GEO_FILTER = $event.target.checked ? 'true' : 'false'">
                                    <span class="text-[10px]">Enable Geo Filtering</span>
                                </label>
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="checkbox" class="checkbox checkbox-xs" x-model="envConfig.TRACKING_ENABLE_DEVICE_FILTER" :checked="envConfig.TRACKING_ENABLE_DEVICE_FILTER === 'true'" @change="envConfig.TRACKING_ENABLE_DEVICE_FILTER = $event.target.checked ? 'true' : 'false'">
                                    <span class="text-[10px]">Enable Device Filtering</span>
                                </label>
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="checkbox" class="checkbox checkbox-xs" x-model="envConfig.TRACKING_ENABLE_AUTO_MUTE" :checked="envConfig.TRACKING_ENABLE_AUTO_MUTE === 'true'" @change="envConfig.TRACKING_ENABLE_AUTO_MUTE = $event.target.checked ? 'true' : 'false'">
                                    <span class="text-[10px]">Enable Auto Mute</span>
                                </label>
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="checkbox" class="checkbox checkbox-xs" x-model="envConfig.RATE_LIMIT_TRACKING_ENABLED" :checked="envConfig.RATE_LIMIT_TRACKING_ENABLED === 'true'" @change="envConfig.RATE_LIMIT_TRACKING_ENABLED = $event.target.checked ? 'true' : 'false'">
                                    <span class="text-[10px]">Enable Rate Limiting</span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 7. Postback Configuration -->
            <div class="card">
                <button
                    type="button"
                    class="w-full p-3 flex items-center justify-between hover:bg-muted/5 transition-colors"
                    @click="activeSection = activeSection === 'postback' ? null : 'postback'">
                    <div class="flex items-center gap-2">
                        <svg class="h-4 w-4 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path>
                        </svg>
                        <div class="text-left">
                            <h3 class="text-xs font-semibold">Postback Configuration</h3>
                            <p class="text-[10px] text-muted-foreground">Postback handling settings</p>
                        </div>
                    </div>
                    <svg class="h-4 w-4 text-muted-foreground transition-transform" :class="{ 'rotate-180': activeSection === 'postback' }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>
                <div x-show="activeSection === 'postback'" x-collapse class="border-t p-3">
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block text-[10px] font-medium mb-1">Timeout (seconds)</label>
                            <input type="number" class="input input-sm w-full" x-model="envConfig.POSTBACK_TIMEOUT" placeholder="5">
                        </div>
                        <div>
                            <label class="block text-[10px] font-medium mb-1">Max Retries</label>
                            <input type="number" class="input input-sm w-full" x-model="envConfig.POSTBACK_MAX_RETRIES" placeholder="3">
                        </div>
                        <div>
                            <label class="block text-[10px] font-medium mb-1">Retry Delay (seconds)</label>
                            <input type="number" class="input input-sm w-full" x-model="envConfig.POSTBACK_RETRY_DELAY" placeholder="60">
                        </div>
                        <div>
                            <label class="block text-[10px] font-medium mb-1">Default Payout</label>
                            <input type="number" step="0.01" class="input input-sm w-full" x-model="envConfig.DEFAULT_PAYOUT" placeholder="0.00">
                        </div>
                    </div>
                    <div class="mt-2 space-y-2">
                        <div>
                            <label class="block text-[10px] font-medium mb-1">HMAC Secret</label>
                            <input type="password" class="input input-sm w-full" x-model="envConfig.POSTBACK_HMAC_SECRET" placeholder="Secret key for HMAC validation">
                        </div>
                        <div>
                            <label class="block text-[10px] font-medium mb-1">Postback API Key</label>
                            <input type="password" class="input input-sm w-full" x-model="envConfig.POSTBACK_API_KEY" placeholder="API key for postback authentication">
                        </div>
                        <div class="flex items-center gap-2">
                            <input type="checkbox" class="checkbox checkbox-xs" x-model="envConfig.POSTBACK_REQUIRE_API_KEY" :checked="envConfig.POSTBACK_REQUIRE_API_KEY === 'true'" @change="envConfig.POSTBACK_REQUIRE_API_KEY = $event.target.checked ? 'true' : 'false'">
                            <label class="text-[10px]">Require API Key for Postbacks</label>
                        </div>
                        <div class="flex items-center gap-2">
                            <input type="checkbox" class="checkbox checkbox-xs" x-model="envConfig.POSTBACK_FORWARD_ENABLED" :checked="envConfig.POSTBACK_FORWARD_ENABLED === 'true'" @change="envConfig.POSTBACK_FORWARD_ENABLED = $event.target.checked ? 'true' : 'false'">
                            <label class="text-[10px]">Enable Postback Forwarding</label>
                        </div>
                        <div x-show="envConfig.POSTBACK_FORWARD_ENABLED === 'true'">
                            <label class="block text-[10px] font-medium mb-1">Forward URL</label>
                            <input type="url" class="input input-sm w-full" x-model="envConfig.POSTBACK_FORWARD_URL" placeholder="https://example.com/postback">
                        </div>
                    </div>
                </div>
            </div>

            <!-- 8. Path Configuration -->
            <div class="card">
                <button
                    type="button"
                    class="w-full p-3 flex items-center justify-between hover:bg-muted/5 transition-colors"
                    @click="activeSection = activeSection === 'paths' ? null : 'paths'">
                    <div class="flex items-center gap-2">
                        <svg class="h-4 w-4 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"></path>
                        </svg>
                        <div class="text-left">
                            <h3 class="text-xs font-semibold">Path Configuration</h3>
                            <p class="text-[10px] text-muted-foreground">Server paths and directories</p>
                        </div>
                    </div>
                    <svg class="h-4 w-4 text-muted-foreground transition-transform" :class="{ 'rotate-180': activeSection === 'paths' }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>
                <div x-show="activeSection === 'paths'" x-collapse class="border-t p-3">
                    <div class="space-y-2">
                        <div>
                            <label class="block text-[10px] font-medium mb-1">Application Root</label>
                            <input type="text" class="input input-sm w-full font-mono text-[10px]" x-model="envConfig.APP_ROOT" placeholder="/home/gassstea/srp">
                        </div>
                        <div>
                            <label class="block text-[10px] font-medium mb-1">Log Path</label>
                            <input type="text" class="input input-sm w-full font-mono text-[10px]" x-model="envConfig.LOG_PATH" placeholder="/home/gassstea/logs/app.log">
                        </div>
                    </div>
                </div>
            </div>

            <!-- 9. External Services -->
            <div class="card">
                <button
                    type="button"
                    class="w-full p-3 flex items-center justify-between hover:bg-muted/5 transition-colors"
                    @click="activeSection = activeSection === 'external' ? null : 'external'">
                    <div class="flex items-center gap-2">
                        <svg class="h-4 w-4 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path>
                        </svg>
                        <div class="text-left">
                            <h3 class="text-xs font-semibold">External Services</h3>
                            <p class="text-[10px] text-muted-foreground">Third-party service integrations</p>
                        </div>
                    </div>
                    <svg class="h-4 w-4 text-muted-foreground transition-transform" :class="{ 'rotate-180': activeSection === 'external' }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>
                <div x-show="activeSection === 'external'" x-collapse class="border-t p-3">
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block text-[10px] font-medium mb-1">VPN Check URL</label>
                            <input type="url" class="input input-sm w-full" x-model="envConfig.VPN_CHECK_URL" placeholder="https://blackbox.ipinfo.app/lookup/">
                        </div>
                        <div>
                            <label class="block text-[10px] font-medium mb-1">VPN Check Timeout (seconds)</label>
                            <input type="number" class="input input-sm w-full" x-model="envConfig.VPN_CHECK_TIMEOUT" placeholder="2">
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <!-- Info Notice -->
        <div class="rounded-[0.3rem] border border-primary/30 bg-primary/5 p-2.5">
            <div class="flex items-start gap-2">
                <svg class="h-4 w-4 text-primary mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <div>
                    <p class="text-[10px] font-medium text-foreground">Important Notes</p>
                    <ul class="text-[10px] text-muted-foreground mt-1 space-y-0.5">
                        <li>• Changes are saved to both database and .env file</li>
                        <li>• A backup of .env is created before saving</li>
                        <li>• Some changes may require application restart</li>
                        <li>• Keep API keys secure and rotate them regularly</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>