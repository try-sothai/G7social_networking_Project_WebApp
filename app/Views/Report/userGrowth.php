<!DOCTYPE html>

<html class="light" lang="en"><head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title>User Growth Analysis - Social Analytics</title>
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<link href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@300;400;500;600;700&amp;display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght@100..700,0..1&amp;display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
<script id="tailwind-config">
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        "primary": "#ec5b13",
                        "background-light": "#f8f6f6",
                        "background-dark": "#221610",
                    },
                    fontFamily: {
                        "display": ["Public Sans"]
                    },
                    borderRadius: {"DEFAULT": "0.25rem", "lg": "0.5rem", "xl": "0.75rem", "full": "9999px"},
                },
            },
        }
    </script>
    <style>
        body { font-family: 'Public Sans', sans-serif; }
        .material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; }
        .active-icon { font-variation-settings: 'FILL' 1; }
    </style>
</head>
<body class="bg-background-light dark:bg-background-dark text-slate-900 dark:text-slate-100 font-display">
<div class="flex h-screen overflow-hidden">
<!-- Sidebar -->
<aside class="w-64 border-r border-slate-200 dark:border-slate-800 bg-white dark:bg-background-dark flex flex-col h-full">
<div class="p-6 flex items-center gap-3">
<div class="size-10 bg-primary rounded-xl flex items-center justify-center text-white">
<span class="material-symbols-outlined">analytics</span>
</div>
<div>
<h1 class="text-sm font-bold leading-none">Social Analytics</h1>
<p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Growth Platform</p>
</div>
</div>
<nav class="flex-1 px-4 space-y-2">
<a class="flex items-center gap-3 px-3 py-2 rounded-xl text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors" href="#">
<span class="material-symbols-outlined">grid_view</span>
<span class="text-sm font-medium">Overview</span>
</a>
<a class="flex items-center gap-3 px-3 py-2 rounded-xl bg-primary/10 text-primary transition-colors" href="#">
<span class="material-symbols-outlined active-icon">trending_up</span>
<span class="text-sm font-bold">User Growth</span>
</a>
<a class="flex items-center gap-3 px-3 py-2 rounded-xl text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors" href="#">
<span class="material-symbols-outlined">group</span>
<span class="text-sm font-medium">Audience</span>
</a>
<a class="flex items-center gap-3 px-3 py-2 rounded-xl text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors" href="#">
<span class="material-symbols-outlined">share</span>
<span class="text-sm font-medium">Referrals</span>
</a>
</nav>
<div class="p-4 border-t border-slate-200 dark:border-slate-800">
<div class="flex items-center gap-3 p-2">
<div class="size-10 rounded-full bg-slate-200 dark:bg-slate-700 overflow-hidden">
<img alt="User Profile Avatar" data-alt="User profile avatar of a young professional" src="https://lh3.googleusercontent.com/aida-public/AB6AXuASq21P3fOL2Y_-HlOVcZlKA7f3JhYOHttqKa7JjtSsYXsDr6-Q_CS3tlBDlC8hC3AeMcAoGs3lNVQTkTzoJRfMAg8-1UzhUacwbJhjGNLG7767Po0CxeYv8libmFQl-hMwifdhthQ9mWCVe9om0TvN1RcX4IXAOsMxF3_-HzQMf1egxh24TFrCKhIc1t4k8Xg8e_O5YCVLpejy5wR7gjkApgA_TE8jRKwjz_PSWlxO3wGQ53vwTs-9iCTd-EBfsxqjDiPbTKOjznfl"/>
</div>
<div class="flex-1">
<p class="text-xs font-bold truncate">Alex Morgan</p>
<p class="text-[10px] text-slate-500">Premium Admin</p>
</div>
<span class="material-symbols-outlined text-slate-400 text-sm">settings</span>
</div>
</div>
</aside>
<!-- Main Content -->
<main class="flex-1 flex flex-col overflow-y-auto">
<!-- Header -->
<header class="h-16 border-b border-slate-200 dark:border-slate-800 bg-white/80 dark:bg-background-dark/80 backdrop-blur-md sticky top-0 z-10 px-8 flex items-center justify-between">
<div class="flex items-center gap-2">
<h2 class="text-lg font-bold">User Growth Analysis</h2>
</div>
<div class="flex items-center gap-4">
<div class="relative">
<span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm">search</span>
<input class="pl-9 pr-4 py-2 bg-slate-100 dark:bg-slate-800 border-none rounded-xl text-sm focus:ring-2 focus:ring-primary w-64" placeholder="Search analytics..." type="text"/>
</div>
<button class="size-10 flex items-center justify-center rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400">
<span class="material-symbols-outlined">notifications</span>
</button>
<button class="bg-primary text-white px-4 py-2 rounded-xl text-sm font-bold flex items-center gap-2">
<span class="material-symbols-outlined text-sm">file_download</span>
                        Export Report
                    </button>
