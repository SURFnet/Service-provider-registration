(function ($, Highcharts) {
    "use strict";

    console.log('loading dashboard',Highcharts);

    function Dashboard() {
        this.styleSwitch();
        this.bindSwitch();
        this.bindControls();
        this.loadCurrentStatusesChart();
        this.loadStatusHistoryChart();
    }
    Dashboard.prototype.switchSelector = '#enable-date-selection';
    Dashboard.prototype.currentStatusChartSelector = '#current-status-chart';
    Dashboard.prototype.statusHistoryChartSelector = '#status-history-chart';
    Dashboard.prototype.previousQuarterButtonSelector  = '#status-history-prev';
    Dashboard.prototype.nextQuarterButtonSelector  = '#status-history-next';
    Dashboard.prototype.currentStatusChart = null;
    Dashboard.prototype.statusHistoryChart = null;
    Dashboard.prototype.quarterMonths = {
        1: ['January', 'March'],
        2: ['April', 'June'],
        3: ['July', 'September'],
        4: ['October', 'December']
    };
    Dashboard.prototype.getYearlyQuarter = function(d) {
        var date = d || new Date();
        return Math.floor(date.getMonth() / 3) + 1;
    };
    Dashboard.prototype.currentYear = (new Date()).getFullYear();
    Dashboard.prototype.currentQuarter = Dashboard.prototype.getYearlyQuarter();
    Dashboard.prototype.year = (new Date()).getFullYear();
    Dashboard.prototype.quarter = Dashboard.prototype.getYearlyQuarter();
    Dashboard.prototype.styleSwitch = function() {
        $(this.switchSelector).bootstrapSwitch();
    };
    Dashboard.prototype.bindSwitch = function() {
        var that = this;
        $(this.switchSelector).on('click', function() {
            if (this.value) {
                $(this.currentStatusChartSelector).hide();
                $(this.statusHistoryChartSelector).show()
            }
            else {
                $(this.currentStatusChartSelector).show();
                $(this.statusHistoryChartSelector).hide();
            }
        });
    };
    Dashboard.prototype.previousQuarter = function() {
        if (this.quarter <= 1) {
            this.quarter = 4;
            this.year--;
            return;
        }

        this.quarter--;
    };
    Dashboard.prototype.nextQuarter = function() {
        if (this.quarter >= 4) {
            this.quarter = 1;
            this.year++;
        }

        this.quarter++;
    };
    Dashboard.prototype.bindControls = function() {
        var that = this;
        $(this.previousQuarterButtonSelector).on('click', function() {
            that.previousQuarter();
            that.loadStatusHistoryData();

            $(that.nextQuarterButtonSelector).attr('disabled', '');
        });
        $(this.nextQuarterButtonSelector).on('click', function() {
            that.nextQuarter();
            that.loadStatusHistoryData();

            if (that.year !== that.currentYear) {
                return;
            }
            if (that.quater !== that.currentQuarter) {
                return;
            }
            $(that.nextQuarterButtonSelector).attr('disabled', 'disabled');
        });
    };
    Dashboard.prototype.loadPerStatusData = function() {
        var that = this;
        $.ajax({
            dataType: "json",
            url: "status/current.json",
            success: function (data) {
                that.currentStatusChart.series[0].setData([data.draft]);
                that.currentStatusChart.series[1].setData([data.published]);
                that.currentStatusChart.series[2].setData([data.finished]);
            }
        });
    };
    Dashboard.prototype.loadStatusHistoryData = function() {
        var fromStrtotime = 'first day in ' +
                            this.quarterMonths[this.quarter] +
                            ' ' +
                            this.year,
            toStrtotime   = 'last day in ' + this.quarterMonths[this.quarter] +
                            ' ' +
                            this.year,
            that = this;

        $.ajax({
            dataType: "json",
            url: "status/history.json?from=" +
                    encodeURIComponent(fromStrtotime) +
                    'to=' +
                    encodeURIComponent(toStrtotime),
            success: function (data) {
                that.statusHistoryChart.series[0].setData([+data[0].total]);
                that.statusHistoryChart.series[1].setData([+data[1].total]);
                that.statusHistoryChart.series[2].setData([+data[2].total]);
            }
        });
    };
    Dashboard.prototype.loadCurrentStatusesChart = function() {
        this.currentStatusChart = new Highcharts.Chart({
            chart: {"renderTo": "current-status-chart", "backgroundColor": "#f8f8f8"},
            series: [
                {"name": "Draft", "data": [0], "color": "white", "type": "column"},
                {
                    "name": "Published",
                    "data": [0],
                    "color": "#d9edf7",
                    "type": "column"
                },
                {"name": "Final", "data": [0], "color": "#dff0d8", "type": "column"}
            ],
            title: {"text": "Registrations per status"},
            xAxis: {"title": {"text": "Status"}},
            yAxis: {
                "allowDecimals": false,
                "title": {"text": "Number of registrations"}
            }
        });
    };
    Dashboard.prototype.loadStatusHistoryChart = function() {
        this.statusHistoryChart = new Highcharts.Chart({
            chart: {
                "renderTo": "status-history-chart",
                "backgroundColor": "#f8f8f8"
            },
            series: [
                {
                    "name": "Created",
                    "data": [0],
                    "color": "white",
                    "type": "column"
                },
                {
                    "name": "Published",
                    "data": [0],
                    "color": "#d9edf7",
                    "type": "column"
                },
                {
                    "name": "Finalized",
                    "data": [0],
                    "color": "#dff0d8",
                    "type": "column"
                }
            ],
            title: {"text": "Registrations per status"},
            xAxis: {"title": {"text": "Status"}},
            yAxis: {
                "allowDecimals": false,
                "title": {"text": "Number of registrations"}
            }
        });
    };

    return new Dashboard();
}(window.jQuery, window.Highcharts));

