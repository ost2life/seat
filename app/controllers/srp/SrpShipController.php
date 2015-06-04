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

use Seat\Services\Helpers\SrpHelper;

class SrpShipController extends \BaseController
{

	/*
	|--------------------------------------------------------------------------
	| __construct()
	|--------------------------------------------------------------------------
	|
	| Constructs the class.
	|
	*/
	public function __construct()
	{
		if (!SrpHelper::canConfigure()) {
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

		return view::make('srp.config.ship.index')
			->with('ships', SrpShip::all())
			->with('all_ships', $all_ships)
		;
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
		$validator = Validator::make(Input::all(), array(
			'ship' => 'required|min:3|max:50',
			'name' => 'required|min:3|max:50',
			'value' => 'required|numeric|min:0|max:9999999999999',
		));

		if ($validator->fails()) {
			return Redirect::back()
				->withErrors($validator->errors())
				->withInput(Input::all()); }

		if (!$type = SrpInvType::where('typeName', Input::get('ship'))->first()) {
			return Redirect::back()
				->withErrors("That ship does not exist.")
				->withInput(); }

		try {
			$ship = SrpShip::create(array(
				'name' => Input::get('name'),
				'value' => Input::get('value'),
				'typeID' => $type->typeID,
			));
			Session::flash('success', "{$ship->name} ({$ship->type()->first()->typeName}) has been created."); }

		catch (PDOException $e) { switch ($e->getCode()) {

			case 23000: // Integrity Constraint Violation
				$ship = SrpShip::withTrashed()
					->where('name', '=', Input::get('name'))
					->where('typeID', '=', $type->typeID)
					->first();

				if ($ship->trashed()) {
					$ship->value = Input::get('value');
					$ship->restore();
					Session::flash('success', "{$ship->name} ({$ship->type()->first()->typeName}) has been restored.");
					break; }

				return Redirect::back()
					->withErrors("{$ship->name} ({$ship->type()->first()->typeName}) already exists.")
					->withInput();

				default:
					return Redirect::back()
						->withErrors('A database error has occurred.')
						->withInput(); } }

		catch (Exception $e) {
			return Redirect::back()
				->withErrors('An unknown error has occurred.')
				->withInput(); }

		return Redirect::back();
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
		if(!$ship = SrpShip::find($id)) {
			App::abort(404); }

		$assigned_doctrines = $ship->doctrines()->get();
		$available_doctrines = SrpDoctrine::whereNotIn('id', $assigned_doctrines->count()
			? $assigned_doctrines->lists('id')
			: array(0))->get();

		return view::make('srp.config.ship.show')
			->with('available_doctrines', $available_doctrines)
			->with('ship', $ship)
		;
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
		$validator = Validator::make(Input::all(), array(
			'doctrine' => 'required_without:name,value|integer|min:1',
			'name' => 'required_without:doctrine|min:3|max:20',
			'value' => 'required_without:doctrine|numeric|min:0|max:9999999999999',
		));

		if ($validator->fails()) {
			return Redirect::back()
				->withErrors($validator->errors())
				->withInput(Input::all()); }

		if (!$ship = SrpShip::find($id)) {
			App::abort(404); }

		if (Input::exists('doctrine') && !$doctrine = SrpDoctrine::find(Input::get('doctrine'))) {
			App::abort(404); }

		try {
			// Attach doctrine
			if (Input::exists('doctrine')) {
				$ship->doctrines()->attach($doctrine);
				Session::flash('success', "{$doctrine->name} has been assigned to {$ship->name} ({$ship->type()->first()->typeName})."); }

			// Update ship
			else {
				$ship->fill(Input::all());
				$ship->save();
				Session::flash('success', "{$ship->name} ({$ship->type()->first()->typeName}) has been updated."); } }

		catch (PDOException $e) { switch ($e->getCode()) {

			case 23000: // Integrity Constraint Violation
				return Redirect::back()
					->withErrors("{$ship->name} ({$ship->type()->first()->typeName}) already exists.")
					->withInput();

				default:
					return Redirect::back()
						->withErrors('A database error has occurred.')
						->withInput(); } }

		catch (Exception $e) {
			return Redirect::back()
				->withErrors('An unknown error has occurred.')
				->withInput(); }

		return Redirect::back();
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
		$validator = Validator::make(Input::all(), array(
			'doctrine' => 'integer|min:1',
		));

		if ($validator->fails()) {
			return Redirect::back()
				->withErrors($validator->errors())
				->withInput(Input::all()); }

		if (!$ship = SrpShip::find($id)) {
			App::abort(404); }

		if (Input::exists('doctrine') && !$doctrine = SrpDoctrine::find(Input::get('doctrine'))) {
			App::abort(404); }

		// Detach doctrine
		if (Input::exists('doctrine')) {
			$ship->doctrines()->detach($doctrine);
			Session::flash('success', "{$doctrine->name} has been unassigned from {$ship->name} ({$ship->type()->first()->typeName}).");
			return Redirect::back(); }

		// Delete ship
		else {
			$ship->delete();
			Session::flash('success', "{$ship->name} ({$ship->type()->first()->typeName}) has been deleted.");
			return $this->index(); }
	}

}
