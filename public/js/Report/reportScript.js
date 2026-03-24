document.addEventListener('DOMContentLoaded', () => {
    const ctx = document.getElementById('growthChart');
    if (!ctx) return;

    // userGrowthData is passed from the PHP file
    if (typeof userGrowthData !== 'undefined' && userGrowthData.length > 0) {
        
        const labels = userGrowthData.map(item => item.label);
        const dataPoints = userGrowthData.map(item => item.count);

        // Function to calculate cumulative data
        const cumulativeData = ((arr) => {
            let result = [];
            arr.reduce((acc, val, i) => result[i] = acc + val, 0);
            return result;
        })(dataPoints);


        new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Cumulative Users',
                    data: cumulativeData,
                    borderColor: 'rgb(75, 192, 192)',
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        });
    } else {
        // Fallback for no data
        ctx.getContext('2d').fillText("No user growth data available.", 10, 50);
    }
});
