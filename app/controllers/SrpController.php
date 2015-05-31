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

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Seat\Services\Helpers\SrpHelper;

class SrpController extends \BaseController {
	/*
	|--------------------------------------------------------------------------
	| __construct()
	|--------------------------------------------------------------------------
	|
	| Sets up the class to ensure that CSRF tokens are validated on the POST
	| verb.
	|
	*/
	public function __construct()
	{
		$this->beforeFilter('csrf', array('on' => 'post'));
	}

	/*
	|--------------------------------------------------------------------------
	| getConfigure()
	|--------------------------------------------------------------------------
	|
	| Returns the main configuration view.
	|
	*/
	public function getConfigure()
	{
		if(!SrpHelper::isAdmin()) {
			throw new NotFoundHttpException; }

		// Fetch every ship from invTypes instead of only configured ships from srp_ship_type
		$shipTypes = DB::table('invTypes')
			->select('invTypes.typeID AS shipTypeID', 'invTypes.typeName AS shipTypeName', DB::raw('COALESCE(srp_ship_type.shipTypeValue, 0) AS shipTypeValue'))
			->join('invGroups', 'invGroups.groupID', '=', 'invTypes.groupID')
			->join('srp_ship_type', 'srp_ship_type.shipTypeID', '=', 'invTypes.typeID', 'left')
			->where('invGroups.categoryID', '=', 6)
			->get();

		$fleetTypes = SrpFleetType::withFleetCount()->get();

		return View::make('srp.config')
			->with('fleetTypes', $fleetTypes)
			->with('shipTypes', $shipTypes);
	}

	/*
	|--------------------------------------------------------------------------
	| getConfigureFleetType()
	|--------------------------------------------------------------------------
	|
	| Returns the fleet type configuration view.
	|
	*/
	public function getConfigureFleetType($fleetTypeID)
	{
		if(!SrpHelper::isAdmin() || !$fleetType = SrpFleetType::find($fleetTypeID)) {
			throw new NotFoundHttpException; }

		return View::make('srp.fleetType')->with('fleetType', $fleetType);
	}

	/*
	|--------------------------------------------------------------------------
	| getConfigureShipType()
	|--------------------------------------------------------------------------
	|
	| Returns the ship type configuration view.
	|
	*/
	public function getConfigureShipType($shipTypeID)
	{
		if(!SrpHelper::isAdmin()) {
			throw new NotFoundHttpException; }

		$shipType = SrpShipType::firstOrCreate(array('shipTypeID' => $shipTypeID, 'shipTypeValue' => 0.00));

		return View::make('srp.shipType')->with('shipType', SrpShipType::withTypeName()->find($shipTypeID));
	}

	/*
	|--------------------------------------------------------------------------
	| postConfigureFleetType()
	|--------------------------------------------------------------------------
	|
	| Handles inserting and updating fleet types.
	|
	*/
	public function postConfigureFleetType($fleetTypeID = null)
	{
		if(!SrpHelper::isAdmin()) {
			throw new NotFoundHttpException; }

		$validator = Validator::make(Input::all(), array(
			'fleetTypeName' => 'required|min:3|max:20',
		));

		if($validator->fails()) {
			return Redirect::back()
				->withErrors($validator->errors())
				->withInput(Input::all()); }

		try {
			// Insert
			if(!$fleetType = SrpFleetType::find($fleetTypeID)) {
				SrpFleetType::create(array('fleetTypeName' => Input::get('fleetTypeName'))); }
			// Update
			else {
				$fleetType->fleetTypeName = Input::get('fleetTypeName');
				$fleetType->save(); } }

		// Database exceptions
		catch(PDOException $e) {
			switch ($e->getCode()) {

				case 23000: // Duplicate
					// Restore a record if it is trashed
					$fleetType = SrpFleetType::withTrashed()->where('fleetTypeName', '=', Input::get('fleetTypeName'))->first();
					if ($fleetType->trashed()) { $fleetType->restore(); break; }

					return Redirect::back()
						->withErrors(array('error' => 'That fleet type already exists.'))
						->withInput(Input::all());

				default: // Unknown
					return Redirect::back()
						->withErrors(array('error' => 'Failed update fleet types.'))
						->withInput(Input::all()); } }

		// Other exceptions
		catch(Exception $e) {
			return Redirect::back()
				->withErrors('Failed to update fleet types.')
				->withInput(Input::all()); }

		Session::flash('success', 'Fleet types updated.');
		return Redirect::action('SrpController@getConfigure');
	}

