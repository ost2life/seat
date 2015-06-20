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

use App\Services\Validators\SrpFleetValidator;
use Seat\Services\Helpers\SrpHelper;

class SrpFleetController extends BaseController
{

	// Models
	protected $srp_character;
	protected $srp_doctrine;
	protected $srp_fleet_type;
	protected $srp_fleet;

	// Validators
	protected $srp_fleet_validator;

	/*
	|--------------------------------------------------------------------------
	| __construct()
	|--------------------------------------------------------------------------
	|
	| Constructs the class.
	|
	*/
	public function __construct(
		SrpCharacter $srp_character,
		SrpDoctrine $srp_doctrine,
		SrpFleetType $srp_fleet_type,
		SrpFleet $srp_fleet,
		SrpFleetValidator $srp_fleet_validator)
	{
		// Models
		$this->srp_character = $srp_character;
		$this->srp_doctrine = $srp_doctrine;
		$this->srp_fleet_type = $srp_fleet_type;
		$this->srp_fleet = $srp_fleet;

		// Validators
		$this->srp_fleet_validator = $srp_fleet_validator;
	}


	/*
	|--------------------------------------------------------------------------
	| ownsFleet()
	|--------------------------------------------------------------------------
	|
	| Checks if a user owns a fleet.
	|
	*/
	private function ownsFleet(SrpFleet $fleet)
	{
		return in_array($fleet->characterID, $this->srp_character->self()->lists('characterID')) === true;
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
		$characters = $this->srp_character->self()->lists('characterName', 'characterID');

		$fleet_types = $this->srp_fleet_type->available()->lists('name', 'id');

		$fleets = $this->srp_fleet
			->available($this->srp_character)
			->with('character', 'requests')
			->orderBy('created_at', 'DESC')
			->paginate(20);

		// Return
		return View::make('srp.fleet.index')
			->with('characters', $characters)
			->with('fleet_types', $fleet_types)
			->with('fleets', $fleets);
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
		if (!$this->srp_fleet_validator->passes()) {
			return Redirect::action('SrpFleetController@index')
				->withErrors($this->srp_fleet_validator->errors)
				->withInput(); }

		// Must have permission to create the fleet
		$fleet_types = $this->srp_fleet_type->available()->lists('id');;

		if (!in_array(Input::get('type'), $fleet_types)) {
			return Redirect::action('SrpFleetController@index')
				->withErrors('You are not allowed to do that.')
				->withInput(); }

		// Check if srp code is free
		if (count($this->srp_fleet->where('code', '=', Input::get('code'))->get())) {
			return Redirect::action('SrpFleetController@index')
				->withErrors('That srp code already exists.')
				->withInput(); }

		// Insert fleet
		$fleet = $this->srp_fleet->create(array(
			'code' => Input::get('code'),
			'characterID' => Input::get('commander'),
			'fleetTypeID' => Input::get('type'),
		));

		// Return
		return Redirect::action('SrpFleetController@show', array($fleet->id))
			->with('success', "Your fleet was created.");
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
		// Fleet must exist
		if (!$fleet = $this->srp_fleet->find($id)) {
			return Redirect::action('SrpFleetController@index')
				->withErrors('That fleet does not exist.')
				->withInput(); }
		// Doctrines not attached to fleet
		$doctrines = $this->srp_doctrine->whereNotIn('id',
			!!$fleet->doctrines->count()
			? $fleet->doctrines->lists('id')
			: array(0)
		)->get();

		// Must own or have permission to modify the fleet
		if (!SrpHelper::canCommand() && !$this->ownsFleet($fleet)) {
			return Redirect::action('SrpFleetController@index')
				->withErrors('You are not allowed to view that.')
				->withInput(); }

		// Return
		return View::make('srp.fleet.show')
			->with('doctrines', $doctrines)
			->with('fleet', $fleet);
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
		// Fleet must exist
		if (!$fleet = $this->srp_fleet->find($id)) {
			return Redirect::action('SrpFleetController@index')
				->withErrors('That fleet does not exist.')
				->withInput(); }

		// Must own or have permission to modify the fleet
		if (!SrpHelper::canCommand() && !SrpHelper::ownsFleet($fleet)) {
			return Redirect::action('SrpFleetController@show', $id)
				->withErrors('You are not allowed to do that.')
				->withInput(); }

		// Attach doctrine
		if (Input::exists('doctrine')) {
			// Validate input
			if (!$this->srp_fleet_doctrine_validator->passes()) {
				return Redirect::action('SrpFleetController@show', $id)
					->withErrors($this->srp_fleet_doctrine_validator->errors)
					->withInput(); }

			// Doctrine must exist
			if (!$doctrine = $this->srp_doctrine->find(Input::get('doctrine'))) {
				return Redirect::action('SrpFleetController@show', $id)
					->withErrors('That doctrine does not exist.')
					->withInput(); }

			// Attach doctrine
			if (!$fleet->doctrines->contains($doctrine)) {
				$fleet->doctrines()->attach($doctrine); }

			// Return
			return Redirect::action('SrpFleetController@show', $id)
				->with('success', "The doctrine '{$doctrine->name}' was assigned to this fleet."); }

		// Failure
		App::abort(404);
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
		// Fleet must exist
		if (!$fleet = $this->srp_fleet->find($id)) {
			return Redirect::action('SrpFleetController@index')
				->withErrors('That fleet does not exist.')
				->withInput(); }

		// Must own or have permission to modify the fleet
		if (!SrpHelper::canCommand() && !$this->ownsFleet($fleet)) {
			return Redirect::action('SrpFleetController@index')
				->withErrors('You are not allowed to do that.')
				->withInput(); }

		// Detach doctrine
		if (Input::exists('doctrine')) {
			// Validate input
			if (!$this->srp_fleet_validator->passes()) {
				return Redirect::action('SrpFleetController@show', $id)
					->withErrors($this->srp_fleet_validator->errors)
					->withInput(); }

			// Doctrine must exist
			if (!$doctrine = $this->srp_doctrine->find(Input::get('doctrine'))) {
				return Redirect::action('SrpFleetController@show', $id)
					->withErrors('That doctrine does not exist.')
					->withInput(); }

			// Detach doctrine
			if ($fleet->doctrines->contains($doctrine)) {
				$fleet->doctrines()->detach($doctrine); }

			// Return
			return Redirect::action('SrpFleetController@show', $id)
				->with('success', "The doctrine '{$doctrine->name}' was removed from this fleet."); }

		// Delete fleet
		else {
			// Allow srp code to be reused
			$fleet->code = null;
			$fleet->save();

			// Delete fleet
			$fleet->delete();

			// Return
			Session::flash('success', "The fleet was deleted.");
			return Response::make(null, 204); }
	}

}
