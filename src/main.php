<?php

error_reporting(E_ERROR | E_PARSE);

define('DB_NAME', getenv('MYSQL_DATABASE'));
define('DB_USER', 'root');
define('DB_PASSWORD', getenv('MYSQL_ROOT_PASSWORD'));

try {
    echo <<<TEXT

    MySQL dates and time expressed in descending order from largest unit to smallest unit: year, month, day, hour, minutes, seconds.
    Checkout date and time functions at https://dev.mysql.com/doc/refman/8.0/en/date-and-time-functions.html


    TEXT;

    $conn = new PDO('mysql:host=db;dbname=' . DB_NAME, DB_USER, DB_PASSWORD, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

    echo <<<TEXT

    MySQL DATE_FORMAT(date, format)
    %Y - 2022; %y - 22
    %M - January; %b - Jan; %m - 03; %c - 3
    %d - 08; %e - 8; %D - 8th (english text suffix)
    %W - Monday; %a - Mon
    %H - 06; %k - 6; %h - 06 (12-hour clock); %l - 6 (12-hour clock)
    %i - 05
    %S - 07
    %p - AM/PM


    TEXT;

    $result = $conn->query('SELECT amount, DATE_FORMAT(payment_date, "%e/%c/%Y") AS payment_at FROM payment ORDER BY payment_date DESC LIMIT 5');

    $payments = $result->fetchAll(PDO::FETCH_OBJ);

    foreach ($payments as $payment) {
        echo $payment->amount, ' ', $payment->payment_at, PHP_EOL;
    }

    echo <<<TEXT

    MySQL DATE_ADD(date, INTERVAL value interval_type), DATE_SUB() or ADDDATE(), SUBDATE()
    Interval types:
    DAY: a number
    DAY_HOUR: a string "DD hh"
    WEEK: a number
    MONTH: a number
    QUARTER: a number
    YEAR: a number
    YEAR_MONTH: a string "YY-MM"


    TEXT;

    $result = $conn->query('SELECT amount, payment_date FROM payment WHERE payment_date > DATE_SUB(NOW(), INTERVAL 18 YEAR) ORDER BY payment_date DESC LIMIT 10;');

    $payments = $result->fetchAll(PDO::FETCH_OBJ);

    foreach ($payments as $payment) {
        echo $payment->amount, ' ', $payment->payment_date, PHP_EOL;
    }

    echo <<<TEXT

    PHP Date Handling
    The DateTime and related classes store date and time internally as a 64-bit number,
    making it possible to represent dates from about 292 billion years in the past to the sam number of years in the future.
    However, if PHP is compiled on a 32-bit processor, the range may be restricted to approximately 1901 to January 2038.

    All date and time info is stored according to server's default time zone setting. It's common for web servers to be located
    in a different time zone from your target audience. The server's default time zone can be set in date.timezone directive in php.ini.

    The list of supported time zones are based on geographic regions and cities rather than on official time zones,
    because PHP automatically takes daylight saving time into account. Important to note when checking the regions are:
    - America does not mean United States, but the continents of North and South America and the Caribbean. As
    a result, Honolulu is not listed in America, but as a Pacific time zone.
    - Europe is European continent, including the British Isles but excluding other islands. So Reykjavik and Madeira are listed as
    Atlantic time zones and Longyearbyen on the Norwegian island of Svalbard has the exclusive privilege of being the only Arctic time zone.

    The accuracy of time zone conversions depends on the time zone database compiled into PHP being up to date.
    
    www.php.net/manual/en/timezones.php
    

    The deprecated as of 8.1 strftime() uses the language specified by the server's locale to display names of weekdays and months,
    whereas date() uses English only.
    www.php.net/manual/en/datetime.formats.php

    Format characters for format() and date():
    d - 02
    j - day of month without leading zero
    S - English ordinal suffix for day of month: st, nd, rd or th
    D - Sun
    l - Sunday
    m - 06
    n - number of month without leading zero
    M - Jan
    F - January
    Y - 2022
    y - 22
    g - Hour in 12-hour without leading zero
    h - Hour in 12-hour with leading zero
    G - Hour in 24-hour without leading zero
    H - Hour in 24-hour with leading zero
    i - Minutes
    s - Seconds
    a - Lowercase am/pm
    A - Uppercase am/pm


    TEXT;

    $date = new DateTime();
    echo 'Time zone of server is ', $date->getTimezone()->getName(), PHP_EOL;

    $now = new DateTime('now', new DateTimeZone('America/Sao_Paulo'));
    echo $now->format('d/m/Y H:i'), PHP_EOL;

    $christmasDay = new DateTime('2022-12-25');
    echo 'Christmas 2022 falls on ', $christmasDay->format('l'), PHP_EOL;

    // Using static method createFromFormat.
    // Call static method using a class name followed by the scope resolution operator (::), or PAAMAYIM_NEKUDOTAYIM, which is Hebrew
    // for "double colon", and the method name.
    // Why Hebrew? The Zend Engine that powers PHP was developed by Zeev Suraski and Andi Gutmans when they were students at Technion in Israel.
    $date = DateTime::createFromFormat('d/m/Y H:i', '01/06/1981 00:00', new DateTimeZone('America/Sao_Paulo'));
    echo $date->format('l, jS F Y ha'), PHP_EOL;

    // Adding out-of-range values with setDate() and setTime() results in the excess being added to the resulting date and time.
    // A useful trick to get the last day of any month is by setting the month value to the folllowing month and the day to zero.
    $date = new Datetime();
    $date->setDate(2019, 3, 0); // Non-leap year: 28th February 2019
    echo $date->format('F jS, Y'), PHP_EOL;

    $date->setDate(2020, 3, 0); // Leap year: 29th February 2020
    echo $date->format('F jS, Y'), PHP_EOL;

    // modify() accepts a relative date string. This can produce strange results when the resulting date overflows.
    $date = new DateTime('2019-01-31');
    $date->modify('+1 month'); // This results in March 3rd, not the last day of February.
    echo $date->format('F jS, Y'), PHP_EOL;
    $date->modify('-1 month'); // This results in February 3rd, not the original date January 31st.
    echo $date->format('F jS, Y'), PHP_EOL;

    $date = new DateTime('2019-01-31');
    $date->modify('last day of +1 month'); // Use last day of to produce the correct result February 28th.
    echo $date->format('F jS, Y'), PHP_EOL;
    $date->modify('last day of -1 month'); // Use last day of to produce the correct result January 31st.
    echo $date->format('F jS, Y'), PHP_EOL;

    echo <<<TEXT

    Other methods of DateTime include add(DateInterval), sub(DateInterval), diff(DateTime).
    Adding and substracting months wiht DateInterval yields the same problems as modify(),
    so keep that in mind and use 'last day of' where appropriate.
    Interval designation must start with a 'P'. ISO 8601 period designators for DateInterval are:
    Y - Years
    M - Months
    W - Weeks (cannot be combined with days)
    D - Days (cannot be combined with weeks)
    For the time designators, a 'T' must be prepended:
    H - Hours
    M - Minutes
    S - Seconds


    TEXT;

    $interval1 = new DateInterval('P2Y'); // 2 years
    $interval2 = new DateInterval('P5W'); // 5 weeks
    $interval3 = new DateInterval('P37D'); // 5 weeks 2 days
    $interval4 = new DateInterval('PT6H20M'); // 6 hours 20 minutes
    $interval5 = new DateInterval('P1Y2DT3H5M50S'); // 1 year 2 days 3 hours 5 minutes 50 seconds

    $xmas2019 = new DateTime('12/25/2019');
    $interval = new DateInterval('P12D');
    $xmas2019->add($interval);
    echo $xmas2019->format('F jS, Y'), PHP_EOL;

    $xmas2014 = new DateTime('12/25/2014');
    $xmas2014->add(DateInterval::createFromDateString('+12 days')); // Using static createFromDateString() allows you to pass English relative date string
    echo $xmas2014->format('F jS, Y'), PHP_EOL;

    echo <<<TEXT

    Using diff() to find the difference between two dates yields a DateInterval. Follow that with a call to format() with the following format characters:
    %Y - Years with leading zero (12, 01)
    %y - Years, no leading zero (12, 1)
    %M - Months with leading zero (02, 11)
    %m - Months, no leading zero (2, 11)
    %D - Days with leading zero (03, 24)
    %d - Days, no leading zero (3, 24)
    %a - Total number of days
    %H - Hours with leading zero (03, 23)
    %h - Hours, no leading zero (3, 23)
    %I - Minutes with leading zero (05, 59)
    %i - Minutes, no leading zero (5, 59)
    %S - Seconds with leading zero (05, 59)
    %s - Seconds, no leading zero (5, 59)
    %R - Display minus when negative, + when positive
    %r - Display minus when negative, no sign when positive
    %% - Percentage sign


    TEXT;

    $independence = new DateTime('7/4/1776');
    $now = new DateTime();
    $interval = $now->diff($independence);
    echo $interval->format('%Y years %m months %d days'), PHP_EOL;

    echo <<<TEXT

    Calculate recurring dates with DatePeriod(DateTime $startDate, DateInterval $recurringInterval, int $numberOfRecurrences, [DatePeriod::EXCLUDE_START_DATE])


    TEXT;

    $start = new DateTime('12/31/2018');
    $interval = DateInterval::createFromDateString('second Tuesday of next month');
    $period = new DatePeriod($start, $interval, 12, DatePeriod::EXCLUDE_START_DATE);

    foreach ($period as $date) {
        echo $date->format('F jS Y'), PHP_EOL;
    }
} catch (PDOException $e) {

    echo $e->getMessage(), PHP_EOL;
} catch (Throwable $t) {

    echo $t->getMessage(), PHP_EOL;
}