	/*
	|--------------------------------------------------------------------------
	| postConfigureShipType()
	|--------------------------------------------------------------------------
	|
	| Handles updating ship types.
	|
	*/
	public function postConfigureShipType($shipTypeID)
	{
		if(!SrpHelper::isAdmin() || !$shipType = SrpShipType::find($shipTypeID)) {
			throw new NotFoundHttpException; }

		$validator = Validator::make(Input::all(), array(
			'srpValue' => array('required', 'regex:/^[\d]{1,12}(?:$|\.[\d]{2}$)/'),
		));

		if($validator->fails()) {
			return Redirect::back()
				->withErrors($validator->errors())
				->withInput(Input::all()); }

		// Update the database
		$shipType->shipTypeValue = Input::get('srpValue');
		$shipType->save();

		Session::flash('success', "Ship types updated.");
		return Redirect::action('SrpController@getConfigure');
	}

	/*
	|--------------------------------------------------------------------------
	| getDeleteFleetType()
	|--------------------------------------------------------------------------
	|
	| Handles deleting fleet types.
	|
	*/
	public function getDeleteFleetType($fleetTypeID)
	{
		if(!SrpHelper::isAdmin() || !$fleetType = SrpFleetType::find($fleetTypeID)) {
			throw new NotFoundHttpException; }

		$fleetType->delete();

		Session::flash('success', 'Fleet type deleted.');
		return Redirect::action('SrpController@getConfigure');
	}

	/*
	|--------------------------------------------------------------------------
	| getFleets()
	|--------------------------------------------------------------------------
	|
	| Returns the view that shows all of a user's created fleets, or every
	| single fleet if permissions allow it. Also contains the form for creating
	| new fleets.
	|
	*/
	public function getFleets($scope = 'self')
	{
		// Limit the scope of characters to search from
		if     ($scope == 'self') { $characters = SrpHelper::getCharacters(false); }
		else if($scope ==  'all') { $characters = SrpHelper::getCharacters(true ); }
		else { throw new NotFoundHttpException; }

		// Fetch fleets from the database
		$fleets = DB::table('srp_fleet AS fleet')
			->select('fleet.fleetID', 'fleet.created_at', 'fleet.fleetSrpCode', 'fleetType.fleetTypeName', 'character.characterName AS fleetCharacterName', DB::raw('COUNT(request.requestID) AS totalRequests'))
			->join('account_apikeyinfo_characters AS character', 'character.characterID', '=', 'fleet.fleetCharacterID')
			->join('srp_fleet_type AS fleetType', 'fleetType.fleetTypeID', '=', 'fleet.fleetTypeID')
			->leftJoin('srp_request AS request', 'request.fleetID', '=', 'fleet.fleetID')
			->groupBy('fleet.fleetID')
			->orderBy('fleet.created_at', 'DESC')
			->whereIn('fleet.fleetCharacterID', count($characters) ? array_keys($characters) : array(0))
			->get();

		return View::make('srp.fleets')
			->with('fleetTypes', SrpHelper::getFleetTypes())
			->with('characters', SrpHelper::getCharacters(false))
			->with('fleets', $fleets);
	}

	/*
	|--------------------------------------------------------------------------
	| postFleet()
	|--------------------------------------------------------------------------
	|
	| Handles inserting new fleets into the database.
	|
	*/
	public function postFleet()
	{
		$validator = Validator::make(Input::all(), array(
			'fleetCommander' => 'required|integer',
			'fleetType' => 'required|integer',
			'srpCode' => 'required|min:5|max:20',
		));

		if($validator->fails()) {
			return Redirect::back()
				->withErrors($validator->errors())
				->withInput(Input::all()); }

		// Attempt to update the database
		try {
			$fleet = new SrpFleet;
			$fleet->fleetCharacterID = Input::get('fleetCommander');
			$fleet->fleetTypeID = Input::get('fleetType');
			$fleet->fleetSrpCode = Input::get('srpCode');
			$fleet->save(); }

		// Database exceptions
		catch(PDOException $e) {
			switch ($e->getCode()) {

				case 23000: // Duplicate
					return Redirect::back()
						->withErrors(array('error' => 'That srp code has already been used.'))
						->withInput(Input::all());

				default: // Unknown
					return Redirect::back()
						->withErrors(array('error' => 'Failed to create a new fleet.'))
						->withInput(Input::all());  } }

		// Other exceptions
		catch(Exception $e) {
			return Redirect::back()
				->withErrors('Failed to create a new fleet.')
				->withInput(Input::all()); }

		Session::flash('success', 'Your fleet has been created.');
		return Redirect::back();
	}

