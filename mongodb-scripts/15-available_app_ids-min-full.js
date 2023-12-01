// apps -> available_app_ids
recreate(db.apps, [
    {
        $match: {
            available: {
                $gt: 0
            }
        }
    }, {
        $project: {
            _id: '$_id'
        }
    }
], {_id: 1}, 'available_app_ids');
