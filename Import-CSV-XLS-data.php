<?php
namespace App\Http\Controllers;
use App\Helpers\MongoHelper;
use Auth;
use Illuminate\Pagination\Paginator;
use Illuminate\Pagination\LengthAwarePaginator;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use Redirect;
use Storage;
use URL;
use Validator;
use view;
class ImportController extends Controller 
{
	public function CharacterUpdate($str) 
	{
		return $str;
		$cp1252_map = array(
			"\xc2\x80" => "\xe2\x82\xac", /* EURO SIGN */
			"\xc2\x82" => "\xe2\x80\x9a", /* SINGLE LOW-9 QUOTATION MARK */
			"\xc2\x83" => "\xc6\x92",     /* LATIN SMALL LETTER F WITH HOOK */
			"\xc2\x84" => "\xe2\x80\x9e", /* DOUBLE LOW-9 QUOTATION MARK */
			"\xc2\x85" => "\xe2\x80\xa6", /* HORIZONTAL ELLIPSIS */
			"\xc2\x86" => "\xe2\x80\xa0", /* DAGGER */
			"\xc2\x87" => "\xe2\x80\xa1", /* DOUBLE DAGGER */
			"\xc2\x88" => "\xcb\x86",     /* MODIFIER LETTER CIRCUMFLEX ACCENT */
			"\xc2\x89" => "\xe2\x80\xb0", /* PER MILLE SIGN */
			"\xc2\x8a" => "\xc5\xa0",     /* LATIN CAPITAL LETTER S WITH CARON */
			"\xc2\x8b" => "\xe2\x80\xb9", /* SINGLE LEFT-POINTING ANGLE QUOTATION */
			"\xc2\x8c" => "\xc5\x92",     /* LATIN CAPITAL LIGATURE OE */
			"\xc2\x8e" => "\xc5\xbd",     /* LATIN CAPITAL LETTER Z WITH CARON */
			"\xc2\x91" => "\xe2\x80\x98", /* LEFT SINGLE QUOTATION MARK */
			"\xc2\x92" => "\xe2\x80\x99", /* RIGHT SINGLE QUOTATION MARK */
			"\xc2\x93" => "\xe2\x80\x9c", /* LEFT DOUBLE QUOTATION MARK */
			"\xc2\x94" => "\xe2\x80\x9d", /* RIGHT DOUBLE QUOTATION MARK */
			"\xc2\x95" => "\xe2\x80\xa2", /* BULLET */
			"\xc2\x96" => "\xe2\x80\x93", /* EN DASH */
			"\xc2\x97" => "\xe2\x80\x94", /* EM DASH */
		
			"\xc2\x98" => "\xcb\x9c",     /* SMALL TILDE */
			"\xc2\x99" => "\xe2\x84\xa2", /* TRADE MARK SIGN */
			"\xc2\x9a" => "\xc5\xa1",     /* LATIN SMALL LETTER S WITH CARON */
			"\xc2\x9b" => "\xe2\x80\xba", /* SINGLE RIGHT-POINTING ANGLE QUOTATION*/
			"\xc2\x9c" => "\xc5\x93",     /* LATIN SMALL LIGATURE OE */
			"\xc2\x9e" => "\xc5\xbe",     /* LATIN SMALL LETTER Z WITH CARON */
			"\xc2\x9f" => "\xc5\xb8"      /* LATIN CAPITAL LETTER Y WITH DIAERESIS*/
		);
	
		return strtr(utf8_encode($str), $cp1252_map);
	
	}

