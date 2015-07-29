@extends('layouts.masterLayout')

@section('html_title', 'Configure Fleet Types')

@section('page_content')

	<div class="row">
		<ol class="breadcrumb">
			<li><a>SRP</a></li>
			<li><a href="{{ action('SrpConfigController@index') }}">Configure</a></li>
			<li><a href="{{ action('SrpFleetTypeController@index') }}">Fleet Types</a></li>
			<li class="active">{{ $fleet_type->name }}</li>
		</ol>
	</div>

	<div class="row">

		<div class="col-md-12">

			<div class="box">

				<div class="box-header">
					<h3 class="box-title">Edit Fleet Type
					</h3>
				</div>

				<div class="box-body table-responsive">
					{{ Form::open(array('action' => array('SrpFleetTypeController@update', $fleet_type->id), 'method' => 'PUT', 'class' => 'form-horizontal')) }}
						<fieldset>
							<!-- Text Input -->
							<div class="form-group">
								<label class="col-md-3 control-label" for="name">Name</label>
								<div class="col-md-7">
									<div class="input-group">
										<span class="input-group-addon"><i class="fa fa-fw fa-pencil"></i></span>
										{{ Form::text('name', $fleet_type->name, array('class' => ' form-control', 'placeholder' => $fleet_type->name)) }}
									</div>
								</div>
							</div>
							<!-- Checkbox -->
							<div class="form-group">
								<label class="col-md-3 control-label" for="public">Public</label>
								<div class="col-md-7">
									<label>
										{{ Form::hidden('public', 0) }}
										{{ Form::checkbox('public', 1, $fleet_type->public) }}
										Allow anyone to use this fleet type
									</label>
								</div>
							</div>
							<!-- Button -->
							<div class="form-group">
								<label class="col-md-3 control-label" for="singlebutton"></label>
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

@stop
