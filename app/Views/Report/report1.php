<!DOCTYPE html>

<html class="light" lang="en"><head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<link href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@300;400;500;600;700&amp;display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght@100..700,0..1&amp;display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<script id="tailwind-config">
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        "primary": "#ec5b13", // Using requested theme primary
                        "accent-purple": "#6366f1", // Requested accent color
                        "background-light": "#f8f6f6",
                        "background-dark": "#221610",
                    },
                    fontFamily: {
                        "display": ["Public Sans", "sans-serif"]
                    },
                    borderRadius: {"DEFAULT": "0.25rem", "lg": "0.5rem", "xl": "0.75rem", "full": "9999px"},
                },
            },
        }
    </script>
<style>
        body { font-family: 'Public Sans', sans-serif; }
        .material-symbols-outlined { font-size: 24px; }
    </style>
</head>
<body class="bg-background-light dark:bg-background-dark font-display text-slate-900 dark:text-slate-100">
<div class="flex h-screen overflow-hidden">
<!-- Side Navigation -->
<aside class="w-64 flex-shrink-0 bg-slate-900 dark:bg-black text-white flex flex-col h-full">
<div class="p-6 flex items-center gap-3">
<div class="size-10 rounded-full bg-primary flex items-center justify-center">
<span class="material-symbols-outlined text-white">hub</span>
</div>
<div>
<h1 class="text-lg font-bold leading-tight">LocalConnect</h1>
<p class="text-slate-400 text-xs">Admin Dashboard</p>
</div>
</div>
<nav class="flex-1 px-4 space-y-1 mt-4">
<a class="flex items-center gap-3 px-3 py-2 rounded-xl text-slate-400 hover:bg-slate-800 hover:text-white transition-colors" href="#">
<span class="material-symbols-outlined">home</span>
<span class="text-sm font-medium">Home</span>
</a>
<a class="flex items-center gap-3 px-3 py-2 rounded-xl text-slate-400 hover:bg-slate-800 hover:text-white transition-colors" href="#">
<span class="material-symbols-outlined">explore</span>
<span class="text-sm font-medium">Explore</span>
</a>
<a class="flex items-center gap-3 px-3 py-2 rounded-xl text-slate-400 hover:bg-slate-800 hover:text-white transition-colors" href="#">
<span class="material-symbols-outlined">groups</span>
<span class="text-sm font-medium">Groups</span>
</a>
<a class="flex items-center gap-3 px-3 py-2 rounded-xl text-slate-400 hover:bg-slate-800 hover:text-white transition-colors" href="#">
<span class="material-symbols-outlined">chat</span>
<span class="text-sm font-medium">Messages</span>
</a>
<a class="flex items-center gap-3 px-3 py-2 rounded-xl bg-accent-purple/20 text-accent-purple font-semibold" href="#">
<span class="material-symbols-outlined">analytics</span>
<span class="text-sm font-medium">Analytics</span>
</a>
</nav>
<div class="p-4 mt-auto border-t border-slate-800">
<div class="flex items-center gap-3 p-2">
<div class="size-8 rounded-full bg-slate-700" data-alt="User avatar profile picture" style="background-image: url('https://lh3.googleusercontent.com/aida-public/AB6AXuCoSclqZLtm3BNNKPqhrzq5vWzOBbS1SW8Nmi0HG9vRKmwiZkVeBBYBpoOz_1HIJN5MkwXfDMW91-sVBWmwfOUd9Y3RfwJxTK0bDm2J9qaQyC34S2VHMQtXCjSDYuwXtdqS_8d0R-ZQF_iU0L5PG7uaDAren-DsU3oCy62aiGL03dGxEf95dcU1z3tlfiUSyglRjtq7NZVIMEDRYDkrCs4hh_GBSr4YaIo02hp0YNRR9F8jEdnNedp10JnNCqvnL-ss6Wzc31KAVcxO');"></div>
<div class="flex-1 min-w-0">
<p class="text-sm font-medium truncate">Alex Thompson</p>
<p class="text-xs text-slate-500 truncate">Admin</p>
</div>
<span class="material-symbols-outlined text-slate-500">settings</span>
</div>
</div>
</aside>
<!-- Main Content Area -->
<main class="flex-1 flex flex-col overflow-hidden">
<!-- Header -->
<header class="h-16 flex items-center justify-between px-8 bg-white dark:bg-slate-900 border-b border-slate-200 dark:border-slate-800">
<div class="flex items-center gap-4">
<h2 class="text-xl font-bold">Social Activity &amp; Engagement</h2>
</div>
<div class="flex items-center gap-6">
<div class="relative">
<span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm">search</span>
<input class="pl-10 pr-4 py-2 bg-slate-100 dark:bg-slate-800 border-none rounded-xl text-sm focus:ring-2 focus:ring-accent-purple w-64" placeholder="Search analytics..." type="text"/>
</div>
<div class="flex gap-2">
<button class="p-2 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300">
<span class="material-symbols-outlined">notifications</span>
</button>
<button class="p-2 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300">
<span class="material-symbols-outlined">calendar_today</span>
</button>
</div>
</div>
</header>
<!-- Scrollable Content -->
<div class="flex-1 overflow-y-auto p-8 bg-background-light dark:bg-background-dark">
<!-- Overview Metrics Grid -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
<div class="bg-white dark:bg-slate-900 p-6 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm">
<div class="flex justify-between items-start mb-4">
<span class="p-2 bg-accent-purple/10 text-accent-purple rounded-lg material-symbols-outlined">post_add</span>
<span class="text-emerald-500 text-xs font-bold flex items-center gap-1">
<span class="material-symbols-outlined text-xs">trending_up</span> 12.5%
                        </span>
