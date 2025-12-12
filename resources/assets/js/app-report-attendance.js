/**
 * Attendance Report
 * Handles attendance report filtering, charts, and export
 */

'use strict';

(function () {
  // Variables
  let classroomSelect,
    startDatePicker,
    endDatePicker,
    applyFilterBtn,
    resetFilterBtn,
    exportBtn,
    reportContent,
    loadingState,
    emptyState,
    trendChart,
    donutChart,
    studentTable;

  // Chart colors from config
  const chartColors = {
    present: '#28c76f', // success
    late: '#ff9f43', // warning
    excused: '#00cfe8', // info
    sick: '#82868b', // secondary
    absent: '#ea5455' // danger
  };

  // Initialize
  document.addEventListener('DOMContentLoaded', function () {
    initElements();
    initSelect2();
    initFlatpickr();
    initEventListeners();
    showEmptyState();
  });

  /**
   * Initialize DOM elements
   */
  function initElements() {
    classroomSelect = document.querySelector('#filter-classroom');
    applyFilterBtn = document.querySelector('#btn-apply-filter');
    resetFilterBtn = document.querySelector('#btn-reset-filter');
    exportBtn = document.querySelector('#btn-export-report');
    reportContent = document.querySelector('#report-content');
    loadingState = document.querySelector('#loading-state');
    emptyState = document.querySelector('#empty-state');
  }

  /**
   * Initialize Select2
   */
  function initSelect2() {
    if (typeof jQuery !== 'undefined' && jQuery.fn.select2) {
      jQuery('#filter-classroom').select2({
        placeholder: 'Pilih Kelas',
        allowClear: true
      });

      // Enable apply button when classroom is selected
      jQuery('#filter-classroom').on('change', function () {
        const hasValue = !!this.value;
        applyFilterBtn.disabled = !hasValue;
      });
    }
  }

  /**
   * Initialize Flatpickr date pickers
   */
  function initFlatpickr() {
    const flatpickrConfig = {
      dateFormat: 'Y-m-d',
      allowInput: true
    };

    // Start date - default to start of current month
    startDatePicker = flatpickr('#filter-start-date', {
      ...flatpickrConfig,
      defaultDate: new Date(new Date().getFullYear(), new Date().getMonth(), 1)
    });

    // End date - default to today
    endDatePicker = flatpickr('#filter-end-date', {
      ...flatpickrConfig,
      defaultDate: new Date()
    });
  }

  /**
   * Initialize event listeners
   */
  function initEventListeners() {
    // Apply filter button
    if (applyFilterBtn) {
      applyFilterBtn.addEventListener('click', loadReport);
    }

    // Reset filter button
    if (resetFilterBtn) {
      resetFilterBtn.addEventListener('click', resetFilters);
    }

    // Export button
    if (exportBtn) {
      exportBtn.addEventListener('click', exportReport);
    }
  }

  /**
   * Show empty state
   */
  function showEmptyState() {
    reportContent?.classList.add('d-none');
    loadingState?.classList.add('d-none');
    emptyState?.classList.remove('d-none');
  }

  /**
   * Show loading state
   */
  function showLoadingState() {
    reportContent?.classList.add('d-none');
    emptyState?.classList.add('d-none');
    loadingState?.classList.remove('d-none');
  }

  /**
   * Show report content
   */
  function showReportContent() {
    loadingState?.classList.add('d-none');
    emptyState?.classList.add('d-none');
    reportContent?.classList.remove('d-none');
  }

  /**
   * Load report data
   */
  function loadReport() {
    const classroomId = classroomSelect?.value;
    if (!classroomId) {
      Swal.fire({
        icon: 'warning',
        title: 'Pilih Kelas',
        text: 'Silakan pilih kelas terlebih dahulu.'
      });
      return;
    }

    showLoadingState();

    const startDate = startDatePicker?.selectedDates[0]
      ? formatDate(startDatePicker.selectedDates[0])
      : '';
    const endDate = endDatePicker?.selectedDates[0] ? formatDate(endDatePicker.selectedDates[0]) : '';

    // Build query params
    const params = new URLSearchParams({
      classroom_id: classroomId
    });
    if (startDate) params.append('start_date', startDate);
    if (endDate) params.append('end_date', endDate);

    // Fetch report data
    fetch(`/admin/reports/attendance/by-classroom?${params.toString()}`, {
      method: 'GET',
      headers: {
        Accept: 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
      }
    })
      .then(response => response.json())
      .then(result => {
        if (result.success) {
          renderReport(result.data);
          loadTrendChart(classroomId, startDate, endDate);
          showReportContent();
          exportBtn.disabled = false;
        } else {
          showEmptyState();
          Swal.fire({
            icon: 'error',
            title: 'Error',
            text: result.message || 'Gagal memuat data laporan.'
          });
        }
      })
      .catch(error => {
        console.error('Error loading report:', error);
        showEmptyState();
        Swal.fire({
          icon: 'error',
          title: 'Error',
          text: 'Terjadi kesalahan saat memuat data laporan.'
        });
      });
  }

  /**
   * Render report data
   */
  function renderReport(data) {
    // Update summary cards
    const summary = data.summary;
    document.querySelector('#summary-total-records').textContent = summary.total_records;
    document.querySelector('#summary-present').textContent = summary.present;
    document.querySelector('#summary-present-pct').textContent = summary.present_percentage;
    document.querySelector('#summary-late').textContent = summary.late;
    document.querySelector('#summary-late-pct').textContent = summary.late_percentage;
    document.querySelector('#summary-excused').textContent = summary.excused;
    document.querySelector('#summary-excused-pct').textContent = summary.excused_percentage;
    document.querySelector('#summary-sick').textContent = summary.sick;
    document.querySelector('#summary-sick-pct').textContent = summary.sick_percentage;
    document.querySelector('#summary-absent').textContent = summary.absent;
    document.querySelector('#summary-absent-pct').textContent = summary.absent_percentage;
    document.querySelector('#summary-attendance-rate').textContent = summary.attendance_rate;

    // Render donut chart
    renderDonutChart(summary);

    // Render student table
    renderStudentTable(data.students);
  }

  /**
   * Render donut chart for status distribution
   */
  function renderDonutChart(summary) {
    const donutChartEl = document.querySelector('#statusDonutChart');

    if (!donutChartEl) return;

    // Destroy existing chart if any
    if (donutChart) {
      donutChart.destroy();
    }

    const donutConfig = {
      chart: {
        height: 350,
        type: 'donut',
        parentHeightOffset: 0
      },
      labels: ['Hadir', 'Terlambat', 'Izin', 'Sakit', 'Alpha'],
      series: [summary.present, summary.late, summary.excused, summary.sick, summary.absent],
      colors: [
        chartColors.present,
        chartColors.late,
        chartColors.excused,
        chartColors.sick,
        chartColors.absent
      ],
      stroke: {
        width: 0
      },
      dataLabels: {
        enabled: true,
        formatter: function (val, opts) {
          return opts.w.config.series[opts.seriesIndex];
        }
      },
      legend: {
        show: true,
        position: 'bottom',
        markers: {
          size: 6,
          strokeWidth: 0
        },
        labels: {
          colors: config.colors.bodyColor
        }
      },
      plotOptions: {
        pie: {
          donut: {
            size: '70%',
            labels: {
              show: true,
              name: {
                fontSize: '0.938rem',
                fontFamily: config.fontFamily
              },
              value: {
                fontSize: '1.5rem',
                fontWeight: 600,
                color: config.colors.headingColor,
                fontFamily: config.fontFamily
              },
              total: {
                show: true,
                fontSize: '0.813rem',
                fontFamily: config.fontFamily,
                color: config.colors.textMuted,
                label: 'Total'
              }
            }
          }
        }
      },
      responsive: [
        {
          breakpoint: 992,
          options: {
            chart: {
              height: 300
            }
          }
        }
      ]
    };

    donutChart = new ApexCharts(donutChartEl, donutConfig);
    donutChart.render();
  }

  /**
   * Load and render trend chart
   */
  function loadTrendChart(classroomId, startDate, endDate) {
    const params = new URLSearchParams({ classroom_id: classroomId });
    if (startDate) params.append('start_date', startDate);
    if (endDate) params.append('end_date', endDate);

    fetch(`/admin/reports/attendance/weekly-trend?${params.toString()}`, {
      method: 'GET',
      headers: {
        Accept: 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
      }
    })
      .then(response => response.json())
      .then(result => {
        if (result.success) {
          renderTrendChart(result.data);
        }
      })
      .catch(error => {
        console.error('Error loading trend chart:', error);
      });
  }

  /**
   * Render trend line chart
   */
  function renderTrendChart(data) {
    const trendChartEl = document.querySelector('#attendanceTrendChart');

    if (!trendChartEl) return;

    // Destroy existing chart if any
    if (trendChart) {
      trendChart.destroy();
    }

    const trendConfig = {
      chart: {
        height: 350,
        type: 'line',
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
      stroke: {
        width: 3,
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
        intersect: false
      }
    };

    trendChart = new ApexCharts(trendChartEl, trendConfig);
    trendChart.render();
  }

  /**
   * Render student table
   */
  function renderStudentTable(students) {
    const tbody = document.querySelector('#studentReportBody');
    if (!tbody) return;

    let html = '';
    students.forEach((student, index) => {
      const attendanceClass =
        student.attendance_rate >= 80 ? 'success' : student.attendance_rate >= 60 ? 'warning' : 'danger';

      html += `
        <tr>
          <td>${index + 1}</td>
          <td><strong>${student.name}</strong></td>
          <td>${student.nisn || '-'}</td>
          <td class="text-center"><span class="badge bg-label-success">${student.present}</span></td>
          <td class="text-center"><span class="badge bg-label-warning">${student.late}</span></td>
          <td class="text-center"><span class="badge bg-label-info">${student.excused}</span></td>
          <td class="text-center"><span class="badge bg-label-secondary">${student.sick}</span></td>
          <td class="text-center"><span class="badge bg-label-danger">${student.absent}</span></td>
          <td class="text-center">
            <span class="badge bg-${attendanceClass}">${student.attendance_rate}%</span>
          </td>
        </tr>
      `;
    });

    tbody.innerHTML = html || '<tr><td colspan="9" class="text-center">Tidak ada data siswa</td></tr>';

    // Initialize DataTable if not already
    if (typeof jQuery !== 'undefined' && jQuery.fn.DataTable) {
      const dt = jQuery('#studentReportTable');
      if (jQuery.fn.DataTable.isDataTable(dt)) {
        dt.DataTable().destroy();
      }
      dt.DataTable({
        dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>t<"row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>',
        pageLength: 25,
        order: [[8, 'desc']],
        language: {
          search: '',
          searchPlaceholder: 'Cari siswa...',
          lengthMenu: '_MENU_ data per halaman',
          info: 'Menampilkan _START_ - _END_ dari _TOTAL_ siswa',
          paginate: {
            first: '«',
            last: '»',
            next: '›',
            previous: '‹'
          }
        }
      });
    }
  }

  /**
   * Reset filters
   */
  function resetFilters() {
    // Reset Select2
    if (typeof jQuery !== 'undefined') {
      jQuery('#filter-classroom').val('').trigger('change');
    }

    // Reset date pickers to defaults
    if (startDatePicker) {
      startDatePicker.setDate(new Date(new Date().getFullYear(), new Date().getMonth(), 1));
    }
    if (endDatePicker) {
      endDatePicker.setDate(new Date());
    }

    // Disable buttons
    applyFilterBtn.disabled = true;
    exportBtn.disabled = true;

    // Show empty state
    showEmptyState();

    // Destroy charts
    if (trendChart) {
      trendChart.destroy();
      trendChart = null;
    }
    if (donutChart) {
      donutChart.destroy();
      donutChart = null;
    }
  }

  /**
   * Export report to CSV
   */
  function exportReport() {
    const classroomId = classroomSelect?.value;
    const startDate = startDatePicker?.selectedDates[0]
      ? formatDate(startDatePicker.selectedDates[0])
      : '';
    const endDate = endDatePicker?.selectedDates[0] ? formatDate(endDatePicker.selectedDates[0]) : '';

    if (!classroomId || !startDate || !endDate) {
      Swal.fire({
        icon: 'warning',
        title: 'Data Tidak Lengkap',
        text: 'Pastikan kelas dan periode sudah dipilih untuk export.'
      });
      return;
    }

    // Build export URL
    const params = new URLSearchParams({
      classroom_id: classroomId,
      start_date: startDate,
      end_date: endDate
    });

    // Open download in new tab/window
    window.location.href = `/admin/reports/attendance/export?${params.toString()}`;
  }

  /**
   * Format date to YYYY-MM-DD
   */
  function formatDate(date) {
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');
    return `${year}-${month}-${day}`;
  }
})();
