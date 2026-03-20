<!DOCTYPE html>

<html lang="en"><head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title>User Profile Report - LocalConnect</title>
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
</head>
<body class="bg-background-light dark:bg-background-dark font-display text-slate-900 dark:text-slate-100">
<div class="relative flex h-auto min-h-screen w-full flex-col overflow-x-hidden">
<div class="layout-container flex h-full grow flex-col">
<!-- Header Section -->
<header class="flex items-center justify-between border-b border-slate-200 dark:border-slate-800 bg-background-light dark:bg-background-dark px-6 py-4 sticky top-0 z-50">
<div class="flex items-center gap-4">
<div class="p-2 bg-primary rounded-lg text-white">
<span class="material-symbols-outlined">hub</span>
</div>
<h2 class="text-lg font-bold tracking-tight">LocalConnect</h2>
</div>
<div class="flex flex-1 justify-center max-w-xl mx-8">
<div class="relative w-full">
<span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">search</span>
<input class="w-full pl-10 pr-4 py-2 rounded-xl border-none bg-slate-100 dark:bg-slate-800 focus:ring-2 focus:ring-primary/50" placeholder="Search insights..."/>
</div>
</div>
<div class="flex items-center gap-4">
<button class="p-2 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 transition-colors">
<span class="material-symbols-outlined">notifications</span>
</button>
<button class="p-2 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 transition-colors">
<span class="material-symbols-outlined">settings</span>
</button>
<div class="h-10 w-10 rounded-full bg-primary/20 flex items-center justify-center overflow-hidden border-2 border-primary">
<img alt="User profile photo" data-alt="Portrait of a young male professional smiling" src="https://lh3.googleusercontent.com/aida-public/AB6AXuAfNvwRIw_a96qrhBqM6GfGp05AHqEpEcYp_bckE7OvrY3MioS-wTHs5nXloH3Dyb7AcZlnFnm-jB-7OuX572X7qm9m-6PVpKZml0aynPbzzJfeNnSJdwpOjMJb3h8YbflXZUgqin1yVGTdh6fXTv45GGk8uXhleCUodFnQW1Bed3RyTVGdhr2nizHCf-V25uM-Ab45-0bSLH8iFmrRyPLcwzFIfOXx7l7V_RbAVNZRNL9BkOY09Zczj5iS75SZ2f49q0IhJ9FGNmvL"/>
</div>
</div>
</header>
<div class="flex flex-1">
<!-- Sidebar -->
<aside class="w-64 border-r border-slate-200 dark:border-slate-800 p-4 hidden lg:flex flex-col gap-2 bg-background-light dark:bg-background-dark">
<div class="flex items-center gap-3 px-3 py-2 rounded-xl bg-primary/10 text-primary font-semibold">
<span class="material-symbols-outlined">analytics</span>
<span>Insights</span>
</div>
<div class="flex items-center gap-3 px-3 py-2 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
<span class="material-symbols-outlined">explore</span>
<span>Discovery</span>
</div>
<div class="flex items-center gap-3 px-3 py-2 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
<span class="material-symbols-outlined">group</span>
<span>Network</span>
</div>
<div class="flex items-center gap-3 px-3 py-2 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
<span class="material-symbols-outlined">mail</span>
<span>Messages</span>
</div>
<div class="mt-auto flex flex-col gap-2">
<div class="p-4 rounded-xl bg-primary/5 border border-primary/20">
<p class="text-xs font-semibold text-primary uppercase">Pro Plan</p>
<p class="text-sm mt-1">Unlock advanced analytics and deeper networking tools.</p>
<button class="mt-3 w-full py-2 bg-primary text-white rounded-lg text-sm font-bold">Upgrade</button>
</div>
</div>
</aside>
<!-- Main Content -->
<main class="flex-1 overflow-y-auto p-6 @container">
<div class="max-w-6xl mx-auto space-y-6">
<!-- Profile Summary Header -->
<div class="bg-white dark:bg-slate-900 rounded-xl p-6 shadow-sm border border-slate-200 dark:border-slate-800 flex flex-col @[520px]:flex-row gap-6 items-center">
<div class="relative">
<div class="w-24 h-24 rounded-full border-4 border-primary p-1">
<div class="w-full h-full rounded-full overflow-hidden">
<img class="w-full h-full object-cover" data-alt="Close up of Alex Rivers profile picture" src="https://lh3.googleusercontent.com/aida-public/AB6AXuACudE4wIbVW7y50IhrNmWm59uVr0ZLX7LI9WDGVHWIP2IcY0vrYUzVuygQA4OAwOlVlrJdABf8V9N2xzAa8Sjhz9JFIrP6QdZgfTqWnA8B_ur5N-O9unfNIVIQK1cyCvesMM8apB2BgzazWRMn_MLxhpCmkiBXHnBE2yzQb1-dWYalOkR7ia8W7453G4UzkbNWoIidKtMzFZRIsZGkPCYGVnngene26e3Ly_DhNn2wVGqoJ7scHT-_blsgMHnMx7j28XD7ntK7KqlW"/>
</div>
</div>
<div class="absolute bottom-1 right-1 w-6 h-6 bg-green-500 border-2 border-white dark:border-slate-900 rounded-full"></div>
</div>
<div class="flex-1 text-center @[520px]:text-left">
<div class="flex flex-col @[520px]:flex-row @[520px]:items-center gap-2">
<h1 class="text-2xl font-bold">Alex Rivers</h1>
<span class="px-2 py-0.5 bg-primary/20 text-primary text-xs font-bold rounded-full uppercase tracking-wider">Local Enthusiast</span>
</div>
<p class="text-slate-500 dark:text-slate-400 mt-2 max-w-2xl">
                                    Exploring the hidden gems of our city. Connector &amp; Storyteller. Passionate about sustainable urban living and community art projects.
                                </p>
