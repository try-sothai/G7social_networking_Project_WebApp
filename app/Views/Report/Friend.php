<!DOCTYPE html>

<html class="light" lang="en"><head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title>LocalConnect - Network Insights</title>
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
                        "primary": "#7C5CFF",
                        "background-light": "#f8f6f6",
                        "background-dark": "#221610",
                    },
                    fontFamily: {
                        "display": ["Public Sans"]
                    },
                    borderRadius: { "DEFAULT": "0.25rem", "lg": "0.5rem", "xl": "0.75rem", "full": "9999px" },
                },
            },
        }
    </script>
<style>
        body { font-family: 'Public Sans', sans-serif; }
        .material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; }
        .filled-icon { font-variation-settings: 'FILL' 1; }
    </style>
</head>
<body class="bg-background-light dark:bg-background-dark text-slate-900 dark:text-slate-100 font-display">
<div class="flex min-h-screen">
<!-- Sidebar -->
<aside class="w-64 bg-slate-900 dark:bg-black text-white flex flex-col fixed h-full z-20">
<div class="p-6">
<div class="flex items-center gap-3 mb-8">
<div class="bg-primary rounded-full size-10 flex items-center justify-center">
<span class="material-symbols-outlined text-white">hub</span>
</div>
<div class="flex flex-col">
<h1 class="text-white text-base font-bold leading-none">LocalConnect</h1>
<p class="text-slate-400 text-xs mt-1">Social Network</p>
</div>
</div>
<nav class="flex flex-col gap-2">
<a class="flex items-center gap-3 px-3 py-2.5 rounded-xl hover:bg-slate-800 transition-colors" href="#">
<span class="material-symbols-outlined text-slate-400">dashboard</span>
<span class="text-sm font-medium">Dashboard</span>
</a>
<a class="flex items-center gap-3 px-3 py-2.5 rounded-xl bg-primary text-white shadow-lg shadow-primary/20" href="#">
<span class="material-symbols-outlined filled-icon">group</span>
<span class="text-sm font-medium">Friends</span>
</a>
<a class="flex items-center gap-3 px-3 py-2.5 rounded-xl hover:bg-slate-800 transition-colors" href="#">
<span class="material-symbols-outlined text-slate-400">chat_bubble</span>
<span class="text-sm font-medium">Messages</span>
</a>
<a class="flex items-center gap-3 px-3 py-2.5 rounded-xl hover:bg-slate-800 transition-colors" href="#">
<span class="material-symbols-outlined text-slate-400">groups</span>
<span class="text-sm font-medium">Groups</span>
</a>
<a class="flex items-center gap-3 px-3 py-2.5 rounded-xl hover:bg-slate-800 transition-colors mt-4" href="#">
<span class="material-symbols-outlined text-slate-400">settings</span>
<span class="text-sm font-medium">Settings</span>
</a>
</nav>
</div>
<div class="mt-auto p-6">
<button class="w-full bg-primary/20 text-primary border border-primary/30 hover:bg-primary/30 py-2.5 rounded-xl text-sm font-bold transition-all">
                    Invite Friends
                </button>
