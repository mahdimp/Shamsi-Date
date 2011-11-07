<?php
namespace Jalali;
/**
 * Date
 * Privides an exact scientific approach to create Jalali calendar
 * with an interface similar to php date()
 * 
 * @package Calendar
 * @subpackage JalaliDate
 * @author Keyhan Sedaghat<keyhansedaghat@netscape.net>
 * @author Amin Saeedi<amin.w3dev@gmail.com>
 * @copyright 1371-1390 (1991-2011)
 * @version 4.0.0
 *
 * Examples:
 * Constructing the class (although this is not relly needed):
 * 1. Constructing a class from php.time():
 * 	  $jDate_1 = new \Jalali\Date( ) ;
 * 2. Constructing the class from any given time stamp:
 * 	  $jDate_2 = new \Jalali\Date( TIME_STAMP ) ;
 * 3. Constructing the class from (year, month, day, hour, minute, second) values:
 * 	  This is the format used by php.mktime() but gets Persian date values
 * 	  $jDate_3 = new \Jalali\Date( 1383, 12, 30, 13, 45, 25 ) ;
 *
 * Calling class methods:
 * 1. $jDate->date( FORMAT ) ; //syntax of FORMAT is same as php.date() syntax.
 * 	  [see: hhtp://www.php.net/manual/en/function.date.php].
 * 2. $jDate->timestamp( ) ; //returns the used (internal) timestamp.
 * 	  This is the same value returned from php.time() for the equivalent Julian date.
 * 	  (php.date() returns equival Julian date for this time stamp.)
 *
 * Function call:
 * 1. \Jalali\date( format, [time stamp], [decorate] ) ; 
 *    using the format outputs the timestamp -- or the current
 * 	  stamp if left blank, in the desired shape. 
 * 	  use decorate to show numbers as Arabic numbers --or strings.
 *
 * In general only the following methods should be called:
 * mktime(year, month, day, hour, minute, second) to create time stamp for a given date.
 * date(format, [time stamp], [decorate]) to build readable output from time stamp.
 */
class Date
{
    /**
	 * Length of a year
	 * Calculated by Khayam is 365.2422 days (approx.);
	 * but as the years are getting shorter the new value
	 * (valid from year 1380 Per./2000 A.D.) is used instead.
	 */
    const KHAYAM_YEAR = 365.24218956;

    /**
	 * Recent calculations has introduced a correcting factor,
	 * which Khayam could not reach.
	 * This is used to better adjust length of each year in seconds.
	 */
    const KHAYAM_YEAR_CORRECTION = 0.00000006152;

	/**
	 * Corresponding to the real date-time.
	 * Timestamp in format of and equivalent to standard unix time.
	 *
	 *
	 * @access private
	 * @var int
	 */
	private $_timestamp = 0;

	/**
	 * Reference table made by Khayam for leap years
	 *
	 * @access private
	 * @var array
	 */
	private $_khayamii = array(
		5, 9, 13, 17, 21, 25, 29,
		34, 38, 42, 46, 50, 54, 58, 62,
		67, 71, 75, 79, 83, 87, 91, 95,
		100, 104, 108, 112, 116, 120, 124, 0
	);

	/**
	 * Count of days at the end of each Persian month
	 *
	 * @access private
	 * @var array
	 */
	private $_mountCounter = array(
		0, 31, 62, 93, 124, 155,
		186, 216, 246, 276, 306, 336
	);

	/**
	 * value of second in output time
	 *
	 * @access private
	 * @var int
	 */
	private $_second = 0;

	/**
	 * value of minute in output time
	 *
	 * @access private
	 * @var int
	 */
	private $_minute = 0;

	/**
	 * value of hour in output time
	 *
	 * @access private
	 * @var int
	 */
	private $_hour = 0;

	/**
	 * value of day in output time
	 *
	 * @access private
	 * @var int
	 */
	private $_day = 0;

	/**
	 * value of month in output time
	 *
	 * @access private
	 * @var int
	 */
	private $_month = 0;

	/**
	 * value of year in output time
	 *
	 * @access private
	 * @var int
	 */
	private $_year = 0;

	/**
	 * number of days from start of the current year
	 *
	 * @access private
	 * @var int
	 */
	private $_dayOfYear = 0;

	/**
	 * standard php timeZone identifier e.g.:('Asia/Tehran')
	 * ::date('e')
	 *
	 * @access private
	 * @var string
	 */
	private $_timeZone = '';

