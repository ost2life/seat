@extends('layouts.masterLayout')

@section('html_title', 'Configure Doctrines')

@section('page_content')

	<div class="row">
		<ol class="breadcrumb">
			<li><a>SRP</a></li>
			<li><a href="{{ action('SrpConfigController@index') }}">Configure</a></li>
			<li><a href="{{ action('SrpDoctrineController@index') }}">Doctrines</a></li>
			<li class="active">{{ $doctrine->name }}</li>
		</ol>
	</div>

	<div class="row">

		<div class="col-md-12">

			<div class="box">

				<div class="box-header">
					<h3 class="box-title">Edit Doctrine
					</h3>
				</div>

				<div class="box-body table-responsive">
					{{ Form::open(array('action' => array('SrpDoctrineController@update', $doctrine->id), 'method' => 'PUT', 'class' => 'form-horizontal')) }}
						<fieldset>
							<!-- Text Input -->
							<div class="form-group">
								<label class="col-md-3 control-label" for="name">Name</label>
								<div class="col-md-7">
									<div class="input-group">
										<span class="input-group-addon"><i class="fa fa-fw fa-pencil"></i></span>
										{{ Form::text('name', $doctrine->name, array('class' => 'form-control input-md')) }}
									</div>
								</div>
							</div>
							<!-- Button -->
							<div class="form-group">
								<label class="col-md-3 control-label"></label>
								<div class="col-md-7">
									<button class="btn btn-block btn-primary">Update</button>
								</div>
							</div>
						</fieldset>
					{{ Form::close() }}
				</div><!-- /.box-body -->

			</div><!-- /.box -->

		</div><!-- /.col -->

	</div><!-- ./row -->

	<div class="row">

		<div class="col-md-6">

			<div class="box">

				<div class="box-header">
					<h3 class="box-title">Assigned Ships</h3>
				</div>

				<div class="box-body">
					<table class="table table-condensed compact table-hover" id="datatable" style="white-space: nowrap;">
						<thead>
							<tr>
								<th style="width: 99%;">Name</th>
								<th>Ship</th>
								<th>Value</th>
								<th></th>
							</tr>
						</thead>
						<tbody>
							@foreach($doctrine->ships as $ship)
							<tr>
								<td>{{ $ship->name }}</td>
								<td>{{ $ship->type()->first()->typeName }}</td>
								<td>{{ App\Services\Helpers\Helpers::formatBigNumber($ship->value) }}</td>
								<td>
									{{ Form::open(array('action' => array('SrpDoctrineController@destroy', $doctrine->id), 'method' => 'DELETE')) }}
										{{ Form::hidden('ship', $ship->id) }}
										<button class="btn btn-default btn-xs pull-right"><i class="fa fa-angle-double-right"></i></button>
									{{ Form::close() }}
								</td>
							</tr>
							@endforeach
						</tbody>
					</table>
				</div><!-- /.box-body -->

			</div><!-- /.box -->

		</div><!-- /.col -->

		<div class="col-md-6">

			<div class="box">

				<div class="box-header">
					<h3 class="box-title">Available Ships</h3>
				</div>

				<div class="box-body">
					<table class="table table-condensed compact table-hover" id="datatable" style="white-space: nowrap;">
						<thead>
							<tr>
								<th></th>
								<th style="width: 99%;">Name</th>
								<th>Ship</th>
								<th>Value</th>
							</tr>
						</thead>
						<tbody>
							@foreach($available_ships as $ship)
							<tr>
								<td>
									{{ Form::open(array('action' => array('SrpDoctrineController@update', $doctrine->id), 'method' => 'PUT')) }}
										{{ Form::hidden('ship', $ship->id) }}
										<button class="btn btn-default btn-xs pull-left"><i class="fa fa-angle-double-left"></i></button>
									{{ Form::close() }}
								</td>
								<td>{{ $ship->name }}</td>
								<td>{{ $ship->type()->first()->typeName }}</td>
								<td>{{ App\Services\Helpers\Helpers::formatBigNumber($ship->value) }}</td>
							</tr>
							@endforeach
						</tbody>
					</table>
				</div><!-- /.box-body -->

			</div><!-- /.box -->

		</div><!-- /.col -->

	</div><!-- /.row -->

@stop
