<!DOCTYPE html>

<html class="light" lang="en"><head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title>Referrals Report - Social Analytics</title>
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<link href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@300;400;500;600;700;800;900&amp;display=swap" rel="stylesheet"/>
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
</head>
<body class="bg-background-light dark:bg-background-dark font-display text-slate-900 dark:text-slate-100 transition-colors duration-200">
<div class="flex min-h-screen">
<!-- SideNavBar -->
<aside class="w-64 border-r border-slate-200 dark:border-slate-800 bg-white dark:bg-background-dark hidden md:flex flex-col sticky top-0 h-screen">
<div class="p-6 flex items-center gap-3">
<div class="bg-primary rounded-lg size-10 flex items-center justify-center text-white">
<span class="material-symbols-outlined">analytics</span>
</div>
<div class="flex flex-col">
<h1 class="text-slate-900 dark:text-white text-base font-bold leading-none">Social Analytics</h1>
<p class="text-slate-500 dark:text-slate-400 text-xs mt-1">Admin Console</p>
</div>
</div>
<nav class="flex-1 px-4 py-4 space-y-1">
<a class="flex items-center gap-3 px-3 py-2 text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-xl transition-colors" href="#">
<span class="material-symbols-outlined">dashboard</span>
<span class="text-sm font-medium">Dashboard</span>
</a>
<a class="flex items-center gap-3 px-3 py-2 text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-xl transition-colors" href="#">
<span class="material-symbols-outlined">bar_chart</span>
<span class="text-sm font-medium">Analytics</span>
</a>
<a class="flex items-center gap-3 px-3 py-2 bg-primary/10 text-primary rounded-xl transition-colors" href="#">
<span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1">account_circle</span>
<span class="text-sm font-medium">Referrals</span>
</a>
<a class="flex items-center gap-3 px-3 py-2 text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-xl transition-colors" href="#">
<span class="material-symbols-outlined">campaign</span>
<span class="text-sm font-medium">Campaigns</span>
</a>
<a class="flex items-center gap-3 px-3 py-2 text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-xl transition-colors" href="#">
<span class="material-symbols-outlined">settings</span>
<span class="text-sm font-medium">Settings</span>
</a>
</nav>
<div class="p-4 border-t border-slate-200 dark:border-slate-800">
<div class="flex items-center gap-3 p-2">
<div class="size-8 rounded-full bg-slate-200 dark:bg-slate-700 overflow-hidden">
<img alt="Profile" data-alt="User profile picture" src="https://lh3.googleusercontent.com/aida-public/AB6AXuCylnfJ7UhNv740BZZ2Yaz9v0ufO9Lctxo94D_1PkvokbgS-cpqUryjzxiKKK6U0jFWETg4nC-ILk1Fo4wfYfwjyyTeDf-9Lef6UeNWYkqt-kH6Rpj5XhK97GSAJ7L9lIqZE3XDbhp4uiglUI0eCqFmMxKPvg2DEp6Dq4tZ58YuM9fPQCC5u4BNY8IOklhsKtKVr9JfdED1L5fOZ8J8fsnyUGsr_W1jYFcKBVQ1MaBKV7lRJpJx6Bojjnn8JkJcRrQgvADjae9xXkkG"/>
</div>
<div class="flex flex-col overflow-hidden">
<p class="text-sm font-semibold truncate">Alex Rivera</p>
<p class="text-xs text-slate-500 truncate">alex@analytics.com</p>
</div>
</div>
</div>
</aside>
<!-- Main Content -->
<main class="flex-1 flex flex-col min-w-0">
<!-- Header -->
<header class="h-16 border-b border-slate-200 dark:border-slate-800 bg-white/80 dark:bg-background-dark/80 backdrop-blur-md sticky top-0 z-10 px-8 flex items-center justify-between">
<div class="flex items-center gap-4">
<span class="material-symbols-outlined md:hidden cursor-pointer">menu</span>
<h2 class="text-lg font-bold">Referrals Report</h2>
</div>
<div class="flex items-center gap-4">
<div class="relative hidden sm:block">
<span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm">search</span>
<input class="bg-slate-100 dark:bg-slate-800 border-none rounded-xl pl-10 pr-4 py-2 text-sm focus:ring-2 focus:ring-primary w-64" placeholder="Search referrals..." type="text"/>
</div>
<button class="p-2 text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-xl transition-colors">
<span class="material-symbols-outlined">notifications</span>
</button>
<button class="bg-primary text-white px-4 py-2 rounded-xl text-sm font-bold flex items-center gap-2">
<span class="material-symbols-outlined text-sm">download</span>
                        Export
                    </button>