	/**
	 * standard php timeZone abbreviation e.g.:('IRST')
	 * ::date('T')
	 *
	 * @access private
	 * @var string
	 */
	private $_timeZoneAbb = '';

	/**
	 * daylight saving
	 * ::date('I')
	 *
	 * @access private
	 * @var int (0|1)
	 */
	private $_DLS = 0;

	/**
	 * difference from GMT in hours
	 * ::date('O')
	 *
	 * @access private
	 * @var int
	 */
	private $_GMTDiff = 0;

	/**
	 * difference from GMT in hours including colon
	 * ::date('P')
	 *
	 * @access private
	 * @var string
	 */
	private $_GMTDiffC = "";

	/**
	 * defference from GMT in seconds
	 * ::date('Z')
	 *
	 * @access private
	 * @var int
	 */
	private $_timezoneOffset = 0;

	/**
	 * JalaliDate::__construct()
	 * 
	 * @param integer $year --Optional timestamp or hour value
	 * @param integer $month --Optional
	 * @param integer $day --Optional
	 * @param integer $hour --Optional
	 * @param integer $minute --Optional
	 * @param integer $second --Optional
	 * 
	 * single parameter is supposed to be a valid timestamp multiple 
     * --6-- parameters are supposed to be year, month, day, hour, 
     * minute, second values.
	 */
	public function __construct() 
    {
		if (func_num_args() == 1) {
			$this->_timestamp = func_get_arg(0);
		} else if (func_num_args() == 6) {
			$this->_timestamp = $this->mktime(
                func_get_arg(0), func_get_arg(1), func_get_arg(2), 
                func_get_arg(3), func_get_arg(4), func_get_arg(5)
            );
		} else {
			$this->_timestamp = \time();
		}
		$this->_date();
	}

	/**
	 * JalaliDate::ParsiNumbers()
	 *
	 * converts digits to Persian traditional font face and
	 * corrects the no-width space in words
	 * use the function on the returned value in the source code
	 * 
	 * @access private
	 * @param string $phrase
	 * @return string
	 */
	public function ParsiNumbers($phrase) 
    {
		$L = array("0", "1", "2", "3", "4", "5", "6", "7", "8", "9");
		$F = array("۰", "۱", "۲", "۳", "۴", "۵", "۶", "۷", "۸", "۹");
		return str_replace($L, $F, $phrase);
	}

	/**
	 * JalaliDate::dayOfYear()
	 *
	 * get value of the day in the year
	 *  
	 * @access private
	 * @return int
	 */
	private function dayOfYear() 
    {
		return $this->$dayOfYear;
	}

	/**
	 * JalaliDate::weekOfYear()
	 * 
	 * @access private
	 * @return int
	 */
	private function weekOfYear() 
    {
		$x = ( 7 - $this->dayOfWeek($this->_year, 1) ) % 7;
		$z = $this->_dayOfYear - $x;
		return abs(ceil($z / 7));
	}

	/**
	 * JalaliDate::calcRasad()
	 * 
	 * calculates the years from reference Observation year
	 * 
	 * @param int $yearValue
	 * @param boolean $calendarType
	 * @access private
	 * @return int
	 */
	private function calcRasad($yearValue) 
    {
		$Rasad = $yearValue + 2346;
		return $Rasad;
	}

	/**
	 * JalaliDate::isLeap()
	 * 
	 * Checks the specified year for a leap year
	 * The return value is the number of the leap year (1 - 31)in one cycle
	 * for leap years and false for normal years
	 * 
	 * @param int $yearValue
	 * @access private
	 * @return mixed
	 */
	private function isLeap($yearValue) 
    {
		$Rasad = $this->calcRasad($yearValue);
		$yrNam = $Rasad % 2820;
		$yrNam = $yrNam % 128;
		$leapCount = array_search($yrNam, $this->_khayamii);
		return $leapCount;
	}

	/**
	 * JalaliDate::dayOfWeek()
	 * 
	 * returns weekday of the specified day of the year
	 * 
	 * @param int $yearValue
	 * @param boolean $calendarType
	 * @access private
	 * @return mixed
	 */
	private function dayOfWeek($yearValue, $dayOfYear = 0) 
    {
		$Rasad = $this->calcRasad($yearValue);

		$count2820 = floor($Rasad / 2820);
		$mod2820 = $Rasad % 2820;
		$count128 = floor($mod2820 / 128);
		$mod128 = $mod2820 % 128;

		$leapCount = 0;
		while ($mod128 > $this->_khayamii [$leapCount])
			$leapCount++;

		$yearStartDay = ( $count2820 + 1 ) * 3 +
				$count128 * 5 +
				$mod128 +
				$leapCount
		;
		if ($dayOfYear > 0) 
            $dayOfYear--;
		return ($yearStartDay + $dayOfYear) % 7;
	}

