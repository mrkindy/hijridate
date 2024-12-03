<?php

namespace Kindy\HijriDate;

/**
 * 
 * This file is part of the Kindy\HijriDate Package.
 *
 * (c) Ibrahim Abotaleb <admin@mrkindy.com> and Saeed Hubaishan
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Hijri Date custom extensions to the PHP DateTime class
 * This handles the Hijri Calendar beside the Gregorian Calendar
 *
 * This class override PHP DateTime Class to show the Hijri Calendar Date and also
 * showing the Gregorian Calendar Translated to Arabic Language
 *
 */

class HijriDateTime extends \DateTime
{
	
	/**
	 *
	 * @var Calendar Calendar Object used to produce the Hijri Calendar from the timestamp
	 */
	protected $hcal;
	
	/**
	 *
	 * @var Calendar static Calendar Object used to the last used Calendar
	 */
	protected static $s_hcal;
	
	/**
	 *
	 * @var string the default datetime format
	 */
	public $defaultformat = '_j _M _Yﻫ';
	
	/**
	 *
	 * @var string language code can set to 'ar' to show Arabic Date
	 */
	public $langcode = 'ar';

	/**
	 * Constructs a new instance of datetime, expanded to include an argument to inject
	 * the user context and modify the timezone to the users selected timezone if one is not set.
	 *
	 *
	 * @param string $time
	 *        	String in a format accepted by strtotime() default is 'now'
	 * @param \DateTimeZone $timezone
	 *        	Time zone of the time default is ini timezone
	 * @param string $langcode
	 *        	set the language which can be any of supported languages in Calendar object if not set the default is 'ar'
	 * @param Calendar $hijriCalendar
	 *        	Calendar object which used for calendar
	 *        	converting, if not set the class will create new Calendar object with
	 *        	default settings
	 *
	 */
	public function __construct($time = 'now', \DateTimeZone $timezone = null, $langcode = null, $hijriCalendar = null)
	{
		global $hijri_settings;
		if (isset($hijriCalendar)) {
			if ($hijriCalendar instanceof Calendar) {
				$this->hcal = $hijriCalendar;
			} else {
				$error = 'The fourth param of datetime() must be "hijri\Calendar"';
				throw new \Exception($error);
			}
		}
		
		if (isset($langcode)) {
			$this->langcode = $langcode;
		} elseif (isset($hijri_settings['langcode'])) {
			$this->langcode = $hijri_settings['langcode'];
		}
		if (isset($hijri_settings['defaultformat'])) {
			$this->defaultformat = $hijri_settings['defaultformat'];
		}
		parent::__construct($time, $timezone);
	}

    /**
     * @param string format     date format (see http://php.net/manual/en/function.date.php)
     *         Integer timestamp   time measured in the number of seconds since
     *                        the Unix Epoch (January 1 1970 00:00:00 GMT)
     *         Integer hijri   boolean defines whether you want to get Hijri or Gregorian date
     * @param int $timestamp
     *
     * @return string Returns the Hijri date according to format and timestamp in Arabic
     *                string date string [format , int timestamp]
     * @desc   date returns a string formatted according to the given format string using the given
     *                  integer timestamp or the current time if no timestamp is given. In other words, timestamp
     *                  is optional and defaults to the value of time(), also hijri can be set to 0 to get Greg date.
     */
	public static function Date($format, $date = "now", \DateTimeZone $timezone = null, $langcode = null, $hijriCalendar = null)
	{
		$date = new static($date, $timezone, $langcode, $hijriCalendar);
		return $date->format($format);

	}
	/**
	 * Create DateTime object from hijri date
	 *
	 * @param integer $year
	 *        	the hijri year
	 * @param integer $month
	 *        	the hijri month
	 * @param integer $day
	 *        	the hijri day
	 * @param \DateTimeZone $timezone
	 *        	Optional the time zone object
	 * @param string $langcode
	 *        	Optional the langcode
	 * @param Calendar $hijriCalendar
	 *        	Optional the Calendar object
	 * @return self datetime object from the given hijri date
	 */
	public static function createFromHijri($year, $month, $day, \DateTimeZone $timezone = null, $langcode = null, $hijriCalendar = null)
	{
		if (isset($hijriCalendar) && ($hijriCalendar instanceof Calendar)) {
			static::$hcal = $hijriCalendar;
		} elseif (!empty(static::$s_hcal)) {
			$hijriCalendar = static::$s_hcal;
		} else {
			$hijriCalendar = new Calendar();
			static::$s_hcal = $hijriCalendar;
		}
		$gr_date = $hijriCalendar->HijriToGregorian($year, $month, $day);
		$d = new static(sprintf('%04s', $gr_date['y']) . '-' . $gr_date['m'] . '-' . $gr_date['d'], $timezone, $langcode, $hijriCalendar);
		return $d;
	}

