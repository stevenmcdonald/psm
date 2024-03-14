db.agg_statuses.ensureIndex({'_id.id': 1});

// db.agg_statuses > agg_statuses_extended
recreate(db.agg_statuses, [
    {
        $lookup: {
            from: 'apps',
            as: 'app',
            localField: '_id.id',
            foreignField: '_id'
        }
    }
    ]
, [
    {'territory': 1, last_available: -1, 'app.available': 1},
    {'_id.territory': 1, last_available: -1, 'app.available': 1},
    {'app._id': 1},
    {id: 1}
], 'agg_statuses_extended');
