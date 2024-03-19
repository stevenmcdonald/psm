<?php

const MIN_DURATION = 60 * 60 * 6;
const MIN_CONFIRMATIONS = 2;

function getAllStatusesGrouped() {
    static $apps;
    if(!isset($apps)) {
        global $mongodb_manager;
        $mongodb_query = new MongoDB\Driver\Query([], ['sort' => ['ts' => 1]]);
        $rows = $mongodb_manager->executeQuery('psm.statuses', $mongodb_query);
        $apps = [];

        foreach($rows as $row) {
            if(!isset($apps[$row->id])) {
                $apps[$row->id] = ['t' => []];
            }
            if(!isset($apps[$row->id]['t'][$row->territory])) {
                $apps[$row->id]['t'][$row->territory] = [];
            }
            $apps[$row->id]['t'][$row->territory][$row->ts] = $row->available;
        }
    }
    return $apps;
}

function getChanges() {
    $apps = getAllStatusesGrouped();
    $changes = [];
    foreach($apps as $id => $app) {
        foreach($app['t'] as $territory => $statuses) {
            $change = null;
            $previous = new stdClass;
            foreach($statuses as $ts => $status) {
                if(isset($previous->status)) {
                    if($previous->status == $status) {
                        if($change) {
                            $change->confirmations++;
                            $change->duration = $ts - $change->ts;
                        }
                    } else {
                        if($change) {
                            $changes[] = $change;
                        }
                        $change = new stdClass;
                        $change->id = $id;
                        $change->territory = $territory;
                        $change->change = $status ? 1 : -1;
                        $change->ts = $ts;
                        $change->confirmations = 1;

                        // $change->app = new stdClass;
                        // $change->app->id = $app['_id'];
                        //$change->app->ranking = $app['ranking'];
                    }
                }
                $previous->status = $status;
                $previous->ts = $ts;
            }
            if($change) {
                $changes[] = $change;
            }
        }
    }
    return $changes;
}

function isAppAvailableSomewhereDuring($id, $ts_start, $ts_end) {
    $apps = getAllStatusesGrouped();
    foreach($apps[$id]['t'] as $statuses) {
        foreach($statuses as $ts => $status) {
            if($ts < $ts_start) {
                continue;
            }
            if($ts > $ts_end) {
                break;
            }
            if($status) {
                return true;
            }
        }
    }
    return false;
}

function isChangeSignificant($change) {

    if(!isset($change->duration)) {
        return false;
    }

    if($change->duration < MIN_DURATION) {
        return false;
    }

    if($change->confirmations < MIN_CONFIRMATIONS) {
        return false;
    }

    if($change->change == -1) {
        if(!isAppAvailableSomewhereDuring($change->id, $change->ts + MIN_DURATION, $change->ts + $change->duration)) {
            return false;
        }
    }

    return true;
}
