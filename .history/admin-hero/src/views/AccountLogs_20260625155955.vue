const renderChart = (labels, data) => {
  const ctx = document.getElementById('playerChart').getContext('2d');

  if (window.myChart) {
    window.myChart.destroy();
  }

  // Gradient cột
  const barGradient = ctx.createLinearGradient(0, 0, 0, 450);
  barGradient.addColorStop(0, '#38bdf8');
  barGradient.addColorStop(0.5, '#3b82f6');
  barGradient.addColorStop(1, '#1d4ed8');

  // Gradient line
  const lineGradient = ctx.createLinearGradient(0, 0, 1000, 0);
  lineGradient.addColorStop(0, '#f59e0b');
  lineGradient.addColorStop(1, '#ef4444');

  window.myChart = new Chart(ctx, {
    data: {
      labels,
      datasets: [
        {
          type: 'bar',
          label: 'Người chơi mới',

          data,

          backgroundColor: barGradient,

          borderColor: '#93c5fd',
          borderWidth: 2,

          borderRadius: 20,
          borderSkipped: false,

          barThickness: 30,
          maxBarThickness: 35,

          hoverBackgroundColor: '#60a5fa'
        },

        {
          type: 'line',

          label: 'Xu hướng tăng trưởng',

          data,

          borderColor: lineGradient,

          borderWidth: 4,

          pointRadius: 6,
          pointHoverRadius: 10,

          pointBackgroundColor: '#fbbf24',
          pointBorderColor: '#ffffff',
          pointBorderWidth: 2,

          tension: 0.45,

          fill: false
        }
      ]
    },

    options: {
      responsive: true,
      maintainAspectRatio: false,

      interaction: {
        intersect: false,
        mode: 'index'
      },

      plugins: {
        legend: {
          position: 'top',

          labels: {
            color: '#ffffff',

            font: {
              size: 14,
              weight: 'bold'
            },

            padding: 20
          }
        },

        tooltip: {
          backgroundColor: '#020617',

          titleColor: '#ffffff',
          bodyColor: '#ffffff',

          padding: 14,

          cornerRadius: 12,

          borderColor: '#3b82f6',
          borderWidth: 1
        }
      },

      scales: {
        x: {
          grid: {
            display: false
          },

          ticks: {
            color: '#cbd5e1',

            font: {
              weight: 'bold'
            }
          }
        },

        y: {
          beginAtZero: true,

          ticks: {
            precision: 0,

            color: '#cbd5e1'
          },

          grid: {
            color: 'rgba(255,255,255,0.08)'
          }
        }
      },

      animation: {
        duration: 1500,
        easing: 'easeOutQuart'
      }
    },

    plugins: [
      {
        id: 'valueLabels',

        afterDatasetsDraw(chart) {
          const { ctx } = chart;

          const meta = chart.getDatasetMeta(0);

          meta.data.forEach((bar, index) => {
            const value = data[index];

            ctx.save();

            ctx.fillStyle = '#ffffff';

            ctx.font = 'bold 13px Inter';

            ctx.textAlign = 'center';

            ctx.shadowColor = '#60a5fa';
            ctx.shadowBlur = 15;

            ctx.fillText(
              value,
              bar.x,
              bar.y - 12
            );

            ctx.restore();
          });
        }
      }
    ]
  });
};