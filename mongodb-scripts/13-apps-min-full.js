// agg_statuses + app_basics -> apps
recreate(db.agg_statuses, [
    {
        $group: {
            _id: '$id',
            territories: {
                $sum: 1
            },
            available: {
                $sum: {
                    $cond: {
                        if: { $eq: ['$last_available', true] },
                        then: 1,
                        else: 0
                    }
                }
            },
            first_unavailable_ts: {
                $min: '$first_unavailable_ts'
            },
            last_available_ts: {
                $max: '$last_available_ts'
            },
            last_unavailable_ts: {
                $max: '$last_unavailable_ts'
            },
            last_tested_territory: {
                $last: '$territory'
            }
        }
    }, {
        $project: {
            _id: '$_id',
            territories: '$territories',
            available: '$available',
            first_unavailable_ts: '$first_unavailable_ts',
            last_available_ts: '$last_available_ts',
            last_unavailable_ts: '$last_unavailable_ts',
            unavailable_somewhere: {
                $sum: {
                    $cond: [
                        {
                            $and: [
                                { $gt: ['$available', 0] },
                                { $lt: ['$available', '$territories'] },
                                { $gt: ['$last_available_ts', '$first_unavailable_ts'] },
                            ]
                        },
                        1,
                        0
                    ]
                }
            },
            last_tested_territory: '$last_tested_territory'
        }
    }, {
        $lookup: {
            from: 'app_basics',
            localField: '_id',
            foreignField: '_id',
            as: 'app'
        }
    }, {
        $unwind: {
            path: '$app'
        }
    }, {
        $project: {
            _id: '$_id',
            name: '$app.name',
            first_unavailable_ts: '$first_unavailable_ts',
            last_available_ts: '$last_available_ts',
            last_unavailable_ts: '$last_unavailable_ts',
            unavailable_somewhere: '$unavailable_somewhere',
            icon: '$app.icon',
            available: '$available',
            short_description: '$app.short_description',
            description: '$app.description',
            developer: '$app.developer',
            // ranking: '$app.ranking',
            territories: '$territories',
            first_ts: '$app.first_ts',
            last_ts: '$app.last_ts',
            star_rating: '$app.star_rating',
            category: '$app.category',
            categories: '$app.categories',
            last_tested_territory: '$last_tested_territory'
        }
    }
], {'unavailable_somewhere': 1}, 'apps');