	/**
	 * JalaliDate::dayName()
	 * 
	 * returns names of the weekday
	 * 
	 * @param int $dayValue
	 * @access private
	 * @return string
	 */
	private function dayName($dayValue) 
    {
		$weekAlpha = array(
			'شنبه', 'يکشنبه', 'دوشنبه', 'سه شنبه', 'چهارشنبه', 'پنج شنبه', 'آدينه'
		);
		return $weekAlpha [$dayValue];
	}

	/**
	 * JalaliDate::dayShortName()
	 * 
	 * returns abbreviated names of the weekday
	 * 
	 * @param int $dayValue
	 * @access private
	 * @return string
	 */
	private function dayShortName($dayValue) 
    {
		$weekShort = array(
			'ش', 'ي', 'د', 'س', 'چ', 'پ', 'آ'
		);
		return $weekShort [$dayValue];
	}

	/**
	 * JalaliDate::monthName()
	 * 
	 * returns names of the month
	 * 
	 * @param int $monthValue
	 * @access private
	 * @return string
	 */
	private function monthName($monthValue) 
    {
		$monthAlpha = array(
			1 => 'فروردين', 
            'ارديبهشت', 'خرداد',
			'تير', 'امرداد', 'شهريور',
			'مهر', 'آبان', 'آذر',
			'دي', 'بهمن', 'اسفند'
		);
		return $monthAlpha [$monthValue];
	}

	/**
	 * JalaliDate::monthShortName()
	 * 
	 * returns abbreviated names of the month
	 * 
	 * @param int $monthValue
	 * @access private
	 * @return string
	 */
	private function monthShortName($monthValue) 
    {
		$monthShort = array(
			1 => 'فرو', 'ارد', 'خرد',
			'تير', 'امر', 'شهر',
			'مهر', 'آبا', 'آذر',
			'دي', 'بهم', 'اسف'
		);
		return $monthShort [$monthValue];
	}

	/**
	 * JalaliDate::monthDayString()
	 * 
	 * returns long text day of the month
	 * 
	 * @param int $monthDayValue
	 * @access private
	 * @return string
	 */
	private function monthDayString($monthDayValue) 
    {
		$monthDays = array(
			1 => 'یکم', 'دوم', 'سوم', 'چهارم', 'پنجم', 'ششم',
			'هفتم', 'هشتم', 'نهم', 'دهم', 'یازدهم', 'دوازهم',
			'سیزدهم', 'چهاردهم', 'پانزدهم', 'شانزدهم', 'هفدهم', 'هژدهم',
			'نوزدهم', 'بیستم', 'بیست و یکم', 'بیست و دوم', 'بیست و سوم', 'بیست و چهارم',
			'بیست و پنجم', 'بیست و ششم', 'بیست و هفتم', 'بیست و هشتم', 'بیست و نهم', 'سی ام', 'سی و یکم'
		);
		return $monthDays [$monthDayValue];
	}

	/**
	 * JalaliDate::_date()
	 * 
	 * sets the full date data Y;M;D;H;I;S in various class attributes
	 * 
	 * @access private
	 * @return void
	 */
	private function _date() 
    {
		$this->_zone();
		$timeStamp = $this->_timestamp;
		$timeStamp = $timeStamp + $this->_timezoneOffset;

		$Seconds = floor($timeStamp % 60);
		$Minutes = floor(( $timeStamp % 3600 ) / 60);
		$Hours = floor(( $timeStamp % 86400 ) / 3600);
		$Days = floor($timeStamp / 86400);
		$Days += 287;
		$Years = floor(
            ($Days / self::KHAYAM_YEAR) - ($Days * self::KHAYAM_YEAR_CORRECTION)
        );
		$dayOfYear = $Days - round($Years * self::KHAYAM_YEAR, 0);
		if ($dayOfYear == 0) {
			$dayOfYear = 366;
        }
		$Years += 1348;
		$Months = 0;
		while ($Months < 12 && $dayOfYear > $this->_mountCounter [$Months]) {
            $Months++;
        }
		$Days = $dayOfYear - $this->_mountCounter [$Months - 1];

		$this->_second = $Seconds;
		$this->_minute = $Minutes;
		$this->_hour = $Hours;
		$this->_day = $Days;
		$this->_month = $Months;
		$this->_year = $Years;
		$this->_dayOfYear = $dayOfYear;
	}

