<div class="col-lg-4 col-xs-12">
	<!-- small box -->
	<div class="small-box bg-maroon">
		<div class="inner">
			<h3>{{ number_format($total_fleet_types, 0, $settings['decimal_seperator'], $settings['thousand_seperator']) }}</h3>
			<p>Fleet Types</p>
		</div>
		<div class="icon">
			<i class="fa fa-filter"></i>
		</div>
		<a href="{{ action('SrpFleetTypeController@index') }}" class="small-box-footer">
			Configure Fleet Types <i class="fa fa-arrow-circle-right"></i>
		</a>
	</div>
</div><!-- ./col -->

<div class="col-lg-4 col-xs-12">
	<!-- small box -->
	<div class="small-box bg-maroon">
		<div class="inner">
			<h3>{{ number_format($total_ships, 0, $settings['decimal_seperator'], $settings['thousand_seperator']) }}</h3>
			<p>Ships</p>
		</div>
		<div class="icon">
			<i class="fa fa-rocket"></i>
		</div>
		<a href="{{ action('SrpShipController@index') }}" class="small-box-footer">
			Configure Ships <i class="fa fa-arrow-circle-right"></i>
		</a>
	</div>
</div><!-- ./col -->

<div class="col-lg-4 col-xs-12">
	<!-- small box -->
	<div class="small-box bg-maroon">
		<div class="inner">
			<h3>{{ number_format($total_doctrines, 0, $settings['decimal_seperator'], $settings['thousand_seperator']) }}</h3>
			<p>Doctrines</p>
		</div>
		<div class="icon">
			<i class="fa fa-university"></i>
		</div>
		<a href="{{ action('SrpDoctrineController@index') }}" class="small-box-footer">
			Configure Doctrines <i class="fa fa-arrow-circle-right"></i>
		</a>
	</div>
</div><!-- ./col -->
