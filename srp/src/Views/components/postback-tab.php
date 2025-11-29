<!-- Postback Tab -->
<div x-show="activeTab === 'postback'" x-cloak>
    <div class="space-y-4">
        <!-- Daily Payout Statistics -->
        <div class="card">
            <div class="p-4 border-b">
                <div class="flex items-center justify-between gap-3">
                    <div class="flex-1">
                        <h3 class="font-semibold tracking-tight text-sm">
                            <span x-text="statsView === 'weekly' ? 'Weekly' : 'Daily'"></span> Payout Statistics
                        </h3>
                        <p class="text-[11px] text-muted-foreground">
                            Aggregate payout received from networks
                            <span x-show="statsView === 'weekly'">(weeks start Monday 07:00 UTC+7)</span>
                            <span x-show="statsView === 'daily'">(last <span x-text="statsPeriod"></span> days)</span>
                        </p>
                    </div>
                    <div class="flex items-center gap-2">
                        <!-- View Selector -->
                        <div class="flex gap-1 mr-2">
                            <button type="button"
                                    @click="changeStatsView('daily')"
                                    class="btn btn-sm"
                                    :class="statsView === 'daily' ? 'btn-default' : 'btn-ghost'">
                                <svg class="h-3 w-3 md:mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                                <span class="hidden md:inline text-xs">Daily</span>
                            </button>
                            <button type="button"
                                    @click="changeStatsView('weekly')"
                                    class="btn btn-sm"
                                    :class="statsView === 'weekly' ? 'btn-default' : 'btn-ghost'">
                                <svg class="h-3 w-3 md:mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                </svg>
                                <span class="hidden md:inline text-xs">Weekly</span>
                            </button>
                        </div>

                        <!-- Period Selector -->
                        <div class="flex gap-1">
                            <button type="button"
                                    @click="changeStatsPeriod(7)"
                                    class="btn btn-sm"
                                    :class="statsPeriod === 7 ? 'btn-default' : 'btn-ghost'">
                                <span class="text-xs">7d</span>
                            </button>
                            <button type="button"
                                    @click="changeStatsPeriod(30)"
                                    class="btn btn-sm"
                                    :class="statsPeriod === 30 ? 'btn-default' : 'btn-ghost'">
                                <span class="text-xs">30d</span>
                            </button>
                            <button type="button"
                                    @click="changeStatsPeriod(90)"
                                    class="btn btn-sm"
                                    :class="statsPeriod === 90 ? 'btn-default' : 'btn-ghost'">
                                <span class="text-xs">90d</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Summary Cards -->
            <div class="p-4">
                <div class="grid gap-3 grid-cols-2 md:grid-cols-4">
                    <!-- Total Postbacks -->
                    <div class="card p-3 text-center">
                        <div class="inline-flex items-center justify-center gap-1.5 mb-1.5">
                            <svg class="h-3.5 w-3.5 text-blue-500/80" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                            </svg>
                            <h4 class="text-[11px] font-medium text-muted-foreground uppercase tracking-wide">Total</h4>
                        </div>
                        <div class="text-xl font-semibold leading-tight" x-text="formatNumber(statsSummary.total_postbacks)"></div>
                        <p class="text-[10px] text-muted-foreground mt-0.5">Postbacks</p>
                    </div>

                    <!-- Total Payout -->
                    <div class="card p-3 text-center">
                        <div class="inline-flex items-center justify-center gap-1.5 mb-1.5">
                            <svg class="h-3.5 w-3.5 text-emerald-500/80" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <h4 class="text-[11px] font-medium text-muted-foreground uppercase tracking-wide">Total</h4>
                        </div>
                        <div class="text-xl font-semibold leading-tight text-emerald-600" x-text="'$' + statsSummary.total_payout.toFixed(2)"></div>
                        <p class="text-[10px] text-muted-foreground mt-0.5">Payout</p>
                    </div>

                    <!-- Average Per Period -->
                    <div class="card p-3 text-center">
                        <div class="inline-flex items-center justify-center gap-1.5 mb-1.5">
                            <svg class="h-3.5 w-3.5 text-amber-500/80" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                            </svg>
                            <h4 class="text-[11px] font-medium text-muted-foreground uppercase tracking-wide">
                                <span x-text="statsView === 'weekly' ? 'Avg/Week' : 'Avg/Day'"></span>
                            </h4>
                        </div>
                        <div class="text-xl font-semibold leading-tight" x-text="'$' + statsSummary.avg_daily_payout.toFixed(2)"></div>
                        <p class="text-[10px] text-muted-foreground mt-0.5">Average</p>
                    </div>

                    <!-- Active Periods -->
                    <div class="card p-3 text-center">
                        <div class="inline-flex items-center justify-center gap-1.5 mb-1.5">
                            <svg class="h-3.5 w-3.5 text-purple-500/80" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                            <h4 class="text-[11px] font-medium text-muted-foreground uppercase tracking-wide">
                                <span x-text="statsView === 'weekly' ? 'Weeks' : 'Days'"></span>
                            </h4>
                        </div>
                        <div class="text-xl font-semibold leading-tight" x-text="statsSummary.days_count"></div>
                        <p class="text-[10px] text-muted-foreground mt-0.5">Active</p>
                    </div>
                </div>

                <!-- Daily Stats Table -->
                <div class="mt-4">
                    <div class="relative overflow-x-auto overflow-y-auto max-h-[300px] scroll-logs border rounded-md">
                        <table class="w-full text-[12px]">
                            <thead class="border-b bg-white sticky top-0 z-10">
                            <tr>
                                <th class="h-8 px-3 text-left align-middle font-medium text-muted-foreground">
                                    <span x-text="statsView === 'weekly' ? 'Week Start' : 'Date'"></span>
                                </th>
                                <th class="h-8 px-3 text-right align-middle font-medium text-muted-foreground">Postbacks</th>
                                <th class="h-8 px-3 text-right align-middle font-medium text-muted-foreground">Total Payout</th>
                                <th class="h-8 px-3 text-right align-middle font-medium text-muted-foreground hidden md:table-cell">Avg</th>
                                <th class="h-8 px-3 text-right align-middle font-medium text-muted-foreground hidden lg:table-cell">Min</th>
                                <th class="h-8 px-3 text-right align-middle font-medium text-muted-foreground hidden lg:table-cell">Max</th>
                                <th class="h-8 px-3 text-center align-middle font-medium text-muted-foreground hidden xl:table-cell">Networks</th>
                                <th class="h-8 px-3 text-center align-middle font-medium text-muted-foreground hidden xl:table-cell">Countries</th>
                            </tr>
                            </thead>
                            <tbody class="[&_tr:last-child]:border-0">
                            <template x-if="dailyStats.length === 0 && !statsLoading">
                                <tr class="border-b">
                                    <td colspan="8" class="p-3 text-center text-[11px] text-muted-foreground">
                                        No payout data available for the selected period.
                                    </td>
                                </tr>
                            </template>
                            <template x-if="statsLoading">
                                <tr class="border-b">
                                    <td colspan="8" class="p-3 text-center text-[11px] text-muted-foreground">
                                        <svg class="inline h-4 w-4 animate-spin mr-2" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                        Loading statistics...
                                    </td>
                                </tr>
                            </template>
                            <template x-for="day in dailyStats" :key="day.date">
                                <tr class="border-b transition-colors hover:bg-muted/50">
                                    <td class="p-2 align-middle">
                                        <span class="text-[11px] font-medium"
                                              x-text="statsView === 'weekly' && day.week_end ? formatWeekRange(day.date, day.week_end) : day.date"></span>
                                    </td>
                                    <td class="p-2 align-middle text-right">
                                        <span class="text-[11px]" x-text="formatNumber(day.total_postbacks)"></span>
                                    </td>
                                    <td class="p-2 align-middle text-right">
                                        <span class="text-[11px] font-medium text-emerald-600" x-text="'$' + day.total_payout.toFixed(2)"></span>
                                    </td>
                                    <td class="p-2 align-middle text-right hidden md:table-cell">
                                        <span class="text-[11px] text-muted-foreground" x-text="'$' + day.avg_payout.toFixed(2)"></span>
                                    </td>
                                    <td class="p-2 align-middle text-right hidden lg:table-cell">
                                        <span class="text-[11px] text-muted-foreground" x-text="'$' + day.min_payout.toFixed(2)"></span>
                                    </td>
                                    <td class="p-2 align-middle text-right hidden lg:table-cell">
                                        <span class="text-[11px] text-muted-foreground" x-text="'$' + day.max_payout.toFixed(2)"></span>
                                    </td>
                                    <td class="p-2 align-middle text-center hidden xl:table-cell">
                                        <span class="badge badge-outline text-[11px]" x-text="day.unique_networks"></span>
                                    </td>
                                    <td class="p-2 align-middle text-center hidden xl:table-cell">
                                        <span class="badge badge-outline text-[11px]" x-text="day.unique_countries"></span>
                                    </td>
                                </tr>
                            </template>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Postback Configuration -->
        <div class="card" x-data="{ showConfig: true }">
            <div class="p-4 border-b">
                <div class="flex items-center justify-between gap-3">
                    <div class="flex-1">
                        <div class="flex items-center gap-2 mb-1">
                            <h3 class="font-semibold tracking-tight text-sm">Postback Configuration</h3>
                            <span class="badge badge-default text-[10px] px-1.5 py-0.5">
                                Always Active
                            </span>
                        </div>
                        <p class="text-[11px] text-muted-foreground">
                            Configure postback URL for conversion notifications (always enabled)
                        </p>
                    </div>
                    <div class="flex items-center gap-2">
                        <!-- Show/Hide Config Button -->
                        <button type="button"
                                class="btn btn-sm btn-ghost"
                                @click="showConfig = !showConfig">
                            <svg x-show="!showConfig" class="h-3.5 w-3.5 md:mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                            <svg x-show="showConfig" class="h-3.5 w-3.5 md:mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path>
                            </svg>
                            <span class="hidden md:inline text-xs" x-text="showConfig ? 'Hide' : 'Show'"></span>
                        </button>
                    </div>
                </div>
            </div>

            <div x-show="showConfig"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 transform scale-95"
                 x-transition:enter-end="opacity-100 transform scale-100"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100 transform scale-100"
                 x-transition:leave-end="opacity-0 transform scale-95"
                 class="p-4">

                <!-- Warning: URL not configured -->
                <div x-show="!cfg.postback_enabled && !cfg.postback_url"
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0 transform scale-95"
                     x-transition:enter-end="opacity-100 transform scale-100"
                     class="mb-3 p-3 bg-amber-50 border border-amber-200 rounded-md">
                    <div class="flex items-start gap-2">
                        <svg class="h-4 w-4 text-amber-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                        </svg>
                        <div>
                            <p class="text-xs font-medium text-amber-800">Postback Not Configured</p>
                            <p class="text-xs text-amber-700 mt-1">Enter your postback URL below before enabling</p>
                        </div>
                    </div>
                </div>

                <!-- Success: Postback enabled -->
                <div x-show="cfg.postback_enabled && cfg.postback_url"
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0 transform scale-95"
                     x-transition:enter-end="opacity-100 transform scale-100"
                     class="mb-3 p-3 bg-green-50 border border-green-200 rounded-md">
                    <div class="flex items-start gap-2">
                        <svg class="h-4 w-4 text-green-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <div>
                            <p class="text-xs font-medium text-green-800">Postback Active</p>
                            <p class="text-xs text-green-700 mt-1">Conversion notifications will be sent to your endpoint</p>
                        </div>
                    </div>
                </div>

                <div class="space-y-3">
                    <!-- Postback URL Template -->
                    <div class="space-y-1.5">
                        <label class="text-xs font-medium leading-none">
                            Postback URL Template
                        </label>
                        <input type="url"
                               class="input font-mono text-[11px]"
                               placeholder="https://yourdomain.com/postback.php?country={country}&device={traffic_type}&payout={payout}&status={status}&click_id={click_id}&network={network}"
                               x-model="cfg.postback_url"
                               @input.debounce.400ms="savePostback()">
                        <p class="text-[11px] text-muted-foreground">
                            Use placeholders: {country}, {traffic_type}, {payout}
                        </p>
                    </div>

                    <!-- Default Payout -->
                    <div class="space-y-1.5">
                        <label class="text-xs font-medium leading-none">
                            Default Payout (USD)
                        </label>
                        <input type="number"
                               class="input"
                               placeholder="0.00"
                               step="0.01"
                               min="0"
                               x-model="cfg.default_payout"
                               @input.debounce.400ms="savePostback()">
                        <p class="text-[11px] text-muted-foreground">
                            Default payout amount for Decision A conversions
                        </p>
                    </div>

                    <!-- Example URL -->
                    <div class="space-y-1.5">
                        <label class="text-xs font-medium leading-none">
                            Example Postback URL
                        </label>
                        <div class="p-2 bg-muted rounded border text-[11px] font-mono break-all"
                             x-text="getPostbackExample()"></div>
                        <p class="text-[11px] text-muted-foreground">
                            Preview of how the postback URL will be called
                        </p>
                    </div>
                </div>

            </div><!-- End x-show showConfig -->
        </div>

        <!-- Test Postback -->
        <div class="card p-4" x-data="{ showTestPostback: false }">
            <div class="flex items-center justify-between gap-3 mb-3">
                <div class="flex-1">
                    <h3 class="font-semibold tracking-tight text-sm">Test Postback</h3>
                    <p class="text-[11px] text-muted-foreground">
                        Send a test postback to verify your configuration
                    </p>
                </div>
                <button type="button"
                        class="btn btn-sm btn-ghost"
                        @click="showTestPostback = !showTestPostback">
                    <svg x-show="!showTestPostback" class="h-3.5 w-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                    <svg x-show="showTestPostback" class="h-3.5 w-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path>
                    </svg>
                    <span class="text-xs" x-text="showTestPostback ? 'Hide' : 'Show'"></span>
                </button>
            </div>

            <div x-show="showTestPostback"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 transform scale-95"
                 x-transition:enter-end="opacity-100 transform scale-100"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100 transform scale-100"
                 x-transition:leave-end="opacity-0 transform scale-95">

                <div x-show="!cfg.postback_enabled" class="mb-3">
                    <div class="alert alert-info p-3 rounded-md bg-blue-50 border border-blue-200">
                        <div class="flex items-start gap-2">
                            <svg class="h-4 w-4 text-blue-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <div>
                                <p class="text-xs font-medium text-blue-800">Auto-Enable Mode</p>
                                <p class="text-xs text-blue-700 mt-1">Configure URL above, then click "Send Test Postback" - it will auto-enable and test</p>
                            </div>
                        </div>
                    </div>
                </div>

            <div class="grid gap-3 md:grid-cols-3 mb-3">
                <div class="space-y-1.5">
                    <label class="text-xs font-medium leading-none">
                        Country Code
                    </label>
                    <input type="text"
                           class="input font-mono up-text"
                           placeholder="US"
                           maxlength="2"
                           x-model="postbackTest.country">
                </div>

                <div class="space-y-1.5">
                    <label class="text-xs font-medium leading-none">
                        Traffic Type
                    </label>
                    <select class="input" x-model="postbackTest.trafficType">
                        <option value="WAP">Mobile (WAP)</option>
                        <option value="WEB">Desktop (WEB)</option>
                        <option value="TABLET">Tablet</option>
                    </select>
                </div>

                <div class="space-y-1.5">
                    <label class="text-xs font-medium leading-none">
                        Payout
                    </label>
                    <input type="number"
                           class="input"
                           placeholder="0.00"
                           step="0.01"
                           x-model="postbackTest.payout">
                </div>
            </div>

            <div class="flex items-center justify-between">
                <button type="button"
                        class="btn btn-default btn-sm"
                        @click="testPostback()"
                        :disabled="isTestingPostback">
                    <svg x-show="!isTestingPostback"
                         class="h-3.5 w-3.5 mr-1.5"
                         fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                    </svg>
                    <svg x-show="isTestingPostback"
                         class="h-3.5 w-3.5 mr-1.5 animate-spin"
                         fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10"
                                stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75"
                              d="M4 12a8 8 0 0 1 8-8"
                              stroke="currentColor" stroke-width="4" stroke-linecap="round"></path>
                    </svg>
                    <span x-text="isTestingPostback ? 'Testing...' : 'Send Test Postback'"></span>
                </button>

                <template x-if="postbackTestResult">
                    <div class="text-right text-[11px] space-y-0.5">
                        <div>
                            Status:
                            <span class="badge"
                                  :class="postbackTestResult.success ? 'badge-default' : 'badge-destructive'"
                                  x-text="postbackTestResult.success ? 'Success' : 'Failed'">
                            </span>
                        </div>
                        <div class="text-muted-foreground" x-text="postbackTestResult.message"></div>
                    </div>
                </template>
            </div>

            </div><!-- End x-show showTestPostback -->
        </div>

        <!-- Recent Postback Logs (Outgoing) -->
        <div class="card" x-data="{ showOutgoing: true }">
            <div class="p-4 border-b">
                <div class="flex items-center justify-between">
                    <div class="flex-1">
                        <h3 class="font-semibold tracking-tight text-sm">Outgoing Postbacks</h3>
                        <p class="text-[11px] text-muted-foreground">Postbacks sent to networks (last 20)</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="badge badge-outline text-[11px]">
                            <svg class="h-3 w-3 mr-1 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path>
                            </svg>
                            SRP → Networks
                        </span>
                        <button type="button"
                                class="btn btn-sm btn-ghost"
                                @click="showOutgoing = !showOutgoing">
                            <svg x-show="!showOutgoing" class="h-3.5 w-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                            <svg x-show="showOutgoing" class="h-3.5 w-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path>
                            </svg>
                            <span class="text-xs" x-text="showOutgoing ? 'Hide' : 'Show'"></span>
                        </button>
                    </div>
                </div>
            </div>

            <div x-show="showOutgoing"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 transform scale-95"
                 x-transition:enter-end="opacity-100 transform scale-100"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100 transform scale-100"
                 x-transition:leave-end="opacity-0 transform scale-95"
                 class="relative overflow-x-auto overflow-y-auto max-h-[420px] scroll-logs">
                <table class="w-full text-[12px]">
                    <thead class="border-b bg-white sticky top-0 z-10">
                    <tr>
                        <th class="h-8 px-3 text-left align-middle font-medium text-muted-foreground">
                            Time
                        </th>
                        <th class="h-8 px-3 text-left align-middle font-medium text-muted-foreground">
                            Country
                        </th>
                        <th class="h-8 px-3 text-left align-middle font-medium text-muted-foreground hidden md:table-cell">
                            Traffic Type
                        </th>
                        <th class="h-8 px-3 text-left align-middle font-medium text-muted-foreground">
                            Payout
                        </th>
                        <th class="h-8 px-3 text-left align-middle font-medium text-muted-foreground">
                            Status
                        </th>
                        <th class="h-8 px-3 text-left align-middle font-medium text-muted-foreground hidden lg:table-cell">
                            Response
                        </th>
                    </tr>
                    </thead>
                    <tbody class="[&_tr:last-child]:border-0">
                    <template x-if="postbackLogs.length === 0">
                        <tr class="border-b">
                            <td colspan="6" class="p-3 text-center text-[11px] text-muted-foreground">
                                No postback logs yet.
                            </td>
                        </tr>
                    </template>
                    <template x-for="log in postbackLogs" :key="log.id">
                        <tr class="border-b transition-colors hover:bg-muted/50">
                            <td class="p-2 align-middle">
                                <span class="text-[11px] text-muted-foreground" x-text="fmt(log.ts)"></span>
                            </td>
                            <td class="p-2 align-middle">
                                <span class="badge badge-outline text-[11px]" x-text="log.country_code"></span>
                            </td>
                            <td class="p-2 align-middle hidden md:table-cell">
                                <span class="text-[11px]" x-text="log.traffic_type"></span>
                            </td>
                            <td class="p-2 align-middle">
                                <span class="text-[11px] font-medium" x-text="'$' + log.payout"></span>
                            </td>
                            <td class="p-2 align-middle">
                                <span class="badge text-[11px]"
                                      :class="log.success ? 'badge-default' : 'badge-destructive'"
                                      x-text="log.success ? 'Success' : 'Failed'"></span>
                            </td>
                            <td class="p-2 align-middle hidden lg:table-cell">
                                <span class="text-[11px] text-muted-foreground" x-text="log.response_code || '-'"></span>
                            </td>
                        </tr>
                    </template>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Received Postbacks (Incoming) -->
        <div class="card">
            <div class="p-4 border-b">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="font-semibold tracking-tight text-sm">Incoming Postbacks</h3>
                        <p class="text-[11px] text-muted-foreground">Postbacks received from networks (last 50)</p>
                    </div>
                    <span class="badge badge-default text-[11px]">
                        <svg class="h-3 w-3 mr-1 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16l-4-4m0 0l4-4m-4 4h18"></path>
                        </svg>
                        Networks → SRP
                    </span>
                </div>
            </div>

            <div class="relative overflow-x-auto overflow-y-auto max-h-[420px] scroll-logs">
                <table class="w-full text-[12px]">
                    <thead class="border-b bg-white sticky top-0 z-10">
                    <tr>
                        <th class="h-8 px-3 text-left align-middle font-medium text-muted-foreground">
                            Time
                        </th>
                        <th class="h-8 px-3 text-left align-middle font-medium text-muted-foreground">
                            Network
                        </th>
                        <th class="h-8 px-3 text-left align-middle font-medium text-muted-foreground">
                            Country
                        </th>
                        <th class="h-8 px-3 text-left align-middle font-medium text-muted-foreground hidden md:table-cell">
                            Device
                        </th>
                        <th class="h-8 px-3 text-left align-middle font-medium text-muted-foreground">
                            Payout
                        </th>
                        <th class="h-8 px-3 text-left align-middle font-medium text-muted-foreground hidden lg:table-cell">
                            Status
                        </th>
                        <th class="h-8 px-3 text-left align-middle font-medium text-muted-foreground hidden xl:table-cell">
                            Click ID
                        </th>
                    </tr>
                    </thead>
                    <tbody class="[&_tr:last-child]:border-0">
                    <template x-if="receivedPostbacks.length === 0">
                        <tr class="border-b">
                            <td colspan="7" class="p-3 text-center text-[11px] text-muted-foreground">
                                No incoming postbacks yet. Configure your postback receiver URL in network dashboards.
                            </td>
                        </tr>
                    </template>
                    <template x-for="log in receivedPostbacks" :key="log.id">
                        <tr class="border-b transition-colors hover:bg-muted/50">
                            <td class="p-2 align-middle">
                                <span class="text-[11px] text-muted-foreground" x-text="fmt(log.ts)"></span>
                            </td>
                            <td class="p-2 align-middle">
                                <span class="badge badge-outline text-[11px]" x-text="log.network || 'unknown'"></span>
                            </td>
                            <td class="p-2 align-middle">
                                <span class="badge badge-outline text-[11px]" x-text="log.country_code || '-'"></span>
                            </td>
                            <td class="p-2 align-middle hidden md:table-cell">
                                <span class="text-[11px]" x-text="log.traffic_type || '-'"></span>
                            </td>
                            <td class="p-2 align-middle">
                                <span class="text-[11px] font-medium" x-text="'$' + log.payout"></span>
                            </td>
                            <td class="p-2 align-middle hidden lg:table-cell">
                                <span class="badge text-[11px]"
                                      :class="log.status === 'confirmed' || log.status === 'approved' ? 'badge-default' : 'badge-outline'"
                                      x-text="log.status || '-'"></span>
                            </td>
                            <td class="p-2 align-middle hidden xl:table-cell">
                                <span class="text-[11px] font-mono text-muted-foreground" x-text="log.click_id || '-'"></span>
                            </td>
                        </tr>
                    </template>
                    </tbody>
                </table>
            </div>

            <!-- Info banner about incoming postbacks -->
            <div class="p-3 border-t bg-muted/30">
                <div class="flex items-start gap-2">
                    <svg class="h-4 w-4 text-blue-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <div class="flex-1">
                        <p class="text-[11px] font-medium text-foreground">Configure Network Postback URL:</p>
                        <code class="text-[10px] text-muted-foreground break-all block mt-1">
                            https://yourdomain.com/postback.php?country={country}&device={traffic_type}&payout={payout}
                        </code>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