</div>
</header>
<div class="p-8 space-y-8 max-w-[1400px] mx-auto w-full">
<!-- Referral Overview Cards -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
<div class="bg-white dark:bg-slate-900 p-6 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm">
<p class="text-slate-500 dark:text-slate-400 text-sm font-medium">Total Referral Signups</p>
<div class="flex items-end justify-between mt-2">
<h3 class="text-3xl font-bold">1,284</h3>
<span class="text-emerald-500 text-sm font-bold flex items-center bg-emerald-50 dark:bg-emerald-500/10 px-2 py-1 rounded-lg">
<span class="material-symbols-outlined text-sm mr-1">trending_up</span>+14%
                            </span>
</div>
</div>
<div class="bg-white dark:bg-slate-900 p-6 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm">
<p class="text-slate-500 dark:text-slate-400 text-sm font-medium">Referral Conversion Rate</p>
<div class="flex items-end justify-between mt-2">
<h3 class="text-3xl font-bold">12.5%</h3>
<span class="text-emerald-500 text-sm font-bold flex items-center bg-emerald-50 dark:bg-emerald-500/10 px-2 py-1 rounded-lg">
<span class="material-symbols-outlined text-sm mr-1">trending_up</span>+2.1%
                            </span>
</div>
</div>
<div class="bg-white dark:bg-slate-900 p-6 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm">
<p class="text-slate-500 dark:text-slate-400 text-sm font-medium">Top Referral Channel</p>
<div class="flex items-center gap-3 mt-2">
<h3 class="text-2xl font-bold">WhatsApp</h3>
<div class="bg-primary/10 text-primary p-1 rounded-md">
<span class="material-symbols-outlined text-lg">share</span>
</div>
</div>
</div>
<div class="bg-white dark:bg-slate-900 p-6 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm">
<p class="text-slate-500 dark:text-slate-400 text-sm font-medium">Avg. Referral Value</p>
<div class="flex items-end justify-between mt-2">
<h3 class="text-3xl font-bold">$42.00</h3>
<span class="text-emerald-500 text-sm font-bold flex items-center bg-emerald-50 dark:bg-emerald-500/10 px-2 py-1 rounded-lg">
<span class="material-symbols-outlined text-sm mr-1">trending_up</span>+5%
                            </span>
