<!DOCTYPE html>

<html lang="en"><head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<link href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@300;400;500;600;700&amp;display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
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
<title>LocalConnect Admin Overview</title>
</head>
<body class="bg-background-light dark:bg-background-dark font-display text-slate-900 dark:text-slate-100 transition-colors duration-200">
<div class="flex min-h-screen">
<!-- Sidebar -->
<aside class="w-64 bg-slate-900 dark:bg-slate-950 flex flex-col border-r border-slate-200 dark:border-slate-800 shrink-0">
<div class="p-6 flex items-center gap-3">
<div class="w-10 h-10 rounded-xl bg-primary flex items-center justify-center text-white shadow-lg shadow-primary/20">
<span class="material-symbols-outlined">hub</span>
</div>
<div>
<h1 class="font-bold text-white text-lg leading-none">LocalConnect</h1>
<p class="text-slate-400 text-xs mt-1 uppercase tracking-wider">Admin Center</p>
</div>
</div>
<nav class="flex-1 px-4 space-y-1">
<a class="flex items-center gap-3 px-3 py-2.5 rounded-xl bg-primary text-white font-medium transition-colors" href="#">
<span class="material-symbols-outlined text-xl">dashboard</span>
<span>Dashboard</span>
</a>
<a class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-slate-400 hover:bg-slate-800 hover:text-white transition-colors" href="#">
<span class="material-symbols-outlined text-xl">group</span>
<span>Users</span>
</a>
<a class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-slate-400 hover:bg-slate-800 hover:text-white transition-colors" href="#">
<span class="material-symbols-outlined text-xl">article</span>
<span>Content</span>
</a>
<a class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-slate-400 hover:bg-slate-800 hover:text-white transition-colors" href="#">
<span class="material-symbols-outlined text-xl">assessment</span>
<span>Reports</span>
</a>
<div class="pt-4 pb-2 px-3">
<span class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">System</span>
</div>
<a class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-slate-400 hover:bg-slate-800 hover:text-white transition-colors" href="#">
<span class="material-symbols-outlined text-xl">settings</span>
<span>System Settings</span>
</a>
<a class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-slate-400 hover:bg-slate-800 hover:text-white transition-colors" href="#">
<span class="material-symbols-outlined text-xl">security</span>
<span>Security</span>
</a>
</nav>
<div class="p-4 mt-auto border-t border-slate-800">
<div class="flex items-center gap-3 p-2 rounded-xl bg-slate-800/50">
<div class="size-10 rounded-lg bg-cover bg-center shadow-inner" data-alt="Headshot of the system administrator" style="background-image: url('https://lh3.googleusercontent.com/aida-public/AB6AXuC85JrXywRWAk2wpRBbRHVHv4JSzt5mt4tzO86DhEoE4nOL5SyZrFKwcK5Ga7q9KzX40lnje_7U3m4HayuJn-UP9UxSdqmeqFkrX6hXr2Zk7BcmF2LHDz7db2Irr6c5Kn1TJ5szC8Pv9JBvsGIppfle78XlYdXjHZyfPQ19pUtP0voRPLkU5eAFcIXd1L8qykbsCG8Y_6M_QHc1sjy7DKFWEkugNajFvHZz6bzpILtcOH6QSzfYlod8lF6egqC6isTGaPmcq-1vsyv-')"></div>
<div class="flex-1 min-w-0">
<p class="text-xs font-bold text-white truncate">Alex Thompson</p>
<p class="text-[10px] text-slate-400 truncate">Senior Admin</p>
</div>
<button class="text-slate-400 hover:text-white">
<span class="material-symbols-outlined text-lg">logout</span>
</button>
</div>
</div>
</aside>
<!-- Main Content -->
<main class="flex-1 flex flex-col min-w-0 h-screen overflow-y-auto">
<!-- Header -->
<header class="h-16 flex items-center justify-between px-8 bg-white/80 dark:bg-background-dark/80 backdrop-blur-md border-b border-slate-200 dark:border-slate-800 sticky top-0 z-10">
<div class="flex items-center gap-4">
<h2 class="text-xl font-bold">Overview Report</h2>
</div>
<div class="flex items-center gap-4">
<div class="relative hidden md:block">
<span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-lg">search</span>
<input class="pl-10 pr-4 py-2 bg-slate-100 dark:bg-slate-800 border-none rounded-xl text-sm focus:ring-2 focus:ring-primary/50 w-64 transition-all" placeholder="Search analytics..." type="text"/>
</div>
<button class="p-2 text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-lg relative">
<span class="material-symbols-outlined">notifications</span>
<span class="absolute top-2 right-2.5 w-2 h-2 bg-primary rounded-full border-2 border-white dark:border-background-dark"></span>
</button>
<button class="flex items-center gap-2 px-4 py-2 bg-primary text-white rounded-xl text-sm font-semibold hover:bg-primary/90 transition-colors">
<span class="material-symbols-outlined text-sm">download</span>
                        Export Report
                    </button>
