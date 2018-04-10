function onlineUserCount() {

    $.ajax({
        type: "GET",
        url: $('#container_online').attr('index'),
        data: {},
        dataType: "json",
        success: function (data) {

            if (data.code == 1) {
                //在线人数统计
                $('#container_online').highcharts({
                    chart: {
                        type: 'column'
                    },
                    title: {               //标题
                        text: '在线人数统计线状图',
                        x: -20 //center
                    },
                    plotOptions: {
                        series: {
                            stacking: 'normal'
                        }
                    },
                    subtitle: {             //子标题
                        text: '每日在线人数统计',
                        x: -20
                    },
                    xAxis: {
                        categories: data.data.categories
                    },
                    yAxis: {                //Y方向
                        title: {              //标题
                            text: '在线人数'
                        },
                        plotLines: [{
                            value: 0,
                            width: 1
                        }]
                    },
                    tooltip: {              //鼠标悬浮时显示
                        valueSuffix: ' 个',   // 后缀
                        crosshairs: true,     //十字准线
                        shared: true
                    },
                    legend: {               //说明
                        align: 'right',
                        verticalAlign: 'middle',
                        borderWidth: 0,
                        reversed: true

                    },
                    series: data.data.series
                });
            }
        }
    });

}
$(function () {
    // Create the chart
    onlineUserCount();
    $.ajax({
        url: 'ajax_date_msg',
        dataType: 'json',
        success: function (data) {
            if (data.code == 1) {
                Highcharts.chart('container', {
                    chart: {
                        type: 'column'
                    },
                    title: {
                        text: '最近7天的消息发送数量'
                    },
                    subtitle: {
                        text: '点击可查看具体天数的房间消息发送量，数据来源: <a href="https://netmarketshare.com">netmarketshare.com</a>.'
                    },
                    xAxis: {
                        type: 'category'
                    },
                    yAxis: {
                        title: {
                            text: '消息数量'
                        }
                    },
                    legend: {
                        enabled: false
                    },
                    plotOptions: {               //数据列配置
                        series: {                   //通用数据列
                            borderWidth: 0,
                            dataLabels: {
                                enabled: true,
                                format: '{point.y}'
                            }
                        }
                    },
                    tooltip: {                          //数据提示框
                        headerFormat: '<span style="font-size:11px">{series.name}</span><br>',           //标题格式
                        pointFormat: '<span style="color:{point.color}">{point.name}</span>: <b>{point.y}</b> 条<br/>'     //数据点格式化字符串
                    },
                    series: [{                    //列
                        name: '消息发送量',
                        colorByPoint: true,
                        data: data.data.series
                    }],
                    drilldown: {
                        series: data.data.drilldown
                    }
                });
            }
        }
    });
});
/**
 * Created by Administrato on 2018/2/26.
 */
