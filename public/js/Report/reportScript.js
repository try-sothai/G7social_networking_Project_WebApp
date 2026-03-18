const ctx = document.getElementById('growthChart');

new Chart(ctx, {
    type: 'line',
    data: {
        labels: ['Jan','Feb','Mar','Apr','May','Jun'],
        datasets: [{
            label: 'Users',
            data: [20000,40000,50000,80000,90000,110000],
            fill: true,
            tension:0.4
        }]
    },
    options: {
        responsive:true
    }
});