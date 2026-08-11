/**
 * Dashboard KPI charts (Chart.js).
 * Reads its data from window.dashboardChartData, set by a small inline
 * <script> block in dashboard.php (just a JSON assignment — no `new X(...)`
 * calls live in the .php file, which is what was confusing the PHP
 * Namespace Resolver editor extension into flagging `Chart` as an
 * unimported PHP class).
 */
document.addEventListener('DOMContentLoaded', function() {
    const chartData = window.dashboardChartData || {};

    // Doughnut: Status Distribution
    const statusLabels = chartData.statusLabels || [];
    const statusData = chartData.statusData || [];
    if (statusLabels.length) {
        new Chart(document.getElementById('statusDoughnutChart'), {
            type: 'doughnut',
            data: {
                labels: statusLabels,
                datasets: [{
                    data: statusData,
                    backgroundColor: ['#16a34a', '#2563eb', '#FBBF24', '#6b7280', '#dc2626'],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { boxWidth: 10, font: { size: 10 } }
                    }
                },
                cutout: '70%'
            }
        });
    }

    // Bar: Assets by Account
    const accountLabels = chartData.accountLabels || [];
    const accountData = chartData.accountData || [];
    if (accountLabels.length) {
        new Chart(document.getElementById('accountBarChart'), {
            type: 'bar',
            data: {
                labels: accountLabels,
                datasets: [{
                    label: 'Assets',
                    data: accountData,
                    backgroundColor: '#15803d',
                    borderRadius: 4
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: { beginAtZero: true }
                }
            }
        });
    }

    // Bar: Assets by Office (horizontal)
    const officeLabels = chartData.officeLabels || [];
    const officeData = chartData.officeData || [];
    if (officeLabels.length) {
        new Chart(document.getElementById('officeBarChart'), {
            type: 'bar',
            data: {
                labels: officeLabels,
                datasets: [{
                    label: 'Assets',
                    data: officeData,
                    backgroundColor: '#2563eb',
                    borderRadius: 4
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: { beginAtZero: true }
                },
                indexAxis: 'y'
            }
        });
    }
});