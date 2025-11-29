<!-- Statistics Tab -->
<div x-show="activeTab === 'statistics'" x-cloak>
    <div class="space-y-4">
        <!-- Overall Statistics -->
        <div class="grid gap-3 grid-cols-2 md:grid-cols-4">
            <!-- Total Decisions -->
            <div class="card p-3 text-center">
                <div class="inline-flex items-center justify-center gap-1.5 mb-1.5">
                    <svg class="h-3.5 w-3.5 text-blue-500/80" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                    <h3 class="text-[11px] font-medium text-muted-foreground uppercase tracking-wide">Total</h3>
                </div>
                <div class="text-xl font-semibold leading-tight" x-text="formatNumber(totalDecisionA + totalDecisionB)"></div>
                <p class="text-[10px] text-muted-foreground mt-0.5">All decisions</p>
            </div>

            <!-- Decision A -->
            <div class="card p-3 text-center">
                <div class="inline-flex items-center justify-center gap-1.5 mb-1.5">
                    <svg class="h-3.5 w-3.5 text-emerald-500/80" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 1 1-18 0 9 9 0 0 1 18 0z"></path>
                    </svg>
                    <h3 class="text-[11px] font-medium text-muted-foreground uppercase tracking-wide">Decision A</h3>
                </div>
                <div class="text-xl font-semibold leading-tight text-emerald-600" x-text="formatNumber(totalDecisionA)"></div>
                <p class="text-[10px] text-muted-foreground mt-0.5"
                   x-text="totalDecisionA + totalDecisionB > 0 ? Math.round((totalDecisionA / (totalDecisionA + totalDecisionB)) * 100) + '%' : '0%'"></p>
            </div>

            <!-- Decision B -->
            <div class="card p-3 text-center">
                <div class="inline-flex items-center justify-center gap-1.5 mb-1.5">
                    <svg class="h-3.5 w-3.5 text-amber-500/80" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0z"></path>
                    </svg>
                    <h3 class="text-[11px] font-medium text-muted-foreground uppercase tracking-wide">Decision B</h3>
                </div>
                <div class="text-xl font-semibold leading-tight text-amber-600" x-text="formatNumber(totalDecisionB)"></div>
                <p class="text-[10px] text-muted-foreground mt-0.5"
                   x-text="totalDecisionA + totalDecisionB > 0 ? Math.round((totalDecisionB / (totalDecisionA + totalDecisionB)) * 100) + '%' : '0%'"></p>
            </div>

            <!-- Total Revenue -->
            <div class="card p-3 text-center">
                <div class="inline-flex items-center justify-center gap-1.5 mb-1.5">
                    <svg class="h-3.5 w-3.5 text-green-500/80" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <h3 class="text-[11px] font-medium text-muted-foreground uppercase tracking-wide">Revenue</h3>
                </div>
                <div class="text-xl font-semibold leading-tight text-green-600" x-text="'$' + formatNumber(totalDecisionA * (Number(cfg.default_payout) || 0))"></div>
                <p class="text-[10px] text-muted-foreground mt-0.5">Estimated</p>
            </div>
        </div>

        <!-- Country Breakdown -->
        <div class="card">
            <div class="p-4 border-b">
                <h3 class="font-semibold tracking-tight text-sm">Top Countries</h3>
                <p class="text-[11px] text-muted-foreground">Decision breakdown by country (from recent logs)</p>
            </div>

            <div class="p-4">
                <template x-if="getCountryStats().length === 0">
                    <div class="text-center text-[11px] text-muted-foreground py-4">
                        No traffic data available
                    </div>
                </template>

                <template x-if="getCountryStats().length > 0">
                    <div class="space-y-3">
                        <template x-for="stat in getCountryStats().slice(0, 10)" :key="stat.country">
                            <div class="space-y-1">
                                <div class="flex items-center justify-between text-[11px]">
                                    <div class="flex items-center gap-2">
                                        <span class="badge badge-outline" x-text="stat.country"></span>
                                        <span class="text-muted-foreground" x-text="stat.total + ' requests'"></span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <span class="text-emerald-600 font-medium" x-text="stat.decisionA + ' A'"></span>
                                        <span class="text-amber-600 font-medium" x-text="stat.decisionB + ' B'"></span>
                                    </div>
                                </div>
                                <div class="flex h-2 overflow-hidden rounded-full bg-muted">
                                    <div class="bg-emerald-500"
                                         :style="'width: ' + ((stat.decisionA / stat.total) * 100) + '%'"></div>
                                    <div class="bg-amber-500"
                                         :style="'width: ' + ((stat.decisionB / stat.total) * 100) + '%'"></div>
                                </div>
                            </div>
                        </template>
                    </div>
                </template>
            </div>
        </div>

        <!-- Recent Activity Chart -->
        <div class="card">
            <div class="p-4 border-b">
                <h3 class="font-semibold tracking-tight text-sm">Recent Activity</h3>
                <p class="text-[11px] text-muted-foreground">Traffic distribution (last 50 records)</p>
            </div>

            <div class="p-4">
                <template x-if="logs.length === 0">
                    <div class="text-center text-[11px] text-muted-foreground py-4">
                        No activity data available
                    </div>
                </template>

                <template x-if="logs.length > 0">
                    <div class="space-y-3">
                        <!-- Decision A vs B Ratio -->
                        <div class="space-y-2">
                            <div class="flex items-center justify-between text-[11px]">
                                <span class="text-muted-foreground">Decision Distribution</span>
                                <span class="font-medium" x-text="logs.length + ' total'"></span>
                            </div>
                            <div class="flex h-8 overflow-hidden rounded-lg border">
                                <div class="bg-emerald-500 flex items-center justify-center text-white text-[10px] font-medium"
                                     :style="'width: ' + ((logs.filter(l => l.decision === 'A').length / logs.length) * 100) + '%'"
                                     x-text="logs.filter(l => l.decision === 'A').length"></div>
                                <div class="bg-amber-500 flex items-center justify-center text-white text-[10px] font-medium"
                                     :style="'width: ' + ((logs.filter(l => l.decision === 'B').length / logs.length) * 100) + '%'"
                                     x-text="logs.filter(l => l.decision === 'B').length"></div>
                            </div>
                            <div class="flex items-center justify-between text-[10px] text-muted-foreground">
                                <span>Decision A</span>
                                <span>Decision B</span>
                            </div>
                        </div>

                        <!-- Unique Countries -->
                        <div class="pt-3 border-t">
                            <div class="flex items-center justify-between text-[11px]">
                                <span class="text-muted-foreground">Unique Countries</span>
                                <span class="font-medium" x-text="getUniqueCountries().length"></span>
                            </div>
                            <div class="mt-2 flex flex-wrap gap-1">
                                <template x-for="country in getUniqueCountries()" :key="country">
                                    <span class="badge badge-outline text-[10px]" x-text="country"></span>
                                </template>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        <!-- Performance Metrics -->
        <div class="card p-4">
            <h3 class="font-semibold tracking-tight text-sm mb-3">Performance Metrics</h3>

            <div class="grid gap-4 md:grid-cols-2">
                <div class="space-y-2">
                    <div class="flex items-center justify-between py-2 border-b">
                        <span class="text-[11px] text-muted-foreground">Conversion Rate (A)</span>
                        <span class="text-[11px] font-medium"
                              x-text="logs.length > 0 ? ((logs.filter(l => l.decision === 'A').length / logs.length) * 100).toFixed(2) + '%' : '0%'"></span>
                    </div>
                    <div class="flex items-center justify-between py-2 border-b">
                        <span class="text-[11px] text-muted-foreground">Fallback Rate (B)</span>
                        <span class="text-[11px] font-medium"
                              x-text="logs.length > 0 ? ((logs.filter(l => l.decision === 'B').length / logs.length) * 100).toFixed(2) + '%' : '0%'"></span>
                    </div>
                    <div class="flex items-center justify-between py-2">
                        <span class="text-[11px] text-muted-foreground">Active Countries</span>
                        <span class="text-[11px] font-medium" x-text="getUniqueCountries().length"></span>
                    </div>
                </div>

                <div class="space-y-2">
                    <div class="flex items-center justify-between py-2 border-b">
                        <span class="text-[11px] text-muted-foreground">Total URLs</span>
                        <span class="text-[11px] font-medium"
                              x-text="Array.isArray(cfg.redirect_url) ? cfg.redirect_url.length : 0"></span>
                    </div>
                    <div class="flex items-center justify-between py-2 border-b">
                        <span class="text-[11px] text-muted-foreground">Avg Payout</span>
                        <span class="text-[11px] font-medium" x-text="'$' + (Number(cfg.default_payout) || 0).toFixed(2)"></span>
                    </div>
                    <div class="flex items-center justify-between py-2">
                        <span class="text-[11px] text-muted-foreground">Stats Reset In</span>
                        <span class="text-[11px] font-medium" x-text="getStatsResetInfo()"></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
