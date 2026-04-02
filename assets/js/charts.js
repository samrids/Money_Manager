/**
 * charts.js — Chart.js v3 wrapper functions
 * Mobile-optimized defaults
 */

var CHART_DEFAULTS = {
  font: { family: 'system-ui, -apple-system, sans-serif', size: 11 },
  colors: {
    income:  '#22c55e',
    expense: '#ef4444',
    grid:    'rgba(0,0,0,0.05)',
  }
};

/**
 * renderLineChart — รายรับ vs รายจ่ายรายวัน
 */
function renderLineChart(canvasId, labels, incomeData, expenseData) {
  var ctx = document.getElementById(canvasId);
  if (!ctx) return null;

  return new Chart(ctx, {
    type: 'line',
    data: {
      labels: labels,
      datasets: [
        {
          label: 'รายรับ',
          data: incomeData,
          borderColor: CHART_DEFAULTS.colors.income,
          backgroundColor: 'rgba(34,197,94,0.1)',
          borderWidth: 2,
          pointRadius: 2,
          fill: true,
          tension: 0.3
        },
        {
          label: 'รายจ่าย',
          data: expenseData,
          borderColor: CHART_DEFAULTS.colors.expense,
          backgroundColor: 'rgba(239,68,68,0.1)',
          borderWidth: 2,
          pointRadius: 2,
          fill: true,
          tension: 0.3
        }
      ]
    },
    options: {
      responsive: true,
      maintainAspectRatio: true,
      interaction: { mode: 'index', intersect: false },
      plugins: {
        legend: {
          position: 'top',
          labels: { font: CHART_DEFAULTS.font, boxWidth: 12, padding: 8 }
        },
        tooltip: {
          callbacks: {
            label: function(ctx) {
              return ctx.dataset.label + ': ฿' + Number(ctx.parsed.y).toLocaleString('th-TH', { minimumFractionDigits: 0 });
            }
          }
        }
      },
      scales: {
        x: {
          grid: { color: CHART_DEFAULTS.colors.grid },
          ticks: { font: CHART_DEFAULTS.font, maxTicksLimit: 8 }
        },
        y: {
          grid: { color: CHART_DEFAULTS.colors.grid },
          ticks: {
            font: CHART_DEFAULTS.font,
            callback: function(val) {
              if (val >= 1000) return '฿' + (val/1000).toFixed(0) + 'K';
              return '฿' + val;
            }
          }
        }
      }
    }
  });
}

/**
 * renderPieChart — สัดส่วนรายจ่ายตามหมวดหมู่
 */
function renderPieChart(canvasId, labels, data, colors) {
  var ctx = document.getElementById(canvasId);
  if (!ctx) return null;

  // fallback colors ถ้าไม่ได้ส่งมา
  var defaultColors = [
    '#6366f1','#f97316','#22c55e','#ef4444','#f59e0b',
    '#8b5cf6','#06b6d4','#ec4899','#14b8a6','#84cc16'
  ];
  var bgColors = (colors && colors.length) ? colors : defaultColors;

  return new Chart(ctx, {
    type: 'doughnut',
    data: {
      labels: labels,
      datasets: [{
        data: data,
        backgroundColor: bgColors,
        borderWidth: 2,
        borderColor: '#fff'
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: true,
      cutout: '60%',
      plugins: {
        legend: {
          position: 'bottom',
          labels: { font: CHART_DEFAULTS.font, boxWidth: 10, padding: 8 }
        },
        tooltip: {
          callbacks: {
            label: function(ctx) {
              var total = ctx.dataset.data.reduce(function(a,b){ return a+b; }, 0);
              var pct   = total > 0 ? ((ctx.parsed / total) * 100).toFixed(1) : 0;
              return ctx.label + ': ฿' + Number(ctx.parsed).toLocaleString('th-TH') + ' (' + pct + '%)';
            }
          }
        }
      }
    }
  });
}

/**
 * renderBarChart — เปรียบเทียบรายจ่ายแยกหมวดหมู่
 */
function renderBarChart(canvasId, labels, data, colors) {
  var ctx = document.getElementById(canvasId);
  if (!ctx) return null;

  var defaultColors = [
    '#6366f1','#f97316','#22c55e','#ef4444','#f59e0b',
    '#8b5cf6','#06b6d4','#ec4899','#14b8a6','#84cc16'
  ];
  var bgColors = (colors && colors.length) ? colors : defaultColors;

  return new Chart(ctx, {
    type: 'bar',
    data: {
      labels: labels,
      datasets: [{
        label: 'รายจ่าย',
        data: data,
        backgroundColor: bgColors,
        borderRadius: 6,
        borderSkipped: false
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: true,
      indexAxis: 'y',  // horizontal bar สำหรับ mobile
      plugins: {
        legend: { display: false },
        tooltip: {
          callbacks: {
            label: function(ctx) {
              return '฿' + Number(ctx.parsed.x).toLocaleString('th-TH', { minimumFractionDigits: 0 });
            }
          }
        }
      },
      scales: {
        x: {
          grid: { color: CHART_DEFAULTS.colors.grid },
          ticks: {
            font: CHART_DEFAULTS.font,
            callback: function(val) {
              if (val >= 1000) return '฿' + (val/1000).toFixed(0) + 'K';
              return '฿' + val;
            }
          }
        },
        y: {
          grid: { display: false },
          ticks: { font: CHART_DEFAULTS.font }
        }
      }
    }
  });
}
