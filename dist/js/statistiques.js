function executeGererPnJavaScript() {
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
          colors: ["#1C64F2", "#16BDCA", "#FDBA8C", "#E74694"],
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

        // Get all the checkboxes by their class name
        const checkboxes = document.querySelectorAll('#devices input[type="checkbox"]');

        // Function to handle the checkbox change event
        function handleCheckboxChange(event, chart) {
            const checkbox = event.target;
            if (checkbox.checked) {
                switch (checkbox.value) {
                    case 'desktop':
                        chart.updateSeries([15.1, 22.5, 4.4, 8.4]);
                        break;
                    case 'tablet':
                        chart.updateSeries([25.1, 26.5, 1.4, 3.4]);
                        break;
                    case 'mobile':
                        chart.updateSeries([45.1, 27.5, 8.4, 2.4]);
                        break;
                    default:
                        chart.updateSeries([55.1, 28.5, 1.4, 5.4]);
                }
            } else {
                chart.updateSeries(data); // Reset to the original data
            }
        }

        // Attach the event listener to each checkbox
        checkboxes.forEach((checkbox) => {
            checkbox.addEventListener('change', (event) => handleCheckboxChange(event, chart));
        });
    }
}

  // Execute the function when the script is loaded
  executeGererPnJavaScript();