</div>
</header>
<div class="p-8 space-y-8">
<!-- KPI Row -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
<div class="p-6 bg-white dark:bg-slate-900 rounded-xl shadow-sm border border-slate-200 dark:border-slate-800 flex flex-col gap-1">
<div class="flex items-center justify-between mb-2">
<span class="text-xs font-bold text-slate-500 uppercase tracking-tight">Platform Uptime</span>
<span class="material-symbols-outlined text-emerald-500">check_circle</span>
</div>
<div class="flex items-end gap-2">
<h3 class="text-3xl font-bold">99.9%</h3>
<span class="text-emerald-500 text-sm font-semibold mb-1">+0.01%</span>
</div>
<p class="text-xs text-slate-400 mt-2">Target: 99.99%</p>
</div>
<div class="p-6 bg-white dark:bg-slate-900 rounded-xl shadow-sm border border-slate-200 dark:border-slate-800 flex flex-col gap-1">
<div class="flex items-center justify-between mb-2">
<span class="text-xs font-bold text-slate-500 uppercase tracking-tight">Total Alerts</span>
<span class="material-symbols-outlined text-primary">warning</span>
</div>
<div class="flex items-end gap-2">
<h3 class="text-3xl font-bold">12</h3>
<span class="text-emerald-500 text-sm font-semibold mb-1">-5%</span>
</div>
<p class="text-xs text-slate-400 mt-2">Active system monitors</p>
</div>
<div class="p-6 bg-white dark:bg-slate-900 rounded-xl shadow-sm border border-slate-200 dark:border-slate-800 flex flex-col gap-1">
<div class="flex items-center justify-between mb-2">
<span class="text-xs font-bold text-slate-500 uppercase tracking-tight">User Reports</span>
<span class="material-symbols-outlined text-slate-400">flag</span>
</div>
<div class="flex items-end gap-2">
<h3 class="text-3xl font-bold">45</h3>
<span class="text-rose-500 text-sm font-semibold mb-1">-12%</span>
</div>
<p class="text-xs text-slate-400 mt-2">Requires moderator review</p>
</div>
<div class="p-6 bg-white dark:bg-slate-900 rounded-xl shadow-sm border border-slate-200 dark:border-slate-800 flex flex-col gap-1">
<div class="flex items-center justify-between mb-2">
<span class="text-xs font-bold text-slate-500 uppercase tracking-tight">Server Load</span>
<span class="material-symbols-outlined text-slate-400">dns</span>
</div>
<div class="flex items-end gap-2">
<h3 class="text-3xl font-bold">28%</h3>
<span class="text-rose-500 text-sm font-semibold mb-1">-2%</span>
</div>
<div class="w-full bg-slate-100 dark:bg-slate-800 h-1.5 rounded-full mt-3">
<div class="bg-primary h-full rounded-full" style="width: 28%"></div>
</div>
</div>
</div>
<!-- Main Chart -->
<div class="bg-white dark:bg-slate-900 p-8 rounded-xl shadow-sm border border-slate-200 dark:border-slate-800">
<div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
<div>
<h3 class="text-lg font-bold">System Health &amp; Traffic</h3>
<p class="text-sm text-slate-500">Server Requests vs. Response Time (Last 24h)</p>
</div>
<div class="flex items-center gap-6">
<div class="flex items-center gap-2">
<span class="w-3 h-3 rounded-full bg-primary"></span>
<span class="text-xs font-semibold text-slate-600 dark:text-slate-400">Requests (RPM)</span>
</div>
<div class="flex items-center gap-2">
<span class="w-3 h-3 rounded-full bg-slate-400"></span>
<span class="text-xs font-semibold text-slate-600 dark:text-slate-400">Response (ms)</span>
</div>
<select class="text-xs font-semibold bg-slate-100 dark:bg-slate-800 border-none rounded-lg focus:ring-1 focus:ring-primary cursor-pointer">
<option>Last 24 Hours</option>
<option>Last 7 Days</option>
<option>Last 30 Days</option>
</select>
</div>
</div>
<div class="h-64 relative">
<svg class="w-full h-full preserve-3d" viewbox="0 0 1000 200">
<!-- Background Grid -->
<line class="text-slate-100 dark:text-slate-800" stroke="currentColor" stroke-width="1" x1="0" x2="1000" y1="40" y2="40"></line>
<line class="text-slate-100 dark:text-slate-800" stroke="currentColor" stroke-width="1" x1="0" x2="1000" y1="80" y2="80"></line>
<line class="text-slate-100 dark:text-slate-800" stroke="currentColor" stroke-width="1" x1="0" x2="1000" y1="120" y2="120"></line>
<line class="text-slate-100 dark:text-slate-800" stroke="currentColor" stroke-width="1" x1="0" x2="1000" y1="160" y2="160"></line>
<!-- Area Path Requests -->
<path class="opacity-10" d="M0,160 Q100,140 200,150 T400,100 T600,60 T800,90 T1000,120 V200 H0 Z" fill="url(#grad1)"></path>
<defs>
<lineargradient id="grad1" x1="0%" x2="0%" y1="0%" y2="100%">
<stop offset="0%" style="stop-color:var(--primary);stop-opacity:1"></stop>
<stop offset="100%" style="stop-color:var(--primary);stop-opacity:0"></stop>
</lineargradient>
</defs>
<!-- Line Path Requests -->
<path d="M0,160 Q100,140 200,150 T400,100 T600,60 T800,90 T1000,120" fill="none" stroke="#ec5b13" stroke-linecap="round" stroke-width="3"></path>
<!-- Line Path Latency (Secondary) -->
<path class="text-slate-400" d="M0,180 Q150,175 300,170 T500,175 T750,165 T1000,172" fill="none" stroke="currentColor" stroke-dasharray="6,4" stroke-width="2"></path>
</svg>
<div class="flex justify-between mt-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest px-2">
<span>00:00</span>
<span>04:00</span>
<span>08:00</span>
<span>12:00</span>
<span>16:00</span>
<span>20:00</span>
<span>23:59</span>
</div>
</div>
</div>
<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
<!-- Moderation Queue Table -->
<div class="lg:col-span-2 bg-white dark:bg-slate-900 rounded-xl shadow-sm border border-slate-200 dark:border-slate-800 overflow-hidden">
<div class="p-6 border-b border-slate-100 dark:border-slate-800 flex justify-between items-center">
<h3 class="text-lg font-bold">Moderation Queue</h3>
<button class="text-primary text-sm font-semibold hover:underline">View All</button>
</div>
<div class="overflow-x-auto">
<table class="w-full text-left">
<thead class="bg-slate-50 dark:bg-slate-800/50">
<tr>
<th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase">User</th>
<th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase">Reason</th>
<th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase">Status</th>
<th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase text-right">Actions</th>
</tr>
</thead>
<tbody class="divide-y divide-slate-100 dark:divide-slate-800">
<tr class="hover:bg-slate-50 dark:hover:bg-slate-800/30 transition-colors">
<td class="px-6 py-4">
<div class="flex items-center gap-3">
<div class="size-8 rounded-full bg-cover bg-center" data-alt="User avatar for reported user" style="background-image: url('https://lh3.googleusercontent.com/aida-public/AB6AXuB4K_8kL4lpzp1qLAIquFjZhHyN2pnyzk_Epojlp1gKmDxAL5SY-h7lIERAygMnHdQE4j5e0lL75JIsgpAthBk8AoCoSKM9VF-rigJRvkdIni08YkPEhDNLlCdFNcGOqOwS3HAsA6QHiYaYFWypoAtyZrf09gPzYeIpFz8_ff4rccm068oUjwg0YtPt0OZBphTkig6aEXsawugg6sjYuRqW7wTGmnAuir_-dRaLdVtxjhTYreIDUy7oIss6QF7nw5G7qDRmhVY9cHyg')"></div>
<span class="text-sm font-semibold">@jake_smith</span>
</div>
</td>
<td class="px-6 py-4">
<span class="text-sm px-2.5 py-1 rounded-full bg-rose-100 dark:bg-rose-500/20 text-rose-600 dark:text-rose-400 font-medium">Spam</span>
</td>
<td class="px-6 py-4">
<div class="flex items-center gap-1.5 text-sm text-slate-500">
<span class="size-2 rounded-full bg-amber-500"></span> Pending
                                            </div>