</div>
</header>
<div class="p-8 space-y-8">
<!-- Metrics Grid -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
<div class="bg-white dark:bg-slate-900 p-6 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm">
<p class="text-sm font-medium text-slate-500 dark:text-slate-400">Total Users</p>
<div class="flex items-end justify-between mt-2">
<p class="text-3xl font-bold">1.24M</p>
<span class="text-emerald-500 text-sm font-bold flex items-center mb-1">
<span class="material-symbols-outlined text-sm mr-1">trending_up</span>+5.2%
                            </span>
</div>
</div>
<div class="bg-white dark:bg-slate-900 p-6 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm">
<p class="text-sm font-medium text-slate-500 dark:text-slate-400">Net Growth</p>
<div class="flex items-end justify-between mt-2">
<p class="text-3xl font-bold">15.4K</p>
<span class="text-emerald-500 text-sm font-bold flex items-center mb-1">
<span class="material-symbols-outlined text-sm mr-1">trending_up</span>+12.8%
                            </span>
</div>
</div>
<div class="bg-white dark:bg-slate-900 p-6 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm">
<p class="text-sm font-medium text-slate-500 dark:text-slate-400">Churn Rate</p>
<div class="flex items-end justify-between mt-2">
<p class="text-3xl font-bold">2.1%</p>
<span class="text-orange-500 text-sm font-bold flex items-center mb-1">
<span class="material-symbols-outlined text-sm mr-1">trending_down</span>-0.4%
                            </span>
</div>
</div>
<div class="bg-white dark:bg-slate-900 p-6 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm">
<p class="text-sm font-medium text-slate-500 dark:text-slate-400">Acquisition Cost</p>
<div class="flex items-end justify-between mt-2">
<p class="text-3xl font-bold">$12.50</p>
<span class="text-emerald-500 text-sm font-bold flex items-center mb-1">
<span class="material-symbols-outlined text-sm mr-1">trending_down</span>-2.1%
                            </span>
