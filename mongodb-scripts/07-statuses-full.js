// main -> statuses
recreate(db.main, [
    {
        $project: {
            _id: false,
            id: '$request.id',
            territory: '$request.gl',
            main_id: '$_id',
            ts: '$ts',
            available: '$response.available'
        }
    }, {
        $sort: {
            id: 1,
            territory: 1,
            ts: 1
        }
    }
], [
    {id: 1, territory: 1, ts: 1},
    {id: 1},
    {ts: 1}
], 'statuses');