</td>
<td class="px-6 py-4 text-right space-x-2">
<button class="p-1.5 text-slate-400 hover:text-primary transition-colors"><span class="material-symbols-outlined text-lg">visibility</span></button>
<button class="p-1.5 text-slate-400 hover:text-rose-500 transition-colors"><span class="material-symbols-outlined text-lg">gavel</span></button>
<button class="p-1.5 text-slate-400 hover:text-emerald-500 transition-colors"><span class="material-symbols-outlined text-lg">done</span></button>
</td>
</tr>
<tr class="hover:bg-slate-50 dark:hover:bg-slate-800/30 transition-colors">
<td class="px-6 py-4">
<div class="flex items-center gap-3">
<div class="size-8 rounded-full bg-cover bg-center" data-alt="User avatar for reported user" style="background-image: url('https://lh3.googleusercontent.com/aida-public/AB6AXuALDQkiV12yF1-C-CtchOg7n13lpaavWzcXE18pLY3yi3qrZNNnidUarbOVDoBZu2PZhETbpVyy7pUjv4Htp7FSadjfudiEKv3IepFOx3ncdmNz8LNOnw0yTu5FFS3lsmeTtryIzyVtngpFfIuHf7rzX0CIvkRxQAdXZpv-I9o_-UAKOI0VlNJiQre5mTHpz1SDZJ-8-PKdwBXlm4wfHlzBSH7LMgiFWd1sRHr6SJRt53gS9apFG7_Zvmg0DwT4yoM3mNTwDqizsM--')"></div>
<span class="text-sm font-semibold">@sarah.v</span>
</div>
</td>
<td class="px-6 py-4">
<span class="text-sm px-2.5 py-1 rounded-full bg-amber-100 dark:bg-amber-500/20 text-amber-600 dark:text-amber-400 font-medium">Harassment</span>
</td>
<td class="px-6 py-4">
<div class="flex items-center gap-1.5 text-sm text-slate-500">
<span class="size-2 rounded-full bg-amber-500"></span> Pending
                                            </div>
