$(document).ready(function() {
  if (typeof data !== 'undefined') {
    loadGraph();
  }
});

function loadGraph() {
  var totals = [];
  var averages = [];
  var keys = [];
  for (var a in data) {
    keys.push(a);
    totals.push(data[a].total);
    averages.push(data[a].average);
  }
  $('#graph').highcharts({
    chart: {
      type: 'column'
    },
    title: {
      text: 'Class Average Attendance Overview'
    },
    xAxis: {
      categories: keys,
      title: { text: 'Course & Section' }
    },
    yAxis: {
      min: 0,
      title: { text: 'Number of Sessions' }
    },
    plotOptions: {
      column: {
        pointPadding: 0.2,
        borderWidth: 0,
        borderRadius: 4
      }
    },
    series: [{
      name: 'Total Sessions Conducted',
      data: totals,
      color: '#4f46e5'
    }, {
      name: 'Average Attended Per Student',
      data: averages,
      color: '#10b981'
    }]
  });
}