</div>
</div>
</div>
<!-- Charts Section -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
<!-- Referral Trends Chart -->
<div class="lg:col-span-2 bg-white dark:bg-slate-900 p-6 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm">
<div class="flex items-center justify-between mb-6">
<h4 class="font-bold text-lg">Referral Trends (Last 30 Days)</h4>
<select class="bg-slate-100 dark:bg-slate-800 border-none rounded-lg text-xs font-semibold px-3 py-1 cursor-pointer">
<option>Daily</option>
<option>Weekly</option>
</select>
</div>
<div class="h-64 flex items-end justify-between gap-1">
<!-- Visual placeholder for a line chart using CSS gradients and bars for simplicity in static HTML -->
<div class="w-full h-full relative overflow-hidden bg-slate-50 dark:bg-slate-800/50 rounded-lg flex items-end">
<div class="absolute inset-0 p-4 opacity-10">
<div class="border-b border-slate-400 w-full h-1/4"></div>
<div class="border-b border-slate-400 w-full h-1/4"></div>
<div class="border-b border-slate-400 w-full h-1/4"></div>
</div>
<div class="flex-1 h-[40%] bg-primary/20 border-t-2 border-primary mx-px"></div>
<div class="flex-1 h-[45%] bg-primary/20 border-t-2 border-primary mx-px"></div>
<div class="flex-1 h-[35%] bg-primary/20 border-t-2 border-primary mx-px"></div>
<div class="flex-1 h-[60%] bg-primary/20 border-t-2 border-primary mx-px"></div>
<div class="flex-1 h-[55%] bg-primary/20 border-t-2 border-primary mx-px"></div>
<div class="flex-1 h-[80%] bg-primary/20 border-t-2 border-primary mx-px"></div>
<div class="flex-1 h-[70%] bg-primary/20 border-t-2 border-primary mx-px"></div>
<div class="flex-1 h-[85%] bg-primary/20 border-t-2 border-primary mx-px"></div>
<div class="flex-1 h-[65%] bg-primary/20 border-t-2 border-primary mx-px"></div>
<div class="flex-1 h-[90%] bg-primary/20 border-t-2 border-primary mx-px"></div>
<div class="flex-1 h-[75%] bg-primary/20 border-t-2 border-primary mx-px"></div>
<div class="flex-1 h-[60%] bg-primary/20 border-t-2 border-primary mx-px"></div>
<div class="flex-1 h-[50%] bg-primary/20 border-t-2 border-primary mx-px"></div>
<div class="flex-1 h-[65%] bg-primary/20 border-t-2 border-primary mx-px"></div>
</div>
</div>
<div class="flex justify-between mt-4 text-xs text-slate-400 px-2 font-medium">
<span>May 01</span>
<span>May 10</span>
<span>May 20</span>
<span>May 30</span>
</div>
</div>
<!-- Top Referral Sources -->
<div class="bg-white dark:bg-slate-900 p-6 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm">
<h4 class="font-bold text-lg mb-6">Top Referral Sources</h4>
<div class="space-y-6">
<div class="space-y-2">
<div class="flex justify-between text-sm">
<span class="flex items-center gap-2"><span class="material-symbols-outlined text-green-500">chat</span>WhatsApp</span>
<span class="font-bold">42%</span>
</div>
<div class="h-2 w-full bg-slate-100 dark:bg-slate-800 rounded-full overflow-hidden">
<div class="h-full bg-primary" style="width: 42%"></div>
</div>
</div>
<div class="space-y-2">
<div class="flex justify-between text-sm">
<span class="flex items-center gap-2"><span class="material-symbols-outlined text-blue-400">alternate_email</span>X/Twitter</span>
<span class="font-bold">28%</span>
</div>
<div class="h-2 w-full bg-slate-100 dark:bg-slate-800 rounded-full overflow-hidden">
<div class="h-full bg-primary" style="width: 28%"></div>
</div>
</div>
<div class="space-y-2">
<div class="flex justify-between text-sm">
<span class="flex items-center gap-2"><span class="material-symbols-outlined text-slate-400">mail</span>Email</span>
<span class="font-bold">15%</span>
</div>
<div class="h-2 w-full bg-slate-100 dark:bg-slate-800 rounded-full overflow-hidden">
<div class="h-full bg-primary" style="width: 15%"></div>
</div>
</div>
<div class="space-y-2">
<div class="flex justify-between text-sm">
<span class="flex items-center gap-2"><span class="material-symbols-outlined text-slate-400">link</span>Direct Link</span>
<span class="font-bold">15%</span>
</div>
<div class="h-2 w-full bg-slate-100 dark:bg-slate-800 rounded-full overflow-hidden">
<div class="h-full bg-primary" style="width: 15%"></div>
</div>
</div>
</div>
</div>
</div>
<!-- Program Performance -->
<div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
<div class="p-6 border-b border-slate-200 dark:border-slate-800 flex justify-between items-center">
<h4 class="font-bold text-lg">Referral Program Performance</h4>
<button class="text-primary text-sm font-semibold hover:underline">View All Programs</button>
</div>
<div class="grid grid-cols-1 md:grid-cols-3 divide-y md:divide-y-0 md:divide-x divide-slate-200 dark:divide-slate-800">
<div class="p-6">
<p class="text-slate-500 text-sm mb-1">Summer Kickoff Campaign</p>
<h5 class="text-xl font-bold">452 Signups</h5>
<div class="mt-4 flex items-center gap-4">
<div class="flex-1 h-1.5 bg-slate-100 dark:bg-slate-800 rounded-full overflow-hidden">
<div class="h-full bg-orange-500 w-[75%]"></div>
</div>
<span class="text-xs font-bold">75% of target</span>
</div>
</div>
<div class="p-6">
<p class="text-slate-500 text-sm mb-1">Loyalty Rewards v2</p>
<h5 class="text-xl font-bold">819 Signups</h5>
<div class="mt-4 flex items-center gap-4">
<div class="flex-1 h-1.5 bg-slate-100 dark:bg-slate-800 rounded-full overflow-hidden">
<div class="h-full bg-orange-500 w-[92%]"></div>
</div>
<span class="text-xs font-bold">92% of target</span>
</div>
</div>
<div class="p-6">
<p class="text-slate-500 text-sm mb-1">Partner Affiliate Beta</p>
<h5 class="text-xl font-bold">128 Signups</h5>
<div class="mt-4 flex items-center gap-4">
<div class="flex-1 h-1.5 bg-slate-100 dark:bg-slate-800 rounded-full overflow-hidden">
<div class="h-full bg-orange-500 w-[45%]"></div>
</div>
<span class="text-xs font-bold">45% of target</span>
</div>
</div>
</div>
</div>
<!-- Recent Activity Table -->
<div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
<div class="p-6 border-b border-slate-200 dark:border-slate-800">
<h4 class="font-bold text-lg">Recent Referral Activity</h4>
</div>
<div class="overflow-x-auto">
<table class="w-full text-left">
<thead class="bg-slate-50 dark:bg-slate-800/50 text-slate-500 text-xs font-bold uppercase tracking-wider">
<tr>
<th class="px-6 py-4">Inviter</th>
<th class="px-6 py-4">Invitee</th>
<th class="px-6 py-4">Channel</th>
<th class="px-6 py-4">Status</th>
<th class="px-6 py-4 text-right">Date</th>
</tr>
</thead>
<tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-sm">
<tr>
<td class="px-6 py-4 font-medium">Sarah Jenkins</td>
<td class="px-6 py-4">Mike Ross</td>
<td class="px-6 py-4">
<span class="flex items-center gap-2"><span class="material-symbols-outlined text-green-500 text-base">chat</span> WhatsApp</span>
</td>
<td class="px-6 py-4">
<span class="px-2 py-1 rounded-full bg-emerald-100 dark:bg-emerald-500/20 text-emerald-600 dark:text-emerald-400 text-xs font-bold uppercase tracking-wide">Success</span>
</td>
<td class="px-6 py-4 text-right text-slate-500">2 mins ago</td>
</tr>
<tr>
<td class="px-6 py-4 font-medium">David Chen</td>
<td class="px-6 py-4">Anita Kapoor</td>
<td class="px-6 py-4">
<span class="flex items-center gap-2"><span class="material-symbols-outlined text-blue-400 text-base">alternate_email</span> X/Twitter</span>
</td>
<td class="px-6 py-4">
<span class="px-2 py-1 rounded-full bg-emerald-100 dark:bg-emerald-500/20 text-emerald-600 dark:text-emerald-400 text-xs font-bold uppercase tracking-wide">Success</span>
</td>
<td class="px-6 py-4 text-right text-slate-500">15 mins ago</td>
</tr>
<tr>
<td class="px-6 py-4 font-medium">Emma Watson</td>
<td class="px-6 py-4">John Doe</td>
<td class="px-6 py-4">
<span class="flex items-center gap-2"><span class="material-symbols-outlined text-slate-400 text-base">mail</span> Email</span>
</td>
<td class="px-6 py-4">
<span class="px-2 py-1 rounded-full bg-amber-100 dark:bg-amber-500/20 text-amber-600 dark:text-amber-400 text-xs font-bold uppercase tracking-wide">Pending</span>
</td>
<td class="px-6 py-4 text-right text-slate-500">1 hour ago</td>
</tr>
<tr>
<td class="px-6 py-4 font-medium">Marcus Aurelius</td>
<td class="px-6 py-4">Lucius Verus</td>
<td class="px-6 py-4">
<span class="flex items-center gap-2"><span class="material-symbols-outlined text-slate-400 text-base">link</span> Direct Link</span>
</td>
<td class="px-6 py-4">
<span class="px-2 py-1 rounded-full bg-emerald-100 dark:bg-emerald-500/20 text-emerald-600 dark:text-emerald-400 text-xs font-bold uppercase tracking-wide">Success</span>
</td>
<td class="px-6 py-4 text-right text-slate-500">3 hours ago</td>
</tr>
<tr>
<td class="px-6 py-4 font-medium">Linda Stark</td>
<td class="px-6 py-4">Arya Grey</td>
<td class="px-6 py-4">
<span class="flex items-center gap-2"><span class="material-symbols-outlined text-green-500 text-base">chat</span> WhatsApp</span>
</td>
<td class="px-6 py-4">
<span class="px-2 py-1 rounded-full bg-red-100 dark:bg-red-500/20 text-red-600 dark:text-red-400 text-xs font-bold uppercase tracking-wide">Expired</span>
</td>
<td class="px-6 py-4 text-right text-slate-500">Yesterday</td>
</tr>
</tbody>
</table>
</div>
<div class="p-4 border-t border-slate-200 dark:border-slate-800 flex justify-center">
<button class="text-sm font-semibold text-slate-500 hover:text-primary transition-colors">Load More Activity</button>
</div>
</div>
</div>
</main>
</div>
</body></html>