	/*
	|--------------------------------------------------------------------------
	| getFleet()
	|--------------------------------------------------------------------------
	|
	| Returns the view that shows all srp requests related to a fleet.
	|
	*/
	public function getFleet($fleetID)
	{
		// Fetch fleet from the database
		$fleet = DB::table('srp_fleet AS fleet')
			->select('fleet.fleetID', 'fleet.created_at', 'fleet.fleetSrpCode', 'fleetType.fleetTypeName', 'fleet.fleetCharacterID', 'account.characterName AS fleetCharacterName', DB::raw('COUNT(request.requestID) AS totalRequests'))
			->join('account_apikeyinfo_characters AS account','account.characterID', '=', 'fleet.fleetCharacterID')
			->join('srp_fleet_type AS fleetType', 'fleetType.fleetTypeID', '=', 'fleet.fleetTypeID')
			->leftJoin('srp_request AS request','request.fleetID', '=', 'fleet.fleetID')
			->where('fleet.fleetID', '=', $fleetID)
			->first();
		if(!$fleet) { throw new NotFoundHttpException; }

		// Check for permissions
		if(!SrpHelper::ownsFleet($fleetID) && !SrpHelper::isManager()) {
			throw new NotFoundHttpException; }

		// Fetch requests from the database
		$requests = DB::table('srp_request AS request')
			->select('request.requestID', 'request.created_at', 'kill.killID', 'kill.killTime', 'status.statusTypeID', 'type.typeName AS shipTypeName', 'status.statusValue', 'statusType.statusTypeID', 'statusType.statusTypeTag', 'statusType.statusTypeName', 'account.characterName')
			->join('srp_kill AS kill','kill.killID', '=', 'request.killID')
			->join('invTypes AS type','type.typeID', '=', 'kill.shipTypeID')
			->join('srp_status AS status','status.requestID', '=', 'request.requestID')
			->join('account_apikeyinfo_characters AS account','account.characterID', '=', 'request.requestCharacterID')
			->join('srp_status_type AS statusType', 'statusType.statusTypeID', '=', 'status.statusTypeID')
			->orderBy('request.requestID', 'DESC')
			->orderBy('status.created_at', 'DESC')
			->where('request.fleetID', $fleet->fleetID)
			->get();

		// Unset old status updates so that only the newest shows
		$lastRequestID = 0;
		foreach($requests as $key => $value) {
			if($value->requestID == $lastRequestID) { unset($requests[$key]); }
			$lastRequestID = $value->requestID; }

		return View::make('srp.fleet')
			->with('fleet', $fleet)
			->with('requests', $requests);
	}

	/*
	|--------------------------------------------------------------------------
	| getRequests()
	|--------------------------------------------------------------------------
	|
	| Returns the view that shows all of a user's created srp requests, or
	| every single request if permissions allow it. Also contains the form for
	| creating new srp requests.
	|
	*/
	public function getRequests($scope = 'self')
	{
		// Limit the scope of characters to search from
		if     ($scope == 'self') { $characters = SrpHelper::getCharacters(false); }
		else if($scope ==  'all') { $characters = SrpHelper::getCharacters(true ); }
		else { throw new NotFoundHttpException; }

		// Fetch requests from the database
		$requests = DB::table('srp_request AS request')
			->select('request.requestID', 'request.created_at', 'kill.killID', 'kill.killTime', 'status.statusTypeID', 'type.typeName AS shipTypeName', 'status.statusValue', 'statusType.statusTypeID', 'statusType.statusTypeName', 'statusType.statusTypeTag', 'account.characterName')
			->join('srp_kill AS kill','kill.killID', '=', 'request.killID')
			->join('srp_status AS status','status.requestID', '=', 'request.requestID')
			->join('invTypes AS type','type.typeID', '=', 'kill.shipTypeID')
			->join('account_apikeyinfo_characters AS account', 'account.characterID', '=', 'request.requestCharacterID')
			->join('srp_status_type AS statusType', 'statusType.statusTypeID', '=', 'status.statusTypeID')
			->orderBy('request.requestID', 'DESC')
			->orderBy('status.created_at', 'DESC')
			->whereIn('request.requestCharacterID', count($characters) ? array_keys($characters) : array(0))
			->get();

		// Unset old status updates so that only the newest shows
		$lastRequestID = 0;
		foreach($requests as $key => $value) {
			if($value->requestID == $lastRequestID) { unset($requests[$key]); }
			$lastRequestID = $value->requestID; }

		return View::make('srp.requests')
			->with('requests', $requests);
	}

