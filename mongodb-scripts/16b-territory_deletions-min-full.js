// statuses -> territory_deletions
recreate(db.status_changes, [
    {
        $match: {
            change: -1
        }
    },
    {
        $group: {
            _id: '$territory',
            count: {
                $sum: 1
            }
        }
    }
], {_id: 1}, 'territory_deletions');