</td>
<td class="px-6 py-4 text-right space-x-2">
<button class="p-1.5 text-slate-400 hover:text-primary transition-colors"><span class="material-symbols-outlined text-lg">visibility</span></button>
<button class="p-1.5 text-slate-400 hover:text-rose-500 transition-colors"><span class="material-symbols-outlined text-lg">gavel</span></button>
<button class="p-1.5 text-slate-400 hover:text-emerald-500 transition-colors"><span class="material-symbols-outlined text-lg">done</span></button>
</td>
</tr>
<tr class="hover:bg-slate-50 dark:hover:bg-slate-800/30 transition-colors">
<td class="px-6 py-4">
<div class="flex items-center gap-3">
<div class="size-8 rounded-full bg-cover bg-center" data-alt="User avatar for reported user" style="background-image: url('https://lh3.googleusercontent.com/aida-public/AB6AXuAqmmajQZJVDCPodD0jFAD5Ej0lxiGV0uld03sLm3CxXMlb67XtDZ457WXTlOF6d7RzCKO8BFnIq1N7NnZACOqdTZHSpoAKAyNVtKKfPfPSLWVpyHWq25pEPl1kFRtgABka5JQA86QMvIRa6uBUZ7bOP23m-RCR3Bs-7x_vwKZQI_xMMSEI7wmC-jSJbbXS3K9mDp6FMZMt59r2sBW4kF2T0liQTFDl_u6pNZI2xrdGIAW69C4VkWFBQQ8SOtF854TQ7M5ryop_ukQV')"></div>
<span class="text-sm font-semibold">@mystery_man</span>
</div>
</td>
<td class="px-6 py-4">
<span class="text-sm px-2.5 py-1 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 font-medium">Inappropriate Content</span>
</td>
<td class="px-6 py-4">
<div class="flex items-center gap-1.5 text-sm text-slate-500">
<span class="size-2 rounded-full bg-emerald-500"></span> Resolved
                                            </div>
