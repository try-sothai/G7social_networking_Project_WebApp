<!DOCTYPE html>

<html lang="en"><head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title>Social Networking Interface Report</title>
<!-- BEGIN: External Resources -->
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<!-- END: External Resources -->
<!-- BEGIN: Tailwind Configuration -->
<script>
    tailwind.config = {
      theme: {
        extend: {
          colors: {
            'social-blue': '#1877F2',
            'dashboard-bg': '#F0F2F5',
          }
        }
      }
    }
  </script>
<!-- END: Tailwind Configuration -->
<!-- BEGIN: Custom Styles -->
<style data-purpose="layout-refinements">
    body {
      background-color: #F0F2F5;
      font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
    }
    .sidebar-active {
      background-color: rgba(24, 119, 242, 0.1);
      border-left: 4px solid #1877F2;
      color: #1877F2;
    }
    /* Simple custom scrollbar for the table */
    .custom-scrollbar::-webkit-scrollbar {
      width: 6px;
    }
    .custom-scrollbar::-webkit-scrollbar-thumb {
      background: #CBD5E1;
      border-radius: 10px;
    }
  </style>
<!-- END: Custom Styles -->
</head>
<body class="flex h-screen overflow-hidden">
<!-- BEGIN: Sidebar Navigation -->
<aside class="w-64 bg-white border-r border-gray-200 flex-shrink-0 flex flex-col h-full" data-purpose="main-sidebar">
<div class="p-6 border-b border-gray-100 flex items-center gap-3">
<div class="w-8 h-8 bg-social-blue rounded-lg flex items-center justify-center">
<svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewbox="0 0 24 24"><path d="M13 10V3L4 14h7v7l9-11h-7z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"></path></svg>
</div>
<span class="font-bold text-xl text-gray-800 tracking-tight">SocialStat</span>
</div>
<nav class="flex-grow p-4 space-y-2 overflow-y-auto">
<a class="sidebar-active flex items-center gap-3 px-4 py-3 rounded-lg font-medium transition-colors" href="#">
<svg class="w-5 h-5" fill="none" stroke="currentColor" viewbox="0 0 24 24"><path d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"></path></svg>
        Overview
      </a>
<a class="flex items-center gap-3 px-4 py-3 text-gray-600 hover:bg-gray-50 rounded-lg font-medium transition-colors" href="#">
<svg class="w-5 h-5" fill="none" stroke="currentColor" viewbox="0 0 24 24"><path d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"></path></svg>
        User Growth
      </a>
<a class="flex items-center gap-3 px-4 py-3 text-gray-600 hover:bg-gray-50 rounded-lg font-medium transition-colors" href="#">
<svg class="w-5 h-5" fill="none" stroke="currentColor" viewbox="0 0 24 24"><path d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"></path></svg>
        Engagement
      </a>
<a class="flex items-center gap-3 px-4 py-3 text-gray-600 hover:bg-gray-50 rounded-lg font-medium transition-colors" href="#">
<svg class="w-5 h-5" fill="none" stroke="currentColor" viewbox="0 0 24 24"><path d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"></path></svg>
        Content Analysis
      </a>
</nav>
<div class="p-4 border-t border-gray-100">
<a class="flex items-center gap-3 px-4 py-3 text-gray-600 hover:bg-gray-50 rounded-lg font-medium transition-colors" href="#">
<svg class="w-5 h-5" fill="none" stroke="currentColor" viewbox="0 0 24 24"><path d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"></path><path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"></path></svg>
        Settings
      </a>