</div>
</div>
</div>
<!-- Cumulative Chart -->
<div class="bg-white dark:bg-slate-900 p-8 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm">
<div class="flex items-center justify-between mb-8">
<div>
<h3 class="text-lg font-bold">Cumulative User Growth</h3>
<p class="text-sm text-slate-500">Jan 2024 - Jun 2024</p>
</div>
<div class="flex items-center gap-2 bg-slate-100 dark:bg-slate-800 p-1 rounded-lg">
<button class="px-3 py-1 text-xs font-bold rounded-md bg-white dark:bg-slate-700 shadow-sm">6 Months</button>
<button class="px-3 py-1 text-xs font-bold rounded-md hover:bg-white dark:hover:bg-slate-700 transition-all">1 Year</button>
</div>
</div>
<div class="h-[300px] w-full relative">
<!-- Chart Mockup -->
<svg class="w-full h-full" preserveaspectratio="none" viewbox="0 0 1000 300">
<defs>
<lineargradient id="growthGradient" x1="0%" x2="0%" y1="0%" y2="100%">
<stop offset="0%" stop-color="#ec5b13" stop-opacity="0.2"></stop>
<stop offset="100%" stop-color="#ec5b13" stop-opacity="0"></stop>
</lineargradient>
</defs>
<path d="M0,250 Q100,240 200,210 T400,180 T600,120 T800,80 T1000,40 V300 H0 Z" fill="url(#growthGradient)"></path>
<path d="M0,250 Q100,240 200,210 T400,180 T600,120 T800,80 T1000,40" fill="none" stroke="#ec5b13" stroke-linecap="round" stroke-width="4"></path>
<!-- Grid lines -->
<line class="text-slate-200 dark:text-slate-800" stroke="currentColor" stroke-width="1" x1="0" x2="1000" y1="300" y2="300"></line>
<line class="text-slate-200 dark:text-slate-800" stroke="currentColor" stroke-dasharray="4" stroke-width="1" x1="0" x2="1000" y1="200" y2="200"></line>
<line class="text-slate-200 dark:text-slate-800" stroke="currentColor" stroke-dasharray="4" stroke-width="1" x1="0" x2="1000" y1="100" y2="100"></line>
</svg>
<div class="flex justify-between mt-4 text-xs font-bold text-slate-400 uppercase tracking-wider">
<span>Jan</span>
<span>Feb</span>
<span>Mar</span>
<span>Apr</span>
<span>May</span>
<span>Jun</span>
</div>
</div>
</div>
<div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
<!-- Bar Chart Area -->
<div class="bg-white dark:bg-slate-900 p-8 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm">
<div class="flex items-center justify-between mb-8">
<h3 class="text-lg font-bold">New Signups vs. Deactivations</h3>
<div class="flex items-center gap-4 text-xs font-medium">
<div class="flex items-center gap-2">
<span class="size-2 rounded-full bg-primary"></span>
<span>Signups</span>
</div>
<div class="flex items-center gap-2">
<span class="size-2 rounded-full bg-slate-300 dark:bg-slate-600"></span>
<span>Deactivations</span>
</div>
</div>
</div>
<div class="flex items-end justify-between h-48 gap-4">
<!-- Bar pairs -->
<div class="flex-1 flex flex-col justify-end items-center gap-1 group">
<div class="flex gap-1 items-end w-full">
<div class="flex-1 bg-primary rounded-t-sm h-32 transition-all group-hover:opacity-80"></div>
<div class="flex-1 bg-slate-200 dark:bg-slate-700 rounded-t-sm h-8"></div>
</div>
<span class="text-[10px] text-slate-400 mt-2 font-bold uppercase">Jan</span>
</div>
<div class="flex-1 flex flex-col justify-end items-center gap-1 group">
<div class="flex gap-1 items-end w-full">
<div class="flex-1 bg-primary rounded-t-sm h-40 transition-all group-hover:opacity-80"></div>
<div class="flex-1 bg-slate-200 dark:bg-slate-700 rounded-t-sm h-10"></div>
</div>
<span class="text-[10px] text-slate-400 mt-2 font-bold uppercase">Feb</span>
</div>
<div class="flex-1 flex flex-col justify-end items-center gap-1 group">
<div class="flex gap-1 items-end w-full">
<div class="flex-1 bg-primary rounded-t-sm h-36 transition-all group-hover:opacity-80"></div>
<div class="flex-1 bg-slate-200 dark:bg-slate-700 rounded-t-sm h-6"></div>
</div>
<span class="text-[10px] text-slate-400 mt-2 font-bold uppercase">Mar</span>
</div>
<div class="flex-1 flex flex-col justify-end items-center gap-1 group">
<div class="flex gap-1 items-end w-full">
<div class="flex-1 bg-primary rounded-t-sm h-44 transition-all group-hover:opacity-80"></div>
<div class="flex-1 bg-slate-200 dark:bg-slate-700 rounded-t-sm h-12"></div>
</div>
<span class="text-[10px] text-slate-400 mt-2 font-bold uppercase">Apr</span>
</div>
<div class="flex-1 flex flex-col justify-end items-center gap-1 group">
<div class="flex gap-1 items-end w-full">
<div class="flex-1 bg-primary rounded-t-sm h-38 transition-all group-hover:opacity-80"></div>
<div class="flex-1 bg-slate-200 dark:bg-slate-700 rounded-t-sm h-9"></div>
</div>
<span class="text-[10px] text-slate-400 mt-2 font-bold uppercase">May</span>
</div>
<div class="flex-1 flex flex-col justify-end items-center gap-1 group">
<div class="flex gap-1 items-end w-full">
<div class="flex-1 bg-primary rounded-t-sm h-48 transition-all group-hover:opacity-80"></div>
<div class="flex-1 bg-slate-200 dark:bg-slate-700 rounded-t-sm h-11"></div>
</div>
<span class="text-[10px] text-slate-400 mt-2 font-bold uppercase">Jun</span>
</div>
</div>
</div>
<!-- Growth by Region -->
<div class="bg-white dark:bg-slate-900 p-8 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm">
<div class="flex items-center justify-between mb-8">
<h3 class="text-lg font-bold">Growth by Region</h3>
<button class="text-xs font-bold text-primary hover:underline">View All</button>
</div>
<div class="space-y-6">
<div class="flex items-center justify-between">
<div class="flex items-center gap-3">
<div class="size-8 rounded-lg bg-slate-100 dark:bg-slate-800 flex items-center justify-center">
<span class="text-lg">🇺🇸</span>
</div>
<div>
<p class="text-sm font-bold">North America</p>
<p class="text-[10px] text-slate-400">42% of total users</p>
</div>
</div>
<div class="text-right">
<p class="text-sm font-bold">+12.4%</p>
<div class="w-24 h-1.5 bg-slate-100 dark:bg-slate-800 rounded-full mt-1 overflow-hidden">
<div class="h-full bg-primary" style="width: 78%"></div>
</div>
</div>
</div>
<div class="flex items-center justify-between">
<div class="flex items-center gap-3">
<div class="size-8 rounded-lg bg-slate-100 dark:bg-slate-800 flex items-center justify-center">
<span class="text-lg">🇪🇺</span>
</div>
<div>
<p class="text-sm font-bold">Europe</p>
<p class="text-[10px] text-slate-400">28% of total users</p>
</div>
</div>
<div class="text-right">
<p class="text-sm font-bold">+8.1%</p>
<div class="w-24 h-1.5 bg-slate-100 dark:bg-slate-800 rounded-full mt-1 overflow-hidden">
<div class="h-full bg-primary" style="width: 62%"></div>
</div>
</div>
</div>
<div class="flex items-center justify-between">
<div class="flex items-center gap-3">
<div class="size-8 rounded-lg bg-slate-100 dark:bg-slate-800 flex items-center justify-center">
<span class="text-lg">🇦🇺</span>
</div>
<div>
<p class="text-sm font-bold">Asia Pacific</p>
<p class="text-[10px] text-slate-400">18% of total users</p>
</div>
</div>
<div class="text-right">
<p class="text-sm font-bold">+15.6%</p>
<div class="w-24 h-1.5 bg-slate-100 dark:bg-slate-800 rounded-full mt-1 overflow-hidden">
<div class="h-full bg-primary" style="width: 88%"></div>
</div>
</div>
</div>
<div class="flex items-center justify-between">
<div class="flex items-center gap-3">
<div class="size-8 rounded-lg bg-slate-100 dark:bg-slate-800 flex items-center justify-center">
<span class="text-lg">🇧🇷</span>
</div>
<div>
<p class="text-sm font-bold">Latin America</p>
<p class="text-[10px] text-slate-400">12% of total users</p>
</div>
</div>
<div class="text-right">
<p class="text-sm font-bold">+4.3%</p>
<div class="w-24 h-1.5 bg-slate-100 dark:bg-slate-800 rounded-full mt-1 overflow-hidden">
<div class="h-full bg-primary" style="width: 32%"></div>
</div>
</div>
</div>
</div>
</div>
</div>
<!-- Top Referral Sources Table -->
<div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
<div class="p-8 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
<h3 class="text-lg font-bold">Top Referral Sources</h3>
<div class="flex items-center gap-2">
<span class="text-sm font-medium text-slate-400">Filter by:</span>
<select class="text-xs font-bold bg-slate-100 dark:bg-slate-800 border-none rounded-lg py-1 px-3">
<option>This Month</option>
<option>Last Quarter</option>
</select>
</div>
</div>
<table class="w-full text-left">
<thead>
<tr class="text-[10px] font-bold text-slate-400 uppercase tracking-widest bg-slate-50 dark:bg-slate-800/50">
<th class="px-8 py-4">Source</th>
<th class="px-8 py-4">Total Users</th>
<th class="px-8 py-4">Conversion Rate</th>
<th class="px-8 py-4">Trend</th>
<th class="px-8 py-4 text-right">Action</th>
</tr>
</thead>
<tbody class="divide-y divide-slate-100 dark:divide-slate-800">
<tr class="hover:bg-slate-50 dark:hover:bg-slate-800/30 transition-colors">
<td class="px-8 py-4">
<div class="flex items-center gap-3">
<div class="size-8 rounded-full bg-blue-100 dark:bg-blue-900/30 text-blue-600 flex items-center justify-center">
<span class="material-symbols-outlined text-sm">language</span>
</div>
<span class="text-sm font-bold">Organic Search</span>
</div>
</td>
<td class="px-8 py-4 text-sm font-medium">425,000</td>
<td class="px-8 py-4 text-sm font-medium">3.8%</td>
<td class="px-8 py-4">
<div class="text-emerald-500 flex items-center text-xs font-bold">
<span class="material-symbols-outlined text-sm">arrow_upward</span> 12%
                                    </div>
