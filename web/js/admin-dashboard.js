(function ($, Highcharts) {
    "use strict";

    function Dashboard() {
        this.styleSwitch();
        this.bindSwitch();
        this.bindControls();

        this.loadCurrentStatusesChart();
        this.loadStatusHistoryChart();

        this.loadCurrentStatusesData();
        this.loadStatusHistoryData();

        $(this.currentStatusChartSelector).show();
        $(this.statusHistoryChartSelector).hide();
    }
    Dashboard.prototype.switchSelector = '#enable-date-selection';
    Dashboard.prototype.currentStatusChartSelector = '#current-statuses-chart';
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
        $(this.switchSelector).on('switchChange.bootstrapSwitch', function(event, state) {
            if (state) {
                $(that.currentStatusChartSelector).hide();
                $(that.statusHistoryChartSelector).show();
                $('#chart-controls').slideDown();
            }
            else {
                $(that.currentStatusChartSelector).show();
                $(that.statusHistoryChartSelector).hide();
                $('#chart-controls').slideUp();
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

            $(that.nextQuarterButtonSelector).removeAttr('disabled');
        });
        $(this.nextQuarterButtonSelector).on('click', function() {
            that.nextQuarter();
            that.loadStatusHistoryData();

            if (that.year !== that.currentYear) {
                return;
            }
            if (that.quarter !== that.currentQuarter) {
                return;
            }
            $(that.nextQuarterButtonSelector).attr('disabled', 'disabled');
        });
    };
    Dashboard.prototype.loadCurrentStatusesData = function() {
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
        var fromStrtotime = 'first day of ' +
                            this.quarterMonths[this.quarter][0] +
                            ' ' +
                            this.year,
            toStrtotime   = 'last day of ' +
                            this.quarterMonths[this.quarter][1] +
                            ' ' +
                            this.year,
            that = this;

        $.ajax({
            dataType: "json",
            url: "status/history.json?from=" +
                    encodeURIComponent(fromStrtotime) +
                    '&to=' +
                    encodeURIComponent(toStrtotime),
            success: function (data) {
                var draft = 0, published = 0, finished = 0;

                if (data.data[0] !== undefined) {
                    draft = +data.data[0].total;
                }
                if (data.data[1] !== undefined) {
                    published = +data.data[1].total;
                }
                if (data.data[2] !== undefined) {
                    finished = +data.data[2].total;
                }
                that.statusHistoryChart.series[0].setData([draft]);
                that.statusHistoryChart.series[1].setData([published]);
                that.statusHistoryChart.series[2].setData([finished]);

                that.statusHistoryChart.setTitle({
                    text: "Status changes in Q" + that.quarter + " " + that.year
                });
            }
        });
    };
    Dashboard.prototype.loadCurrentStatusesChart = function() {
        this.currentStatusChart = new Highcharts.Chart({
            chart: {"renderTo": "current-statuses-chart", "backgroundColor": "#f8f8f8"},
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
            title: {"text": "Subscriptions per status"},
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
            title: {"text": "Status changes this quarter"},
            xAxis: {"title": {"text": "Status"}},
            yAxis: {
                "allowDecimals": false,
                "title": {"text": "Number of registrations"}
            }
        });
    };

    return new Dashboard();
}(window.jQuery, window.Highcharts));

