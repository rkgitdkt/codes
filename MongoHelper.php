<?php
namespace App\Helpers;
use MongoDB\BSON\UTCDateTime;
use MongoDB\BSON\ObjectID;
use DateTime;
use Carbon\Carbon;
class MongoHelper 
{

	 /* Returns the _id as Object ID format in MongoDB
     *
     * @param   string $_id 
     * @return  ObjectId
     */
  	public static function get_mongo_id($_id)
  	{
  		if($_id instanceof ObjectId)
        {
            return $_id;
        }
        else
        {
          
        	return new ObjectId($_id);
        }
  	}

    /* Returns the date as required by dates format in MongoDB
     *
     * @param   string $date The string to check
     * @return  string
     */
    public static function get_mongo_date($date)
    {
        if ($date instanceof UTCDateTime)
        {
            return $date;
        }
        else if ($date instanceof Carbon)
        {
            return new UTCDateTime(new DateTime($date->toDateTimeString()));
        }
        else
        {
            return new UTCDateTime(new DateTime($date));
        }
    }
}
