<?php

    /**
     * convert
     * 
     * Converts the passed in timestamp, based on the system default timezone,
     * to the timezone specified as the second parameter.
     * 
     * The number of seconds since the unix-epoch are returned. This allows for
     * straight-forward use in the <date> function.
     * 
     * Just always be sure that you connect to the database, and that the
     * <date.timezone> property, are set to the same timezone. Then this convert
     * method should work wonderfully.
     * 
     * @access public
     * @param  String $timestamp
     * @param  String $to timezone that should be converted to
     * @return Integer
     */
    function convert($timestamp, $to)
    {
        // system
        $from = date_default_timezone_get();
        $zone = (new DateTimeZone($from));
        $time = (new DateTime($timestamp, $zone));
        $system = $zone->getOffset($time);

        // desired
        $zone = (new DateTimeZone($to));
        $time = (new DateTime($timestamp, $zone));
        $local = $zone->getOffset($time);

        // difference between timezones
        $offset = $local - $system;

        // seconds
        $seconds = strtotime($timestamp);
        $converted = $seconds + $offset;
        return $converted;
    }

    /**
     * plain
     * 
     * Converts the time passed in to plain english, minimalized.
     * Examples:
     * 
     *     Tomorrow, 5pm
     *     Tuesday, 5:50pm
     *     Yesterday, 11am
     *     Aug 24, 12pm
     * 
     * @note   the flow for this may seem wonky, but it's ordered in such a way
     *         to prevent unnecessary time calculations and executions.
     * @access public
     * @param  String $timestamp
     * @param  String $timezone
     * @return String
     */
    function plain($timestamp, $timezone)
    {
        // boolean to track whether time should be included
        $include = true;

        // convert
        $time = convert($timestamp, $timezone);

        /**
         * Day
         * 
         */

        // today
        $today = date('Y-m-d G:i:s', strtotime('today'));
        $today = convert($today, $timezone);

        // if it's either today, or in the future
        if ($time > $today) {

            // tomorrow
            $tomorrow = date('Y-m-d G:i:s', strtotime('tomorrow'));
            $tomorrow = convert($tomorrow, $timezone);

            // if it's today
            if (
                $time < $tomorrow
                && $time > $today
            ) {
                $date = 'Today';
            }
            // otherwise, in the future
            else {

                // if it's tomorrow
                if (
                    $time > $tomorrow
                    && $time < ($tomorrow + 24 * 60 * 60)
                ) {
                    $date = 'Tomorrow';
                }
                // otherwise, some date in the future
                else {
                    // if it's within the next 7 days
                    if ($time < ($today + (7 * 24 * 60 * 60))) {
                        $date = date('l', $time);
                    }
                    // otherwise, past 6-days in the future
                    else {
                        $date = date('M j', $time);
                    }
                }
            }
        }
        // otherwise, must be in the past
        else {

            // mark that time should *not* be included
            $include = false;

            // yesterday
            $yesterday = date('Y-m-d G:i:s', strtotime('yesterday'));
            $yesterday = convert($yesterday, $timezone);

            // if it was yesterday
            if (
                $time < $today
                && $time > $yesterday
            ) {
                $date = 'Yesterday';
            }
            // otherwise, past yesterday
            else {
                $date = date('M. jS', $time);
            }
        }

        /**
         * Time
         * 
         */

        // if time should be included (eg. not for past date/times)
        if ($include) {

            // minutes
            $minutes = date(':i', $time);
            if ($minutes === ':00') {
                $minutes = '';
            }
    
            // hour
            $hour = date('g', $time);
    
            // am/pm
            $append = date('a', $time);
    
            // put it all together
            $response = ($date) . ', ' . ($hour) . ($minutes) . ($append);
        }
        else {
            $response = $date;
        }

        // return the response
        return $response;
    }
