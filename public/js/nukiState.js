document.addEventListener('DOMContentLoaded', () => {
    // === Chart Setup ===
    const stateChartCanvas = document.getElementById('stateChart');
    const ctx = stateChartCanvas.getContext('2d');
    let onlineCount = parseInt(stateChartCanvas.dataset.onlineCount, 10);
    let offlineCount = parseInt(stateChartCanvas.dataset.offlineCount, 10);

    const chartData = {
        labels: ['Online', 'Offline'],
        datasets: [{
            label: 'Smartlock States',
            data: [onlineCount, offlineCount],
            backgroundColor: [
                'rgba(42,157,143, 0.6)',
                'rgba(231,76,60, 0.6)'
            ],
            borderColor: [
                'rgba(42,157,143, 1)',
                'rgba(231,76,60, 1)'
            ],
            borderWidth: 1,
            barThickness: 50
        }]
    };

    const chartOptions = {
        responsive: true,
        plugins: {
            legend: { display: true, position: 'top' },
            title: { display: true, text: 'Smartlock State Overview' }
        },
        scales: {
            x: { grid: { display: false } },
            y: { beginAtZero: true }
        }
    };

    const stateChart = new Chart(ctx, {
        type: 'bar',
        data: chartData,
        options: chartOptions
    });

    // === Search Bar Logic for Detailed History ===
    const searchInput = document.getElementById('searchInput');
    const historyTableBody = document.getElementById('historyTableBody');

    searchInput.addEventListener('keyup', function () {
        const filter = this.value.toLowerCase();
        const rows = historyTableBody.querySelectorAll('tr');
        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            row.style.display = text.includes(filter) ? '' : 'none';
        });
    });

    // === Real-Time Duration Update for "Ongoing" Entries in Detailed History ===
    function updateOngoingDurations() {
        const rows = historyTableBody.querySelectorAll('tr');
        rows.forEach(row => {
            const endDateCell = row.cells[4];
            const endTimeCell = row.cells[5];
            const durationCell = row.cells[6];
            if (endDateCell.textContent.trim() === 'Ongoing') {
                const startDateTimeStr = row.cells[2].textContent.trim() + " " + row.cells[3].textContent.trim();
                const startTime = new Date(startDateTimeStr);
                const now = new Date();
                const diffSec = Math.floor((now - startTime) / 1000);
                const days = Math.floor(diffSec / 86400);
                const hours = Math.floor((diffSec % 86400) / 3600);
                const minutes = Math.floor((diffSec % 3600) / 60);
                const seconds = diffSec % 60;
                const durationFormatted = (days > 0 ? days + " Day " : "") + 
                                          String(hours).padStart(2, '0') + ":" + 
                                          String(minutes).padStart(2, '0') + ":" + 
                                          String(seconds).padStart(2, '0');
                durationCell.textContent = durationFormatted;
            }
        });
    }
    setInterval(updateOngoingDurations, 1000);

    // === Chart Data Refresh Mechanism ===
    async function refreshChartData() {
        try {
            const response = await fetch('../controllers/Nuki_State_Controller.php?action=refresh_nuki_state');
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            const data = await response.json();
            stateChart.data.datasets[0].data = [data.onlineCount, data.offlineCount];
            stateChart.update();
        } catch (error) {
            console.error('Error refreshing chart data:', error);
        }
    }
    setInterval(refreshChartData, 60000);
});