</div>
</aside>
<!-- Main Content -->
<main class="flex-1 ml-64 p-8">
<!-- Header -->
<header class="flex items-center justify-between mb-8">
<div class="flex items-center gap-4">
<h2 class="text-2xl font-bold tracking-tight">Network Insights</h2>
</div>
<div class="flex items-center gap-6">
<div class="relative w-64">
<span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xl">search</span>
<input class="w-full pl-10 pr-4 py-2 bg-white dark:bg-slate-800 border-none rounded-xl text-sm focus:ring-2 focus:ring-primary/50 transition-all" placeholder="Search network..." type="text"/>
</div>
<div class="flex items-center gap-3 pl-6 border-l border-slate-200 dark:border-slate-700">
<div class="text-right">
<p class="text-sm font-bold leading-none">DY DY</p>
<p class="text-xs text-slate-500 mt-1">Premium Member</p>
</div>
<div class="size-10 rounded-full bg-slate-200 overflow-hidden border-2 border-primary/20 shadow-sm" data-alt="User profile avatar of DY DY" style="background-image: url('https://lh3.googleusercontent.com/aida-public/AB6AXuAmQldGi-JHSkUdqQX9Hlyi1XHZQ0NfRg9l7nVLg1JFsWxzOScamlU9O7ocf-fDGl0pF8BxM1kGDkIfsVjiPyG3hJj75YvtSX5j-2fNyxuhZSL6nlt7pgaPosIb_QAt3Yj2zTNjl7eUQiIbZinv3Z4R31ppWU595zRSIqMw-NJM1v8yq7kvSEd4mAIPXuL0aDNr2tNtqiSDp2fNta3waJ-TeScEOXDxn1fIG8JjPUhqjAd_FbkOkaDREh_kF_vMzS53XtCisRGCONhM'); background-size: cover;"></div>
</div>
</div>
</header>
<!-- Content Grid -->
<div class="max-w-7xl mx-auto space-y-6">
<!-- Overview Section -->
<div class="mb-4">
<h3 class="text-3xl font-black text-slate-900 dark:text-white">Network Overview</h3>
<p class="text-slate-500 dark:text-slate-400 mt-1">Manage and track your social circle growth across the platform.</p>
</div>
<!-- Stats Cards -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
<div class="bg-white dark:bg-slate-800 p-6 rounded-xl border border-slate-100 dark:border-slate-700 shadow-sm">
<p class="text-slate-500 dark:text-slate-400 text-sm font-medium">Total Friends</p>
<h4 class="text-2xl font-bold mt-1">1,284</h4>
<div class="flex items-center gap-1 mt-2 text-emerald-500 text-xs font-bold">
<span class="material-symbols-outlined text-xs">trending_up</span>
<span>+5.2%</span>
</div>
</div>
<div class="bg-white dark:bg-slate-800 p-6 rounded-xl border border-slate-100 dark:border-slate-700 shadow-sm">
<p class="text-slate-500 dark:text-slate-400 text-sm font-medium">New Connections</p>
<h4 class="text-2xl font-bold mt-1">42</h4>
<div class="flex items-center gap-1 mt-2 text-emerald-500 text-xs font-bold">
<span class="material-symbols-outlined text-xs">trending_up</span>
<span>+10.1%</span>
</div>
</div>
<div class="bg-white dark:bg-slate-800 p-6 rounded-xl border border-slate-100 dark:border-slate-700 shadow-sm">
<p class="text-slate-500 dark:text-slate-400 text-sm font-medium">Pending Requests</p>
<h4 class="text-2xl font-bold mt-1">15</h4>
<div class="flex items-center gap-1 mt-2 text-amber-500 text-xs font-bold">
<span class="material-symbols-outlined text-xs">trending_down</span>
<span>-2.4%</span>
</div>
</div>
<div class="bg-white dark:bg-slate-800 p-6 rounded-xl border border-slate-100 dark:border-slate-700 shadow-sm">
<p class="text-slate-500 dark:text-slate-400 text-sm font-medium">Growth Rate</p>
<h4 class="text-2xl font-bold mt-1">12.5%</h4>
<div class="flex items-center gap-1 mt-2 text-emerald-500 text-xs font-bold">
<span class="material-symbols-outlined text-xs">trending_up</span>
<span>+1.2%</span>
</div>
</div>
</div>
<!-- Charts Section -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
<!-- Line Chart Card -->
<div class="lg:col-span-2 bg-white dark:bg-slate-800 p-6 rounded-xl border border-slate-100 dark:border-slate-700 shadow-sm">
<div class="flex justify-between items-center mb-6">
<div>
<h5 class="text-lg font-bold">Connection Trends</h5>
<p class="text-slate-400 text-xs">Monthly friendship growth for 2024</p>
</div>
<select class="bg-slate-50 dark:bg-slate-900 border-none rounded-lg text-xs font-semibold py-1.5 pl-3 pr-8 focus:ring-1 focus:ring-primary">
<option>Last 6 Months</option>
<option>Last Year</option>
</select>
</div>
<div class="h-64 flex flex-col justify-end">
<svg class="w-full h-full" viewbox="0 0 400 150">
<defs>
<lineargradient id="grad" x1="0%" x2="0%" y1="0%" y2="100%">
<stop offset="0%" style="stop-color:rgba(124, 92, 255, 0.2); stop-opacity:1"></stop>
<stop offset="100%" style="stop-color:rgba(124, 92, 255, 0); stop-opacity:0"></stop>
</lineargradient>
</defs>
<path d="M0,120 Q50,80 100,100 T200,60 T300,40 T400,20 V150 H0 Z" fill="url(#grad)"></path>
<path d="M0,120 Q50,80 100,100 T200,60 T300,40 T400,20" fill="none" stroke="#7C5CFF" stroke-width="3"></path>
</svg>
<div class="flex justify-between mt-4 text-[10px] font-bold text-slate-400 uppercase tracking-wider">
<span>Jan</span>
<span>Feb</span>
<span>Mar</span>
<span>Apr</span>
<span>May</span>
<span>Jun</span>
</div>
</div>
</div>
<!-- Donut Chart Card -->
<div class="bg-white dark:bg-slate-800 p-6 rounded-xl border border-slate-100 dark:border-slate-700 shadow-sm">
<h5 class="text-lg font-bold mb-6">Friend Requests</h5>
<div class="relative h-48 flex items-center justify-center">
<svg class="size-40" viewbox="0 0 36 36">
<circle cx="18" cy="18" fill="none" r="16" stroke="#f1f5f9" stroke-dasharray="100 100" stroke-width="3"></circle>
<circle cx="18" cy="18" fill="none" r="16" stroke="#7C5CFF" stroke-dasharray="65 100" stroke-dashoffset="0" stroke-width="3"></circle>
<circle cx="18" cy="18" fill="none" r="16" stroke="#f59e0b" stroke-dasharray="15 100" stroke-dashoffset="-65" stroke-width="3"></circle>
<circle cx="18" cy="18" fill="none" r="16" stroke="#ef4444" stroke-dasharray="20 100" stroke-dashoffset="-80" stroke-width="3"></circle>
</svg>
<div class="absolute text-center">
<span class="block text-2xl font-bold">142</span>
<span class="text-[10px] text-slate-400 font-medium uppercase">Total</span>
</div>
</div>
<div class="mt-6 space-y-2">
<div class="flex items-center justify-between text-xs">
<div class="flex items-center gap-2">
<span class="size-2 rounded-full bg-primary"></span>
<span class="text-slate-500 font-medium">Accepted</span>
</div>
<span class="font-bold">65%</span>
</div>
<div class="flex items-center justify-between text-xs">
<div class="flex items-center gap-2">
<span class="size-2 rounded-full bg-amber-500"></span>
<span class="text-slate-500 font-medium">Pending</span>
</div>
<span class="font-bold">15%</span>
</div>
<div class="flex items-center justify-between text-xs">
<div class="flex items-center gap-2">
<span class="size-2 rounded-full bg-red-500"></span>
<span class="text-slate-500 font-medium">Declined</span>
</div>
<span class="font-bold">20%</span>
</div>
</div>
</div>
</div>
<!-- Mutual Friends Table -->
<div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-100 dark:border-slate-700 shadow-sm overflow-hidden">
<div class="p-6 border-b border-slate-50 dark:border-slate-700">
<h5 class="text-lg font-bold">Top Mutual Friends</h5>
<p class="text-slate-400 text-xs">Connections with the highest number of shared friends</p>
</div>
<div class="overflow-x-auto">
<table class="w-full text-left">
<thead>
<tr class="bg-slate-50 dark:bg-slate-900/50">
<th class="px-6 py-4 text-xs font-bold uppercase text-slate-400">User</th>
<th class="px-6 py-4 text-xs font-bold uppercase text-slate-400">Mutual Friends</th>
<th class="px-6 py-4 text-xs font-bold uppercase text-slate-400">Location</th>
<th class="px-6 py-4 text-xs font-bold uppercase text-slate-400">Status</th>
<th class="px-6 py-4 text-xs font-bold uppercase text-slate-400 text-right">Action</th>
</tr>
</thead>
<tbody class="divide-y divide-slate-50 dark:divide-slate-700">
<tr class="hover:bg-slate-50 dark:hover:bg-slate-700/30 transition-colors">
<td class="px-6 py-4">
<div class="flex items-center gap-3">
<div class="size-9 rounded-full bg-primary/10 flex items-center justify-center text-primary font-bold text-sm">AS</div>
<div>
<p class="text-sm font-bold">Alex Smith</p>
<p class="text-xs text-slate-400">Digital Creator</p>
</div>
</div>
</td>
<td class="px-6 py-4">
<div class="flex -space-x-2">
<div class="size-6 rounded-full border-2 border-white dark:border-slate-800 bg-slate-200" data-alt="Mutual friend avatar 1" style="background-image: url('https://lh3.googleusercontent.com/aida-public/AB6AXuB0R-xNtZP4FawAI-xolHff4vRlrr0SWYuAE5hL3gdw4_gpLZCPwv6rQseA2baPzWFX_JcpI_SjIgmr-bzzbjr_B60EUcAg3l8ChyltmIurNulDmFbzaMLDKzGuYnMVly3kZ6im271Z_V7f8hsbgqxc7uEdi-jiHOgqruiX8W_NzRzBKezfdzejWMETXx2T6Y_sZWdjEPk3JKzyUiqWeU3JSgpy7PwDR741tmAcsHVFTpWmnJ08fRUojRjSn6kzglk9gz8rCwhkDIUW'); background-size: cover;"></div>
<div class="size-6 rounded-full border-2 border-white dark:border-slate-800 bg-slate-300" data-alt="Mutual friend avatar 2" style="background-image: url('https://lh3.googleusercontent.com/aida-public/AB6AXuCt3HgGA5S64NixthYGVtcuToNA97uvobheaX5YCNEy0G57Y6FlLDtieFZQR8Kqibm64u8kx4nr4ckY2CNoQqomQLrL7WPs78Fp3tYPrdaB7FSXPBy3VVgLE6nwH_tFP2W81hkumoHlF6yW1dPeCAuWDeJZ5iPpmmqVXIQU2UkcfNPDxP5emtdRpNBnt5iPwzp49-w9j2cre4PKZKT4ZkXMnvQASuCyTOOjXHrv0qTuH0X1UxrPQhGxqUJEneNBjcDSRP1B4amgoJSX'); background-size: cover;"></div>
<div class="size-6 rounded-full border-2 border-white dark:border-slate-800 bg-primary text-[8px] flex items-center justify-center text-white font-bold">+122</div>
</div>
</td>
<td class="px-6 py-4 text-sm text-slate-600 dark:text-slate-400">London, UK</td>
<td class="px-6 py-4">
<span class="px-2.5 py-1 rounded-full bg-emerald-100 text-emerald-600 text-[10px] font-bold uppercase">Connected</span>
</td>
<td class="px-6 py-4 text-right">
<button class="text-slate-400 hover:text-primary"><span class="material-symbols-outlined">more_vert</span></button>
</td>
</tr>
<tr class="hover:bg-slate-50 dark:hover:bg-slate-700/30 transition-colors">
<td class="px-6 py-4">
<div class="flex items-center gap-3">
<div class="size-9 rounded-full bg-slate-200 overflow-hidden" data-alt="Avatar of Sarah Jenkins" style="background-image: url('https://lh3.googleusercontent.com/aida-public/AB6AXuD9aTUN9OiXP7np4RDKDA4z0hsyTDhcSqHQRXzck6E69TqgoKZXlmMWHNLzVEMmz-rMOqflgwRN5FULD0gtEuPzdq9Hv5_YsmaDdxCVEiY7uZ4jl20KbDlQgipfGIgWIUwIUMt3N54U_diIOKA3PwZAeq24TdPl6LurdcYbCxbunfRKx8syvE8bgUwfCeU5AJOGIlY5U3NlSU8dgp8Od8kzs2mE_YicfPfCq_eVZIbQ8UlwkgjwyG38OZV90bjHD9rBZGc4Mbo7mjpI'); background-size: cover;"></div>
<div>
<p class="text-sm font-bold">Sarah Jenkins</p>
<p class="text-xs text-slate-400">Software Engineer</p>
</div>
</div>
</td>
<td class="px-6 py-4">
<div class="flex -space-x-2">
<div class="size-6 rounded-full border-2 border-white dark:border-slate-800 bg-slate-200" data-alt="Mutual friend avatar 3" style="background-image: url('https://lh3.googleusercontent.com/aida-public/AB6AXuD4E18kJ6-hU8fqtizOocfeQRO1rmiZV6J3McQk_hudq4AaBJ0xYvZ6U7OeMs149MliYGghFadj_HqboRBSKorxfpRAefSyoMOAP663l9RCuTEGK3PzWgeoK7N6KjD2hLS8JdtKFLh08i8A6lgHaRDampXNazvByiYvTD20vHCG1GF4LHPt16zm_fAnBCHwzQmjIxb-AmklW4XYOmCe6p3uNLbseeQ8xKcWvfF7uaCVSIXWTuZhCc1EY9tkTmQNQuU4uhXda9HiDZQT'); background-size: cover;"></div>
<div class="size-6 rounded-full border-2 border-white dark:border-slate-800 bg-primary text-[8px] flex items-center justify-center text-white font-bold">+89</div>
</div>
</td>
<td class="px-6 py-4 text-sm text-slate-600 dark:text-slate-400">New York, USA</td>
<td class="px-6 py-4">
<span class="px-2.5 py-1 rounded-full bg-emerald-100 text-emerald-600 text-[10px] font-bold uppercase">Connected</span>
</td>
<td class="px-6 py-4 text-right">
<button class="text-slate-400 hover:text-primary"><span class="material-symbols-outlined">more_vert</span></button>
</td>
</tr>
<tr class="hover:bg-slate-50 dark:hover:bg-slate-700/30 transition-colors">
<td class="px-6 py-4">
<div class="flex items-center gap-3">
<div class="size-9 rounded-full bg-amber-100 flex items-center justify-center text-amber-600 font-bold text-sm">MK</div>
<div>
<p class="text-sm font-bold">Michael Klein</p>
<p class="text-xs text-slate-400">Investor</p>
</div>
</div>
</td>
<td class="px-6 py-4">
<div class="flex -space-x-2">
<div class="size-6 rounded-full border-2 border-white dark:border-slate-800 bg-slate-300" data-alt="Mutual friend avatar 4" style="background-image: url('https://lh3.googleusercontent.com/aida-public/AB6AXuBSDs06nP_hJuyzIqjta7AUj-9nTgyTp6O6SSqg5sAT0s4UrzFHKEGq3sEW7rKGCPf0YW2Pv-rnvmntCUmT7wcSnCMZiw8xbF38_7fCyRR4n45QuKcCi-fPQSPKTlanXAecGlQf_7bSl_RrmoPNW4Ak4wV52dR80SSWrJPE5Noimn90Ba12J9CbAAQTnWuS-EcUtyvCCBRzEytQ8BK4LTw_c3k_DpXSIRX-qnTalUhMzJd0seGObMhYX0VKu7Oyil-qWSdXJbOhWTkF'); background-size: cover;"></div>
<div class="size-6 rounded-full border-2 border-white dark:border-slate-800 bg-primary text-[8px] flex items-center justify-center text-white font-bold">+45</div>
</div>
</td>
<td class="px-6 py-4 text-sm text-slate-600 dark:text-slate-400">Berlin, Germany</td>
<td class="px-6 py-4">
<span class="px-2.5 py-1 rounded-full bg-amber-100 text-amber-600 text-[10px] font-bold uppercase">Pending</span>
</td>
<td class="px-6 py-4 text-right">
<button class="text-slate-400 hover:text-primary"><span class="material-symbols-outlined">more_vert</span></button>
</td>
</tr>
</tbody>
</table>
</div>
</div>
</div>
</main>
</div>
</body></html>