// statuses -> agg_statuses

db.agg_statuses.ensureIndex({'_id.id': 1});

recreate(db.main, [
    {
        $group: {
            _id: {
                id: '$id',
                territory: '$territory'
            },
            // XXX
            // $available is a boolean, ASM does this though
            // I don't really understand what $first and $last means for
            // booleans
            //
            // this looks like it should be a date
            last_available: {
                $last: '$available'
            },
            min_available: {
                $min: '$available'
            },
            max_available: {
                $max: '$available'
            },
            first_ts: {
                $min: '$ts'
            },
            last_ts: {
                $max: '$ts'
            },
            first_unavailable_ts: {
                $min: {
                    $cond: {
                        if: { $eq: ['$available', false] },
                        then: '$ts',
                        else: null
                    }
                }
            },
            last_available_ts: {
                $max: {
                    $cond: {
                        if: { $eq: ['$available', true] },
                        then: '$ts',
                        else: 0
                    }
                }
            },
            last_unavailable_ts: {
                $max: {
                    $cond: {
                        if: { $eq: ['$available', false] },
                        then: '$ts',
                        else: 0
                    }
                }
            }
        }
    }, {
        $project: {
            id: '$_id.id',
            territory: '$_id.territory',
            last_available: '$last_available',
            min_available: '$min_available',
            max_available: '$max_available',
            first_ts: '$first_ts',
            last_ts: '$last_ts',
            first_unavailable_ts: '$first_unavailable_ts',
            last_available_ts: '$last_available_ts',
            last_unavailable_ts: '$last_unavailable_ts'
        }
    }
], [
    {territory: 1, last_available: -1},
    {'_id.territory': 1, last_available: -1}
], 'agg_statuses');