	#[\ReturnTypeWillChange]
	/**
	 * Formats the current date time into the specified format, this method overrides
	 * Datetime original method, if format characters are
	 * with "_" underscore prefix it will return hijri equivalent, if langcode
	 * set to 'ar' it will return Arabic translated date for Hijri or Gregorian Calendars
	 *
	 * @param string $format
	 *        	Optional format to use for output
	 *        	The following characters are recognized to output Hijri Calendar in the format parameter string
	 *        	<table><tr><th>format character</th><th>Description</th><th>Example Output</th></tr>
	 *        	<tr><td>_j</td><td>Day of the hijri month without leading zeros 1 to 30</td><td>1-30</td></tr>
	 *        	<tr><td>_d</td><td>Day of the hijri month with leading zeros 01 to 30</td><td>01-30</td></tr>
	 *        	<tr><td>_S</td><td>English suffix for numbers (new in 2.3.0)</td><td>st, nd ,th</td></tr>
	 *        	<tr><td>_z</td><td>The day of the year (starting from 0)</td><td>0-354</td></tr>
	 *        	<tr><td>_F</td><td>A full textual representation of a month, such as Muharram or Safar</td><td>Muharram-Dhul Hijjah</td></tr>
	 *        	<tr><td>_M</td><td>A short textual representation of a month, three letters(in Arabic same as _F)</td><td>Muh-Hij</td></tr>
	 *        	<tr><td>_m</td><td>Numeric representation of a month, with leading zeros</td><td>01-12</td></tr>
	 *        	<tr><td>_n</td><td>Numeric representation of a month, without leading zeros</td><td>1-12</td></tr>
	 *        	<tr><td>_L</td><td>Whether it's a leap year</td><td>1 if it is a leap year, 0 otherwise</td></tr>
	 *        	<tr><td>_Y</td><td>A full numeric representation of a year, 4 digits</td><td>1380 or 1436</td></tr>
	 *        	<tr><td>_y</td><td>A two digit representation of a year</td><td>80 or 36</td></tr>
	 *        	<tr><td colspan=3>These format character will overridden if langcode set to 'ar'</td></tr>
	 *        	<tr><td>l, D</td><td>A full textual representation of the day of the week in Arabic</td><td>السبت-الجمعة </td></tr>
	 *        	<tr><td>F</td><td>A full textual representation of a month, Syrian Name</td><td>كانون الثاني، شباط </td></tr>
	 *        	<tr><td>M</td><td>A full textual representation of a month, English translated</td><td>يناير، فبراير</td></tr>
	 *        	<tr><td>a</td><td>Lowercase Ante meridiem and Post meridiem in Arabic</td><td>ص ، م</td></tr>
	 *        	<tr><td>A</td><td>Full Ante meridiem and Post meridiem in Arabic</td><td>صباحا ، مساء</td></tr>
	 *        	</table>
	 *        	if it is not given it defaults to $hijri_settings['defaultformat'], if it is not set it defaults to '_j _M _Yهـ'
	 * @param bool $force_hijri
	 *        	force the returned date to be Hijri if the $format does not contain underscore (_)
	 *        	
	 * @return string Formatted date time
	 */
	public function format($format = null, $force_hijri = FALSE) : string
	{
		if (!isset($format)) {
			$format = $this->defaultformat;
		}
		$hsmonths = array(1 => 'Muh', 'Saf', 'Rb1', 'Rb2', 'Jm1', 'Jm2', 'Raj', 'Sha', 'Ram', 'Shw', 'Qid', 'Hij');
		if ($this->langcode == 'ar') {
			$gmonths = array(1 => 'يناير', 'فبراير', 'مارس', 'أبريل', 'مايو', 'يونيو', 'يوليو', 'أغسطس', 'سبتمبر', 'أكتوبر', 'نوفمبر', 'ديسمبر');
			$smonths = array(1 => 'كانون الثاني', 'شباط', 'آذار', 'نيسان', 'أيار', 'حزيران', 'تموز', 'آب', 'أيلول', 'تشرين الأول', 'تشرين الثاني', 'كانون الأول');
			$days = array('الأحد', 'الإثنين', 'الثلاثاء', 'الأربعاء', 'الخميس', 'الجمعة', 'السبت');
		}
		
		list($gy, $gm, $gd, $w, $mn, $am) = explode('/', parent::format('Y/m/d/w/n/a'));
		if (strpos($format, '_') !== FALSE) {
			$j = gregoriantojd($gm, $gd, $gy);
			$this->init_calendar();
			$this->hcal->jd2hijri($j, $hy, $hm, $hd, $z);
		}
		
		// Begin of formating
		if ($force_hijri) {
			$format = substr(preg_replace('/([^\\_])([jdzFMtmnyYL])/', '\1_\2', ' ' . $format), 1);
		}
		$str = '';
		$c = str_split($format);
		
		for ($i = 0, $count_c = count($c); $i < $count_c; $i++) {
			
			if ($c[$i] == '\\') {
				if ($i < ($count_c-1)) {
					$str .= '\\' . $c[++$i];
				}
			} elseif ($c[$i] == '_') {
				$i++;
				if (($count_c-1) < $i)
					break;

				switch ($c[$i]) {
					case 'j':
						$str .= $hd;
						if (($i + 1) < $count_c && $c[$i + 1] == 'S') {
							if ($this->langcode == 'en') {
								$str .= addcslashes(Calendar::english_suffix($hd), 'A..z');
							}
							$i++;
						}

						break;

					case 'd':
						$str .= str_pad($hd, 2, '0', STR_PAD_LEFT);
						if (($i + 1) < $count_c && $c[$i + 1] == 'S') {
							if ($this->langcode == 'en') {
								$str .= addcslashes(Calendar::english_suffix($hd), 'A..z');
							}
							$i++;
						}
						break;

					case 'z':
						$str .= $z - 1;
						break;

					case 'F':
						$str .= addcslashes($this->hcal->month_name($hm, $this->langcode), 'A..z');
						break;

					case 'M':
						if (in_array($this->langcode, array('en', 'fr', 'de', 'es', 'pt', 'it', 'en'))) {
							$str .= addcslashes($hsmonths[$hm], 'A..z');
						} else {
							$str .= addcslashes($this->hcal->month_name($hm, $this->langcode), 'A..z');
						}
						break;
					case 't':
						$str .= $this->hcal->days_in_month($hm, $hy);
						break;

					case 'm':
						$str .= str_pad($hm, 2, '0', STR_PAD_LEFT);
						break;

					case 'n':
						$str .= $hm;
						break;

					case 'y':
						$str .= substr($hy, 2);
						break;

					case 'Y':
						$str .= $hy;
						break;

					case 'L':
						$str .= $this->hcal->leap_year($hy);
						break;

					case 'S':
						if ($this->langcode == 'en') {
							$str .= addcslashes(Calendar::english_suffix($hd), 'A..z');
						}
						break;

					case 'W':
					case 'o':
						break;

					default:
						$str .= $c[$i];
				}
			} elseif ($this->langcode == 'ar') {
				switch ($c[$i]) {
					case 'l':
					case 'D':
						$str .= $days[$w];
						break;

					case 'F':
						$str .= $smonths[$mn];
						break;

					case 'M':
						$str .= $gmonths[$mn];
						break;

					case 'a':
						$str .= ($am == 'am') ? ('ص') : ('م');
						break;

					case 'A':
						$str .= ($am == 'am') ? ('صباحًا') : ('مساءً');
						break;

					case 'S': // not used in Arabic
						break;

					default:
						$str .= $c[$i];
						break;
				}
			} else {
				$str .= $c[$i];
			}
		}

		return parent::format($str);
	}

	/**
	 * Magic method to convert DateTime object to string
	 *
	 * @return string Formatted date time, according to the default settings in $hijri_settings variable
	 */
	public function __toString()
	{
		return $this->format();
	}

	/**
	 * Resets the current date of the DateTime object to a different hijri date
	 *
	 * @param integer $year
	 *        	the hijri year
	 * @param integer $month
	 *        	the hijri month
	 * @param integer $day
	 *        	the hijri day
	 * @return void
	 */
	public function setDateHijri($year, $month, $day)
	{
		$this->init_calendar();
		$gr_date = $this->hcal->HijriToGregorian($year, $month, $day);
		$this->setDate($gr_date['y'], $gr_date['m'], $gr_date['d']);
	}
	/**
	 *
	 * @internal
	 *
	 */
	private function init_calendar()
	{
		if (empty($this->hcal)) {
			if (empty(self::$s_hcal)) {
				$this->hcal = new Calendar();
				self::$s_hcal = $this->hcal;
			} else {
				$this->hcal = self::$s_hcal;
			}
		}
	}
}