<!-- System Note -->
<div class="mt-4 p-3 bg-gray-50 rounded-md border border-gray-100">
<p class="text-[10px] text-gray-400 uppercase font-semibold">Backend Engine</p>
<p class="text-xs text-gray-600 font-medium">PHP 8.2 Data Processing</p>
</div>
</div>
</aside>
<!-- END: Sidebar Navigation -->
<!-- BEGIN: Main Dashboard Content -->
<main class="flex-grow overflow-y-auto custom-scrollbar bg-dashboard-bg">
<!-- Header -->
<header class="bg-white px-8 py-5 border-b border-gray-200 sticky top-0 z-10 flex justify-between items-center">
<div>
<h1 class="text-2xl font-bold text-gray-800">Dashboard Overview</h1>
<p class="text-sm text-gray-500">Welcome back, Administrator. Real-time stats are up to date.</p>
</div>
<div class="flex items-center gap-4">
<button class="p-2 text-gray-400 hover:text-social-blue transition-colors">
<svg class="w-6 h-6" fill="none" stroke="currentColor" viewbox="0 0 24 24"><path d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"></path></svg>
</button>
<div class="w-10 h-10 rounded-full bg-gray-200 border-2 border-white shadow-sm overflow-hidden">
<img alt="Admin Avatar" src="https://lh3.googleusercontent.com/aida-public/AB6AXuCqxQ051nmZJrCcZhCGtuv_yqoORwKYtBz7WJBDCLzVR78VBJlYD2Sud2JD5Smttjyi9cEyrO7lBp4JOUCXtdz_Ux1wDcVPNtnW_nwX_Z1q9ibkVEaDN8x7Eh1pnnG8MnF29LmHwNdkLDW2L_OKsF9vr6mrMW9VmpKDLEphuXcTCLlbvk4YZhhfR9xxXNkcvhOq0o5Bm7bb1suZ_zUGJUVelrJT6nJ80rfPodVyKL7upORSdg8NiU4XiDJkghcXycKzdsl5Bk7F6y_x"/>
</div>
</div>
</header>
<div class="p-8">
<!-- BEGIN: KPI Cards -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8" data-purpose="kpi-section">
<!-- Card 1: Total Users -->
<div class="bg-white p-5 rounded-xl border border-gray-100 shadow-sm flex flex-col justify-between h-32">
<div class="flex justify-between items-start">
<div>
<p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Total Users</p>
<h3 class="text-2xl font-bold text-gray-800 mt-1">1,284,502</h3>
</div>
<div class="text-green-500 bg-green-50 px-2 py-1 rounded text-xs font-bold">+12%</div>
</div>
<canvas class="h-10 w-full" id="miniChartUsers"></canvas>
</div>
<!-- Card 2: DAU -->
<div class="bg-white p-5 rounded-xl border border-gray-100 shadow-sm flex flex-col justify-between h-32">
<div class="flex justify-between items-start">
<div>
<p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Daily Active Users</p>
<h3 class="text-2xl font-bold text-gray-800 mt-1">842,110</h3>
</div>
<div class="text-green-500 bg-green-50 px-2 py-1 rounded text-xs font-bold">+5.4%</div>
</div>
<canvas class="h-10 w-full" id="miniChartDAU"></canvas>
</div>
<!-- Card 3: Engagement Rate -->
<div class="bg-white p-5 rounded-xl border border-gray-100 shadow-sm flex flex-col justify-between h-32">
<div class="flex justify-between items-start">
<div>
<p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Engagement Rate</p>
<h3 class="text-2xl font-bold text-gray-800 mt-1">4.82%</h3>
</div>
<div class="text-red-500 bg-red-50 px-2 py-1 rounded text-xs font-bold">-0.2%</div>
</div>
<canvas class="h-10 w-full" id="miniChartEngagement"></canvas>
</div>
<!-- Card 4: New Signups -->
<div class="bg-white p-5 rounded-xl border border-gray-100 shadow-sm flex flex-col justify-between h-32">
<div class="flex justify-between items-start">
<div>
<p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">New Signups</p>
<h3 class="text-2xl font-bold text-gray-800 mt-1">14,209</h3>
</div>
<div class="text-green-500 bg-green-50 px-2 py-1 rounded text-xs font-bold">+21%</div>
</div>
<canvas class="h-10 w-full" id="miniChartSignups"></canvas>
</div>
</div>
<!-- END: KPI Cards -->
<!-- BEGIN: Visual Data Section -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8" data-purpose="charts-section">
<!-- Large Line Chart -->
<div class="lg:col-span-2 bg-white p-6 rounded-xl border border-gray-100 shadow-sm">
<div class="flex items-center justify-between mb-6">
<h4 class="text-lg font-bold text-gray-800">User Engagement Over Time</h4>
<select class="text-xs border-gray-200 rounded-lg text-gray-600 focus:ring-social-blue focus:border-social-blue">
<option>Last 30 Days</option>
<option>Last 90 Days</option>
<option>Full Year</option>
</select>
</div>
<div class="h-80 w-full">
<canvas id="mainEngagementChart"></canvas>
</div>
</div>
<!-- Doughnut Chart -->
<div class="bg-white p-6 rounded-xl border border-gray-100 shadow-sm flex flex-col">
<h4 class="text-lg font-bold text-gray-800 mb-6">Traffic Sources</h4>
<div class="flex-grow flex items-center justify-center relative">
<canvas id="trafficDoughnutChart"></canvas>
</div>
<div class="mt-4 space-y-2">
<div class="flex justify-between text-sm">
<span class="text-gray-500">Direct Search</span>
<span class="font-semibold">45%</span>
</div>
<div class="flex justify-between text-sm">
<span class="text-gray-500">Referrals</span>
<span class="font-semibold">30%</span>
</div>
<div class="flex justify-between text-sm">
<span class="text-gray-500">Social Ads</span>
<span class="font-semibold">25%</span>
</div>
</div>
</div>
</div>
<!-- END: Visual Data Section -->
<!-- BEGIN: Recent Activity Table -->
<section class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden" data-purpose="recent-activity">
<div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center">
<h4 class="text-lg font-bold text-gray-800">Recent Social Activity</h4>
<button class="text-social-blue text-sm font-semibold hover:underline">View All Activity</button>
</div>
<div class="overflow-x-auto">
<table class="w-full text-left border-collapse">
<thead class="bg-gray-50 text-gray-400 text-xs uppercase tracking-wider">
<tr>
<th class="px-6 py-4 font-semibold">User</th>
<th class="px-6 py-4 font-semibold">Action</th>
<th class="px-6 py-4 font-semibold">Target Content</th>
<th class="px-6 py-4 font-semibold text-right">Timestamp</th>
</tr>
</thead>
<tbody class="divide-y divide-gray-100 text-sm text-gray-700">
<tr class="hover:bg-gray-50 transition-colors">
<td class="px-6 py-4 flex items-center gap-3">
<div class="w-8 h-8 rounded-full bg-blue-100 text-social-blue flex items-center justify-center font-bold text-xs">AS</div>
<span class="font-medium">Alex Smith</span>
</td>
<td class="px-6 py-4">
<span class="px-2 py-1 bg-blue-50 text-social-blue text-[10px] font-bold rounded-full uppercase">Posted Photo</span>
</td>
<td class="px-6 py-4 text-gray-500 italic">"Summer Vacation 2024"</td>
<td class="px-6 py-4 text-right text-gray-400">2 minutes ago</td>
</tr>
<tr class="hover:bg-gray-50 transition-colors">
<td class="px-6 py-4 flex items-center gap-3">
<div class="w-8 h-8 rounded-full bg-purple-100 text-purple-600 flex items-center justify-center font-bold text-xs">MJ</div>
<span class="font-medium">Maria Jimenez</span>
</td>
<td class="px-6 py-4">
<span class="px-2 py-1 bg-purple-50 text-purple-600 text-[10px] font-bold rounded-full uppercase">New Follow</span>
</td>
<td class="px-6 py-4 text-gray-500">@tech_weekly</td>
<td class="px-6 py-4 text-right text-gray-400">14 minutes ago</td>
</tr>
<tr class="hover:bg-gray-50 transition-colors">
<td class="px-6 py-4 flex items-center gap-3">
<div class="w-8 h-8 rounded-full bg-red-100 text-red-600 flex items-center justify-center font-bold text-xs">KD</div>
<span class="font-medium">Kevin Durant</span>
</td>
<td class="px-6 py-4">
<span class="px-2 py-1 bg-red-50 text-red-600 text-[10px] font-bold rounded-full uppercase">Liked Post</span>
</td>
<td class="px-6 py-4 text-gray-500">"Top 10 Backend Frameworks"</td>
<td class="px-6 py-4 text-right text-gray-400">32 minutes ago</td>
</tr>
<tr class="hover:bg-gray-50 transition-colors">
<td class="px-6 py-4 flex items-center gap-3">
<div class="w-8 h-8 rounded-full bg-green-100 text-green-600 flex items-center justify-center font-bold text-xs">YL</div>
<span class="font-medium">Yuki Li</span>
</td>
<td class="px-6 py-4">
<span class="px-2 py-1 bg-green-50 text-green-600 text-[10px] font-bold rounded-full uppercase">Account Created</span>
</td>
<td class="px-6 py-4 text-gray-500">New User Registration</td>
<td class="px-6 py-4 text-right text-gray-400">1 hour ago</td>
</tr>
</tbody>
</table>
</div>
</section>
<!-- END: Recent Activity Table -->
</div>
</main>
<!-- END: Main Dashboard Content -->
<!-- BEGIN: Chart Initialization -->
<script data-purpose="chart-rendering">
    document.addEventListener('DOMContentLoaded', function() {
      // Shared Chart Configuration Helper
      const miniChartConfig = (color) => ({
        type: 'line',
        data: {
          labels: ['', '', '', '', '', '', ''],
          datasets: [{
            data: Array.from({length: 7}, () => Math.floor(Math.random() * 50) + 10),
            borderColor: color,
            borderWidth: 2,
            pointRadius: 0,
            tension: 0.4,
            fill: false,
          }]
        },
        options: {
          plugins: { legend: { display: false } },
          scales: { x: { display: false }, y: { display: false } },
          responsive: true,
          maintainAspectRatio: false
        }
      });

      // Initialize Mini Sparklines
      new Chart(document.getElementById('miniChartUsers'), miniChartConfig('#1877F2'));
      new Chart(document.getElementById('miniChartDAU'), miniChartConfig('#10B981'));
      new Chart(document.getElementById('miniChartEngagement'), miniChartConfig('#EF4444'));
      new Chart(document.getElementById('miniChartSignups'), miniChartConfig('#1877F2'));

      // Main Engagement Line Chart
      const ctxMain = document.getElementById('mainEngagementChart').getContext('2d');
      new Chart(ctxMain, {
        type: 'line',
        data: {
          labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
          datasets: [{
            label: 'Engaged Users',
            data: [65000, 78000, 72000, 85000, 95000, 110000, 105000],
            borderColor: '#1877F2',
            backgroundColor: 'rgba(24, 119, 242, 0.1)',
            borderWidth: 3,
            fill: true,
            tension: 0.3
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: { legend: { display: false } },
          scales: {
            y: {
              beginAtZero: true,
              grid: { color: '#F1F5F9' },
              ticks: { color: '#94A3B8', font: { size: 11 } }
            },
            x: {
              grid: { display: false },
              ticks: { color: '#94A3B8', font: { size: 11 } }
            }
          }
        }
      });

      // Traffic Doughnut Chart
      const ctxTraffic = document.getElementById('trafficDoughnutChart').getContext('2d');
      new Chart(ctxTraffic, {
        type: 'doughnut',
        data: {
          labels: ['Direct', 'Referral', 'Ads'],
          datasets: [{
            data: [45, 30, 25],
            backgroundColor: ['#1877F2', '#34D399', '#F87171'],
            hoverOffset: 4,
            borderWidth: 0
          }]
        },
        options: {
          cutout: '70%',
          responsive: true,
          maintainAspectRatio: false,
          plugins: { legend: { display: false } }
        }
      });
    });
  </script>
<!-- END: Chart Initialization -->
</body></html>