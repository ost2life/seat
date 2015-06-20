@extends('layouts.masterLayout')

@section('html_title', 'SRP Fleets')

@section('page_content')

	<div class="row">
		<ol class="breadcrumb">
			<li><a>SRP</a></li>
			<li class="active">Fleets</li>
		</ol>
	</div>

	<div class="row">

		<div class="col-md-7">

			<div class="box">
				<div class="box-header">
					<h3 class="box-title">Fleets</h3>
				</div>

				<div class="box-body">
					<table class="table table-condensed compact table-hover" id="datatable" style="white-space: nowrap;">
						<thead>
							<tr>
								<th>Created</th>
								<th width="99%">Commander</th>
								<th>Type</th>
								<th>Code</th>
								<th>Requests</th>
								<th></th>
								<th></th>
							</tr>
						</thead>
						<tbody>
							@foreach($fleets as $fleet)
							<tr>
								<td>{{ $fleet->created_at }}</td>
								<td>{{ $fleet->character->characterName }}</td>
								<td>{{ $fleet->type->name }}</td>
								<td>{{ $fleet->code }}</td>
								<td>{{ $fleet->requests->count() }}</td>
								<td><a href="{{ action('SrpFleetController@show', array($fleet->id)) }}" class="btn btn-default btn-xs"><i class="fa fa-eye"></i> Details</a></td>
								<td><a a-delete-item="{{ action('SrpFleetController@destroy', array($fleet->id)) }}" a-item-name="{{ $fleet->code }}" class="btn btn-danger btn-xs delete-item"><i class="fa fa-times"></i> Delete</a></td>
							</tr>
							@endforeach
						</tbody>
					</table>
					<div class="pull-right">{{ $fleets->links() }}</div>
				</div><!-- /.box-body -->
			</div><!-- /.box -->

		</div><!-- /.col -->

		<div class="col-md-5">

			<div class="box">

				<div class="box-header">
					<h3 class="box-title">Create Fleet</h3>
				</div>

				<div class="box-body">
					@if (count($characters) > 0)
						{{ Form::open(array('action' => 'SrpFleetController@store', 'class' => 'form-horizontal')) }}
							<fieldset>
								<!-- Text Input-->
								<div class="form-group">
									<label class="col-md-4 control-label" for="commander">Fleet Commander</label>
									<div class="col-md-6">
										<div class="input-group">
											<span class="input-group-addon"><i class="fa fa-fw fa-user"></i></span>
												{{ Form::select('commander', $characters, $settings['main_character_id'] ? $settings['main_character_id'] : 0, array('class' => 'form-control')) }}
											</div>
									</div>
								</div>
								<!-- Text Input-->
								<div class="form-group">
									<label class="col-md-4 control-label" for="type">Fleet Type</label>
									<div class="col-md-6">
										<div class="input-group">
											<span class="input-group-addon"><i class="fa fa-fw fa-info"></i></span>
												{{ Form::select('type', $fleet_types, reset($fleet_types), array('class' => 'form-control')) }}
											</div>
									</div>
								</div>
								<!-- Text Input-->
								<div class="form-group">
									<label class="col-md-4 control-label" for="code">SRP Code</label>
									<div class="col-md-6">
										<div class="input-group">
											<span class="input-group-addon"><i class="fa fa-fw fa-pencil"></i></span>
												{{ Form::text('code', null, array('class' => 'form-control input-md')) }}
											</div>
									</div>
								</div>
								<!-- Button -->
								<div class="form-group">
									<label class="col-md-4 control-label" for="singlebutton"></label>
									<div class="col-md-6">
										<button id="singlebutton" name="singlebutton" class="btn btn-block btn-primary">Create</button>
									</div>
								</div>
							</fieldset>
						{{ Form::close()}}
					@else
						<p>You must have at least one character registered on SeAT to create a fleet.</p>
					@endif

				</div>
			</div><!-- box -->

		</div><!-- /.col -->

	</div><!-- /.row -->

@stop

@section('javascript')

	<script type="text/javascript">
		$(document).on("click", ".delete-item", function(e) {
			var delete_item = $(this).attr("a-delete-item");

			bootbox.dialog({
				message: "Are you sure that you want to delete this?",
				title: "Delete " + $(this).attr("a-item-name") + "?",
				buttons: {
					success: {
						label: "No Thanks",
						className: "btn-default"
					},
					danger: {
						label: "Delete Item",
						className: "btn-danger",
						callback: function() {
							$.ajax({
								url: delete_item,
								type: 'DELETE',
								success: function(result) {
									location.reload();
								}
							});
						}
					}
				}
			});
		});
	</script>

@stop
