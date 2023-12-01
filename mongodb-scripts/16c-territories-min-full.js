// agg_statuses -> territories
recreate(db.agg_statuses, [
    {
        $lookup: {
            from: 'available_app_ids',
            localField: 'id',
            foreignField: '_id',
            as: 'available_app_id'
        }
    },
    {
        $match: {
            available_app_id: {
                $ne: []
            }
        }
    },
    {
        $group: {
            _id: '$territory',
            apps_count: {
                $sum: 1
            },
            apps_unavailable: {
                $sum: {
                    $cond: {
                        if: { $eq: ['$last_available', true] },
                        then: 0,
                        else: 1
                    }
                }
            }
        }
    },
    {
        $match: {
            _id: {
                $ne: null
            }
        }
    },
    {
        $lookup: {
            from: 'territory_deletions',
            localField: '_id',
            foreignField: '_id',
            as: 'territory_deletions'
        }
    },
    {
        $unwind: {
            path: '$territory_deletions',
            preserveNullAndEmptyArrays: true
        }
    },
    {
        $lookup: {
            from: 'territory_statuses',
            localField: '_id',
            foreignField: '_id',
            as: 'territory_statuses'
        }
    },
    {
        $unwind: {
            path: '$territory_statuses',
            preserveNullAndEmptyArrays: true
        }
    },
    {
        $project: {
            _id: '$_id',
            apps_count: '$apps_count',
            apps_unavailable: '$apps_unavailable',
            statuses: '$territory_statuses.count',
            deletions: '$territory_deletions.count'
        }
    }
], {_id: 1}, 'territories');
