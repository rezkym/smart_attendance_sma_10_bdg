/**
 * Admin Dashboard
 * Handles dashboard charts and data refresh
 */

'use strict';

(function () {
  // Variables
  let weeklyTrendChart;

  // Chart colors
  const chartColors = {
    present: '#28c76f', // success
    late: '#ff9f43', // warning
    excused: '#00cfe8', // info
    sick: '#82868b', // secondary
    absent: '#ea5455' // danger
  };

  // Initialize
  document.addEventListener('DOMContentLoaded', function () {
    loadWeeklyTrendChart();
    initRefreshButton();
  });

  /**
   * Initialize refresh button
   */
  function initRefreshButton() {
    const refreshBtn = document.querySelector('#btn-refresh-trend');
    if (refreshBtn) {
      refreshBtn.addEventListener('click', function () {
        loadWeeklyTrendChart();
      });
    }
  }

  /**
   * Load weekly trend chart data
   */
  function loadWeeklyTrendChart() {
    fetch('/admin/dashboard/attendance-trend', {
      method: 'GET',
      headers: {
        Accept: 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
      }
    })
      .then(response => response.json())
      .then(result => {
        if (result.success) {
          renderWeeklyTrendChart(result.data);
        }
      })
      .catch(error => {
        console.error('Error loading attendance trend:', error);
      });
  }

  /**
   * Render weekly attendance trend chart
   */
  function renderWeeklyTrendChart(data) {
    const chartEl = document.querySelector('#weeklyTrendChart');

    if (!chartEl) return;

    // Destroy existing chart if any
    if (weeklyTrendChart) {
      weeklyTrendChart.destroy();
    }

    const chartConfig = {
      chart: {
        height: 350,
        type: 'area',
        parentHeightOffset: 0,
        toolbar: {
          show: false
        }
      },
      series: [
        {
          name: 'Hadir',
          data: data.datasets.present
        },
        {
          name: 'Alpha',
          data: data.datasets.absent
        },
        {
          name: 'Sakit',
          data: data.datasets.sick
        },
        {
          name: 'Izin',
          data: data.datasets.excused
        }
      ],
      colors: [chartColors.present, chartColors.absent, chartColors.sick, chartColors.excused],
      fill: {
        type: 'gradient',
        gradient: {
          shadeIntensity: 0.8,
          opacityFrom: 0.5,
          opacityTo: 0.1,
          stops: [0, 100]
        }
      },
      stroke: {
        width: 2,
        curve: 'smooth'
      },
      markers: {
        size: 4,
        strokeWidth: 2,
        hover: {
          size: 6
        }
      },
      xaxis: {
        categories: data.labels,
        axisBorder: {
          show: false
        },
        axisTicks: {
          show: false
        },
        labels: {
          style: {
            colors: config.colors.textMuted,
            fontSize: '13px'
          }
        }
      },
      yaxis: {
        min: 0,
        labels: {
          style: {
            colors: config.colors.textMuted,
            fontSize: '13px'
          }
        }
      },
      legend: {
        show: true,
        position: 'top',
        horizontalAlign: 'left',
        labels: {
          colors: config.colors.bodyColor
        }
      },
      grid: {
        borderColor: config.colors.borderColor,
        strokeDashArray: 4,
        padding: {
          top: -10
        }
      },
      tooltip: {
        shared: true,
        intersect: false,
        y: {
          formatter: function (val) {
            return val + ' siswa';
          }
        }
      },
      dataLabels: {
        enabled: false
      }
    };

    weeklyTrendChart = new ApexCharts(chartEl, chartConfig);
    weeklyTrendChart.render();
  }
})();
