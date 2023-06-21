function chartData(chartId, siteId, chartType) {
    return {
        data: null,
        fetch: function () {
            fetch(`${wpdChart.ajaxUrl}?action=wpd_site_stats&id=${siteId}&type=${chartType}`)
                .then(res => res.json())
                .then(res => {
                    this.data = res.data;
                    this.renderChart();
                })
        },
        renderChart: function () {
            new Chart(
                document.getElementById(chartId).getContext('2d'),
                {
                    type: "line",
                    data: {
                        labels: this.data.labels,
                        datasets: this.data.datasets
                    },
                    options: {},
                });
        }
    }
}
