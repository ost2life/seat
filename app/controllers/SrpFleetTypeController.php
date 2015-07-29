<?php
/*
The MIT License (MIT)

Copyright (c) 2014 eve-seat

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.
*/

use App\Services\Validators\SrpFleetTypeValidator;

class SrpFleetTypeController extends BaseController
{

	// Models
	protected $srp_fleet_type;

	// Validators
	protected $srp_fleet_type_validator;

	/*
	|--------------------------------------------------------------------------
	| __construct()
	|--------------------------------------------------------------------------
	|
	| Constructs the class.
	|
	*/
	public function __construct(
		SrpFleetType $srp_fleet_type,
		SrpFleetTypeValidator $srp_fleet_type_validator)
	{
		// Models
		$this->srp_fleet_type = $srp_fleet_type;

		// Validators
		$this->srp_fleet_type_validator = $srp_fleet_type_validator;

		// Must have permission to configure
		if (!Auth::hasAccess('srp_configure')) {
			App::abort(404); }
	}


	/*
	|--------------------------------------------------------------------------
	| index()
	|--------------------------------------------------------------------------
	|
	| Display a listing of the resource.
	|
	*/
	public function index()
	{
		// View data
		$fleet_types = $this->srp_fleet_type->all();

		// Return
		return View::make('srp.config.fleet.index')
			->with('fleet_types', $fleet_types);
	}


	/*
	|--------------------------------------------------------------------------
	| create()
	|--------------------------------------------------------------------------
	|
	| Show the form for creating a new resource.
	|
	*/
	public function create()
	{
		App::abort(404);
	}


	/*
	|--------------------------------------------------------------------------
	| store()
	|--------------------------------------------------------------------------
	|
	| Store a newly created resource in storage.
	|
	*/
	public function store()
	{
		// Validate input
		if (!$this->srp_fleet_type_validator->passes()) {
			return Redirect::action('SrpFleetTypeController@index')
				->withErrors($this->srp_fleet_type_validator->errors)
				->withInput(); }

		// Check if name is free
		$fleet_type = $this->srp_fleet_type
			->where('name', '=', Input::get('name'))
			->withTrashed()
			->first();

		if ($fleet_type && !$fleet_type->trashed()) {
			return Redirect::action('SrpFleetTypeController@index')
				->withErrors("The fleet type '{$fleet_type->name}' already exists.")
				->withInput(); }

		// Restore
		if ($fleet_type->trashed()) {
			$fleet_type->fill(Input::all());
			$fleet_type->restore(); }

		// Create
		else {
			$fleet_type = $this->srp_fleet_type->create(Input::all()); }

		return Redirect::action('SrpFleetTypeController@index')
			->with('success', "The fleet type '{$fleet_type->name}' was created.");
	}


	/*
	|--------------------------------------------------------------------------
	| show()
	|--------------------------------------------------------------------------
	|
	| Display the specified resource.
	|
	*/
	public function show($id)
	{
		// Fleet type must exist
		if (!$fleet_type = $this->srp_fleet_type->find($id)) {
			return Redirect::action('SrpFleetTypeController@index')
				->withErrors('That fleet type does not exist.')
				->withInput(); }

		// Return
		return View::make('srp.config.fleet.show')
			->with('fleet_type', $fleet_type);
	}


	/*
	|--------------------------------------------------------------------------
	| edit()
	|--------------------------------------------------------------------------
	|
	| Show the form for editing the specified resource.
	|
	*/
	public function edit($id)
	{
		App::abort(404);
	}


	/*
	|--------------------------------------------------------------------------
	| update()
	|--------------------------------------------------------------------------
	|
	| Update the specified resource in storage.
	|
	*/
	public function update($id)
	{
		// Validate input
		if (!$this->srp_fleet_type_validator->passes()) {
			return Redirect::action('SrpFleetTypeController@index')
				->withErrors($this->srp_fleet_type_validator->errors)
				->withInput(); }

		// Fleet type must exist
		if (!$fleet_type = $this->srp_fleet_type->find($id)) {
			return Redirect::action('SrpFleetTypeController@index')
				->withErrors('That fleet type does not exist.')
				->withInput(); }

		// Check if name is free
		$fleet_type = $this->srp_fleet_type->where('name', '=', Input::get('name'))
			->withTrashed()
			->first();

		if ($fleet_type && !$fleet_type->trashed()) {
			return Redirect::action('SrpFleetTypeController@show', $id)
				->withErrors("The fleet type '{$fleet_type->name}' already exists.")
				->withInput(); }

		// Update
		$fleet_type->fill(Input::all());
		$fleet_type->save();

		// Return
		return Redirect::action('SrpFleetTypeController@show', $id)
			->with('success', "This fleet type was updated.");
	}


	/*
	|--------------------------------------------------------------------------
	| destroy()
	|--------------------------------------------------------------------------
	|
	| Remove the specified resource from storage.
	|
	*/
	public function destroy($id, $data = null)
	{
		// Fleet type must exist
		if (!$fleet_type = $this->srp_fleet_type->find($id)) {
			return Redirect::action('SrpFleetTypeController@index')
				->withErrors('That fleet type does not exist.')
				->withInput(); }

		// Delete fleet type
		$fleet_type->delete();

		// Return
		Session::flash('success', "The fleet type '{$fleet_type->name}' was deleted.");
		return Response::make(null, 204);
	}

}
