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

class SrpSeeder extends Seeder
{
	/*
	|--------------------------------------------------------------------------
	| run()
	|--------------------------------------------------------------------------
	|
	| Seeds all of the databases related to srp.
	|
	*/
	public function run()
	{
		// Seed fleetTypes.
		DB::table('srp_fleet_type')->truncate();
		SrpFleetType::create(array('fleetTypeName' => 'Defense'));
		SrpFleetType::create(array('fleetTypeName' => 'Fun'));
		SrpFleetType::create(array('fleetTypeName' => 'Roam'));
		SrpFleetType::create(array('fleetTypeName' => 'Stratop'));
		SrpFleetType::create(array('fleetTypeName' => 'Structure Bash'));

		// Seed statusTypes.
		DB::table('srp_status_type')->truncate();
		SrpStatusType::create(array('statusTypeName' => 'Rejected', 'statusTypeTag' => 'danger'));
		SrpStatusType::create(array('statusTypeName' => 'Evaluating', 'statusTypeTag' => 'warning'));
		SrpStatusType::create(array('statusTypeName' => 'Approved', 'statusTypeTag' => 'primary'));
		SrpStatusType::create(array('statusTypeName' => 'Paid', 'statusTypeTag' => 'success'));

		// Seed permissions.
		Eloquent::unguard();
		SeatPermissions::firstOrCreate(array('permission' => 'srp_admin'));
		SeatPermissions::firstOrCreate(array('permission' => 'srp_manager'));

		// Done.
		$this->command->info('SRP tables seeded!');
	}
}