</div>
<p class="text-slate-500 dark:text-slate-400 text-sm font-medium">Total Posts</p>
<h3 class="text-2xl font-bold mt-1">1,284</h3>
</div>
<div class="bg-white dark:bg-slate-900 p-6 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm">
<div class="flex justify-between items-start mb-4">
<span class="p-2 bg-blue-500/10 text-blue-500 rounded-lg material-symbols-outlined">visibility</span>
<span class="text-emerald-500 text-xs font-bold flex items-center gap-1">
<span class="material-symbols-outlined text-xs">trending_up</span> 8.2%
                        </span>
</div>
<p class="text-slate-500 dark:text-slate-400 text-sm font-medium">Story Views</p>
<h3 class="text-2xl font-bold mt-1">45.2K</h3>
</div>
<div class="bg-white dark:bg-slate-900 p-6 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm">
<div class="flex justify-between items-start mb-4">
<span class="p-2 bg-amber-500/10 text-amber-500 rounded-lg material-symbols-outlined">favorite</span>
<span class="text-emerald-500 text-xs font-bold flex items-center gap-1">
<span class="material-symbols-outlined text-xs">trending_up</span> 0.5%
                        </span>
</div>
<p class="text-slate-500 dark:text-slate-400 text-sm font-medium">Avg. Engagement</p>
<h3 class="text-2xl font-bold mt-1">4.8%</h3>
</div>
<div class="bg-white dark:bg-slate-900 p-6 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm">
<div class="flex justify-between items-start mb-4">
<span class="p-2 bg-primary/10 text-primary rounded-lg material-symbols-outlined">history_toggle_off</span>
<span class="text-rose-500 text-xs font-bold flex items-center gap-1">
<span class="material-symbols-outlined text-xs">trending_down</span> 2.1%
                        </span>