<div class="flex flex-wrap justify-center @[520px]:justify-start gap-4 mt-4 text-sm font-medium">
<span class="flex items-center gap-1 text-slate-500"><span class="material-symbols-outlined text-sm">location_on</span> Portland, OR</span>
<span class="flex items-center gap-1 text-slate-500"><span class="material-symbols-outlined text-sm">calendar_today</span> Member since Oct 2023</span>
</div>
</div>
<div class="flex gap-2">
<button class="px-4 py-2 border border-slate-300 dark:border-slate-700 rounded-xl font-semibold hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors">Edit Profile</button>
<button class="px-4 py-2 bg-primary text-white rounded-xl font-semibold hover:opacity-90 transition-opacity">Export Report</button>
</div>
</div>
<!-- Key Metrics Grid -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
<div class="bg-white dark:bg-slate-900 p-6 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm">
<div class="flex justify-between items-start">
<div>
<p class="text-sm font-medium text-slate-500 uppercase tracking-wider">Total Posts</p>
<p class="text-3xl font-bold mt-1">124</p>
</div>
<div class="p-2 bg-primary/10 rounded-lg text-primary">
<span class="material-symbols-outlined">post_add</span>
</div>
</div>
<div class="mt-4 flex items-center text-green-500 text-xs font-bold">
<span class="material-symbols-outlined text-xs">trending_up</span>
<span class="ml-1">+12% from last month</span>
</div>
</div>
<div class="bg-white dark:bg-slate-900 p-6 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm">
<div class="flex justify-between items-start">
<div>
<p class="text-sm font-medium text-slate-500 uppercase tracking-wider">Comments</p>
<p class="text-3xl font-bold mt-1">482</p>
</div>
<div class="p-2 bg-blue-500/10 rounded-lg text-blue-500">
<span class="material-symbols-outlined">forum</span>
</div>
</div>
<div class="mt-4 flex items-center text-green-500 text-xs font-bold">
<span class="material-symbols-outlined text-xs">trending_up</span>
<span class="ml-1">+5% from last month</span>
</div>
</div>
<div class="bg-white dark:bg-slate-900 p-6 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm">
<div class="flex justify-between items-start">
<div>
<p class="text-sm font-medium text-slate-500 uppercase tracking-wider">Likes Given</p>
<p class="text-3xl font-bold mt-1">1.2k</p>
</div>
<div class="p-2 bg-red-500/10 rounded-lg text-red-500">
<span class="material-symbols-outlined">favorite</span>
</div>
</div>
<div class="mt-4 flex items-center text-slate-400 text-xs font-bold">
<span class="material-symbols-outlined text-xs">trending_flat</span>
<span class="ml-1">Consistent activity</span>
</div>
</div>
<div class="bg-white dark:bg-slate-900 p-6 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm">
<div class="flex justify-between items-start">
<div>
<p class="text-sm font-medium text-slate-500 uppercase tracking-wider">Days Active</p>
<p class="text-3xl font-bold mt-1">145</p>
</div>
<div class="p-2 bg-amber-500/10 rounded-lg text-amber-500">
<span class="material-symbols-outlined">calendar_today</span>
</div>
</div>
<div class="mt-4 flex items-center text-green-500 text-xs font-bold">
<span class="material-symbols-outlined text-xs">verified</span>
<span class="ml-1">92% Login rate</span>
</div>
</div>
</div>
<!-- Content Grid: Activity Timeline & Connection Stats -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
<!-- Activity Timeline -->
<div class="lg:col-span-2 bg-white dark:bg-slate-900 p-6 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm">
<div class="flex items-center justify-between mb-6">
<h2 class="text-lg font-bold">Activity Timeline</h2>
<select class="text-sm border-slate-200 dark:border-slate-700 bg-transparent rounded-lg focus:ring-primary">
<option>Last 30 Days</option>
<option>Last 3 Months</option>
</select>
</div>
<div class="h-64 w-full flex items-end gap-1 sm:gap-2">
<!-- Bar Chart Visualization -->
<div class="flex-1 bg-primary/20 rounded-t h-[40%] hover:bg-primary transition-all group relative">
<div class="absolute -top-8 left-1/2 -translate-x-1/2 bg-slate-800 text-white text-[10px] px-1.5 py-1 rounded hidden group-hover:block">12</div>
</div>
<div class="flex-1 bg-primary/20 rounded-t h-[60%] hover:bg-primary transition-all group relative">
<div class="absolute -top-8 left-1/2 -translate-x-1/2 bg-slate-800 text-white text-[10px] px-1.5 py-1 rounded hidden group-hover:block">18</div>
</div>
<div class="flex-1 bg-primary/20 rounded-t h-[30%] hover:bg-primary transition-all group relative">
<div class="absolute -top-8 left-1/2 -translate-x-1/2 bg-slate-800 text-white text-[10px] px-1.5 py-1 rounded hidden group-hover:block">9</div>
</div>
<div class="flex-1 bg-primary/20 rounded-t h-[80%] hover:bg-primary transition-all group relative">
<div class="absolute -top-8 left-1/2 -translate-x-1/2 bg-slate-800 text-white text-[10px] px-1.5 py-1 rounded hidden group-hover:block">24</div>
</div>
<div class="flex-1 bg-primary/20 rounded-t h-[50%] hover:bg-primary transition-all group relative">
<div class="absolute -top-8 left-1/2 -translate-x-1/2 bg-slate-800 text-white text-[10px] px-1.5 py-1 rounded hidden group-hover:block">15</div>
</div>
<div class="flex-1 bg-primary/20 rounded-t h-[95%] hover:bg-primary transition-all group relative border-t-2 border-primary">
<div class="absolute -top-8 left-1/2 -translate-x-1/2 bg-slate-800 text-white text-[10px] px-1.5 py-1 rounded hidden group-hover:block">29</div>
</div>
<div class="flex-1 bg-primary/20 rounded-t h-[45%] hover:bg-primary transition-all group relative">
<div class="absolute -top-8 left-1/2 -translate-x-1/2 bg-slate-800 text-white text-[10px] px-1.5 py-1 rounded hidden group-hover:block">13</div>
</div>
<div class="flex-1 bg-primary/20 rounded-t h-[70%] hover:bg-primary transition-all group relative">
<div class="absolute -top-8 left-1/2 -translate-x-1/2 bg-slate-800 text-white text-[10px] px-1.5 py-1 rounded hidden group-hover:block">21</div>
</div>
<div class="flex-1 bg-primary/20 rounded-t h-[35%] hover:bg-primary transition-all group relative">
<div class="absolute -top-8 left-1/2 -translate-x-1/2 bg-slate-800 text-white text-[10px] px-1.5 py-1 rounded hidden group-hover:block">10</div>
</div>
<div class="flex-1 bg-primary/20 rounded-t h-[65%] hover:bg-primary transition-all group relative">
<div class="absolute -top-8 left-1/2 -translate-x-1/2 bg-slate-800 text-white text-[10px] px-1.5 py-1 rounded hidden group-hover:block">19</div>
</div>
<div class="flex-1 bg-primary/20 rounded-t h-[85%] hover:bg-primary transition-all group relative">
<div class="absolute -top-8 left-1/2 -translate-x-1/2 bg-slate-800 text-white text-[10px] px-1.5 py-1 rounded hidden group-hover:block">26</div>
</div>
<div class="flex-1 bg-primary/20 rounded-t h-[55%] hover:bg-primary transition-all group relative">
<div class="absolute -top-8 left-1/2 -translate-x-1/2 bg-slate-800 text-white text-[10px] px-1.5 py-1 rounded hidden group-hover:block">16</div>
</div>
</div>
<div class="flex justify-between mt-4 text-xs text-slate-500 font-medium">
<span>Week 1</span>
<span>Week 2</span>
<span>Week 3</span>
<span>Week 4</span>
</div>
</div>
<!-- Connection Network -->
<div class="bg-white dark:bg-slate-900 p-6 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm flex flex-col">
<h2 class="text-lg font-bold mb-6">Connection Network</h2>
<div class="space-y-6 flex-1">
<div class="flex items-center gap-4">
<div class="w-12 h-12 rounded-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-primary">
<span class="material-symbols-outlined">person_add</span>
</div>
<div>
<p class="text-2xl font-bold leading-none">248</p>
<p class="text-sm text-slate-500">Close Friends</p>
</div>
</div>
<div class="flex items-center gap-4">
<div class="w-12 h-12 rounded-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-primary">
<span class="material-symbols-outlined">groups</span>
</div>
<div>
<p class="text-2xl font-bold leading-none">15</p>
<p class="text-sm text-slate-500">Groups Joined</p>
</div>
</div>
<div class="flex items-center gap-4">
<div class="w-12 h-12 rounded-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-primary">
<span class="material-symbols-outlined">connect_without_contact</span>
</div>
<div>
<p class="text-2xl font-bold leading-none">1,402</p>
<p class="text-sm text-slate-500">Mutual Connections</p>
</div>
</div>
</div>
<div class="mt-6 pt-6 border-t border-slate-100 dark:border-slate-800">
<p class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-3">Top Mutuals</p>
<div class="flex -space-x-3 overflow-hidden">
<img alt="Mutual 1" class="inline-block h-8 w-8 rounded-full ring-2 ring-white dark:ring-slate-900" data-alt="Profile of a male connection" src="https://lh3.googleusercontent.com/aida-public/AB6AXuCEg1IepsstDxbkZf0mcBJ5KBdipE_fLCQcTo1qVLWx4NPOpjbnABhfxasChdPu32g0y1IgtF-PljEU_EpXvlVGWddxY4aAeHr9MDMgD0_8QVWEjYAyQt_1Iyb25ghgsh18x-0idEwebRrkfVUmLaeAuBZmaIslxG5iWprFZ3pPG65g1kqr0dHn1aINqQo6L9a79QrGr022yiG37PD5Op5S5XEggch3B4z2cSc0bV8QoW-fmj-s20wT5HA3H0-zYHXj0fbNDxgl8N59"/>
<img alt="Mutual 2" class="inline-block h-8 w-8 rounded-full ring-2 ring-white dark:ring-slate-900" data-alt="Profile of a female connection" src="https://lh3.googleusercontent.com/aida-public/AB6AXuDXFAF7WpS7_0WUHtlxtHcBizLZyRRIhp92my8xPEN4s2MbQ0Vp74q0Ydsr-_bOY5rq7CTINRWILxTfNyUjqkbbkE9axo-nektaKw8DRKtunqj1K3TVxkAeYfG_4hEpY_Ip3rQxbtedZqDvbMgxiX5p7zTfeJfOiC8qVgrvwYvBZU7Znixx1BaawQ4Ld67T1ShzjwUwb2xGCKkT9BoP42vdXFdCFxXsPEr98_Z_dMuS7f-F2ATK7xDHTTOS7EfAjqIi_Yg__ZS-wF17"/>
<img alt="Mutual 3" class="inline-block h-8 w-8 rounded-full ring-2 ring-white dark:ring-slate-900" data-alt="Profile of a third connection" src="https://lh3.googleusercontent.com/aida-public/AB6AXuDRAv8WzEUsNO-YQlbIi2Ir_HfwpJ8lBtKIgU9FQ1JYbgHb_nuNbhavXtmzPI-dOVzMiugcts9Av7JzOazqObzU_RnOorUs4qYslyB56klfBxIXYgXkBx_H-TTRN-ZcqWxiYz1nz9YI-Xx_2Yo0nf7DjxN7gdQhLWlQSYPx-8D9ZFAij5W95NGug-Xlu3srYHYzRy2FXWzoJvQZKNwQH4TMxL17zR5um6xULxYL9wPaK1NyQiA_Hpc8P-c2E4NngceiqjWE5OUP_lUH"/>
<div class="inline-flex items-center justify-center h-8 w-8 rounded-full ring-2 ring-white dark:ring-slate-900 bg-slate-200 dark:bg-slate-700 text-[10px] font-bold">+1.4k</div>
</div>
</div>
</div>
</div>
<!-- Bottom Grid: Top Content & Interests -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
<!-- Top Content Section -->
<div class="bg-white dark:bg-slate-900 p-6 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm">
<h2 class="text-lg font-bold mb-4">Top Content</h2>
<div class="space-y-4">
<div class="p-4 rounded-xl bg-slate-50 dark:bg-slate-800/50 border border-slate-100 dark:border-slate-800">
<div class="flex justify-between items-start gap-4">
<div class="w-16 h-16 rounded-lg bg-cover bg-center shrink-0" data-alt="Modern cafe interior with plants" style="background-image: url('https://lh3.googleusercontent.com/aida-public/AB6AXuB32x_AwXly5kAoIZct1H74nMVkbhXB1S7FTMMUa2qwfCiOd1MTM5qap9OPuYP5j9Kq1JODsj74xobzNg2oCrO4hTtA607WUPc6YJ7oV9dyXeFDmpYj2sQ2-nQJ4o1dfFangSxtp_Wkt47fJGoKevHb5t3pcErPvPnsB_-JFcAgLmm5U6pXCcUAqmfUkX1WZtDcYuFjiwWicXrF9WjPJZlg0bI3P3u9EWFBOSQMWUGHmJ3oMxgn52wPsYyOD73BF6glUs4g7BKNvHuo')"></div>
<div class="flex-1">
<h3 class="font-bold text-sm line-clamp-1">Hidden Rooftop Café in Pearl District</h3>
<p class="text-xs text-slate-500 mt-1">"Found this amazing spot today..."</p>
<div class="flex items-center gap-3 mt-2 text-[10px] font-bold text-primary">
<span class="flex items-center gap-0.5"><span class="material-symbols-outlined text-[12px]">favorite</span> 458</span>
<span class="flex items-center gap-0.5"><span class="material-symbols-outlined text-[12px]">share</span> 89</span>
</div>
</div>
</div>
</div>
<div class="p-4 rounded-xl bg-slate-50 dark:bg-slate-800/50 border border-slate-100 dark:border-slate-800">
<div class="flex justify-between items-start gap-4">
<div class="w-16 h-16 rounded-lg bg-cover bg-center shrink-0" data-alt="Red public transit bus at night" style="background-image: url('https://lh3.googleusercontent.com/aida-public/AB6AXuAPEr0P3n1dviFC-bTSTgbJQY81jMYUb-1VSw3xnpTdpo_Ca2-yoe-oykoj4yweA3mZEeEM24wSK-slqDAr7_sF935isNa9JFAPG3aHUeXkn_ICvh_JsQL1MusQRm-KbFNhDtHfb8_xCzc9JZEhLXVJYHo_E5ZITIY4p8Z2qk4UBVq5tHHG2qSD_mHKFcZKldGmrvEDKaNfcvvnAnYqjlk2reAXj91lMsTvE4in08ykIIeWIfRWfjF4gcHgzIs1Zjes9DPd8imE4TfO')"></div>
<div class="flex-1">
<h3 class="font-bold text-sm line-clamp-1">Late Night Transit Appreciation</h3>
<p class="text-xs text-slate-500 mt-1">"Sometimes the city lights just hit..."</p>
<div class="flex items-center gap-3 mt-2 text-[10px] font-bold text-primary">
<span class="flex items-center gap-0.5"><span class="material-symbols-outlined text-[12px]">favorite</span> 321</span>
<span class="flex items-center gap-0.5"><span class="material-symbols-outlined text-[12px]">share</span> 42</span>
</div>
</div>
</div>
</div>
</div>
</div>
<!-- Interests & Tags Cloud -->
<div class="bg-white dark:bg-slate-900 p-6 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm">
<h2 class="text-lg font-bold mb-4">Interests &amp; Activity Tags</h2>
<div class="flex flex-wrap gap-2 pt-2">
<span class="px-4 py-2 bg-primary text-white rounded-full text-sm font-bold">Urban Exploration</span>
<span class="px-3 py-1.5 bg-primary/20 text-primary rounded-full text-sm font-medium">Coffee Enthusiast</span>
<span class="px-3 py-1.5 bg-primary/20 text-primary rounded-full text-sm font-medium">Photography</span>
<span class="px-5 py-2.5 bg-primary/10 text-primary rounded-full text-lg font-bold">Portland</span>
<span class="px-3 py-1.5 bg-slate-100 dark:bg-slate-800 rounded-full text-sm font-medium">Street Art</span>
<span class="px-4 py-2 bg-primary/30 text-primary rounded-full text-base font-bold">Sustainability</span>
<span class="px-3 py-1.5 bg-slate-100 dark:bg-slate-800 rounded-full text-sm font-medium">Local Events</span>
<span class="px-4 py-2 bg-primary/10 text-primary rounded-full text-sm font-medium">Architecture</span>
<span class="px-3 py-1.5 bg-slate-100 dark:bg-slate-800 rounded-full text-sm font-medium">Hiking</span>
<span class="px-3 py-1.5 bg-primary/20 text-primary rounded-full text-sm font-medium">Transit</span>
<span class="px-2 py-1 bg-slate-100 dark:bg-slate-800 rounded-full text-xs font-medium">Foodie</span>
</div>
<div class="mt-6 p-4 rounded-xl bg-slate-50 dark:bg-slate-800 flex items-center gap-4">
<div class="shrink-0">
<span class="material-symbols-outlined text-primary text-3xl">lightbulb</span>
</div>
<p class="text-sm">Based on your activity, we suggest checking out the <span class="font-bold text-primary">#DowntownRevival</span> group.</p>
</div>
</div>
</div>
</div>
</main>
</div>
</div>
</div>
</body></html>