	/**
	 * JalaliDate::zone()
	 *
	 * set all properties related to time zone
	 *
	 * @access private
	 * @return void
	 */
	private function _zone() {
		$this->_timeZone       = \date_default_timezone_get();
		$this->_timeZoneAbb    = \date('T', $this->_timestamp);
		$this->_DLS            = \date('I', $this->_timestamp);
		$this->_GMTDiff        = \date('O', $this->_timestamp);
		$this->_GMTDiffC       = \date('P', $this->_timestamp);
		$this->_timezoneOffset = \date('Z', $this->_timestamp);
	}

	/**
	 * JalaliDate::date()
	 *
	 * this is a clone of the internal php function date()
	 * with a few exceptions in the acceptable parameters
	 *
	 * These are the supported formats from php.date():
	 * a: Lowercase Ante meridiem and Post meridiem  	am or pm
	 * A: Uppercase Ante meridiem and Post meridiem 	AM or PM
	 * d: days from 01 to 31
	 * D: days --short-- from ش to آ
	 * j: days from 1 to 31
	 * l (lowercase 'L'): days from شنبه to آدینه
	 * N: number of day in week from 1 (شنبه) to 7 (آدینه)
	 * w: number of day in week
	 * S: month days from یکم to سی و یکم 
     *    this is slightly different from php.date()!
	 * z: day in the year
	 * W: week in the year
	 * F: Month name from قروردین to اسفند
	 * m: Month number from 01 to 12
	 * M: month from فرو to اسف
	 * n: Month number from 1 to 12
	 * Y: full year numeric representation -- 4 digit
	 * y: year numeric representation -- 2 digit
	 * g: 12-hour format of an hour without leading zeros 	1 through 12
	 * G: 24-hour format of an hour without leading zeros 	0 through 23
	 * h: 12-hour format of an hour with leading zeros 	01 through 12
	 * H: 24-hour format of an hour with leading zeros 	00 through 23
	 * i: Minutes with leading zeros 	00 to 59
	 * s: Seconds, with leading zeros 	00 through 59
	 * T: Timezone abbreviation 	Examples: EST, MDT ...
	 * U: Seconds since the Unix Epoch See also time()
	 * L: whether it's a leap year
	 * I: (capital i) Whether or not the date is in daylight saving time 1 if 
     *    Daylight Saving Time, 0 otherwise.
	 * O: Difference to Greenwich time (GMT) in hours 	Example: +0200
	 * P: Difference to Greenwich time (GMT) with colon 
     *    between hours and minutes (added in PHP 5.1.3)
	 * Z: Timezone offset in seconds. The offset for timezones west of UTC is 
     *    always negative, and for those east of UTC is always positive. 	
     *    -43200 through 50400
	 * c: ISO 8601 date (added in PHP 5) 	2004-02-12T15:19:21+00:00
	 * r: » RFC 2822 formatted date	Example: Thu, 21 Dec 2000 16:01:07 +0200
	 * e: Timezone identifier (added in PHP 5.1.0) Examples: GMT, Atlantic/Azores
	 *
	 * The following identifiers are not available:
	 * t: number of days in the given month
	 * o: year number
	 * B: Swatch Internet time 	000 through 999
	 * u: Microseconds (added in PHP 5.2.2) 	Example: 54321
	 * @param string $format
	 * @param int $timestamp the unix-type timestamp to be used for output
	 * @param boolean $decorate if true function decorate is used for chanhing 
     *        the face of output. if false the normal face of output is returned. 
     *        for numbers false returns number, true returns string.
	 * @access public
	 * @return mixed
	 */
	public function date($format, $timestamp = 0, $decorate = true) 
    {
		$this->_date();
		$format = str_replace("a", ($this->_hour <= 12 ? "ق.ظ" : "ب.ظ"), $format);
		$format = str_replace("A", ($this->_hour <= 12 ? "ق.ظ" : "ب.ظ"), $format);
		$format = str_replace("d", str_pad($this->_day, 2, '0', STR_PAD_LEFT), $format);
		$format = str_replace(
                "D", $this->dayShortName(
                     $this->dayOfWeek($this->_year, $this->_dayOfYear)
                     ), $format
                );
		$format = str_replace("j", $this->_day, $format);
		$format = str_replace("l", $this->dayName(
                    $this->dayOfWeek($this->_year, $this->_dayOfYear)), $format
                  );
		$format = str_replace("N", $this->dayOfWeek($this->_year, $this->_dayOfYear) + 1, $format);
		$format = str_replace("w", $this->dayOfWeek($this->_year, $this->_dayOfYear), $format);
		$format = str_replace("S", $this->monthDayString($this->_day), $format);
		$format = str_replace("z", $this->_dayOfYear, $format);
		$format = str_replace("W", $this->weekOfYear(), $format);
		$format = str_replace("F", $this->monthName($this->_month), $format);
		$format = str_replace("m", str_pad($this->_month, 2, '0', STR_PAD_LEFT), $format);
		$format = str_replace("M", $this->monthShortName($this->_month), $format);
		$format = str_replace("n", $this->_month, $format);
		$format = str_replace("Y", $this->_year, $format);
		$format = str_replace("y", ( $this->_year % 100), $format);
		$format = str_replace("g", ( $this->_hour % 12), $format);
		$format = str_replace("G", $this->_hour, $format);
		$format = str_replace("h", str_pad(( $this->_hour % 12), 2, '0', STR_PAD_LEFT), $format);
		$format = str_replace("H", str_pad($this->_hour, 2, '0', STR_PAD_LEFT), $format);
		$format = str_replace("i", str_pad($this->_minute, 2, '0', STR_PAD_LEFT), $format);
		$format = str_replace("s", str_pad($this->_second, 2, '0', STR_PAD_LEFT), $format);
		$format = str_replace("U", $this->_timestamp, $format);
		$format = str_replace("L", $this->isLeap($this->_year), $format);
		$format = str_replace("Y", $this->_year, $format);
		$format = str_replace("I", $this->_DLS, $format);
		$format = str_replace("O", $this->_GMTDiff, $format);
		$format = str_replace("P", $this->_GMTDiffC, $format);
		$format = str_replace("Z", $this->_timezoneOffset, $format);
		$format = str_replace("c", $this->_year . "-" .
						str_pad($this->_month, 2, '0', STR_PAD_LEFT) . "-" .
						str_pad($this->_day, 2, '0', STR_PAD_LEFT) . "ز" .
						str_pad($this->_hour, 2, '0', STR_PAD_LEFT) . ":" .
						str_pad($this->_minute, 2, '0', STR_PAD_LEFT) . ":" .
						str_pad($this->_second, 2, '0', STR_PAD_LEFT) .
						$this->_GMTDiffC, $format)
		;
		$format = str_replace("r", 
                        $this->dayShortName(
                            $this->dayOfWeek($this->_year, $this->_dayOfYear)
                        )."، " .
						$this->_day . " " .
						$this->monthShortName($this->_month) . " " .
						$this->_year . " " .
						$this->_hour . ":" .
						$this->_minute . ":" .
						$this->_second .
						$this->_GMTDiff, $format);
		$format = str_replace("T", $this->_timeZoneAbb, $format);
		$format = str_replace("e", $this->_timeZone, $format);

		if ($decorate) {
			return self::ParsiNumbers($format);
		} else {
			return $format;
		}
	}

