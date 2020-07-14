<?php 
namespace SFW\Traits;
trait TimezoneHelper {
    public function generate_timezone_list($isforApi=false) {
        static $regions = array(
            \DateTimeZone::AFRICA,
            \DateTimeZone::AMERICA,
            \DateTimeZone::ANTARCTICA,
            \DateTimeZone::ASIA,
            \DateTimeZone::ATLANTIC,
            \DateTimeZone::AUSTRALIA,
            \DateTimeZone::EUROPE,
            \DateTimeZone::INDIAN,
            \DateTimeZone::PACIFIC,
        );

        $timezones = array();
        foreach( $regions as $region ) {
            $timezones = array_merge( $timezones, \DateTimeZone::listIdentifiers( $region ) );
        }

        $timezoneOffsets = array();
        foreach( $timezones as $timezone ) {
            $tz = new \DateTimeZone($timezone);
            $timezoneOffsets[$timezone] = $tz->getOffset(new \DateTime);
        }

        // sort timezone by offset
        asort($timezoneOffsets);

        $timezoneList = array();
        foreach( $timezoneOffsets as $timezone => $offset ) {
            $offsetPrefix = $offset < 0 ? '-' : '+';
            $offsetFormatted = gmdate( 'H:i', abs($offset) );

            $prettyOffset = "UTC${offsetPrefix}${offsetFormatted}";

            //$timezone_list[$timezone] = "(${pretty_offset}) $timezone";
            if($isforApi) {
                array_push($timezoneList, ['id'=>abs($offset),'title'=> $timezone."(".$prettyOffset.")"]);
            } else {
                array_push($timezoneList,  $timezone."(".$prettyOffset.")");
            }
            

        }
        if(!$isforApi)
        asort($timezoneList);
        return $timezoneList;
    }
}