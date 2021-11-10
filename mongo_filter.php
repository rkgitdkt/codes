<?php 
class MongoFilter
{
  public function data(Request $request, $type)
    {	
    	/*now collect all matches that will match together*/
		$and_match['role.name'] = $type;

    	if($request->has('user_id') && !empty($request->user_id))
    		$and_match['_id'] = (int)trim($request->user_id);

    	if($request->has('sponsor_by') && $request->sponsor_by != 'All')
    	{
    		if($request->sponsor_by == 'self')
    		{
    			$and_match['sponsor_id']['$eq'] = null;
    		}
    		else
    			$and_match['sponsor_id']['$ne'] = null;

    	}

    	if($request->has('plan_id') && !empty($request->plan_id))
    		$and_match['plan'] = (int)trim($request->plan_id);

    	/*renewal time today, this week, this month*/
    	if($request->has('renewal_time') && !empty($request->renewal_time) && $request->renewal_time != 'All')
    	{
    		if($request->renewal_time == 'Today')
    		{
	    		$and_match['expire_date']['$eq'] = date('Y-m-d');
    		}
    		elseif($request->renewal_time == 'Week')
	    	{
	    		$and_match['expire_date'] = [
	    			'$gte' => date("Y-m-d", strtotime('monday this week')),
	    			'$lte' => date("Y-m-d", strtotime('sunday this week'))
	    		];
	    	}
	    	else
	    	{
	    		$and_match['expire_date'] = [
	    			'$gte' => date("Y-m-d", strtotime('first day of this month')),
	    			'$lte' => date("Y-m-d", strtotime('last day of this month'))
	    		];
	    	}
    	}

    	/*filter dates*/
    	if($request->has('expire_date_from') && !empty($request->expire_date_from))
	    		$and_match['expire_date']['$gte'] = date('Y-m-d', strtotime($request->expire_date_from));

    	if($request->has('expire_date_to') && !empty($request->expire_date_to))
    		$and_match['expire_date']['$lte'] = date('Y-m-d', strtotime($request->expire_date_to));

		$data = User::where('deleted_at', NUll)->raw(function($collection)use ($type, $and_match)
		{
			return $collection->aggregate([
				// Join with user_role table
				[
					'$lookup'=>[
						'from' => 'role_users',  //collection name of which you want to join
						'localField' => '_id',   //localfidl name
						'foreignField' => 'user_id', // foreign field
						'as' => 'user_role'
					]
				],

				['$unwind' => '$user_role'], // $unwind used for getting data in object or for one record only



				[
					'$lookup'=>[
						'from' => 'roles',
						'localField' => 'user_role.role_id',
						'foreignField' => '_id',
						'as' => 'role'
					]
				],

				['$unwind' => '$role'],

				[
					'$lookup'=>[
						'from' => 'plans',  //collection name of which you want to join
						'localField' => 'plan',   //localfidl name
						'foreignField' => '_id', // foreign field
						'as' => 'plans'
					]
				],
				[
					'$unwind' => [
						'path' => '$plans',
						'preserveNullAndEmptyArrays' => true
					]
				],

				[
					'$match'=>[
						'$and'=>[
							$and_match
						]
					]
				],
				['$project' => [
					  '_id' => 1,
					  'first_name' => 1,
					  'company_name'=>1,
					  'email' => 1,
					  'mobileno' => 1,
					  'plan'=>1,
					  'expire_date'=>1,
					  'plans'=>['name'=>1],

					]
				  ],
			]);
		});

		// dd($data->toArray());

		foreach($data as $key=>$val)
		{
			if($type=='Demo')
			{
				$val->wpstatus=0;
			}
			else
			{
				$val->wpstatus = GlobalNumber::where('user_id',(int)$val->_id)->where('status','Active')->where('wpstatus','Active')->count();
			}

			/*get remaining blance
				$val->balance = @Recharge::select('balance')->where('user_id',$val->_id)->orderBy('_id','DESC')->first()->balance;
			*/
			
			$val->balance=User::getUserBalance((int)$val->_id);
			if($val->expire_date!="")
				$val->expire_date=date("d,M Y",strtotime($val->expire_date));
		}

		return DataTables::of($data)
		->addColumn('actions','
			<a href="{{route(\'edit.user\',Crypt::encrypt($_id))}}" class="btn btn-xs" title="Edit"><i class="fa fa-pencil"></i></a><a href="{{route(\'autologin\',Crypt::encrypt($_id))}}" class="btn btn-xs" title="Auto Login"><i class="fa fa-sign-in"></i></a><a href="{{route(\'troubleshoot.user\',Crypt::encrypt($_id))}}" class="btn btn-xs" title="Troubleshot"><i class="fa fa-cog"></i></a>
		')
		->rawColumns(['actions'])
		 ->make(true);
	}
}
?>