	/**
	 * JalaliDate::mktime()
	 * 
	 * creates timestamp depending on the Persian time parameters
	 * 
	 * @access private 
	 *
	 * @param mixed $year
	 * @param mixed $month
	 * @param mixed $day
	 * @param mixed $hour
	 * @param mixed $minute
	 * @param mixed $second
	 * @return integer time stamp
	 */
	public function mktime($year, $month, $day, $hour=0, $minute=0, $second=0)
    {
		$timeStamp = $second;
		$timeStamp += $minute * 60;
		$timeStamp += $hour * 60 * 60;

		$dayOfYear = ($day + $this->_mountCounter[$month - 1]);
		if ($year < 1300) {
			$year += 1300;
        }
		$year -= 1348;
		$day   = $dayOfYear + round(( self::KHAYAM_YEAR * $year), 0);
		$day  -= 287;
		$timeStamp += $day * 86400;
		$this->_timestamp = $timeStamp ;
		$this->_zone();
		$timeStamp = $timeStamp - $this->_timezoneOffset;
		return $timeStamp;
	}

	/**
	 * JalaliDate::timestamp()
	 * 
	 * returns current timestamp of the object
	 * this time stamp is equivalent to system timestamp; for current time
	 *
	 * @access public 
	 * @return int
	 */
	public function timestamp() {
		return $this->_timestamp;
	}
}

function date($format, $timestamp=0, $decorate=true)
{
   $jalali = new Date();
   return $jalali->date($format, $timestamp, $decorate); 
}