	/*import questions from word file*/
	private function import_word_questions($file, $added_question, $test_series, $test_series_id)
	{
		$zip = zip_open($file);
		if (!$zip || is_numeric($zip))
		{
			return response()->json(['errorArray' => [], 'error_msg' => 'Something went wrong docx file couldn\'t be open.', 'slideToTop' => 'yes']);
		} 

		$questions_string = '';
    	$content = '';
		while ($zip_entry = zip_read($zip)) 
		{
			if (zip_entry_open($zip, $zip_entry) == FALSE) continue;

			if (zip_entry_name($zip_entry) != "word/document.xml") continue;

			$content .= zip_entry_read($zip_entry, zip_entry_filesize($zip_entry));
			zip_entry_close($zip_entry);
		}

		zip_close($zip);

        $content = str_replace('</w:r></w:p></w:tc><w:tc>', " ", $content);
        $content = str_replace('</w:r></w:p>', "\r\n", $content);
        $questions_string = strip_tags($content);

		$length = strlen($questions_string);
		
		$questions = [];
		$question = '';
		$options_chr = ['a', 'b', 'c', 'd', 'e', 'f', 'g', 'h'];
		$options_num = ['1', '2', '3', '4', '5', '6', '7', '8'];
		for($index = 0; $index < $length; $index++)
		{   
			$char = $questions_string[$index];
			$next_one = isset($questions_string[$index+1]) ? $questions_string[$index+1] : '';
			$next_two = isset($questions_string[$index+2]) ? $questions_string[$index+2] : '';
			$next_three = isset($questions_string[$index+3]) ? $questions_string[$index+3] : '';
			$next_four = isset($questions_string[$index+4]) ? $questions_string[$index+4] : '';

			$q_no = $next_one.$next_two.$next_three;
			if($char == 'Q' && is_numeric($q_no) && strlen($q_no)==3 && $next_four == '.')
			{
				/*now extract question*/
				for($q_index = ($index+5); $q_index<$length ; $q_index++)
				{	
					$q_char = $questions_string[$q_index];
					$next_q_char = $questions_string[$q_index+1] ?? '';
					$next_two = isset($questions_string[$q_index+2]) ? $questions_string[$q_index+2] : 0;

					/*check if there is any answers : (a) or (1)*/
					if(	
						$q_char == '(' &&
						(in_array($next_q_char, $options_chr) || in_array($next_q_char, $options_num)) && 
						$next_two == ')'
					)
					{
						/*now extract answer*/
						$answers = array();
						$answer = '';
						$solution = '';
						$right_answer = null;

						for($ans_index = ($q_index+3); $ans_index<$length ; $ans_index++)
						{
							$ans_char = $questions_string[$ans_index];
							
							$next_ans_char = isset($questions_string[$ans_index+1]) ? $questions_string[$ans_index+1] : 0;
							$next_two_ans_char = isset($questions_string[$ans_index+2]) ? $questions_string[$ans_index+2] : 0;

							/*check if there is next answer then push previous answer*/
							if($ans_char == '(' &&  
								(in_array($next_ans_char, $options_chr) || in_array($next_ans_char, $options_num)) && 
								$next_two_ans_char == ')'
							)
							{
								array_push($answers, trim($answer));
								$answer = '';
							}
							else
							{
								/*again check if it's a new question*/
								$next_three = isset($questions_string[$ans_index+3]) ? $questions_string[$ans_index+3] : 0;
								$sol = $ans_char.$next_ans_char.$next_two_ans_char.$next_three;

								
								/*if it's an answer or solution*/
								$is_solution = ($sol == 'Sol.');
								$is_rght_answer = ($sol == 'Ans.');

								if($is_rght_answer || $is_solution)
								{
									if($is_rght_answer)
									{
										for($ra_index = ($ans_index+4); $ra_index<$length ; $ra_index++)
										{
											$ra_char = $questions_string[$ra_index];
											$next_ra_char = isset($questions_string[$ra_index+1]) ? $questions_string[$ra_index+1] : 0;
											$next_two_ra_char = isset($questions_string[$ra_index+2]) ? $questions_string[$ra_index+2] : 0;
											$next_three_ra_char = isset($questions_string[$ra_index+3]) ? $questions_string[$ra_index+3] : 0;

											$sol = $ra_char.$next_ra_char.$next_two_ra_char.$next_three_ra_char;

											/*if it's a question then break 2 loop*/
											if($sol == 'Sol.')
											{	
												break 1;
											}
											else
											{
												$right_answer .= $ra_char;
											}
										}

										$ans_index = $ra_index-1;
									}
									else
									{
										array_push($answers, trim($answer));
										$answer = '';

										/*now extract solution*/
										for($sol_index = ($ans_index+4); $sol_index<$length ; $sol_index++)
										{
											$sol_char = $questions_string[$sol_index];
											/*if it's a question then break 2 loop*/
											$next_one = isset($questions_string[$index+1]) ? $questions_string[$index+1] : '';
											$next_two = isset($questions_string[$index+2]) ? $questions_string[$index+2] : '';
											$next_three = isset($questions_string[$index+3]) ? $questions_string[$index+3] : '';
											$next_four = isset($questions_string[$index+4]) ? $questions_string[$index+4] : '';

											$q_no = $next_one.$next_two.$next_three;
											if($sol_char == 'Q' && is_numeric($q_no) && strlen($q_no)==3 && $next_four == '.')
											{
												$index = $ans_index-1;
												break 2;
											}

											$solution .= $sol_char;
										}
									}

								}
								else
								{
									/*skip (1), (2), (3) or (a) or (b)*/
									if((in_array($ans_char, $options_chr) || in_array($ans_char, $options_num)) && $next_ans_char == ')')
									{
										$ans_index += 1;
										continue;
									}

									$answer .= $ans_char;
								}

							}
						}

						if(count($answers) > 0)
						{
							$questions[] = [
								'question'=>trim($question),
								'solution'=>trim($solution), 
								'right_answer'=>(int)trim($right_answer),
								'answer'=>$answers
							];
						}

						$question = '';
						$solution = '';
						// $index =  $q_index;
						break 1;
					}
					else
					{
						$question .= $q_char;
					}

				}

			}
		}

		/*now start importing into database*/
		if(count($questions) <= 1)
		{
			return response()->json(['success' => false, 'error_msg' => 'Error ! File is empty.']);
		}
		
	
		$total = $added_question + count($questions);
		
		if ($total > (int)$test_series->total_questions) 
		{
			$errorMsg = "Sorry, the file you are uploading has more questions then required , please fix and try again.";
			return response()->json(['success' => false, 'error_msg' => $errorMsg, 'slideToTop' => 'yes']);
		}

		$user_id = (int)Auth::user()->_id;

		/*update test series status if required questions have stored*/
		if ($total == (int)$test_series->total_questions)
		{
			TestSeries::where('_id', $test_series_id)->where('user_id', $user_id)
						->update(['in_draft' => "No"]);
		}

		$level = $test_series->level;

		$error_count = 0;
		$success_count = 0;
		foreach ($questions as $key => $row) 
		{
			/*if question or answer or right answer not found*/
			if(!isset($row['question']) || !isset($row['right_answer']) || !isset($row['answer']))
			{
				$error_count++;
				continue;
			}

			// replace special characters like single quote and double quote
			$question_title = $this->CharacterUpdate($row['question'], 'UTF-8', 'UTF-8');
			$solution = $this->CharacterUpdate($row['solution'], 'UTF-8', 'UTF-8');
			$correct_option = $row['right_answer'];
			$answers = $row['answer'];
			$no_of_option = count($row['answer']);

			
			// finally procceed
			$question = new TestSeriesQuestion();
			$question->question_title = $question_title;
			$question->no_of_option = $no_of_option;
			$question->user_id = $user_id;
			$question->test_series_id = $test_series_id;
			$question->solution = $solution;
			$question->level = $level;
			$question->answer_status = 'Yes';
			$question->save();

			$_id = MongoHelper::get_mongo_id($question->_id);
			foreach($answers as $index => $answer)
			{
				$ans = new TestSeriesAnswer();
				$ans->testseries_question_id = $_id;
				$ans->option_no = ($index+1);
				$ans->answer = $this->CharacterUpdate($answer, 'UTF-8', 'UTF-8');
				if ($ans->option_no == $correct_option)
					$ans->is_correct = 'Yes';
				else 
					$ans->is_correct = 'No';
				$ans->save();

				$success_count++;
			}
		}

		if($error_count > 0)
		{
			$message = "$success_count questions inserted successfully, but $error_count questions could not be insert due to invalid / duplicate questions.";
		}
		else
		{
			$message = "All Questions Added Successfully";
		}

		$output['status'] = 'success';
		$output['success_msg'] = $message;
		$output['msg'] = $message;
		$output['msgHead'] = "Success ! ";
		$output['msgType'] = "success";
		$output['success'] = true;
		$output['slideToTop'] = true;
		$output['url'] = route('question.add', $test_series_id);
		return response()->json($output);
	}
}
