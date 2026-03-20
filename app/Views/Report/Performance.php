<!DOCTYPE html>

<html lang="en"><head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title>LocalConnect Content Performance</title>
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<link href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@300;400;500;600;700;800;900&amp;display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
<script id="tailwind-config">
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        "primary": "#ec5b13",
                        "accent-purple": "#8b5cf6",
                        "accent-blue": "#3b82f6",
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
        .bg-vibrant-gradient {
            background: linear-gradient(135deg, #8b5cf6 0%, #3b82f6 100%);
        }
        .bg-vibrant-gradient-hover:hover {
            background: linear-gradient(135deg, #7c3aed 0%, #2563eb 100%);
        }
    </style>
</head>
<body class="bg-background-light dark:bg-background-dark text-slate-900 dark:text-slate-100 font-display">
<div class="flex h-screen overflow-hidden">
<!-- Sidebar Navigation -->
<aside class="w-64 border-r border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 flex flex-col">
<div class="p-6 flex items-center gap-3">
<div class="bg-primary rounded-lg p-1.5 flex items-center justify-center">
<span class="material-symbols-outlined text-white text-2xl">network_node</span>
</div>
<h1 class="text-xl font-bold tracking-tight text-slate-900 dark:text-white">LocalConnect</h1>
</div>
<nav class="flex-1 px-4 space-y-1">
<a class="flex items-center gap-3 px-3 py-2 text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-xl transition-colors" href="#">
<span class="material-symbols-outlined">home</span>
<span class="font-medium text-sm">Home</span>
</a>
<a class="flex items-center gap-3 px-3 py-2 text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-xl transition-colors" href="#">
<span class="material-symbols-outlined">explore</span>
<span class="font-medium text-sm">Explore</span>
</a>
<a class="flex items-center gap-3 px-3 py-2 text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-xl transition-colors" href="#">
<span class="material-symbols-outlined">groups</span>
<span class="font-medium text-sm">Groups</span>
</a>
<a class="flex items-center gap-3 px-3 py-2 text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-xl transition-colors" href="#">
<span class="material-symbols-outlined">calendar_month</span>
<span class="font-medium text-sm">Events</span>
</a>
<a class="flex items-center gap-3 px-3 py-2 text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-xl transition-colors" href="#">
<span class="material-symbols-outlined">person_search</span>
<span class="font-medium text-sm">Friends</span>
</a>
<a class="flex items-center gap-3 px-3 py-2 text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-xl transition-colors" href="#">
<span class="material-symbols-outlined">chat</span>
<span class="font-medium text-sm">Messages</span>
</a>
<div class="pt-4 pb-2 px-3">
<p class="text-[10px] uppercase font-bold text-slate-400 tracking-wider">Analytics</p>
</div>
<a class="flex items-center gap-3 px-3 py-2 bg-primary/10 text-primary rounded-xl" href="#">
<span class="material-symbols-outlined">bar_chart_4_bars</span>
<span class="font-medium text-sm">Performance</span>
</a>
</nav>
<div class="p-4 mt-auto border-t border-slate-200 dark:border-slate-800">
<button class="w-full flex items-center justify-center gap-2 bg-vibrant-gradient bg-vibrant-gradient-hover text-white py-2.5 px-4 rounded-xl font-bold text-sm transition-all shadow-md">
<span class="material-symbols-outlined text-lg">add_circle</span>
                    Create Post
                </button>
</div>
</aside>
<!-- Main Content -->
<main class="flex-1 overflow-y-auto">
<!-- Header -->
<header class="sticky top-0 z-10 flex items-center justify-between px-8 py-4 bg-background-light/80 dark:bg-background-dark/80 backdrop-blur-md border-b border-slate-200 dark:border-slate-800">
<div>
<h2 class="text-2xl font-black text-slate-900 dark:text-white">Content Performance</h2>
<p class="text-sm text-slate-500 dark:text-slate-400">Review your network reach and audience engagement</p>
</div>
<div class="flex items-center gap-4">
<div class="flex items-center bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl px-4 py-2 gap-3 shadow-sm">
<span class="material-symbols-outlined text-slate-400">calendar_today</span>
<span class="text-sm font-medium text-slate-700 dark:text-slate-300">Oct 1 - Oct 31, 2023</span>
<span class="material-symbols-outlined text-slate-400">expand_more</span>
</div>
<button class="flex items-center gap-2 px-4 py-2 border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 hover:bg-slate-50 dark:hover:bg-slate-800 rounded-xl font-bold text-sm transition-colors shadow-sm">
<span class="material-symbols-outlined text-lg">download</span>
                        Export
                    </button>
<div class="w-10 h-10 rounded-full bg-slate-300 overflow-hidden border-2 border-primary/20">
<img class="w-full h-full object-cover" data-alt="User profile avatar placeholder" src="https://lh3.googleusercontent.com/aida-public/AB6AXuDyJbzhnLMvNuh0VnD_f5ML5Hfman4gJCreiDOEaqiAtdrDoNZ6CT3xP4_8Gpovi87DZV0kGYlR4FBKmIYGCnio1lKfTSP7AvGmnB25aJN10vyLXMFYfHv2YXgIIFRj1abXOkDN_j0R__9-FssgP-DrL7Zo9lzZtiAgMmaFt_eoZyoYnI7tQ6X7Z0uGITbyk9YiAohtuQ88IvXkDlH8anrvXIOSh_BB5qbPNTQ-xbyjqdH3QMU2hU8xifVNey3wKfp3kBZkA-1v2RKG"/>
</div>
</div>
</header>
<div class="p-8 space-y-8">
<!-- Key Metric Cards -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
<div class="bg-white dark:bg-slate-900 p-6 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-800">
<div class="flex justify-between items-start mb-4">
<span class="p-2 bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 rounded-lg material-symbols-outlined">visibility</span>
</div>
<p class="text-sm font-medium text-slate-500 dark:text-slate-400">Total Post Reach</p>
<div class="flex items-end gap-2 mt-1">
<h3 class="text-2xl font-black text-slate-900 dark:text-white">124.5K</h3>
<span class="text-emerald-500 text-xs font-bold mb-1.5 flex items-center">
<span class="material-symbols-outlined text-[16px]">trending_up</span>12.4%
                            </span>
</div>
</div>
<div class="bg-white dark:bg-slate-900 p-6 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-800">
<div class="flex justify-between items-start mb-4">
<span class="p-2 bg-purple-100 dark:bg-purple-900/30 text-purple-600 dark:text-purple-400 rounded-lg material-symbols-outlined">favorite</span>
</div>
<p class="text-sm font-medium text-slate-500 dark:text-slate-400">Engagement Rate</p>
<div class="flex items-end gap-2 mt-1">
<h3 class="text-2xl font-black text-slate-900 dark:text-white">4.2%</h3>
<span class="text-emerald-500 text-xs font-bold mb-1.5 flex items-center">
<span class="material-symbols-outlined text-[16px]">trending_up</span>0.8%
                            </span>
</div>
</div>
<div class="bg-white dark:bg-slate-900 p-6 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-800">
<div class="flex justify-between items-start mb-4">
<span class="p-2 bg-orange-100 dark:bg-orange-900/30 text-primary rounded-lg material-symbols-outlined">play_circle</span>
</div>
<p class="text-sm font-medium text-slate-500 dark:text-slate-400">Top Post Type</p>
<div class="flex items-end gap-2 mt-1">
<h3 class="text-2xl font-black text-slate-900 dark:text-white">Video</h3>
<span class="text-slate-400 text-xs font-medium mb-1.5 uppercase">84% of Reach</span>
</div>
</div>
<div class="bg-white dark:bg-slate-900 p-6 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-800">
<div class="flex justify-between items-start mb-4">
<span class="p-2 bg-pink-100 dark:bg-pink-900/30 text-pink-600 dark:text-pink-400 rounded-lg material-symbols-outlined">share</span>
</div>
<p class="text-sm font-medium text-slate-500 dark:text-slate-400">Total Shares</p>
<div class="flex items-end gap-2 mt-1">
<h3 class="text-2xl font-black text-slate-900 dark:text-white">12.8K</h3>
<span class="text-emerald-500 text-xs font-bold mb-1.5 flex items-center">
<span class="material-symbols-outlined text-[16px]">trending_up</span>5.2%
                            </span>
</div>
</div>
</div>
<!-- Main Analysis Row -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
<!-- Top Performing Posts -->
<div class="lg:col-span-2 space-y-4">
<div class="flex items-center justify-between">
<h4 class="text-lg font-bold text-slate-900 dark:text-white">Top Performing Posts</h4>
<button class="text-primary text-sm font-bold hover:underline">View All</button>
</div>
<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
<!-- Post Card 1 -->
<div class="bg-white dark:bg-slate-900 rounded-2xl overflow-hidden border border-slate-100 dark:border-slate-800 shadow-sm">
<div class="h-40 bg-slate-200 relative">
<img class="w-full h-full object-cover" data-alt="Dynamic local community event photography" src="https://lh3.googleusercontent.com/aida-public/AB6AXuAs4ayRlLUckVKbdfWVX0rtESNvGtZGJLMaYdCPYZ_lsfXpp3AIa1K0M8RXnjFn6t90TVz3P88RxQax5oCkT5CF2C3uIwuQKH5x2FczNqnJyGo0FJCXg_BRH_1dxCZBYkai124DWWQFtJeSioLxHytx7W504lTALP5pbA0hrbXEnZYlNuFt6AIkH4kXpXZigdLUrSLz2kji1cpw3NVTQZKT8prQBHwuQU9nyh09fgolTe81JiA_O9eJPjFHxUKYFt8kF7tSHEjez11j"/>
<div class="absolute top-3 left-3 bg-black/40 backdrop-blur-md text-white text-[10px] font-bold px-2 py-1 rounded uppercase tracking-wider">Video</div>
</div>
<div class="p-4">
<h5 class="font-bold text-sm text-slate-800 dark:text-slate-200 line-clamp-1 mb-3">Summer Street Festival Highlights...</h5>
<div class="grid grid-cols-4 gap-2 border-t border-slate-100 dark:border-slate-800 pt-3">
<div class="text-center">
<p class="text-[10px] text-slate-400 font-bold uppercase">Likes</p>
<p class="text-sm font-bold">1.2K</p>
</div>
<div class="text-center">
<p class="text-[10px] text-slate-400 font-bold uppercase">Comms</p>
<p class="text-sm font-bold">342</p>
</div>
<div class="text-center">
<p class="text-[10px] text-slate-400 font-bold uppercase">Shares</p>
<p class="text-sm font-bold">89</p>
</div>
<div class="text-center">
<p class="text-[10px] text-slate-400 font-bold uppercase">Reach</p>
<p class="text-sm font-bold">12K</p>
</div>
</div>
</div>
</div>
<!-- Post Card 2 -->
<div class="bg-white dark:bg-slate-900 rounded-2xl overflow-hidden border border-slate-100 dark:border-slate-800 shadow-sm">
<div class="h-40 bg-slate-200 relative">
<img class="w-full h-full object-cover" data-alt="High quality professional culinary food photography" src="https://lh3.googleusercontent.com/aida-public/AB6AXuBbQH-WAQhyqhPuIk0dZiilJdZ4Q0vn5WSJFX5f24UW6T8bE2kkBJqfRoOtl4utOIuXi8hZMdPIY4_CPO5eQL3y1WTK2zeuy6eLsXxsGZKG2GWVfQJg3hPnhwkt2w7W9xjONBRebrBIQOamjr4-4Mqo7txQr4Rnd04Ym5zTakXYB92Z0YLA5gfRNytxMjt1IwadwKLutk-b7JddysJg5UyQejq-eFMJnEQLvJl-27naYUVwITr7X7dhQZSWno6FvqwttF1QvqaiwtL3"/>
<div class="absolute top-3 left-3 bg-black/40 backdrop-blur-md text-white text-[10px] font-bold px-2 py-1 rounded uppercase tracking-wider">Image</div>
</div>
<div class="p-4">
<h5 class="font-bold text-sm text-slate-800 dark:text-slate-200 line-clamp-1 mb-3">Hidden Gem: The Best Pasta in Town!</h5>
<div class="grid grid-cols-4 gap-2 border-t border-slate-100 dark:border-slate-800 pt-3">
<div class="text-center">
<p class="text-[10px] text-slate-400 font-bold uppercase">Likes</p>
<p class="text-sm font-bold">945</p>
</div>
<div class="text-center">
<p class="text-[10px] text-slate-400 font-bold uppercase">Comms</p>
<p class="text-sm font-bold">128</p>
</div>
<div class="text-center">
<p class="text-[10px] text-slate-400 font-bold uppercase">Shares</p>
<p class="text-sm font-bold">45</p>
</div>
<div class="text-center">
<p class="text-[10px] text-slate-400 font-bold uppercase">Reach</p>
<p class="text-sm font-bold">8.4K</p>
</div>
</div>
</div>
</div>
</div>
</div>
<!-- Side Charts -->
<div class="space-y-8">
<!-- Content Type Breakdown -->
<div class="bg-white dark:bg-slate-900 p-6 rounded-2xl border border-slate-100 dark:border-slate-800 shadow-sm">
<h4 class="text-base font-bold text-slate-900 dark:text-white mb-6">Content Type Distribution</h4>
<div class="relative flex justify-center py-4">
<!-- Donut Chart Placeholder Visual -->
<div class="w-32 h-32 rounded-full border-[12px] border-slate-100 dark:border-slate-800 relative flex items-center justify-center">
<div class="absolute inset-[-12px] rounded-full border-[12px] border-accent-purple border-t-transparent border-r-transparent rotate-45"></div>
<div class="absolute inset-[-12px] rounded-full border-[12px] border-accent-blue border-l-transparent border-b-transparent -rotate-12"></div>
<div class="text-center">
<p class="text-lg font-black leading-none">1,248</p>
<p class="text-[10px] text-slate-400 font-bold uppercase mt-1">Total</p>
</div>
</div>
</div>
<div class="space-y-3 mt-4">
<div class="flex items-center justify-between text-sm">
<div class="flex items-center gap-2">
<span class="w-3 h-3 rounded-full bg-accent-purple"></span>
<span class="font-medium text-slate-600 dark:text-slate-400">Videos</span>
</div>
<span class="font-bold">48%</span>
</div>
<div class="flex items-center justify-between text-sm">
<div class="flex items-center gap-2">
<span class="w-3 h-3 rounded-full bg-accent-blue"></span>
<span class="font-medium text-slate-600 dark:text-slate-400">Images</span>
</div>
<span class="font-bold">35%</span>
</div>
<div class="flex items-center justify-between text-sm">
<div class="flex items-center gap-2">
<span class="w-3 h-3 rounded-full bg-slate-200 dark:bg-slate-700"></span>
<span class="font-medium text-slate-600 dark:text-slate-400">Text Posts</span>
</div>
<span class="font-bold">17%</span>
</div>
</div>
</div>
<!-- Audience Reaction -->
<div class="bg-white dark:bg-slate-900 p-6 rounded-2xl border border-slate-100 dark:border-slate-800 shadow-sm">
<h4 class="text-base font-bold text-slate-900 dark:text-white mb-6">Audience Reaction Sentiment</h4>
<div class="space-y-4">
<div>
<div class="flex justify-between items-center text-xs font-bold uppercase mb-1.5">
<span class="text-emerald-500">Positive</span>
<span>72%</span>
</div>
<div class="h-2 bg-slate-100 dark:bg-slate-800 rounded-full overflow-hidden">
<div class="h-full bg-emerald-500 rounded-full w-[72%]"></div>
</div>
</div>
<div>
<div class="flex justify-between items-center text-xs font-bold uppercase mb-1.5">
<span class="text-slate-400">Neutral</span>
<span>22%</span>
</div>
<div class="h-2 bg-slate-100 dark:bg-slate-800 rounded-full overflow-hidden">
<div class="h-full bg-slate-300 dark:bg-slate-600 rounded-full w-[22%]"></div>
</div>
</div>
<div>
<div class="flex justify-between items-center text-xs font-bold uppercase mb-1.5">
<span class="text-rose-500">Negative</span>
<span>6%</span>
</div>
<div class="h-2 bg-slate-100 dark:bg-slate-800 rounded-full overflow-hidden">
<div class="h-full bg-rose-500 rounded-full w-[6%]"></div>
</div>
</div>
</div>
</div>
</div>
</div>
<!-- Footer Stats -->
<div class="bg-white dark:bg-slate-900 rounded-2xl p-8 border border-slate-100 dark:border-slate-800 shadow-sm">
<div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
<div>
<h4 class="text-lg font-bold text-slate-900 dark:text-white">Performance Summary</h4>
<p class="text-sm text-slate-500 dark:text-slate-400">Growth and retention metrics for the last 30 days</p>
</div>
<div class="flex items-center gap-8">
<div class="text-center">
<p class="text-[10px] text-slate-400 font-black uppercase tracking-widest mb-1">New Followers</p>
<p class="text-xl font-black text-primary">+2,481</p>
</div>
<div class="w-px h-10 bg-slate-200 dark:bg-slate-800 hidden md:block"></div>
<div class="text-center">
<p class="text-[10px] text-slate-400 font-black uppercase tracking-widest mb-1">Retention Rate</p>
<p class="text-xl font-black text-accent-blue">94.2%</p>
</div>
<div class="w-px h-10 bg-slate-200 dark:bg-slate-800 hidden md:block"></div>
<div class="text-center">
<p class="text-[10px] text-slate-400 font-black uppercase tracking-widest mb-1">Avg. Response Time</p>
<p class="text-xl font-black text-accent-purple">12m</p>
</div>
</div>
</div>
</div>
</div>
</main>
</div>
</body></html>
