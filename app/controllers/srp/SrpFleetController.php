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

class SrpFleetController extends \BaseController
{
	protected $characters;
	protected $fleet_types;
	protected $fleets;

	/*
	|--------------------------------------------------------------------------
	| ownsFleet()
	|--------------------------------------------------------------------------
	|
	| Checks if a user owns a fleet.
	|
	*/
	private static function ownsFleet(SrpFleet $fleet)
	{
		$characters = $this->characters->lists('characterID');
		return in_array($fleet->characterID, $characters) == 1;
	}

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
		$this->characters = SrpHelper::getCharacters();

		$this->fleet_types = SrpHelper::canCommand()
			? SrpFleetType::all()
			: SrpFleetType::where('public', '=', true)->get();

		$this->fleets = SrpHelper::canReview() || SrpHelper::canPay() || SrpHelper::canCommand()
			? SrpFleet::all()
			: SrpFleet::whereIn('characterID', $this->characters->lists('characterID'))->get();

		// Return 404 if the user is not allowed to command a fleet
		if ($this->fleet_types->count() == 0) { App::abort(404); }
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
		$characters = array_flip($this->characters->lists('characterID', 'characterName'));

		$fleet_types = array_flip($this->fleet_types->lists('id', 'name'));

		$fleets = $this->fleets;

		return view::make('srp.fleet.index')
			->with('characters', $characters)
			->with('fleet_types', $fleet_types)
			->with('fleets', $fleets)
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
		// Validate input
		$validator = Validator::make(Input::all(), array(
			'commander' => 'required|integer|min:1',
			'type' => 'required|integer|min:1',
			'code' => 'required|min:3|max:20',
		));

		if ($validator->fails()) {
			return Redirect::back()
				->withErrors($validator->errors())
				->withInput(Input::all()); }

		try {
			// Insert fleet
			$fleet = SrpFleet::create(array(
				'code' => Input::get('code'),
				'characterID' => Input::get('commander'),
				'fleetTypeID' => Input::get('type'),
			));

			Session::flash('success', "Your fleet has been was created."); }

		catch (PDOException $e) { switch ($e->getCode()) {

			case 23000: // Integrity Constraint Violation
				return Redirect::back()
					->withErrors("A fleet with that srp code already exists.")
					->withInput();

				default:
					return Redirect::back()
						->withErrors('A database error has occurred.')
						->withInput(); } }

		catch (Exception $e) {
			return Redirect::back()
				->withErrors('An unknown error has occurred.')
				->withInput(); }

		return Redirect::route('srp.fleet.show', array($fleet->id));
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
		if (!$fleet = SrpFleet::find($id)) { App::abort(404); }

		// Must own or have permission to modify the fleet
		if (!SrpHelper::canCommand() && !SrpHelper::ownsFleet($fleet)) { App::abort(404); }

		$assigned_doctrines = $fleet->doctrines()->get();

		$available_doctrines = SrpDoctrine::whereNotIn('id', $assigned_doctrines->count()
			? $assigned_doctrines->lists('id')
			: array(0))->get();

		return view::make('srp.fleet.show')
			->with('fleet', $fleet)
			->with('requests', $fleet->requests()->get())
			->with('available_doctrines', $available_doctrines)
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
		// Fleet must exist
		if (!$fleet = SrpFleet::find($id)) { App::abort(404); }

		// Must own or have permission to modify the fleet
		if (!SrpHelper::canCommand() && !SrpHelper::ownsFleet($fleet)) { App::abort(404); }

		try {
			// Attach doctrine
			if (Input::exists('doctrine')) {
				// Validate input
				$validator = Validator::make(Input::all(), array(
					'doctrine' => 'required|integer|min:1',
				));

				if ($validator->fails()) {
					return Redirect::back()
						->withErrors($validator->errors())
						->withInput(Input::all()); }

				// Doctrine must exist
				if (!$doctrine = SrpDoctrine::find(Input::get('doctrine'))) { App::abort(404); }

				$fleet->doctrines()->attach($doctrine);

				Session::flash('success', "{$doctrine->name} has been added to the fleet's doctrine."); } }

		catch (PDOException $e) { switch ($e->getCode()) {

			case 23000: // Integrity Constraint Violation
				return Redirect::back()
					->withErrors("The srp code '{$fleet->code}' already exists.")
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
		// Fleet must exist
		if (!$fleet = SrpFleet::find($id)) { App::abort(404); }

		// Must own or have permission to modify the fleet
		if (!SrpHelper::canCommand() && !$this->ownsFleet($fleet)) { App::abort(404); }

		// Detach doctrine
		if (Input::exists('doctrine')) {
			// Validate input
			$validator = Validator::make(Input::all(), array(
				'doctrine' => 'required|integer|min:1',
			));

			if ($validator->fails()) {
				return Redirect::back()
					->withErrors($validator->errors())
					->withInput(Input::all()); }

			// Doctrine must exist
			if (!$doctrine = SrpDoctrine::find(Input::get('doctrine'))) { App::abort(404); }

			// Detach doctrine
			if (Input::exists('doctrine')) {
				$fleet->doctrines()->detach($doctrine);

				Session::flash('success', "{$doctrine->name} has been removed from the fleet's doctrine.");
				return Redirect::back(); } }

		// Delete fleet
		else {
			// Null the srp code so that it can be used again
			$fleet->code = null;
			$fleet->save();

			$fleet->delete();

			Session::flash('success', "The fleet has been deleted.");
			return $this->index(); }
	}

}
