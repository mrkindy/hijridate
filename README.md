
# Kindy Hijri Date Package

This PHP package provides functionalities for handling Hijri (Islamic) dates, including conversion, formatting, and integration with Gregorian calendars. The package features enhanced modularity and functionality, making it highly useful for PHP developers requiring Hijri date processing.

## Features

- **Hijri Calendar Compliance**: 
  - Fully adheres to the astronomical conventions used by astronomers and matches the standards in Microsoft products and the *Calendar of the Centuries* by Dr. Saleh Al-Ojairi.
  - Includes full support for the Umm Al-Qura calendar.
  
- **Custom Calendar Adjustments**: 
  - Supports saving and restoring calendar adjustments, allowing customization to reflect specific events like Ramadan and Eid declarations.

- **Hijri and Gregorian Integration**: 
  - Combines both calendars seamlessly in a single programming operation.
  - Uses `date`-style format characters prefixed with `_` for Hijri date formatting.

- **Language Support**: 
  - Includes 20 global languages for Hijri month names.
  - Displays Gregorian dates in Arabic when the language is set to Arabic.

- **Integration with PHP `DateTime`**: 
  - Fully compatible with PHP's `DateTime` class, with additional methods for Hijri-specific functionalities.
  - Automatically handles timezone differences.

## Installation

You can install the package via Composer:

```bash
composer require kindy/hijridate
```

## Namespace and Class Structure

The package follows a modular approach with the following structure:

- **Namespace**: `Kindy\HijriDate`
- **Classes**:
  - `HijriDateTime`: Handles Hijri date and time functionalities.
  - `Calendar`: Provides core conversion and calculation utilities.
  - `CalendarAdjustment`: Enables custom calendar adjustments.

## Usage

### Basic Example: Display Current Hijri Date

### HijriDateTime Class
#### Constructor
```php
public HijriDateTime::__construct(
    string $time = "now",
    DateTimeZone $timezone = null,
    string $langcode = null,
    Kindy\HijriDate\Calendar $calendar = null
)
```
- `time` : String in a format accepted by strtotime() default is 'now'
- `$timezone` : Time zone of the time default is ini timezone
- `langcode`: Default language (`ar` for Arabic, others default to English).
- `calendar`: Calendar object which used for calendar converting, if not set the class will create Calendar object with default settings.

#### Format Hijri Dates
```php
public function format(string $format): string
```

#### Format Hijri Dates
```php
public function format(string $format): string
```

#### Hijri Dates Static Method
```php
HijriDateTime::Date('_Y-_m-_d','2024-12-3');
```
#### Hijri Dates objet by new instance
```php
use Kindy\HijriDate\HijriDateTime;

echo (new HijriDateTime())->format('_j _M _Yهـ');
// Output: 8 شعبان 1436هـ
```

#### Hijri and Gregorian Dates Together
```php
echo (new HijriDateTime())->format('D _j _M _Yهـ (j-m-Yم)');
// Output: الثلاثاء 8 شعبان 1436هـ (26-05-2015م)
```

#### Convert Specific Gregorian Date to Hijri
```php
echo (new HijriDateTime("2015-5-22"))->format('D _j _M _Yهـ (j-m-Yم)');
// Output: الجمعة 4 شعبان 1436هـ (22-05-2015م)
```

#### Create Hijri Date Object from Hijri Date
```php
use Kindy\HijriDate\HijriDateTime;

$hijirDate = HijriDateTime::createFromHijri(1436, 9, 1);
echo $hijirDate->format('D _j _M _Yهـ (j-m-Yم)');
// Output: الخميس 1 رمضان 1436هـ (18-06-2015م)
```
### Formatting Time and Date in the Hijri Calendar

The function formats time and date using specific characters, with no differences in parameters but varying outputs depending on the characters used. Below is a table explaining the functionality of each character:

| Character | Description                                             | Example Output      |
|-----------|---------------------------------------------------------|---------------------|
| **_j**    | Day without leading zeros                               | 1-30               |
| **_d**    | Day with leading zeros                                  | 01-30              |
| **S**     | Ordinal suffix (English only, new in version 2.3)       | st-nd-th           |
| **_z**    | Day of the year (starting from zero)                    | 0-354              |
| **_M, _F**| Month name                                              | محرم, صفر, ...    |
| **_m**    | Month number with leading zeros                         | 01-12              |
| **_n**    | Month number without leading zeros                      | 1-12               |
| **_t**    | Number of days in the month                             | 29-30              |
| **_L**    | Leap year indicator (1 = Leap Year, 0 = Not Leap Year)  | 1 or 0             |
| **_Y**    | Full year number                                        | 1436               |
| **_y**    | Two-digit year                                          | 36                 |

### Adjusted Gregorian Date Formatting in Arabic Language

When selecting the Arabic language, some Gregorian date formatting characters are adjusted as follows:

| Character | Description                             | Example Output         |
|-----------|-----------------------------------------|------------------------|
| **l, D**  | Day of the week name                   | السبت, الأحد...       |
| **F**     | Month names in Syriac terms            | كانون الثاني, شباط... |
| **M**     | Month names in English terms           | يناير, فبراير...      |
| **a**     | AM/PM in Arabic symbols                | ص-م                   |
| **A**     | AM/PM in Arabic full text              | صباحا - مساء          |


## Extended Features

### Additional Methods
The `HijriDateTime` class includes custom methods for Hijri-specific functionalities:

- **`HijriDateTime::createFromHijri(year, month, day)`**:
  Creates a `HijriDateTime` object from a specific Hijri date.

- **`HijriDateTime::setDateHijri(year, month, day)`**:
  Sets the Hijri date for an existing `HijriDateTime` object.

### Calendar Class
The `Calendar` class provides low-level utilities such as:
- Conversion between Julian and Hijri calendars.
- Determining leap years.
- Validating Hijri dates.

### Example: Days in Hijri Month
```php
use Kindy\HijriDate\Calendar;

echo (new Calendar())->days_in_month(9, 1436);
// Output: 29
```

## Using the Package with Legacy Code
If you want to use this library without changing existing `DateTime`-dependent code, alias `HijriDateTime` as `DateTime`:

```php
use Kindy\HijriDate\HijriDateTime as DateTime;
```

Alternatively, create a custom `hdate()` function to replace PHP's `date()`:

```php
function hdate($format, $timestamp = null)
{
    if (!isset($timestamp)) {
        $timestamp = time();
    }
    $d = new HijriDateTime('@' . $timestamp);
    return $d->format($format);
}
```

## Contributing

Your contribution is welcome

## Credits

- [Ibrahim Abotaleb](https://github.com/mrkindy)
- [Saeed Hubaishan](https://github.com/hubaishan)

## License

This package is licensed under [MIT License](LICENSE).

---

**Note**: For detailed documentation and examples, refer to the official API or the `examples.php` file in the repository.