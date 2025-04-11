function executeStatistJavaScript() {
    console.log("Chart script executed"); // Debugging

    // Timeframe selector functionality
    const selectButton = document.getElementById('select-button');
    const selectOptions = document.getElementById('select-options');
    const selectedValue = document.getElementById('selected-value');

    // Toggle dropdown visibility
    selectButton.addEventListener('click', function(e) {
        e.stopPropagation();
        const isExpanded = selectButton.getAttribute('aria-expanded') === 'true';
        selectButton.setAttribute('aria-expanded', !isExpanded);
        selectOptions.classList.toggle('hidden');
    });

    // Handle option selection
    selectOptions.querySelectorAll('li').forEach(option => {
        option.addEventListener('click', function() {
            const value = this.getAttribute('data-value');
            selectedValue.textContent = value;
            selectButton.setAttribute('aria-expanded', 'false');
            selectOptions.classList.add('hidden');
            
            // Set cookie
            document.cookie = `timeframe=${value}; path=/; max-age=${30 * 24 * 60 * 60}`;
            
            // Get current URL
            const currentUrl = new URL(window.location.href);
            
            // Get the existing contentpage parameter
            const contentPage = currentUrl.searchParams.get('contentpage');
            
            // Create new URL with updated timeframe
            const newUrl = new URL(window.location.origin + window.location.pathname);
            
            // Add the parameters back
            newUrl.searchParams.set('contentpage', contentPage);
            newUrl.searchParams.set('timeframe', value);
            
            // Preserve other existing parameters
            currentUrl.searchParams.forEach((val, key) => {
                if (key !== 'contentpage' && key !== 'timeframe') {
                    newUrl.searchParams.set(key, val);
                }
            });
            
            // Navigate to the new URL
            window.location.href = newUrl.toString();
        });
    });

    // Close dropdown when clicking outside
    document.addEventListener('click', function() {
        selectButton.setAttribute('aria-expanded', 'false');
        selectOptions.classList.add('hidden');
    });


    // Donut Chart Configuration
    const donutChartConfig = {
        series: data,
        colors: ["#1C64F2", "#16BDCA", "#F4C616", "#E74694", "#7E3AF2", "#FF5A1F", "#F4C616"],
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
                                const sum = w.globals.seriesTotals.reduce((a, b) => a + b, 0);
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
        tooltip: {
            enabled: true,
            y: {
                formatter: function(value) {
                    return value + " pannes";
                }
            }
        }
    };

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
            height: 350,
            toolbar: {
                show: false,
            },
            zoom: {
                enabled: true
            }
        },
        title: {
            text: "Pannes Over Time",
            align: "left",
            style: {
                fontSize: "16px",
                fontWeight: "bold",
                color: "#333"
            }
        },
        dataLabels: {
            enabled: false,
        },
        colors: ["#1C64F2"],
        stroke: {
            width: 3,
            curve: "smooth",
        },
        markers: {
            size: 5,
            hover: {
                size: 7
            }
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
            tooltip: {
                enabled: false
            }
        },
        yaxis: {
            labels: {
                style: {
                    colors: "#616161",
                    fontSize: "12px",
                    fontFamily: "inherit",
                    fontWeight: 400,
                },
                formatter: function(val) {
                    return Math.floor(val) === val ? val : "";
                }
            },
            title: {
                text: "Number of Pannes",
                style: {
                    fontSize: "12px",
                    fontWeight: "bold",
                    color: "#333"
                }
            }
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
        tooltip: {
            enabled: true,
            y: {
                formatter: function(val) {
                    return val + " pannes";
                },
                title: {
                    formatter: function() {
                        return "";
                    }
                }
            },
            x: {
                formatter: function(val) {
                    return "Date: " + val;
                }
            }
        }
    };

    // Status Chart Configuration (Horizontal Bar)
    const statusChartConfig = {
        series: [{
            name: 'Pannes',
            data: statusData
        }],
        chart: {
            type: 'bar',
            height: 350,
            toolbar: {
                show: false
            }
        },
        plotOptions: {
            bar: {
                borderRadius: 4,
                horizontal: true,
            }
        },
        colors: ["#F4C616", "#1C64F2", "#16BDCA", "#E74694"],
        dataLabels: {
            enabled: false
        },
        xaxis: {
            categories: ['Nouveau', 'En cours', 'Résolu', 'Fermé'],
            labels: {
                style: {
                    colors: "#616161",
                    fontSize: "12px",
                    fontFamily: "inherit",
                    fontWeight: 400,
                }
            },
            title: {
                text: 'Number of Pannes',
                style: {
                    fontSize: "12px",
                    fontWeight: "bold",
                    color: "#333"
                }
            }
        },
        yaxis: {
            labels: {
                style: {
                    colors: "#616161",
                    fontSize: "12px",
                    fontFamily: "inherit",
                    fontWeight: 400,
                }
            }
        },
        tooltip: {
            y: {
                formatter: function(val) {
                    return val + " pannes";
                }
            }
        }
    };

    // Top Technicians Chart (Bar)
    const techChartConfig = {
        series: [{
            name: 'Resolved Pannes',
            data: topTechsData
        }],
        chart: {
            type: 'bar',
            height: 350,
            toolbar: {
                show: false
            }
        },
        plotOptions: {
            bar: {
                borderRadius: 4,
                horizontal: false,
            }
        },
        colors: ["#7E3AF2"],
        dataLabels: {
            enabled: false
        },
        xaxis: {
            categories: topTechsLabels,
            labels: {
                style: {
                    colors: "#616161",
                    fontSize: "12px",
                    fontFamily: "inherit",
                    fontWeight: 400,
                }
            }
        },
        yaxis: {
            labels: {
                style: {
                    colors: "#616161",
                    fontSize: "12px",
                    fontFamily: "inherit",
                    fontWeight: 400,
                }
            },
            title: {
                text: 'Number of Resolved Pannes',
                style: {
                    fontSize: "12px",
                    fontWeight: "bold",
                    color: "#333"
                }
            }
        },
        tooltip: {
            y: {
                formatter: function(val) {
                    return val + " pannes resolved";
                }
            }
        }
    };

    // Pannes by Establishment Chart (Bar)
    const etabChartConfig = {
        series: [{
            name: 'Pannes',
            data: pannesEtabData
        }],
        chart: {
            type: 'bar',
            height: 350,
            toolbar: {
                show: false
            }
        },
        plotOptions: {
            bar: {
                borderRadius: 4,
                horizontal: false,
            }
        },
        colors: ["#FF5A1F"],
        dataLabels: {
            enabled: false
        },
        xaxis: {
            categories: pannesEtabLabels,
            labels: {
                style: {
                    colors: "#616161",
                    fontSize: "12px",
                    fontFamily: "inherit",
                    fontWeight: 400,
                },
                formatter: function(value) {
                    return value.length > 15 ? value.substring(0, 15) + '...' : value;
                }
            }
        },
        yaxis: {
            labels: {
                style: {
                    colors: "#616161",
                    fontSize: "12px",
                    fontFamily: "inherit",
                    fontWeight: 400,
                }
            },
            title: {
                text: 'Number of Pannes',
                style: {
                    fontSize: "12px",
                    fontWeight: "bold",
                    color: "#333"
                }
            }
        },
        tooltip: {
            y: {
                formatter: function(val) {
                    return val + " pannes reported";
                }
            }
        }
    };

    // Render Donut Chart
    if (document.getElementById("donut-chart") && typeof ApexCharts !== 'undefined') {
        const donutChart = new ApexCharts(document.getElementById("donut-chart"), donutChartConfig);
        donutChart.render();
    }

    // Render Line Chart
    if (document.getElementById("line-chart") && typeof ApexCharts !== 'undefined') {
        const lineChart = new ApexCharts(document.getElementById("line-chart"), lineChartConfig);
        lineChart.render();
    }

    // Render Status Chart
    if (document.getElementById("status-chart") && typeof ApexCharts !== 'undefined') {
        const statusChart = new ApexCharts(document.getElementById("status-chart"), statusChartConfig);
        statusChart.render();
    }

    // Render Tech Chart
    if (document.getElementById("tech-chart") && typeof ApexCharts !== 'undefined') {
        const techChart = new ApexCharts(document.getElementById("tech-chart"), techChartConfig);
        techChart.render();
    }

    // Render Etab Chart
    if (document.getElementById("etab-chart") && typeof ApexCharts !== 'undefined') {
        const etabChart = new ApexCharts(document.getElementById("etab-chart"), etabChartConfig);
        etabChart.render();
    }
}

// Execute the function when the script is loaded
executeStatistJavaScript();