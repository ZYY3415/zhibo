$(function () {
  //attr  [02/01,02/02]    月份/天数
  $('#container').highcharts({
    title: {               //标题
      text: '发送消息统计线状图',
      x: -20 //center
    },
    colors: ['blue', 'red'],
    plotOptions: {
      line: {
        lineWidth: 3
      },
      tooltip: {
        hideDelay: 200
      }
    },
    subtitle: {             //子标题
      text: 'Source: local_svncmf.com',
      x: -20
    },
    xAxis: {
      categories: attr
    },
    yAxis: {                //Y方向
      title: {              //标题
        text: '发送量'
      },
      plotLines: [{
        value: 0,
        width: 1
      }]
    },
    tooltip: {              //鼠标悬浮时显示
      valueSuffix: ' 条',   // 后缀
      crosshairs: true,     //十字准线
      shared: true
    },
    legend: {               //说明
      layout: 'vertical',    //布局
      align: 'right',
      verticalAlign: 'middle',
      borderWidth: 0
    },
    series: [{
      name: '发送消息量',
      color: 'rgba(124,181,236,1)',
      lineWidth: 2,
      data: [100, 40, 80, 70, 60, 90, 40]
    }]
  });

});

