<?php
	ini_set('display_errors', 1);
	error_reporting(E_ALL);

	/*
	Factorials
	function get_fact($no)
	{
		//check if number is greater than 0.
		if($no <= 0)
			return 0;

		$result = 1;
		for($number = $no; $number > 0; $number--)
		{
			$result *= $number;
		}

		return $result;
	}

	echo "Factorials of 5 is : ". get_fact(5);
	echo "<br/>Factorials of 7 is : ". get_fact(7);
	*/

	/*swap two numbers
	$no1 = 90;
	$no2 = 100;
	//define a temporary variable.
	$temp = $no1;
	echo "Before swap no1=$no1, no2=$no2";
	$no1 = $no2;
	$no2 = $temp;
	echo "<br/> After swap no1=$no1, no2=$no2";
	*/

	/*check even odd
	function check_even_odd($number)
	{
		if($number%2 == 0)
			echo "$number is an even number.";
		else
			echo "$number is an odd number.";
	}

	check_even_odd(22);
	echo"<br/>";
	check_even_odd(21);
	*/

	/*Print Table of any Number
	* @param $number int
	
	function print_table($number)
	{
		echo "Table of $number : <br/>";
		for($i=1 ; $i<=10 ; $i++)
		{
			echo $i*$number."<br/>";
		}
	}

	print_table(10);
	print_table(14);
	*/

	/*Reverse String Program
	function reverse_str($str)
	{
		$length = strlen($str);
		for($i=($length -1); $i >= 0; $i--)
		{
			echo $str[$i];
		}
	}

	reverse_str('elephant');
	echo '<br>';
	reverse_str('mohit');
	*/

	
?>