</td>
<td class="px-6 py-4 text-right space-x-2">
<button class="p-1.5 text-slate-400 hover:text-primary transition-colors"><span class="material-symbols-outlined text-lg">visibility</span></button>
<button class="p-1.5 text-slate-400 hover:text-rose-500 transition-colors"><span class="material-symbols-outlined text-lg">gavel</span></button>
<button class="p-1.5 text-slate-400 hover:text-emerald-500 transition-colors"><span class="material-symbols-outlined text-lg">done</span></button>
</td>
</tr>
</tbody>
</table>
</div>
</div>
<!-- Regional Performance -->
<div class="bg-white dark:bg-slate-900 rounded-xl shadow-sm border border-slate-200 dark:border-slate-800 p-6">
<h3 class="text-lg font-bold mb-6">Regional Performance</h3>
<div class="space-y-6">
<div class="flex items-center justify-between">
<div class="flex items-center gap-3">
<span class="material-symbols-outlined text-slate-400">public</span>
<div>
<p class="text-sm font-semibold">North America (East)</p>
<p class="text-[10px] text-slate-400 font-bold uppercase">US-EAST-1</p>
</div>
</div>
<div class="text-right">
<p class="text-sm font-bold text-emerald-500">22ms</p>
<p class="text-[10px] text-slate-400">Optimal</p>
</div>
</div>
<div class="flex items-center justify-between">
<div class="flex items-center gap-3">
<span class="material-symbols-outlined text-slate-400">public</span>
<div>
<p class="text-sm font-semibold">Europe (Frankfurt)</p>
<p class="text-[10px] text-slate-400 font-bold uppercase">EU-CENTRAL-1</p>
</div>
</div>
<div class="text-right">
<p class="text-sm font-bold text-emerald-500">38ms</p>
<p class="text-[10px] text-slate-400">Stable</p>
</div>
</div>
<div class="flex items-center justify-between">
<div class="flex items-center gap-3">
<span class="material-symbols-outlined text-slate-400">public</span>
<div>
<p class="text-sm font-semibold">Asia Pacific (Tokyo)</p>
<p class="text-[10px] text-slate-400 font-bold uppercase">AP-NORTHEAST-1</p>
</div>
</div>
<div class="text-right">
<p class="text-sm font-bold text-amber-500">145ms</p>
<p class="text-[10px] text-slate-400">High Load</p>
</div>
</div>
<div class="flex items-center justify-between">
<div class="flex items-center gap-3">
<span class="material-symbols-outlined text-slate-400">public</span>
<div>
<p class="text-sm font-semibold">South America (São Paulo)</p>
<p class="text-[10px] text-slate-400 font-bold uppercase">SA-EAST-1</p>
</div>
</div>
<div class="text-right">
<p class="text-sm font-bold text-emerald-500">64ms</p>
<p class="text-[10px] text-slate-400">Stable</p>
</div>
</div>
</div>
<div class="mt-8">
<div class="w-full aspect-video bg-slate-100 dark:bg-slate-800 rounded-lg flex items-center justify-center overflow-hidden border border-slate-200 dark:border-slate-800">
<span class="material-symbols-outlined text-slate-300 dark:text-slate-700 text-6xl">map</span>
<!-- Minimal Map Placeholder -->
</div>
</div>
</div>
</div>
<!-- Recent Admin Activity -->
<div class="bg-white dark:bg-slate-900 rounded-xl shadow-sm border border-slate-200 dark:border-slate-800 p-8">
<h3 class="text-lg font-bold mb-6">Recent Admin Activity</h3>
<div class="relative">
<div class="absolute left-4 top-0 bottom-0 w-px bg-slate-100 dark:bg-slate-800"></div>
<div class="space-y-8 relative">
<div class="flex items-start gap-4">
<div class="z-10 w-8 h-8 rounded-full bg-primary flex items-center justify-center text-white ring-4 ring-white dark:ring-slate-900 shadow-sm">
<span class="material-symbols-outlined text-sm">block</span>
</div>
<div class="flex-1">
<p class="text-sm"><span class="font-bold">Alex Thompson</span> suspended account <span class="font-bold">@bot_9921</span></p>
<p class="text-xs text-slate-400 mt-1">Today at 10:45 AM</p>
</div>
</div>
<div class="flex items-start gap-4">
<div class="z-10 w-8 h-8 rounded-full bg-slate-800 dark:bg-slate-700 flex items-center justify-center text-white ring-4 ring-white dark:ring-slate-900 shadow-sm">
<span class="material-symbols-outlined text-sm">settings_backup_restore</span>
</div>
<div class="flex-1">
<p class="text-sm"><span class="font-bold">System</span> automatically cleared 1.2GB of cache files</p>
<p class="text-xs text-slate-400 mt-1">Today at 09:12 AM</p>
</div>
</div>
<div class="flex items-start gap-4">
<div class="z-10 w-8 h-8 rounded-full bg-primary flex items-center justify-center text-white ring-4 ring-white dark:ring-slate-900 shadow-sm">
<span class="material-symbols-outlined text-sm">admin_panel_settings</span>
</div>
<div class="flex-1">
<p class="text-sm"><span class="font-bold">Marcus Chen</span> updated community guidelines</p>
<p class="text-xs text-slate-400 mt-1">Yesterday at 04:30 PM</p>
</div>
</div>
</div>
</div>
</div>
</div>
<footer class="p-8 border-t border-slate-200 dark:border-slate-800 text-center text-slate-400 text-xs">
                © 2024 LocalConnect Social Network. All Rights Reserved. Infrastructure Status: Normal.
            </footer>
</main>
</div>
</body></html>
