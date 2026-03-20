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
<style>
        body {
            font-family: "Public Sans", sans-serif;
        }
        .chart-bar {
            transition: height 0.3s ease;
        }
    </style>
</head>
<body class="bg-background-light dark:bg-background-dark text-slate-900 dark:text-slate-100 font-display">
<div class="flex h-screen overflow-hidden">
<!-- Sidebar -->
<aside class="w-64 flex-shrink-0 border-r border-slate-200 dark:border-primary/20 bg-white dark:bg-background-dark/50 flex flex-col">
<div class="p-6 flex items-center gap-3">
<div class="w-10 h-10 bg-primary rounded-xl flex items-center justify-center text-white">
<span class="material-symbols-outlined">analytics</span>
</div>
<div>
<h1 class="text-lg font-bold leading-tight">Socially</h1>
<p class="text-xs text-slate-500 dark:text-slate-400">Admin Dashboard</p>
</div>
</div>
<nav class="flex-1 px-4 space-y-1">
<a class="flex items-center gap-3 px-3 py-2.5 text-slate-600 dark:text-slate-300 hover:bg-primary/10 hover:text-primary rounded-xl transition-colors" href="#">
<span class="material-symbols-outlined">dashboard</span>
<span class="text-sm font-medium">Overview</span>
</a>
<a class="flex items-center gap-3 px-3 py-2.5 text-slate-600 dark:text-slate-300 hover:bg-primary/10 hover:text-primary rounded-xl transition-colors" href="#">
<span class="material-symbols-outlined">trending_up</span>
<span class="text-sm font-medium">User Growth</span>
</a>
<a class="flex items-center gap-3 px-3 py-2.5 bg-primary text-white rounded-xl transition-colors shadow-lg shadow-primary/20" href="#">
<span class="material-symbols-outlined">group</span>
<span class="text-sm font-medium">Audience</span>
</a>
<a class="flex items-center gap-3 px-3 py-2.5 text-slate-600 dark:text-slate-300 hover:bg-primary/10 hover:text-primary rounded-xl transition-colors" href="#">
<span class="material-symbols-outlined">share</span>
<span class="text-sm font-medium">Referrals</span>
</a>
</nav>
<div class="p-4 border-t border-slate-200 dark:border-primary/20">
<div class="flex items-center gap-3 p-2 bg-slate-100 dark:bg-primary/10 rounded-xl">
<div class="w-8 h-8 rounded-full bg-slate-300 overflow-hidden" data-alt="User profile avatar circle" style="background-image: url('https://lh3.googleusercontent.com/aida-public/AB6AXuAEfUvbnnJtTzbAHYOXi0NZzgiTKinodlib0PTKA6LDCROsyhHg_s_1Pz9elvES8oziiAiZS3esMMEiynpmM-7csHkJI_OunuZ7Z_WQuu_-bYX1aBcfoLSpMaA4b8gJLGo-4h0DskCuEpY8BCWD_xPi58W5BrqEEPwLC7g7VCg79n_atN_VulitJVTIMX_gIk3ipNATxdmlroxlpQDgWY5R40q_FgbNhTw1oTfHpLg50b39tn2Y-wD2hsHMRuHGdcyEWrFwIhGgCiik')"></div>
<div class="flex-1 min-w-0">
<p class="text-xs font-semibold truncate">Alex Rivera</p>
<p class="text-[10px] text-slate-500">Analyst</p>
</div>
<span class="material-symbols-outlined text-slate-400 text-sm">settings</span>
</div>
</div>
</aside>
<!-- Main Content -->
<main class="flex-1 flex flex-col overflow-y-auto">
<!-- Header -->
<header class="h-16 border-b border-slate-200 dark:border-primary/20 flex items-center justify-between px-8 bg-white/80 dark:bg-background-dark/80 backdrop-blur-md sticky top-0 z-10">
<h2 class="text-xl font-bold">Audience Overview</h2>
<div class="flex items-center gap-4">
<div class="relative">
<span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xl">search</span>
<input class="pl-10 pr-4 py-2 bg-slate-100 dark:bg-primary/10 border-none rounded-xl text-sm focus:ring-2 focus:ring-primary w-64" placeholder="Search analytics..." type="text"/>
</div>
<button class="w-10 h-10 flex items-center justify-center text-slate-500 hover:bg-slate-100 dark:hover:bg-primary/20 rounded-xl">
<span class="material-symbols-outlined">notifications</span>
</button>
</div>
</header>
<!-- Dashboard Body -->
<div class="p-8 space-y-8">
<!-- Metric Cards -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
<div class="bg-white dark:bg-background-dark border border-slate-200 dark:border-primary/20 p-6 rounded-xl shadow-sm">
<p class="text-sm text-slate-500 dark:text-slate-400 font-medium">Active Users</p>
<div class="mt-2 flex items-end justify-between">
<h3 class="text-3xl font-bold">1.2M</h3>
<span class="text-emerald-500 text-sm font-bold flex items-center"><span class="material-symbols-outlined text-xs">arrow_upward</span>12%</span>
</div>
</div>
<div class="bg-white dark:bg-background-dark border border-slate-200 dark:border-primary/20 p-6 rounded-xl shadow-sm">
<p class="text-sm text-slate-500 dark:text-slate-400 font-medium">Average Age</p>
<div class="mt-2 flex items-end justify-between">
<h3 class="text-3xl font-bold">24</h3>
<span class="text-slate-400 text-sm font-bold">0%</span>
</div>
</div>
<div class="bg-white dark:bg-background-dark border border-slate-200 dark:border-primary/20 p-6 rounded-xl shadow-sm">
<p class="text-sm text-slate-500 dark:text-slate-400 font-medium">Top Interest</p>
<div class="mt-2 flex items-end justify-between">
<h3 class="text-2xl font-bold">Technology</h3>
<span class="text-emerald-500 text-sm font-bold flex items-center"><span class="material-symbols-outlined text-xs">arrow_upward</span>5%</span>
</div>
</div>
<div class="bg-white dark:bg-background-dark border border-slate-200 dark:border-primary/20 p-6 rounded-xl shadow-sm">
<p class="text-sm text-slate-500 dark:text-slate-400 font-medium">M/F Ratio</p>
<div class="mt-2 flex items-end justify-between">
<h3 class="text-3xl font-bold">52:48</h3>
<span class="text-rose-500 text-sm font-bold flex items-center"><span class="material-symbols-outlined text-xs">arrow_downward</span>2%</span>
</div>
</div>
</div>
<!-- Demographics & Charts -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
<!-- Age Distribution -->
<div class="lg:col-span-2 bg-white dark:bg-background-dark border border-slate-200 dark:border-primary/20 p-6 rounded-xl shadow-sm">
<h3 class="text-lg font-bold mb-6">Demographics: Age Distribution</h3>
<div class="h-64 flex items-end justify-between gap-4 px-2">
<div class="flex-1 flex flex-col items-center gap-2">
<div class="w-full bg-primary/20 rounded-t-lg relative group">
<div class="w-full bg-primary rounded-t-lg h-[40%]" style="height: 35%"></div>
<div class="hidden group-hover:block absolute -top-8 left-1/2 -translate-x-1/2 bg-slate-900 text-white text-[10px] px-2 py-1 rounded">12%</div>
</div>
<span class="text-xs text-slate-500">13-17</span>
</div>
<div class="flex-1 flex flex-col items-center gap-2">
<div class="w-full bg-primary/20 rounded-t-lg relative group">
<div class="w-full bg-primary rounded-t-lg h-[85%]" style="height: 85%"></div>
<div class="hidden group-hover:block absolute -top-8 left-1/2 -translate-x-1/2 bg-slate-900 text-white text-[10px] px-2 py-1 rounded">42%</div>
</div>
<span class="text-xs text-slate-500">18-24</span>
</div>
<div class="flex-1 flex flex-col items-center gap-2">
<div class="w-full bg-primary/20 rounded-t-lg relative group">
<div class="w-full bg-primary rounded-t-lg h-[65%]" style="height: 65%"></div>
<div class="hidden group-hover:block absolute -top-8 left-1/2 -translate-x-1/2 bg-slate-900 text-white text-[10px] px-2 py-1 rounded">28%</div>
</div>
<span class="text-xs text-slate-500">25-34</span>
</div>
<div class="flex-1 flex flex-col items-center gap-2">
<div class="w-full bg-primary/20 rounded-t-lg relative group">
<div class="w-full bg-primary rounded-t-lg h-[30%]" style="height: 30%"></div>
<div class="hidden group-hover:block absolute -top-8 left-1/2 -translate-x-1/2 bg-slate-900 text-white text-[10px] px-2 py-1 rounded">10%</div>
</div>
<span class="text-xs text-slate-500">35-44</span>
</div>
<div class="flex-1 flex flex-col items-center gap-2">
<div class="w-full bg-primary/20 rounded-t-lg relative group">
<div class="w-full bg-primary rounded-t-lg h-[15%]" style="height: 15%"></div>
<div class="hidden group-hover:block absolute -top-8 left-1/2 -translate-x-1/2 bg-slate-900 text-white text-[10px] px-2 py-1 rounded">5%</div>
</div>
<span class="text-xs text-slate-500">45-54</span>
</div>
<div class="flex-1 flex flex-col items-center gap-2">
<div class="w-full bg-primary/20 rounded-t-lg relative group">
<div class="w-full bg-primary rounded-t-lg h-[10%]" style="height: 10%"></div>
<div class="hidden group-hover:block absolute -top-8 left-1/2 -translate-x-1/2 bg-slate-900 text-white text-[10px] px-2 py-1 rounded">3%</div>
</div>
<span class="text-xs text-slate-500">55+</span>
</div>
</div>
</div>
<!-- Gender Distribution -->
<div class="bg-white dark:bg-background-dark border border-slate-200 dark:border-primary/20 p-6 rounded-xl shadow-sm flex flex-col items-center justify-center">
<h3 class="text-lg font-bold mb-6 w-full text-left">Gender Distribution</h3>
<div class="relative w-48 h-48">
<svg class="w-full h-full transform -rotate-90" viewbox="0 0 100 100">
<!-- Male Circle -->
<circle class="text-primary/20" cx="50" cy="50" fill="transparent" r="40" stroke="currentColor" stroke-width="12"></circle>
<circle class="text-primary" cx="50" cy="50" fill="transparent" r="40" stroke="currentColor" stroke-dasharray="251.2" stroke-dashoffset="120.6" stroke-width="12"></circle>
<!-- Female Circle -->
<circle class="text-orange-300" cx="50" cy="50" fill="transparent" r="40" stroke="currentColor" stroke-dasharray="251.2" stroke-dashoffset="251.2" stroke-width="12" style="stroke-dashoffset: calc(251.2 - (251.2 * 0.48))"></circle>
</svg>
<div class="absolute inset-0 flex flex-col items-center justify-center">
<span class="text-2xl font-bold">1.2M</span>
<span class="text-[10px] uppercase text-slate-500 tracking-wider">Total Reach</span>
</div>
</div>
<div class="mt-6 flex gap-6">
<div class="flex items-center gap-2">
<span class="w-3 h-3 rounded-full bg-primary"></span>
<span class="text-sm font-medium">Male (52%)</span>
</div>
<div class="flex items-center gap-2">
<span class="w-3 h-3 rounded-full bg-orange-300"></span>
<span class="text-sm font-medium">Female (48%)</span>
</div>
</div>
</div>
</div>
<!-- Footer Charts Section -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
<!-- Top Interests -->
<div class="bg-white dark:bg-background-dark border border-slate-200 dark:border-primary/20 p-6 rounded-xl shadow-sm">
<h3 class="text-lg font-bold mb-6">Top Audience Interests</h3>
<div class="flex flex-wrap gap-3">
<span class="px-4 py-2 bg-primary text-white rounded-full text-sm font-semibold">Technology</span>
<span class="px-4 py-2 bg-primary/10 text-primary border border-primary/20 rounded-full text-sm font-medium">Gaming</span>
<span class="px-4 py-2 bg-primary/10 text-primary border border-primary/20 rounded-full text-sm font-medium">Fashion</span>
<span class="px-4 py-2 bg-primary/10 text-primary border border-primary/20 rounded-full text-sm font-medium">Travel</span>
<span class="px-4 py-2 bg-primary/10 text-primary border border-primary/20 rounded-full text-sm font-medium">Photography</span>
<span class="px-4 py-2 bg-primary/10 text-primary border border-primary/20 rounded-full text-sm font-medium">Finance</span>
<span class="px-4 py-2 bg-primary/10 text-primary border border-primary/20 rounded-full text-sm font-medium">Fitness</span>
<span class="px-4 py-2 bg-primary/10 text-primary border border-primary/20 rounded-full text-sm font-medium">Music</span>
<span class="px-4 py-2 bg-primary/10 text-primary border border-primary/20 rounded-full text-sm font-medium">Art</span>
</div>
<div class="mt-8 space-y-4">
<div class="space-y-1">
<div class="flex justify-between text-xs font-medium">
<span>Tech &amp; Gadgets</span>
<span>82%</span>
</div>
<div class="w-full h-2 bg-slate-100 dark:bg-primary/5 rounded-full overflow-hidden">
<div class="bg-primary h-full rounded-full" style="width: 82%"></div>
</div>
</div>
<div class="space-y-1">
<div class="flex justify-between text-xs font-medium">
<span>Gaming</span>
<span>64%</span>
</div>
<div class="w-full h-2 bg-slate-100 dark:bg-primary/5 rounded-full overflow-hidden">
<div class="bg-primary h-full rounded-full" style="width: 64%"></div>
</div>
</div>
<div class="space-y-1">
<div class="flex justify-between text-xs font-medium">
<span>Lifestyle</span>
<span>45%</span>
</div>
<div class="w-full h-2 bg-slate-100 dark:bg-primary/5 rounded-full overflow-hidden">
<div class="bg-primary h-full rounded-full" style="width: 45%"></div>
</div>
</div>
</div>
</div>
<!-- Device Breakdown -->
<div class="bg-white dark:bg-background-dark border border-slate-200 dark:border-primary/20 p-6 rounded-xl shadow-sm">
<h3 class="text-lg font-bold mb-6">Device Breakdown</h3>
<div class="space-y-6">
<div class="flex items-center gap-4">
<div class="w-12 h-12 flex items-center justify-center bg-primary/10 rounded-xl text-primary">
<span class="material-symbols-outlined">smartphone</span>
</div>
<div class="flex-1">
<div class="flex justify-between items-center mb-1">
<span class="text-sm font-bold">Mobile</span>
<span class="text-sm text-slate-500">72%</span>
</div>
<div class="w-full h-3 bg-slate-100 dark:bg-primary/5 rounded-full overflow-hidden">
<div class="bg-primary h-full rounded-full" style="width: 72%"></div>
</div>
</div>
</div>
<div class="flex items-center gap-4">
<div class="w-12 h-12 flex items-center justify-center bg-primary/10 rounded-xl text-primary">
<span class="material-symbols-outlined">laptop</span>
</div>
<div class="flex-1">
<div class="flex justify-between items-center mb-1">
<span class="text-sm font-bold">Desktop</span>
<span class="text-sm text-slate-500">22%</span>
</div>
<div class="w-full h-3 bg-slate-100 dark:bg-primary/5 rounded-full overflow-hidden">
<div class="bg-primary h-full rounded-full" style="width: 22%"></div>
</div>
</div>
</div>
<div class="flex items-center gap-4">
<div class="w-12 h-12 flex items-center justify-center bg-primary/10 rounded-xl text-primary">
<span class="material-symbols-outlined">tablet</span>
</div>
<div class="flex-1">
<div class="flex justify-between items-center mb-1">
<span class="text-sm font-bold">Tablet</span>
<span class="text-sm text-slate-500">6%</span>
</div>
<div class="w-full h-3 bg-slate-100 dark:bg-primary/5 rounded-full overflow-hidden">
<div class="bg-primary h-full rounded-full" style="width: 6%"></div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</main>
</div>
</body></html>
