// statuses -> territory_statuses
recreate(db.statuses, [
    {
        $group: {
            _id: '$territory',
            count: {
                $sum: 1
            }
        }
    }
], {_id: 1}, 'territory_statuses');
