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

namespace Seat\Services\Helpers;

class SrpHelper {
	/*
	|--------------------------------------------------------------------------
	| isAdmin()
	|--------------------------------------------------------------------------
	|
	| Checks if a user is an srp admin.
	|
	*/
	public static function isAdmin() {
		return \Auth::hasAccess('srp_admin') == 1;
	}

	/*
	|--------------------------------------------------------------------------
	| isManager()
	|--------------------------------------------------------------------------
	|
	| Checks if a user is an srp manager.
	|
	*/
	public static function isManager() {
		return (\Auth::hasAccess('srp_manager') || self::isAdmin()) == 1;
	}

	/*
	|--------------------------------------------------------------------------
	| ownsFleet()
	|--------------------------------------------------------------------------
	|
	| Checks if a user owns a fleet.
	|
	*/
	public static function ownsFleet($fleetID) {
		$characters = self::getCharacters();
		$fleet = \SrpFleet::find($fleetID);

		return in_array($fleet->fleetCharacterID, array_keys($characters)) == 1;
	}

	/*
	|--------------------------------------------------------------------------
	| ownsRequest()
	|--------------------------------------------------------------------------
	|
	| Checks if a user owns a request.
	|
	*/
	public static function ownsRequest($requestID) {
		$characters = self::getCharacters();
		$request = \SrpRequest::find($requestID);

		return in_array($request->requestCharacterID, array_keys($characters)) == 1;
	}

	/*
	|--------------------------------------------------------------------------
	| getCharacters()
	|--------------------------------------------------------------------------
	|
	| Returns all charactrers registered to an account, or if permissions allow
	| it, every registered charactrer.
	|
	*/
	public static function getCharacters($all = false)
	{
		// Must have permissions to return all characters
		if($all && !self::isManager()) { $all = false; }

		// Fetch characters from the database
		$rows = \DB::table('account_apikeyinfo_characters')
			->select('characterID', 'characterName')
			->join('seat_keys', 'account_apikeyinfo_characters.keyID', '=', 'seat_keys.keyID')
			->where('seat_keys.user_id', $all ? '>' : '=', $all ? 0 : \Auth::User()->id)
			->get();

		// Format as $array[characterID] = characterName
		$result = array();
		foreach ($rows as $key => $value) {
			$result[$value->characterID] = $value->characterName; }

		return $result;
	}

	/*
	|--------------------------------------------------------------------------
	| getFleetTypes()
	|--------------------------------------------------------------------------
	|
	| Returns all fleet types from the database.
	|
	*/
	public static function getFleetTypes()
	{
		// Fetch fleet types from the database
		$rows = \SrpFleetType::all();

		// Format as $array[fleetTypeID] = fleetTypeName
		$result = array();
		foreach ($rows as $row) {
			$result[$row->fleetTypeID] = $row->fleetTypeName; }

		return $result;
	}

	/*
	|--------------------------------------------------------------------------
	| getStatusTypes()
	|--------------------------------------------------------------------------
	|
	| Returns all status types from the database.
	|
	*/
	public static function getStatusTypes()
	{
		// Fetch status types from the database
		$rows = \SrpStatusType::all();

		// Format as $array[statusTypeID] = statusTypeName
		$result = array();
		foreach ($rows as $row) {
			$result[$row->statusTypeID] = $row->statusTypeName; }

		return $result;
	}
}
