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

use App\Services\Validators\SrpShipValidator;

class SrpShipController extends BaseController
{

	// Models
	protected $srp_doctrine;
	protected $srp_inv_type;
	protected $srp_ship;

	// Validators
	protected $srp_ship_validator;


	/*
	|--------------------------------------------------------------------------
	| __construct()
	|--------------------------------------------------------------------------
	|
	| Constructs the class.
	|
	*/
	public function __construct(
		SrpDoctrine $srp_doctrine,
		SrpInvType $srp_inv_type,
		SrpShip $srp_ship,
		SrpShipValidator $srp_ship_validator)
	{
		// Models
		$this->srp_doctrine = $srp_doctrine;
		$this->srp_inv_type = $srp_inv_type;
		$this->srp_ship = $srp_ship;

		// Validators
		$this->srp_ship_validator = $srp_ship_validator;

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
		// Fetch every ship from invTypes
		$all_ships = DB::table('invTypes')
			->select('invTypes.typeID', 'invTypes.typeName')
			->join('invGroups', 'invGroups.groupID', '=', 'invTypes.groupID')
			->where('invGroups.categoryID', '=', 6)
			->get();

		// View data
		$ships = $this->srp_ship->all();

		// Return
		return View::make('srp.config.ship.index')
			->with('ships', $ships)
			->with('all_ships', $all_ships);
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
		if (!$this->srp_ship_validator->passes()) {
			return Redirect::action('SrpShipController@index')
				->withErrors($this->srp_ship_validator->errors)
				->withInput(); }

		// Ship must exist
		if (!$ship_type = $this->srp_inv_type->where('typeName', '=', Input::get('ship'))->first()) {
			return Redirect::action('SrpShipController@index')
				->withErrors('That ship does not exist.')
				->withInput(); }

		// Check if name is free
		$ship = $this->srp_ship
			->where('name', '=', Input::get('name'))
			->where('typeID', '=', $ship_type->typeID)
			->withTrashed()
			->first();

		if ($ship && !$ship->trashed()) {
			return Redirect::action('SrpShipController@index')
				->withErrors("The ship '{$ship->name} ({$ship->type()->first()->typeName})' already exists.")
				->withInput(); }

		// Restore
		if ($ship && $ship->trashed()) {
			$ship->fill(array(
				'name' => Input::get('name'),
				'typeID' => $ship_type->typeID,
				'value' => Input::get('value'),
			));
			$ship->restore(); }

		// Create
		else {
			$ship = $this->srp_ship->create(array(
				'name' => Input::get('name'),
				'typeID' => $ship_type->typeID,
				'value' => Input::get('value'),
			)); }

		// Return
		return Redirect::action('SrpShipController@index')
			->with('success', "The ship '{$ship->name} ({$ship->type()->first()->typeName})' was created.");
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
		// Ship must exist
		if (!$ship = $this->srp_ship->find($id)) {
			return Redirect::action('SrpShipController@index')
				->withErrors('That ship does not exist.')
				->withInput(); }

		// Doctrines not attached to ship
		$doctrines = $this->srp_doctrine->whereNotIn('id',
			!!$ship->doctrines->count()
			? $ship->doctrines->lists('id')
			: array(0)
		)->get();

		// Return
		return View::make('srp.config.ship.show')
			->with('doctrines', $doctrines)
			->with('ship', $ship);
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
		// Ship must exist
		if (!$ship = $this->srp_ship->find($id)) {
			return Redirect::action('SrpShipController@index')
				->withErrors('That ship does not exist.')
				->withInput(); }

		// Attach doctrine
		if (Input::has('doctrine')) {
			// Validate input
			if (!$this->srp_ship_validator->passes()) {
				return Redirect::action('SrpShipController@show', $id)
					->withErrors($this->srp_ship_validator->errors)
					->withInput(); }

			// Doctrine must exist
			if (!$doctrine = $this->srp_doctrine->find(Input::get('doctrine'))) {
				return Redirect::action('SrpShipController@show', $id)
					->withErrors('That doctrine does not exist.')
					->withInput(); }

			// Attach doctrine
			if (!$ship->doctrines->contains($doctrine)) {
				$ship->doctrines()->attach($doctrine); }

			// Return
			return Redirect::action('SrpShipController@show', $id)
				->with('success', "The doctrine '{$doctrine->name}' has been assigned to this ship."); }

		// Update ship
		else if (Input::has('name') && Input::has('value')) {
			// Validate input
			if (!$this->srp_ship_validator->passes()) {
				return Redirect::action('SrpShipController@show', $id)
					->withErrors($this->srp_ship_validator->errors)
					->withInput(); }

			// Update ship
			$ship->fill(Input::all());
			$ship->save();

			// Return
			return Redirect::action('SrpShipController@show', $id)
				->with('success', "This ship has been updated."); }
	}


	/*
	|--------------------------------------------------------------------------
	| destroy()
	|--------------------------------------------------------------------------
	|
	| Remove the specified resource from storage.
	|
	*/
	public function destroy($id)
	{
		// Ship must exist
		if (!$ship = $this->srp_ship->find($id)) {
			return Redirect::action('SrpShipController@index')
				->withErrors('That ship does not exist.')
				->withInput(); }

		// Detach doctrine
		if (Input::has('doctrine')) {
			// Validate input
			if (!$this->srp_ship_validator->passes()) {
				return Redirect::action('SrpShipController@show', $id)
					->withErrors($this->srp_ship_validator->errors)
					->withInput(); }

			// Doctrine must exist
			if (!$doctrine = $this->srp_doctrine->find(Input::get('doctrine'))) {
				return Redirect::action('SrpShipController@show', $id)
					->withErrors('That doctrine does not exist.')
					->withInput(); }

			// Detach doctrine
			if ($ship->doctrines->contains($doctrine)) {
				$ship->doctrines()->detach($doctrine); }

			// Return
			return Redirect::action('SrpShipController@show', $id)
				->with('success', "The doctrine '{$doctrine->name}' has been unassigned from this ship."); }

		// Delete ship
		else {
			// Delete ship
			$ship->delete();

			// Return
			Session::flash('success', "The ship '{$ship->name} ({$ship->type()->first()->typeName})' has been deleted.");
			return Response::make(null, 204); }
	}

}
