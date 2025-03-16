function executeStatistJavaScript() {
    console.log("Chart script executed"); // Debugging

    const selectButton = document.getElementById('select-button');
    const selectOptions = document.getElementById('select-options');
    const selectedValue = document.getElementById('selected-value');

    // Toggle dropdown visibility
    selectButton.addEventListener('click', function () {
        const isExpanded = selectButton.getAttribute('aria-expanded') === 'true';
        selectButton.setAttribute('aria-expanded', !isExpanded);
        selectOptions.classList.toggle('hidden', isExpanded);
    });

    // Handle option selection
    selectOptions.querySelectorAll('li').forEach(option => {
        option.addEventListener('click', function () {
            const value = this.getAttribute('data-value');
            selectedValue.textContent = value;
            selectButton.setAttribute('aria-expanded', 'false');
            selectOptions.classList.add('hidden');

            // Reload the page with the selected timeframe as a query parameter
            const url = new URL(window.location.href);
            url.searchParams.set('timeframe', value); // Add or update the 'timeframe' parameter
            window.location.href = url.toString(); // Reload the page
        });
    });

    // Close dropdown when clicking outside
    document.addEventListener('click', function (event) {
        if (!selectButton.contains(event.target) && !selectOptions.contains(event.target)) {
            selectButton.setAttribute('aria-expanded', 'false');
            selectOptions.classList.add('hidden');
        }
    });

    const getChartOptions = () => {
        return {
            series: data,
            colors: ["#1C64F2", "#F4C616", "#FDBA8C", "#E74694"],
            chart: {
                height: 320,
                width: "100%",
                type: "donut",
            },
            stroke: {
                colors: ["transparent"],
                lineCap: "",
            },
            plotOptions: {
                pie: {
                    donut: {
                        labels: {
                            show: true,
                            name: {
                                show: true,
                                fontFamily: "Inter, sans-serif",
                                offsetY: 20,
                            },
                            total: {
                                showAlways: true,
                                show: true,
                                label: "Total Pannes",
                                fontFamily: "Inter, sans-serif",
                                formatter: function (w) {
                                    const sum = w.globals.seriesTotals.reduce((a, b) => {
                                        return a + b;
                                    }, 0);
                                    return sum;
                                },
                            },
                            value: {
                                show: true,
                                fontFamily: "Inter, sans-serif",
                                offsetY: -20,
                                formatter: function (value) {
                                    return value;
                                },
                            },
                        },
                        size: "80%",
                    },
                },
            },
            grid: {
                padding: {
                    top: -2,
                },
            },
            labels: labels,
            dataLabels: {
                enabled: false,
            },
            legend: {
                position: "bottom",
                fontFamily: "Inter, sans-serif",
            },
            yaxis: {
                labels: {
                    formatter: function (value) {
                        return value;
                    },
                },
            },
            xaxis: {
                labels: {
                    formatter: function (value) {
                        return value;
                    },
                },
                axisTicks: {
                    show: false,
                },
                axisBorder: {
                    show: false,
                },
            },
        };
    };

    if (document.getElementById("donut-chart") && typeof ApexCharts !== 'undefined') {
        const chart = new ApexCharts(document.getElementById("donut-chart"), getChartOptions());
        chart.render();
    }

    // Line Chart Configuration
    const lineChartConfig = {
        series: [
            {
                name: "Pannes",
                data: lineData,
            },
        ],
        chart: {
            type: "line",
            height: 240,
            toolbar: {
                show: false,
            },
        },
        title: {
            show: "",
        },
        dataLabels: {
            enabled: false,
        },
        colors: ["#020617"],
        stroke: {
            lineCap: "round",
            curve: "smooth",
        },
        markers: {
            size: 0,
        },
        xaxis: {
            categories: lineLabels,
            axisTicks: {
                show: false,
            },
            axisBorder: {
                show: false,
            },
            labels: {
                style: {
                    colors: "#616161",
                    fontSize: "12px",
                    fontFamily: "inherit",
                    fontWeight: 400,
                },
            },

            categories: [
                "Jan",
                "Feb",
                "Mar",
                "Apr",
                "May",
                "Jun",
                "Jul",
                "Aug",
                "Sep",
                "Oct",
                "Nov",
                "Dec",
              ],
        },
        yaxis: {
            labels: {
                style: {
                    colors: "#616161",
                    fontSize: "12px",
                    fontFamily: "inherit",
                    fontWeight: 400,
                },
            },
        },
        grid: {
            show: true,
            borderColor: "#dddddd",
            strokeDashArray: 5,
            xaxis: {
                lines: {
                    show: true,
                },
            },
            padding: {
                top: 5,
                right: 20,
            },
        },
        fill: {
            opacity: 0.8,
        },
        tooltip: {
            theme: "dark",
        },
    };

    // Render Line Chart
    if (document.getElementById("line-chart") && typeof ApexCharts !== 'undefined') {
        const lineChart = new ApexCharts(document.getElementById("line-chart"), lineChartConfig);
        lineChart.render();
    }
    
}

// Execute the function when the script is loaded
executeStatistJavaScript();