	/*
	|--------------------------------------------------------------------------
	| getRequests()
	|--------------------------------------------------------------------------
	|
	| Returns the view that shows all of a user's created srp requests, or
	| every single request if permissions allow it. Also contains the form for
	| creating new srp requests.
	|
	*/
	public function getRequest($requestID)
	{
		// Fetch statuses from the database
		$statuses = DB::table('srp_status AS status')
			->select('status.statusID', 'status.created_at', 'status.statusTypeID', 'status.requestID', 'status.statusValue', 'status.statusNotes', 'statusType.statusTypeID', 'statusType.statusTypeName', 'statusType.statusTypeTag', 'request.fleetID', 'account.characterName', 'type.typeName AS shipTypeName', 'shipType.shipTypeValue')
			->join('account_apikeyinfo_characters AS account', 'account.characterID', '=', 'status.statusCharacterID')
			->join('srp_status_type AS statusType', 'statusType.statusTypeID', '=', 'status.statusTypeID')
			->join('srp_request AS request', 'request.requestID', '=', 'status.requestID')
			->join('srp_kill AS kill', 'kill.killID', '=', 'request.killID')
			->join('invTypes AS type', 'type.typeID', '=', 'kill.shipTypeID')
			->join('srp_ship_type AS shipType', 'shipType.shipTypeID', '=', 'kill.shipTypeID', 'left' )
			->where('status.requestID', '=', $requestID)
			->orderBy('status.created_at', 'DESC')
			->get();
		if (!count($statuses)) { throw new NotFoundHttpException; }

		// Check permissions
		$canUpdate = SrpHelper::ownsFleet($statuses[0]->fleetID) || SrpHelper::isManager();
		$canView   = SrpHelper::ownsFleet($statuses[0]->fleetID) || SrpHelper::isManager() || SrpHelper::ownsRequest($statuses[0]->requestID);
		if (!$canUpdate && !$canView) { throw new NotFoundHttpException; }

		return View::make('srp.request')
			->with('statuses', $statuses )
			->with('canUpdate', $canUpdate)
			->with('characters', SrpHelper::getCharacters(false))
			->with('statusTypes', SrpHelper::getStatusTypes());
	}

	/*
	|--------------------------------------------------------------------------
	| postRequest()
	|--------------------------------------------------------------------------
	|
	| Handles updating the status for an srp request.
	|
	*/
	public function postRequest($requestID)
	{
		if(!$request = SrpRequest::find($requestID)) {
			throw new NotFoundHttpException; }

		$validator = Validator::make(Input::all(), array(
			'updater' => 'required|integer',
			'status' => 'required|integer',
			'value' => array('required', 'regex:/^[\d]{1,12}(?:$|\.[\d]{2}$)/'),
			'notes' => 'max:100',
		));

		if($validator->fails()) {
			return Redirect::back()
				->withErrors($validator->errors())
				->withInput(Input::all()); }

		// Permissions
		$canUpdate = SrpHelper::ownsFleet($request->fleetID) || SrpHelper::isManager();
		if (!$canUpdate) { throw new NotFoundHttpException; }

		// Attempt to insert into the dataabse
		try {
			$status = new SrpStatus;
			$status->statusCharacterID = Input::get('updater');
			$status->statusTypeID = Input::get('status');
			$status->statusValue = Input::get('value');
			$status->statusNotes = Input::get('notes');
			$status->requestID = $requestID;
			$status->save(); }

		// Other exceptions
		catch(Exception $e) {
			return Redirect::back()
				->withErrors('Failed to update request status.')
				->withInput(Input::all()); }

		Session::flash('success', 'Request status has been updated.');
		return Redirect::back();
	}