</td>
<td class="px-8 py-4 text-right">
<button class="text-slate-400 hover:text-primary transition-colors">
<span class="material-symbols-outlined">more_horiz</span>
</button>
</td>
</tr>
<tr class="hover:bg-slate-50 dark:hover:bg-slate-800/30 transition-colors">
<td class="px-8 py-4">
<div class="flex items-center gap-3">
<div class="size-8 rounded-full bg-red-100 dark:bg-red-900/30 text-red-600 flex items-center justify-center">
<span class="material-symbols-outlined text-sm">campaign</span>
</div>
<span class="text-sm font-bold">Instagram Ads</span>
</div>
</td>
<td class="px-8 py-4 text-sm font-medium">312,400</td>
<td class="px-8 py-4 text-sm font-medium">5.2%</td>
<td class="px-8 py-4">
<div class="text-emerald-500 flex items-center text-xs font-bold">
<span class="material-symbols-outlined text-sm">arrow_upward</span> 8.4%
                                    </div>
</td>
<td class="px-8 py-4 text-right">
<button class="text-slate-400 hover:text-primary transition-colors">
<span class="material-symbols-outlined">more_horiz</span>
</button>
</td>
</tr>
<tr class="hover:bg-slate-50 dark:hover:bg-slate-800/30 transition-colors">
<td class="px-8 py-4">
<div class="flex items-center gap-3">
<div class="size-8 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-600 flex items-center justify-center">
<span class="material-symbols-outlined text-sm">link</span>
</div>
<span class="text-sm font-bold">Partner Referrals</span>
</div>
</td>
<td class="px-8 py-4 text-sm font-medium">128,900</td>
<td class="px-8 py-4 text-sm font-medium">1.9%</td>
<td class="px-8 py-4">
<div class="text-orange-500 flex items-center text-xs font-bold">
<span class="material-symbols-outlined text-sm">arrow_downward</span> 2.1%
                                    </div>
</td>
<td class="px-8 py-4 text-right">
<button class="text-slate-400 hover:text-primary transition-colors">
<span class="material-symbols-outlined">more_horiz</span>
</button>
</td>
</tr>
</tbody>
</table>
</div>
</div>
<footer class="mt-auto p-8 border-t border-slate-200 dark:border-slate-800 text-center text-xs text-slate-400">
                © 2024 Social Analytics Growth Platform. All rights reserved.
            </footer>
</main>
</div>
</body></html>
