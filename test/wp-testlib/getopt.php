<?php
/**
 * getopt relacement / extenstion
 * 
 *  prior to PHP 5.3 getopt is not supported on the windows plattform
 *  and it does not support long options on other plattforms as well.
 *  
 *  this file offers a _getopt() function as a replacement. via the  
 *  php.net manual page for getop().
 *  
 *  original author is 可愛柚爸 / uberlinuxguy at tulg dot org
 *  
 *  the function has been taken from that website, and refactored
 *  into a helper class to increase the protability
 *
 * @version 1.0 hakre-1
 *
 *  CHANGELOG:
 *  
 *   - refactored the functions into a class (better portability)
 *   - reformatted the code (copy & paste issues)
 *   - removed eval calls (commented)   
 *   - smarter quoting
 *   - indentation on tab and cleanup of whitespaces
 *   - deprecated string access ({}) fixed with [].
 *  
 *  TODO:
 *   (empty) 
 *  
 *  
 *  @link http://www.ntu.beautifulworldco.com/weblog/?p=526
 *  @link http://www.php.net/getopt
 */

/**
 * getoptParser
 * 
 * getopt() compatible argv parsing.
 * 
 * @see getoptParser::getopt()
 * @see getoptParser::split_para()
 */
class getoptParser {
	/**
	 * getopt()	  
	 * 
	 * Usage: _getopt ( [$flag,] $short_option [, $long_option] );
	 * 
	 * Note that another function split_para() is required, which can be 
	 * found in the same page.
	 * 
	 * _getopt() fully simulates getopt() which is described at 
	 * (@see http://us.php.net/manual/en/function.getopt.php} , including long 
	 * options for PHP version under 5.3.0. (Prior to 5.3.0, long options was 
	 * only available on few systems)
	 * 
	 * Besides legacy usage of getopt(), I also added a new option to manipulate 
	 * your own argument lists instead of those from command lines. This new 
	 * option can be a string or an array such as
	 * 
	 *  $flag = "-f value_f -ab --required 9 --optional=PK --option -v test -k";
	 *  
	 *  or
	 *  
	 *  $flag = array ( "-f", "value_f", "-ab", "--required", "9", "--optional=PK", "--option" );
	 *  
	 *  So there are four ways to work with _getopt(),
	 *  
	 *  1. _getopt ( $short_option );
	 *	it's a legacy usage, same as getopt ( $short_option ).
	 *	
	 *  2. _getopt ( $short_option, $long_option );
	 *	it's a legacy usage, same as getopt ( $short_option, $long_option ).
	 *	
	 *  3. _getopt ( $flag, $short_option );
	 *	use your own argument lists instead of command line arguments.
	 *	
	 *  4. _getopt ( $flag, $short_option, $long_option );
	 *	use your own argument lists instead of command line arguments. 
	 * 
	 * @version 1.3
	 * @date 2009/05/30 (taken from the website 2010-01-11)
	 * @author 可愛柚爸 / uberlinuxguy at tulg dot org
	 * @see http://www.ntu.beautifulworldco.com/weblog/?p=526
	 * 
	 * @params mixed
	 * @return array
	 */
	static function getopt() {
	
		if ( func_num_args() == 1 ) {
			$flag =  $flag_array = $GLOBALS['argv'];
			$short_option = func_get_arg ( 0 );
			$long_option = array ();
		} elseif ( func_num_args() == 2 ) {
			if ( is_array ( func_get_arg ( 1 ) ) ) {
			$flag = $GLOBALS['argv'];
			$short_option = func_get_arg ( 0 );
			$long_option = func_get_arg ( 1 );
			} else {
			$flag = func_get_arg ( 0 );
			$short_option = func_get_arg ( 1 );
			$long_option = array ();
			}
		} elseif ( func_num_args() == 3 ) {
			$flag = func_get_arg ( 0 );
			$short_option = func_get_arg ( 1 );
			$long_option = func_get_arg ( 2 );
		} else {
			exit ( "wrong options\n" );
		}
	
		$short_option         = trim($short_option);
		$short_no_value       = array();
		$short_required_value = array();
		$short_optional_value = array();
		$long_no_value        = array();
		$long_required_value  = array();
		$long_optional_value  = array();
		$options              = array();
	
		for ( $i = 0; $i < strlen ( $short_option ); ) {
			if ( $short_option[$i] != ':' ) {
				if ( $i == strlen ( $short_option ) - 1 ) {
					$short_no_value[] = $short_option[$i];
					break;
				} elseif ( $short_option[$i+1] != ':' ) {
					$short_no_value[] = $short_option[$i];
					$i++;
					continue;
				} elseif ( $short_option[$i+1] == ':' && $short_option[$i+2] != ':' ) {
					$short_required_value[] = $short_option[$i];
					$i += 2;
					continue;
				} elseif ( $short_option[$i+1] == ':' && $short_option[$i+2] == ':' ) {
					$short_optional_value[] = $short_option[$i];
					$i += 3;
					continue;
				}
			} else {
				continue;
			}
		}
	
		foreach ( $long_option as $a ) {
			if ( substr( $a, -2 ) == '::' ) {
				$long_optional_value[] = substr($a, 0, -2);
			} elseif ( substr($a, -1) == ':' ) {
				$long_required_value[] = substr($a, 0, -1);
			} else {
				$long_no_value[] = $a;
			}
		}
	
		if ( is_array ( $flag ) ) {
			$flag_array = $flag;
		} else {
			$flag = "- $flag";
			$flag_array = self::_split_para($flag);
		}
	
		for ( $i = 0; $i < count($flag_array); ) {
	
			if ( !$flag_array[$i] || ( '-' == $flag_array[$i] ) ) {
				$i++;
				continue;
			} elseif ( '-' != $flag_array[$i][0] ) {
				$i++;
				continue;
			}
	
			if ( substr($flag_array[$i], 0, 2) == '--' ) {
				if (strpos($flag_array[$i], '=') != false) {
					list($key, $value) = explode('=', substr($flag_array[$i], 2), 2);
					if ( in_array($key, $long_required_value ) || in_array($key, $long_optional_value ) )
						$options[$key][] = $value;
					$i++;
					continue;
				} elseif (strpos($flag_array[$i], '=') == false) {
					$key = substr($flag_array[$i], 2);
					if ( in_array(substr( $flag_array[$i], 2 ), $long_required_value ) ) {
						$options[$key][] = $flag_array[$i+1];
						$i++;
					} elseif ( in_array(substr( $flag_array[$i], 2 ), $long_optional_value ) ) {
						if ( $flag_array[$i+1] != '' && $flag_array[$i+1][0] != '-' ) {
							$options[$key][] = $flag_array[$i+1];
							$i++;
						} else {
							$options[$key][] = FALSE;						
						}
					} elseif ( in_array(substr( $flag_array[$i], 2 ), $long_no_value ) ) {
						$options[$key][] = FALSE;
					}
					$i++;
					continue;				
				}
			} elseif ( $flag_array[$i][0] == '-' && $flag_array[$i][1] != '-' ) {
				for ( $j=1; $j < strlen($flag_array[$i]); $j++ ) {
					if ( in_array($flag_array[$i][$j], $short_required_value ) || in_array($flag_array[$i][$j], $short_optional_value )) {
						if ( $j == strlen($flag_array[$i]) - 1  ) {
							if ( in_array($flag_array[$i][$j], $short_required_value ) ) {
								if (isset($flag_array[$i+1]))
									$options[$flag_array[$i][$j]][] = $flag_array[$i+1];
								$i++;
							} elseif ( in_array($flag_array[$i][$j], $short_optional_value ) && $flag_array[$i+1] != '' && $flag_array[$i+1][0] != '-' ) {
								$options[$flag_array[$i][$j]][] = $flag_array[$i+1];
								$i++;
							} else {
								$options[$flag_array[$i][$j]][] = FALSE;								
							}							
						} else {
							$options[$flag_array[$i][$j]][] = substr ( $flag_array[$i], $j + 1 );
						}
						$plus_i = 0;
						$i++;							
						break;
					} elseif ( in_array($flag_array[$i][$j], $short_no_value ) ) {
						$options[$flag_array[$i][$j]][] = FALSE;
						$plus_i = 1;
						continue;
					} else {
						$plus_i = 1;
						break;
					}
				}
				$i += $plus_i;
				continue;
			} // if
			$i++;
		} // for
	
		// reduce options array depth if possible
		foreach ( $options as $key => $value ) {
			if ( count($value) == 1 )
				$options[$key] = $value[0];
		}
	
		return $options;
	
	}

