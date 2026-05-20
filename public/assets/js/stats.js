const stats = JSON.parse(document.getElementById('stats-data').dataset.stats);
const labels = stats.map(s => s.menu);

new Chart(document.getElementById('chartCommandes'), {
    type: 'bar',
    data: {
        labels,
        datasets: [{
            label: 'Nb commandes',
            data: stats.map(s => s.nb),
            backgroundColor: 'rgba(54, 162, 235, 0.7)',
        }]
    }
});

new Chart(document.getElementById('chartCA'), {
    type: 'bar',
    data: {
        labels,
        datasets: [{
            label: 'CA total (€)',
            data: stats.map(s => s.ca),
            backgroundColor: 'rgba(255, 159, 64, 0.7)',
        }]
    }
});