	/*
	|--------------------------------------------------------------------------
	| postRequests()
	|--------------------------------------------------------------------------
	|
	| Handles inserting a new srp request into the database.
	|
	*/
	public function postRequests()
	{
		$validator = Validator::make(Input::all(), array(
			'zkillLink' => array('required', 'regex:%^(?:http|https)://zkillboard.com/kill/\d+(?:/$|$)%'),
			'srpCode' => 'required|min:5|max:20',
		));

		if ($validator->fails()) {
			return Redirect::back()
				->withErrors($validator->errors())
				->withInput(Input::all()); }

		// Find a fleet with the supplied srp code
		if (!$fleet = SrpFleet::where('fleetSrpCode', '=', Input::get('srpCode'))->first()) {
			return Redirect::back()
				->withErrors(array('error' => 'No fleet exists with that srp code.'))
				->withInput(Input::all()); }

		// Convert a normal link into an api link and fetch the killmail
		// Cache the killmail temporarily incase of failures and having to redo the request
		preg_match('%^(?:http|https)://zkillboard.com/kill/(\d+)(?:/$|$)%', Input::get('zkillLink'), $matches);
		$killData = Cache::has('zkb:' . $matches[1])
			? Cache::get('zkb:' . $matches[1])
			: json_decode(file_get_contents('https://zkillboard.com/api/killID/' . $matches[1]), true);

		if (!is_array($killData)) {
			return Redirect::back()
				->withErrors(array('error' => 'Failed to fetch the killmail from zKillboard.'))
				->withInput(Input::all()); }

		Cache::put('zkb:' . $matches[1], $killData, strtotime('+10 minutes'));

		// Make sure only the character owner can post their own killmail
		if (!in_array($killData[0]['victim']['characterName'], SrpHelper::getCharacters(false))) {
			return Redirect::back()
				->withErrors(array('error' => 'You may only submit your own killmails.'))
				->withInput(Input::all()); }

		// Attempt to insert the request into the database.
		try {
			DB::transaction(function() use ($killData, $fleet) {
				$kill = new SrpKill;
				$kill->killID = $killData[0]['killID'];
				$kill->killTime = $killData[0]['killTime'];
				$kill->solarSystemID = $killData[0]['solarSystemID'];
				$kill->moonID = $killData[0]['moonID'];
				$kill->shipTypeID = $killData[0]['victim']['shipTypeID'];
				$kill->characterID = $killData[0]['victim']['characterID'];
				$kill->corporationID = $killData[0]['victim']['corporationID'];
				$kill->allianceID = $killData[0]['victim']['allianceID'];
				$kill->factionID = $killData[0]['victim']['factionID'];
				$kill->damageTaken = $killData[0]['victim']['damageTaken'];
				$kill->hash = $killData[0]['zkb']['hash'];
				$kill->totalValue = $killData[0]['zkb']['totalValue'];
				$kill->points = $killData[0]['zkb']['points'];
				$kill->save();

				$request = new SrpRequest;
				$request->requestCharacterID = $kill->characterID;
				$request->fleetID = $fleet->fleetID;
				$request->killID = $killData[0]['killID'];
				$request->save();

				if(!$shipType = SrpShipType::find($killData[0]['victim']['shipTypeID'])) {
					$shipType = new SrpShipType;
					$shipType->shipTypeID = $killData[0]['victim']['shipTypeID'];
					$shipType->shipTypeValue = 0.00;
					$shipType->save(); }

				$status   = new SrpStatus;
				$status->statusCharacterID = $kill->characterID;
				$status->statusTypeID = 2; // Evaluating
				$status->statusValue = $shipType->shipTypeValue;
				$status->statusNotes = '';
				$status->requestID = $request->requestID;
				$status->save();
			}); }

		// Database exceptions
		catch (PDOException $e) {
			switch ($e->getCode()) {

				case 23000: // Duplicate value
					return Redirect::back()
						->withErrors(array('error' => 'An srp request has already been made with that killmail.'))
				->withErrors(array('error' => $e->getMessage()))
						->withInput(Input::all());

				default: // Unknown
					return Redirect::back()
						->withErrors(array('error' => 'Failed to insert your request into the database.'))
						->withInput(Input::all()); } }

		// Other exceptions
		catch (Exception $e) {
			return Redirect::back()
				->withErrors(array('error' => 'Failed to insert your request into the database.'))
				->withErrors(array('error' => $e->getMessage()))
				->withInput(Input::all()); }

		Session::flash('success', 'Your srp request has been added to the database.');
		return Redirect::back();
	}
}