</div>
<p class="text-slate-500 dark:text-slate-400 text-sm font-medium">Active Stories</p>
<h3 class="text-2xl font-bold mt-1">32</h3>
</div>
</div>
<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
<!-- Engagement Trends Chart -->
<div class="lg:col-span-2 bg-white dark:bg-slate-900 p-6 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm">
<div class="flex justify-between items-center mb-6">
<div>
<h4 class="text-lg font-bold">Engagement Trends</h4>
<p class="text-slate-500 text-sm">Post Likes vs. Comments (Last 30 Days)</p>
</div>
<div class="flex gap-4 text-xs font-semibold">
<div class="flex items-center gap-1.5"><span class="w-3 h-3 rounded-full bg-accent-purple"></span> Likes</div>
<div class="flex items-center gap-1.5"><span class="w-3 h-3 rounded-full bg-primary"></span> Comments</div>
</div>
</div>
<!-- Mock Line Chart Container -->
<div class="relative h-64 w-full mt-4">
<svg class="w-full h-full overflow-visible" preserveaspectratio="none" viewbox="0 0 400 100">
<!-- Background Grid -->
<line class="text-slate-100 dark:text-slate-800" stroke="currentColor" stroke-dasharray="4 4" stroke-width="1" x1="0" x2="400" y1="20" y2="20"></line>
<line class="text-slate-100 dark:text-slate-800" stroke="currentColor" stroke-dasharray="4 4" stroke-width="1" x1="0" x2="400" y1="50" y2="50"></line>
<line class="text-slate-100 dark:text-slate-800" stroke="currentColor" stroke-dasharray="4 4" stroke-width="1" x1="0" x2="400" y1="80" y2="80"></line>
<!-- Area Gradient -->
<defs>
<lineargradient id="grad1" x1="0%" x2="0%" y1="0%" y2="100%">
<stop offset="0%" style="stop-color:rgba(99, 102, 241, 0.2);stop-opacity:1"></stop>
<stop offset="100%" style="stop-color:rgba(99, 102, 241, 0);stop-opacity:1"></stop>
</lineargradient>
</defs>
<path d="M0 80 Q 50 20, 100 50 T 200 30 T 300 60 T 400 20 V 100 H 0 Z" fill="url(#grad1)"></path>
<!-- Likes Line -->
<path d="M0 80 Q 50 20, 100 50 T 200 30 T 300 60 T 400 20" fill="none" stroke="#6366f1" stroke-linecap="round" stroke-width="3"></path>
<!-- Comments Line -->
<path d="M0 90 Q 50 60, 100 85 T 200 70 T 300 80 T 400 65" fill="none" stroke="#ec5b13" stroke-linecap="round" stroke-width="3"></path>
</svg>
<div class="flex justify-between mt-4 text-[10px] text-slate-400 font-bold px-1">
<span>AUG 1</span><span>AUG 7</span><span>AUG 14</span><span>AUG 21</span><span>AUG 30</span>
</div>
</div>
</div>
<!-- Activity Feed -->
<div class="bg-white dark:bg-slate-900 p-6 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm flex flex-col">
<h4 class="text-lg font-bold mb-6">Real-time Activity</h4>
<div class="space-y-6 flex-1">
<div class="flex gap-4">
<div class="size-10 rounded-full flex-shrink-0 bg-slate-200" data-alt="Activity user avatar" style="background-image: url('https://lh3.googleusercontent.com/aida-public/AB6AXuCMIfektNISSlvxVjJ1OFLgPu22zfVVB8vlCu3WWmJwHgTOkrjXBD86Gky4xeqkRfkGU7dUyt9-CrUfSLNbe5pQAPao5Os2ke913plPIQVPxmsWhucpvafMIx6492y31WPMi-meeEG9mqgVpYHUhYPQMVYsC04LSX3g3zCBKc4qBHvX229ukcMQofzMO_icMuOJ4OdCHUpgD4xLwLzKGBVLFom24t9C-eiUBB206d1SQGN5mErR_YrhYTLjw4XAZGfQEzU67BHRkqp3');"></div>
<div class="flex-1">
<p class="text-sm"><span class="font-bold">Julia Marks</span> liked your story <span class="text-slate-400">"Morning Brew"</span></p>
<p class="text-[10px] text-slate-400 font-medium mt-1 uppercase tracking-wider">2 minutes ago</p>
</div>
</div>
<div class="flex gap-4">
<div class="size-10 rounded-full flex-shrink-0 bg-slate-200" data-alt="Activity user avatar" style="background-image: url('https://lh3.googleusercontent.com/aida-public/AB6AXuBT5aPihUSH4hbhuDLCNGdv9fFpw9Cz6CrWGC7tvF9aRzcq4UBZLCst6UrH1WumXemaHKpc7paQLE2NTDhGgXvDAMTJikhVWtL4UalCSYPSkDpdZCEdtiiw6lhhYQ8kuKkLZgB1zD4iZlYMTqlh6Guk14R1G2v2fUHN5Dt5omEnQ9qtSayV9URuOk9JweSCzG5b30HvHJZ9XHMAMWxVCBvEDSmA2bG8gjs1_D9W2H9EdANPaxAQdfq414qRZOKw-jQOpvu72KXpITzy');"></div>
<div class="flex-1">
<p class="text-sm"><span class="font-bold">David L.</span> commented on <span class="text-slate-400">"Urban Project"</span></p>
<p class="text-[10px] text-slate-400 font-medium mt-1 uppercase tracking-wider">14 minutes ago</p>
</div>
</div>
<div class="flex gap-4">
<div class="size-10 rounded-full flex-shrink-0 bg-slate-200" data-alt="Activity user avatar" style="background-image: url('https://lh3.googleusercontent.com/aida-public/AB6AXuCMq3E2W9-zdgxHoknq82IjfSlvUyGdRpMUbkHxhLGUv8RKgKitXsIeGOY0JNOSxJtlJitsnPQcuE08LFElByoD9DlwvHCAC2T9fxAR-aXdKnlrFTJ85IPXnT_4_YDa_QuLkQwnXMRYh80K3nFxqmL4tBs-A5_EQNGOAsCkXXtRodUBNzisyzmnQvbeywph1Rr0Q11nisBmkxG3JBscB9Qzeqhx8L3l6kJK0-JQ8SYacfkIuARE1lc_z4I_Bi_4pdl5eofEIe78WJFi');"></div>
<div class="flex-1">
<p class="text-sm"><span class="font-bold">Sara Wilson</span> shared your post to <span class="text-accent-purple font-medium">Hiking Group</span></p>
<p class="text-[10px] text-slate-400 font-medium mt-1 uppercase tracking-wider">1 hour ago</p>
</div>
</div>
<div class="flex gap-4">
<div class="size-10 rounded-full flex-shrink-0 bg-slate-200" data-alt="Activity user avatar" style="background-image: url('https://lh3.googleusercontent.com/aida-public/AB6AXuD8p9uJrKPC_sxeR2Lsg2EH9qqDyKJHetyJLlTXJCH1TD_V8BV8Cbw3qRY2ZvP9mASy3PfmLmY422v9ISTZ3d2WGiVU0sTJDCIQbJrdNXUvCvopUJHmQgvpFWry3dO7fgepPe_fbYcbddhwHRD0rbLxyrcCNgGTTC9cvOczYFEBhRXebW6O0trh8zTnmU_8sNWJjBZG5Id5HJS90jwvCLahJoUWN5oPseq6j30VFVGnGmEhURRprie3jzYhb2BZhI6paDdqfulBy28G');"></div>
<div class="flex-1">
<p class="text-sm"><span class="font-bold">Mike Chen</span> and 12 others started following you</p>
<p class="text-[10px] text-slate-400 font-medium mt-1 uppercase tracking-wider">3 hours ago</p>
</div>
</div>
</div>
<button class="w-full mt-6 py-2 text-xs font-bold text-slate-400 border border-slate-100 dark:border-slate-800 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors uppercase tracking-widest">View All Activity</button>
</div>
<!-- Story Performance Bar Chart -->
<div class="bg-white dark:bg-slate-900 p-6 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm">
<h4 class="text-lg font-bold mb-2">Story Performance</h4>
<p class="text-slate-500 text-sm mb-8">Views by recent story posters</p>
<div class="flex items-end justify-between h-48 gap-4 px-2">
<div class="flex flex-col items-center flex-1">
<div class="w-full bg-accent-purple/20 rounded-t-lg relative group transition-all" style="height: 100%;">
<div class="absolute inset-0 bg-accent-purple rounded-t-lg scale-y-[0.85] origin-bottom transition-transform group-hover:scale-y-[0.9]"></div>
<span class="absolute -top-6 left-1/2 -translate-x-1/2 text-[10px] font-bold">12.4K</span>
</div>
<p class="text-[10px] font-bold text-slate-500 mt-3 truncate w-full text-center">Sarah J.</p>
</div>
<div class="flex flex-col items-center flex-1">
<div class="w-full bg-accent-purple/20 rounded-t-lg relative group transition-all" style="height: 100%;">
<div class="absolute inset-0 bg-accent-purple rounded-t-lg scale-y-[0.45] origin-bottom transition-transform group-hover:scale-y-[0.5]"></div>
<span class="absolute -top-6 left-1/2 -translate-x-1/2 text-[10px] font-bold">5.8K</span>
</div>
<p class="text-[10px] font-bold text-slate-500 mt-3 truncate w-full text-center">Mike W.</p>
</div>
<div class="flex flex-col items-center flex-1">
<div class="w-full bg-accent-purple/20 rounded-t-lg relative group transition-all" style="height: 100%;">
<div class="absolute inset-0 bg-accent-purple rounded-t-lg scale-y-[0.7] origin-bottom transition-transform group-hover:scale-y-[0.75]"></div>
<span class="absolute -top-6 left-1/2 -translate-x-1/2 text-[10px] font-bold">9.1K</span>
</div>
<p class="text-[10px] font-bold text-slate-500 mt-3 truncate w-full text-center">Anna K.</p>
</div>
<div class="flex flex-col items-center flex-1">
<div class="w-full bg-accent-purple/20 rounded-t-lg relative group transition-all" style="height: 100%;">
<div class="absolute inset-0 bg-accent-purple rounded-t-lg scale-y-[0.55] origin-bottom transition-transform group-hover:scale-y-[0.6]"></div>
<span class="absolute -top-6 left-1/2 -translate-x-1/2 text-[10px] font-bold">7.2K</span>
</div>
<p class="text-[10px] font-bold text-slate-500 mt-3 truncate w-full text-center">David L.</p>
</div>
<div class="flex flex-col items-center flex-1">
<div class="w-full bg-accent-purple/20 rounded-t-lg relative group transition-all" style="height: 100%;">
<div class="absolute inset-0 bg-accent-purple rounded-t-lg scale-y-[0.35] origin-bottom transition-transform group-hover:scale-y-[0.4]"></div>
<span class="absolute -top-6 left-1/2 -translate-x-1/2 text-[10px] font-bold">4.4K</span>
</div>
<p class="text-[10px] font-bold text-slate-500 mt-3 truncate w-full text-center">Emma R.</p>
</div>
</div>
</div>
<!-- Top Active Users Table -->
<div class="lg:col-span-2 bg-white dark:bg-slate-900 p-6 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
<div class="flex justify-between items-center mb-6">
<h4 class="text-lg font-bold">Top Active Users</h4>
<button class="text-accent-purple text-sm font-bold flex items-center gap-1 hover:underline">
                            Export List <span class="material-symbols-outlined text-sm">download</span>
