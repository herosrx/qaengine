/**
 * site overview render chart for post type
*/
(function (Models, Views, $, Backbone) {
	Views.Overview	=	Backbone.View.extend({
		initialize : function () {

			/**
			 * get data
			*/
			this.daily		=	JSON.parse( $('#daily_data').html() );
			this.weekly		=	JSON.parse( $('#weekly_data').html() );
			this.monthly	=	JSON.parse( $('#monthly_data').html() );

			this.series		=	[];

			var colors		=	['#FF8F00', '#2385DF', '#DF2323' , '#3814AA'];

			/**
			 * generate series label
			*/
			for (var i = this.daily.length - 1; i >= 0 ; i-- ) {
				this.series.push( { label : this.daily[i]['label'] , color : colors[i] } );
			};
			/**
			 * init jqplot chart opitons
			*/
			this.jqplotOptions();
			/**
			 * call function to render chart
			*/

			this.dailyChart();
			this.weeklyChart();
			this.monthlyChart();			

		},

		/**
		 * render weekly overview in 2 weeks
		*/
		dailyChart: function () {
			var max = 0;
			/**
			 * set daily overview title
			*/
			this.jqplotOpt.title	=	this.daily[0]['title'];
			/**
			 * set interval to 4 days
			*/
			this.jqplotOpt.axes.xaxis.tickInterval = '1 days';
			/**
			 * setup jqplot axis min
			*/
			this.jqplotOpt.axes.xaxis.min		   = this.daily[0]['data'][0][0];
			/**
			 * render chart
			*/
			var data  = [];
			for (var i = this.daily.length - 1; i >= 0; i--) {
				data.push(this.daily[i]['data']);
				_.each( this.daily[i]['data'], function(element) {
					max = Math.max(max, element[1]);
				});
			};

			this.jqplotOpt.axes.yaxis.max = max+5;
			this.jqplotOpt.axes.yaxis.min = 0;

			var plot1 = $.jqplot('daily_chart', data , this.jqplotOpt );

		},

		/**
		 * render weekly overview in 3 months
		*/
		weeklyChart : function () {
			var max = 0;
			/**
			 * set weekly overview title
			*/
			this.jqplotOpt.title	=	this.weekly[0]['title'];
			/**
			 * set date interval to 1 week
			*/
			this.jqplotOpt.axes.xaxis.tickInterval = '1 week';
			/**
			 * set min week
			*/
			this.jqplotOpt.axes.xaxis.min		   = this.weekly[0]['data'][0][0];
			/**
			 * render 3 months overview
			*/
			var data  = [];
			for (var i = this.weekly.length - 1; i >= 0; i--) {
				data.push(this.weekly[i]['data']);
				_.each( this.weekly[i]['data'], function(element) {
					max = Math.max(max, element[1]);
				});
			};

			this.jqplotOpt.axes.yaxis.max = max+5;

			var plot1 = $.jqplot('weekly_chart', data, this.jqplotOpt );
		}, 

		/**
		 * render monthly chart 
		*/
		monthlyChart : function() {
			var max	=	0;
			/**
			 * set month title
			*/
			this.jqplotOpt.title	=	this.monthly[0]['title'];
			/**
			 * set the interval to 1 month
			*/
			this.jqplotOpt.axes.xaxis.tickInterval = '1 month';
			/**
			 * set min date of chart to the beginning date
			*/
			this.jqplotOpt.axes.xaxis.min		   = this.monthly[0]['data'][0][0];
			/**
			 * call to render chart
			*/
			var data  = [];
			for (var i = this.monthly.length - 1; i >= 0; i--) {
				data.push(this.monthly[i]['data']);
				_.each( this.monthly[i]['data'], function(element) {
					max = Math.max(max, element[1]);
				});
			};

			this.jqplotOpt.axes.yaxis.max = max+5;
			var plot1 = $.jqplot('monthly_chart', data , this.jqplotOpt );
		},

		/**
		 * init option for jqplot chart
		*/
		jqplotOptions	: function () {
			this.jqplotOpt	=  {
				axes:{
					xaxis:{
						renderer:$.jqplot.DateAxisRenderer,
						tickOptions:{
							formatString:'%b&nbsp;%#d'
						} ,
						tickInterval : '1 month',
						min:'May 14, 2014', 
					},
					yaxis:{
						// max: 27,
						tickOptions:{
							formatString:'%.0f'
						}
					}
				},

				highlighter: {
					show: true,
					sizeAdjust: 7.5
				},

				cursor: {
					show: false
				},

				legend: { show: true , location: 'nw'},	
				
				series : this.series  	
			}
		},

	});

	$(document).ready(function(){
		/**
		 * render overview
		*/
		var ae_verview = new Views.Overview();
		// 
	});

})( window.AE.Models, window.AE.Views, jQuery, Backbone );