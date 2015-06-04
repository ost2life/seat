@extends('layouts.masterLayout')

@section('html_title', 'Configure Ships')

@section('page_content')

	<div class="row">
		<ol class="breadcrumb">
			<li><a>SRP</a></li>
			<li><a href="{{ action('SrpConfigController@index') }}">Configure</a></li>
			<li><a href="{{ action('SrpShipController@index') }}">Ships</a></li>
			<li class="active">{{ $ship->name }} ({{ $ship->type->typeName }})</li>
		</ol>
	</div>

	<div class="row">

		<div class="col-md-12">

			<div class="box">

				<div class="box-header">
					<h3 class="box-title">Edit Ship
					</h3>
				</div>

				<div class="box-body table-responsive">
					{{ Form::open(array('action' => array('SrpShipController@update', $ship->id), 'method' => 'PUT', 'class' => 'form-horizontal')) }}
						<fieldset>
							<!-- Text Input -->
							<div class="form-group">
								<label class="col-md-3 control-label" for="ship">Ship</label>
								<div class="col-md-7">
									<div class="input-group">
										<span class="input-group-addon"><i class="fa fa-fw fa-rocket"></i></span>
										{{ Form::text('ship', $ship->type->typeName, array('class' => 'form-control input-md', 'list' => 'typeNames', 'disabled')) }}
									</div>
								</div>
							</div>
							<!-- Text Input -->
							<div class="form-group">
								<label class="col-md-3 control-label" for="name">Name</label>
								<div class="col-md-7">
									<div class="input-group">
										<span class="input-group-addon"><i class="fa fa-fw fa-pencil"></i></span>
										{{ Form::text('name', $ship->name, array('class' => 'form-control input-md')) }}
									</div>
								</div>
							</div>
							<!-- Text Input -->
							<div class="form-group">
								<label class="col-md-3 control-label" for="value">Value</label>
								<div class="col-md-7">
									<div class="input-group">
										<span class="input-group-addon"><i class="fa fa-fw fa-money"></i></span>
										{{ Form::text('value', $ship->value, array('class' => 'form-control input-md')) }}
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
					<h3 class="box-title">Assigned Doctrines</h3>
				</div>

				<div class="box-body">
					<table class="table table-condensed compact table-hover" id="datatable" style="white-space: nowrap;">
						<thead>
							<tr>
								<th style="width: 99%;">Name</th>
								<th></th>
							</tr>
						</thead>
						<tbody>
							@foreach($ship->doctrines as $doctrine)
							<tr>
								<td>{{ $doctrine->name }}</td>
								<td>
									{{ Form::open(array('action' => array('SrpShipController@destroy', $ship->id), 'method' => 'DELETE')) }}
										{{ Form::hidden('doctrine', $doctrine->id) }}
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
					<h3 class="box-title">Available Doctrines</h3>
				</div>

				<div class="box-body">
					<table class="table table-condensed compact table-hover" id="datatable" style="white-space: nowrap;">
						<thead>
							<tr>
								<th></th>
								<th style="width: 99%;">Name</th>
							</tr>
						</thead>
						<tbody>
							@foreach($available_doctrines as $doctrine)
							<tr>
								<td>
									{{ Form::open(array('action' => array('SrpShipController@update', $ship->id), 'method' => 'PUT')) }}
										{{ Form::hidden('doctrine', $doctrine->id) }}
										<button class="btn btn-default btn-xs pull-left"><i class="fa fa-angle-double-left"></i></button>
									{{ Form::close() }}
								</td>
								<td>{{ $doctrine->name }}</td>
							</tr>
							@endforeach
						</tbody>
					</table>
				</div><!-- /.box-body -->

			</div><!-- /.box -->

		</div><!-- /.col -->

	</div><!-- /.row -->

@stop
