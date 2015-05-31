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

class SrpFleetType extends Eloquent
{
	protected $fillable   = array('fleetTypeName');
	protected $table      = 'srp_fleet_type';
	protected $primaryKey = 'fleetTypeID';
	protected $softDelete = true;

	public function scopeWithFleetCount($query) {
		$query
			->select('srp_fleet_type.fleetTypeID', 'srp_fleet_type.fleetTypeName', DB::raw('COUNT(srp_fleet.fleetID) AS fleetCount'))
			->join('srp_fleet', 'srp_fleet.fleetTypeID', '=', 'srp_fleet_type.fleetTypeID', 'left')
			->groupBy('srp_fleet_type.fleetTypeID');
	}
}