	/**
	 * split parameters
	 * 
	 * static helper function
	 * 
	 * @version 1.0
	 * @date    2008/08/19
	 * @see     http://www.ntu.beautifulworldco.com/weblog/?p=526
	 * 
	 * This function is to parse parameters and split them into smaller pieces.
	 * preg_split() does similar thing but in our function, besides "space", we
	 * also take the three symbols " (double quote), '(single quote),
	 * and \ (backslash) into consideration because things in a pair of " or '
	 * should be grouped together.
	 * 
	 * As an example, this parameter list
	 * 
	 * -f "test 2" -ab --required "t\"est 1" --optional="te'st 3" --option -v 'test 4'
	 * 
	 * will be splited into
	 * 
	 * -f; test 2; -ab; --required; t"est 1; --optional=te'st 3; --option; -v; test 4
	 * 
	 * see the code below:
	 * 
	 * <code> 
	 *	$pattern = "-f \"test 2\" -ab --required \"t\\\"est 1\" --optional=\"te'st 3\" --option -v 'test 4'"; 
	 *	$result = getoptParser::split_para($pattern);
	 *	echo "ORIGINAL PATTERN: $pattern\n\n";
	 *	var_dump($result);
	 * </code>
	 * 
	 * @param string $pattern
	 * @return array
	 */
	public static function split_para($pattern) {
		$begin      = 0;
		$backslash  = 0;
		$quote      = '';
		$quote_mark = array();
		$result     = array();
		$pattern	= trim($pattern);
		$cand1      = '';

		for ( $end = 0; $end < strlen($pattern); ) {
			if ( !in_array($pattern[$end], array(' ', '"', "'", "\\")) ) {
				$backslash = 0;
				$end++;
				continue;
			}
			if ( $pattern[$end] == "\\" ) {
				$backslash++;
				$end++;
				continue;
			} elseif ( $pattern[$end] == '"' ) {
				if ( $backslash % 2 == 1 || $quote == "'" ) {
					$backslash = 0;
					$end++;
					continue;
				}
				if ( $quote == '' ) {
					$quote_mark[] = $end - $begin;
					$quote = '"';
				} elseif ( $quote == '"' ) {
					$quote_mark[] = $end - $begin;
					$quote = '';
				}

				$backslash = 0;
				$end++;
				continue;
			} elseif ( $pattern[$end] == "'" ) {
				if ( $backslash % 2 == 1 || $quote == '"' ) {
					$backslash = 0;
					$end++;
					continue;
				}
				if ( $quote == '' ) {
					$quote_mark[] = $end - $begin;
					$quote = "'";
				} elseif ( $quote == "'" ) {
					$quote_mark[] = $end - $begin;
					$quote = '';
				}

				$backslash = 0;
				$end++;
				continue;
			} elseif ( $pattern[$end] == ' ' ) {
				if ( $quote != '' ) {
					$backslash = 0;
					$end++;
					continue;
				} else {
					$backslash = 0;
					$cand = substr($pattern, $begin, $end-$begin);
					for ( $j = 0; $j < strlen($cand); $j++ ) {
						if ( in_array($j, $quote_mark) )
							continue;

					$cand1 .= $cand[$j];
				}
				if ( $cand1 ) {
					// commented and replaced:
					// eval( "\$cand1 = \"$cand1\";" );
					$result[] = (string) $cand1;
				}
				$quote_mark = array();
				$cand1 = '';
				$begin =++$end;
				continue;
				}
			}
		}

		$cand = substr($pattern, $begin, $end-$begin);
		for ( $j = 0; $j < strlen($cand); $j++ ) {
			if ( in_array($j, $quote_mark ) )
				continue;
			$cand1 .= $cand[$j];
		}

		// commented and replaced:
		// eval( "\$cand1 = \"$cand1\";" );
		$cand1 = (string) $cand1;

		if ( $cand1 )
			$result[] = $cand1;

		return $result;
	}	
}
?>