</button>
</div>
<div class="overflow-x-auto">
<table class="w-full text-left">
<thead>
<tr class="text-[10px] font-bold text-slate-400 uppercase tracking-widest border-b border-slate-100 dark:border-slate-800">
<th class="pb-3 pl-2">User</th>
<th class="pb-3">Posts</th>
<th class="pb-3">Stories</th>
<th class="pb-3">Eng. Rate</th>
<th class="pb-3 text-right pr-2">Growth</th>
</tr>
</thead>
<tbody class="divide-y divide-slate-100 dark:divide-slate-800">
<tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors">
<td class="py-4 pl-2">
<div class="flex items-center gap-3">
<div class="size-8 rounded-full bg-slate-200" data-alt="Top user avatar" style="background-image: url('https://lh3.googleusercontent.com/aida-public/AB6AXuAEZaj5KZ3LKIa6X0AQppvJ5_hILoHBRLfLwBdoeM_2SG6XOltNrmMtGtOS8N5MAx8-5jhxLr7edGaRfL_-l6_LFvVYyPqWJMlGD64FMMXsY_l1NUV0I4mUdG-l-6TNozAn6QrNiagK_aXHgQA5MntF6D8_ePlh8DYoOmaRk8-35C0DTqShUCjvUzFV0JfQSZbBTpv3oAuKuqkux6az5OT8ughD-6GoV8MU_wRxUqkpzQ1zvjbYv1p798oVUPEJoYC_DUo3dQsA4nCn');"></div>
<div>
<p class="text-sm font-bold">Sarah Jenkins</p>
<p class="text-xs text-slate-500">@sarahj</p>
</div>
</div>
</td>
<td class="py-4 text-sm font-medium">142</td>
<td class="py-4 text-sm font-medium">28</td>
<td class="py-4">
<div class="flex items-center gap-2">
<div class="w-16 h-1.5 bg-slate-100 dark:bg-slate-800 rounded-full overflow-hidden">
<div class="h-full bg-accent-purple" style="width: 82%"></div>
</div>
<span class="text-xs font-bold">8.2%</span>
</div>
</td>
<td class="py-4 text-right text-emerald-500 text-xs font-bold pr-2">+12%</td>
</tr>
<tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors">
<td class="py-4 pl-2">
<div class="flex items-center gap-3">
<div class="size-8 rounded-full bg-slate-200" data-alt="Top user avatar" style="background-image: url('https://lh3.googleusercontent.com/aida-public/AB6AXuDOMRAYXFhVqoL4arvYCVzp4EMfoLmOAk3DwQ1STgNlx_uIoPsazUnXy6eq9cirVa4IVbaAf6ivONjEblDa0XtrwjwdwSpKRPoSDvzyEQs7niZnc1cKkSlOdDRVHg-BljHIXZmjWPQFHGJ1zDArI_ZpXD9BmA531qYtQDjyS3GDrJJnMVg4xoXNttFh6Kc5NB_xqvkCazXwItegCX4mxVW37lbPt-nqq_HFOfJMt7r5aLQ-Lj0HYz1b2OAujxvExi4saugetTZn4o5Y');"></div>
<div>
<p class="text-sm font-bold">Mike Wilson</p>
<p class="text-xs text-slate-500">@mikew_design</p>
</div>
</div>
</td>
<td class="py-4 text-sm font-medium">98</td>
<td class="py-4 text-sm font-medium">45</td>
<td class="py-4">
<div class="flex items-center gap-2">
<div class="w-16 h-1.5 bg-slate-100 dark:bg-slate-800 rounded-full overflow-hidden">
<div class="h-full bg-accent-purple" style="width: 65%"></div>
</div>
<span class="text-xs font-bold">6.5%</span>
</div>
</td>
<td class="py-4 text-right text-emerald-500 text-xs font-bold pr-2">+5%</td>
</tr>
<tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors">
<td class="py-4 pl-2">
<div class="flex items-center gap-3">
<div class="size-8 rounded-full bg-slate-200" data-alt="Top user avatar" style="background-image: url('https://lh3.googleusercontent.com/aida-public/AB6AXuCb1munaz55OIBoVERT7a3fg0bP2zw0ALrt5ADjeERSa_94yLtUgeR2A7YjBVcVOG0UmNOi8f68JBrCLnnsdelI_zrnmyw4r_9EnUC2LSF7PjBjaXiaCSJxxAuP89o5pmjzc1ayOX23GekBBxtpdZDoYR9exhb3IpzpsAw4AlwBX0a4pchihoSBpjA_7Hbv4lenRFYKAqP19lyNvSWoAA7mTn6_FqzqQYSXU-TH5uPDpINgpvj4VQZOM2bEmib48AaG0igfTrRk5WUx');"></div>
<div>
<p class="text-sm font-bold">Anna Kowalski</p>
<p class="text-xs text-slate-500">@annak</p>
</div>
</div>
</td>
<td class="py-4 text-sm font-medium">85</td>
<td class="py-4 text-sm font-medium">12</td>
<td class="py-4">
<div class="flex items-center gap-2">
<div class="w-16 h-1.5 bg-slate-100 dark:bg-slate-800 rounded-full overflow-hidden">
<div class="h-full bg-accent-purple" style="width: 48%"></div>
</div>
<span class="text-xs font-bold">4.8%</span>
</div>
</td>
<td class="py-4 text-right text-rose-500 text-xs font-bold pr-2">-2%</td>
</tr>
</tbody>
</table>
</div>
</div>
</div>
</div>
</main>
</div>
</body></html>