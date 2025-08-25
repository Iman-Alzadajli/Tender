document.addEventListener('DOMContentLoaded', () => {

    // Bar Chart for Tender Quantities
    const ctxBar = document.getElementById('tenderBarChart');
    if (ctxBar) {
        const tenderData = JSON.parse(ctxBar.dataset.chartData);
        const tenderLabels = Object.keys(tenderData);
        const tenderValues = Object.values(tenderData);

        new Chart(ctxBar, {
            type: 'bar',
            data: {
                labels: tenderLabels,
                datasets: [{
                    label: 'Quantity',
                    data: tenderValues,
                    // الوان الأعمدة
                    backgroundColor: [
                        'rgba(54, 162, 235, 0.6)', // أزرقQ1
                        'rgba(75, 192, 192, 0.6)', //أخضرQ2
                        'rgba(255, 206, 86, 0.6)', //أصفرQ3
                        'rgba(255, 99, 132, 0.6)'  //أحمرQ4
                    ],
                    // لون الحدود مال الاعمدة
                    borderColor: [
                        'rgba(54, 162, 235, 1)',
                        'rgba(75, 192, 192, 1)',
                        'rgba(255, 206, 86, 1)',
                        'rgba(255, 99, 132, 1)'
                    ],
                    borderWidth: 1,
                    borderRadius: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: { beginAtZero: true, grid: { display: false } },
                    x: { grid: { display: false } }
                },
                plugins: { legend: { display: false } }
            }
        });
    }

    // Pie Chart for Client Types
    const ctxPie = document.getElementById('clientPieChart');
    if (ctxPie) {
        const clientData = JSON.parse(ctxPie.dataset.chartData);
        const clientLabels = Object.keys(clientData);
        const clientValues = Object.values(clientData);

        new Chart(ctxPie, {
            type: 'pie',
            data: {
                labels: clientLabels,
                datasets: [{
                    label: '# of Tenders',
                    data: clientValues,
                    backgroundColor: [
                        'rgba(13, 110, 253, 0.7)',
                        'rgba(25, 135, 84, 0.7)',
                        'rgba(255, 193, 7, 0.7)',
                        'rgba(220, 53, 69, 0.7)',
                        'rgba(108, 117, 125, 0.7)'
                    ],
                    borderColor: '#fff',
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { position: 'top' } }
            }
        });
    }
});
